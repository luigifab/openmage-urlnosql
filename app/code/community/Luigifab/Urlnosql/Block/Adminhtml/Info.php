<?php
/**
 * Created L/03/08/2015
 * Updated S/21/07/2018
 *
 * Copyright 2015-2019 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * https://www.luigifab.fr/magento/urlnosql
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Luigifab_Urlnosql_Block_Adminhtml_Info extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface {

	public function getTabLabel() {
		return $this->__('Product URL rewrite');
	}

	public function getTabTitle() {
		return null;
	}

	public function isHidden() {
		return false;
	}

	public function canShowTab() {
		return (is_object(Mage::registry('current_product'))) ? true : false;
	}

	public function _toHtml($title = true, $product = null) {

		if (!is_object($product))
			$product = clone Mage::registry('current_product');

		$adminLang  = substr(Mage::getSingleton('core/locale')->getLocaleCode(), 0, 2);
		$storeId    = intval($this->getRequest()->getParam('store', Mage::app()->getDefaultStoreView()->getId()));
		$attributes = array_filter(preg_split('#\s+#', 'entity_id '.Mage::getStoreConfig('urlnosql/general/attributes')));
		$ignores    = array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/ignore')));
		$oldids     = Mage::getStoreConfig('urlnosql/general/oldids');

		$html = array();

		if ($title === true) {
			$html[] = '<div class="entry-edit">';
			$html[] = '<div class="entry-edit-head"><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getTabLabel().'</h4></div>';
			$html[] = '<fieldset><legend>'.$this->getTabLabel().'</legend>';
		}
		else {
			$html[] = '<div class="entry-edit-head"><strong>'.$title.'</strong></div>';
			$html[] = '<fieldset clas="config"><legend>'.$title.'</legend>';
		}

		// format de l'url
		// affiche la liste des attributs et ce produit remplace
		if (!empty($oldids) && !empty($product->getData($oldids))) {
			$html[] = '<p>'.$this->__('Format: <strong>www.example.org/%s%s</strong>', str_replace('_', '', implode('-', $attributes)), $this->helper('catalog/product')->getProductURLsuffix());
			$html[] = '<br />'.$this->__('This product replaces the following deleted products (via the <em>%s</em> attribute): %s.', $oldids, str_replace(',', ', ', $product->getData($oldids))).'</p>';
		}
		else {
			$html[] = '<p>'.$this->__('Format: <strong>www.example.org/%s%s</strong>', str_replace('_', '', implode('-', $attributes)), $this->helper('catalog/product')->getProductURLsuffix()).'</p>';
		}

		// détail de l'url
		// pour la vue magasin par défaut, ou pour la vue magasin sélectionnée
		$css = $this->getSkinUrl('images/error_msg_icon.gif');
		$css = 'style="margin-left:8px; padding-left:19px; background:url(\''.$css.'\') no-repeat left center;"';

		$html[] = '<p style="margin:1em 0 0;">'.$this->__('Address description for the store view #%d:', $storeId).'</p>';
		$html[] = '<ul style="margin:0 1em 1em; line-height:110%; list-style:inside;">';

		foreach ($attributes as $attribute) {

			$source = $product->getResource()->getAttribute($attribute);
			$model  = Mage::getResourceModel('catalog/product');

			// il faudrait peut être prendre en charge Mage::getStoreConfigFlag('catalog/frontend/flat_catalog_product')
			// https://stackoverflow.com/a/30519730
			if (is_object($source) && in_array($source->getData('frontend_input'), array('select', 'multiselect'))) {
				$value = $model->getAttributeRawValue($product->getId(), $attribute, $storeId);
				$value = $model->getAttribute($attribute)->setStoreId($storeId)->getSource()->getOptionText($value);
			}
			else if (is_object($source)) {
				$value = $model->getAttributeRawValue($product->getId(), $attribute, $storeId);
			}
			else {
				$value = $product->getData($attribute);
			}

			$value = $this->helper('urlnosql')->normalizeChars($value);

			if (($attribute != 'entity_id') && !is_object($source)) {
				$html[] = '<li>'.$this->__('%s <span %s>Warning! This attribute does not exist.</span>', $attribute, $css).'</li>';
			}
			else if (($attribute != 'entity_id') && empty($source->getData('used_in_product_listing'))) {

				$url = $this->getUrl('*/catalog_product_attribute/edit', array('attribute_id' => $source->getId()));
				$url = 'href="'.$url.'"';

				if (!empty($value))
					$html[] = '<li>'.$this->__('%s: %s <span %s>Warning! This attribute is not used in product listing (<a %s>edit attribute</a>).</span>', $attribute, $value, $css, $url).'</li>';
				else
					$html[] = '<li>'.$this->__('%s <span %s>Warning! This attribute is not used in product listing (<a %s>edit attribute</a>).</span>', $attribute, $css, $url).'</li>';
			}
			else if (!empty($value) && !in_array($value, $ignores)) {
				$html[] = '<li>'.$this->__('%s: %s', $attribute, $value).'</li>';
			}
		}

		$html[] = '</ul>';

		// génération des URLs
		// pour toutes les vues magasins activées
		$html[] = '<p style="margin:1em 0 0;">'.$this->__('List of addresses:').'</p>';
		$html[] = '<ul style="margin:0 1em 1em; list-style:inside;">';

		$stores = Mage::getResourceModel('core/store_collection')->addFieldToFilter('is_active', 1)->setOrder('store_id', 'asc');
		$size = $stores->getSize();

		foreach ($stores as $store) {

			$lang = substr(Mage::getStoreConfig('general/locale/code', $store->getId()), 0, 2);
			$url  = $product->setStoreId($store->getId())->getProductUrl();
			$mark = (($size > 1) && ($storeId == $store->getId()));

			if ($lang != $adminLang) {
				$html[] = '<li>'.
					($mark ? '<strong>' : '').
						$this->__('(%d) <span lang="%s">%s</span>:', $store->getId(), $lang, $store->getData('name')).
						' <a href="'.$url.'">'.$url.'</a>'.
					($mark ? '</strong>' : '').
				'</li>';
			}
			else {
				$html[] = '<li>'.
					($mark ? '<strong>' : '').
						$this->__('(%d) %s:', $store->getId(), $store->getData('name')).
						' <a href="'.$url.'">'.$url.'</a>'.
					($mark ? '</strong>' : '').
				'</li>';
			}
		}

		$html[] = '</ul>';
		$html[] = '</fieldset>';

		if ($title === true)
			$html[] = '</div>';

		return implode("\n", $html);
	}
}