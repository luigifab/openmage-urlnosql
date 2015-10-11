<?php
/**
 * Created L/03/08/2015
 * Updated S/12/09/2015
 * Version 8
 *
 * Copyright 2015 | Fabrice Creuzot <fabrice.creuzot~label-park~com>, Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/urlnosql
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
		return $this->__('Product url rewrite');
	}

	public function getTabTitle() {
		return null;
	}

	public function canShowTab() {
		return (is_object(Mage::registry('current_product'))) ? true : false;
	}

	public function isHidden() {
		return false;
	}

	public function getHtml() {

		$product    = clone Mage::registry('current_product');
		$stores     = Mage::getResourceModel('core/store_collection')->addFieldToFilter('is_active', 1);
		$storeId    = intval($this->getRequest()->getParam('store', Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId()));
		$attributes = array_filter(explode(' ', trim('entity_id '.Mage::getStoreConfig('urlnosql/general/attributes'))));
		$ignores    = array_filter(explode(' ', trim(Mage::getStoreConfig('urlnosql/general/ignore'))));

		$html = array();

		// format de l'url
		// pour information (liste des attributs et liste des valeurs à ignorer)
		if (count($ignores) > 0)
			$html[] = '<p>'.$this->__('Format: <strong>www.example.org/%s%s</strong>', str_replace('_', '', implode('-', $attributes)), Mage::helper('catalog/product')->getProductUrlSuffix()).'<br />'.$this->__('Ignore values: %s.', implode(', ', $ignores)).'</p>';
		else
			$html[] = '<p>'.$this->__('Format: <strong>www.example.org/%s%s</strong>', str_replace('_', '', implode('-', $attributes)), Mage::helper('catalog/product')->getProductUrlSuffix()).'</p>';

		// détail de l'url
		// pour la vue magasin par défaut, ou pour la vue magasin sélectionnée
		$css = 'style="margin-left:8px; padding-left:19px; background:url(\''.$this->getSkinUrl('images/error_msg_icon.gif').'\') no-repeat left center;"';

		$html[] = '<p style="margin:1em 0 0;">'.$this->__('Address description for store view #%d:', $storeId).'</p>';
		$html[] = '<ul style="margin:0 1em 1em; line-height:110%; list-style:inside;">';

		foreach ($attributes as $attribute) {

			$source = $product->getResource()->getAttribute($attribute);

			// $product->getData($attribute) = '' si un attribut liste déroulante n'a pas de valeur (backend_type = int)
			// getAttributeRawValue quand on demande la valeur pour une autre vue magasin
			if (is_object($source) && ($source->getBackendType() == 'varchar'))
				$value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $attribute, $storeId);
			else
				$value = ($product->getData($attribute) == '') ? '' : $source->setStoreId($storeId)->getFrontend()->getValue($product);

			$value = Mage::helper('urlnosql')->normalizeChars(strtolower($value));
			$value = preg_replace('#[^a-z0-9\-]#', '', $value);

			if (($attribute !== 'entity_id') && !is_object($source)) {
				$html[] = '<li>'.$this->__('%s <span %s>Warning! This attribute does not exist.</span>', $attribute, $css).'</li>';
			}
			else if (($attribute !== 'entity_id') && ($source->getUsedInProductListing() !== '1')) {
				$url = 'href="'.$this->getUrl('*/catalog_product_attribute/edit', array('attribute_id' => $source->getId())).'" onclick="window.open(this.href); return false;"';
				if (strlen($value) > 0)
					$html[] = '<li>'.$this->__('%s: %s <span %s>Warning! This attribute is not used in product listing (<a %s>edit attribute</a>).</span>', $attribute, $value, $css, $url).'</li>';
				else
					$html[] = '<li>'.$this->__('%s <span %s>Warning! This attribute is not used in product listing (<a %s>edit attribute</a>).</span>', $attribute, $css, $url).'</li>';
			}
			else if ((strlen($value) > 0) && !in_array($value, $ignores)) {
				$html[] = '<li>'.$this->__('%s: %s', $attribute, $value).'</li>';
			}
		}

		$html[] = '</ul>';

		// génération des urls
		// pour toutes les vues magasins activées
		$html[] = '<p style="margin:1em 0 0;">'.$this->__('List of addresses:').'</p>';
		$html[] = '<ul style="margin:0 1em 1em; list-style:inside;">';

		$stores->getSelect()->order('store_id ASC');
		$current = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);

		foreach ($stores as $store) {
			$lang = substr(Mage::getStoreConfig('general/locale/code', $store->getStoreId()), 0, 2);
			if ($lang != $current)
				$html[] = '<li><span lang="'.$lang.'">'.$this->__('(%d) %s:', $store->getStoreId(), $store->getName()).'</span> <a href="'.$product->setStoreId($store->getStoreId())->getProductUrl().'" onclick="window.open(this.href); return false;">'.$product->getProductUrl().'</a></li>';
			else
				$html[] = '<li>'.$this->__('(%d) %s:', $store->getStoreId(), $store->getName()).' <a href="'.$product->setStoreId($store->getStoreId())->getProductUrl().'" onclick="window.open(this.href); return false;">'.$product->getProductUrl().'</a></li>';
		}

		$html[] = '</ul>';

		return $html;
	}

	public function _toHtml() {

		$html = array();
		$html[] = '<div class="entry-edit">';
		$html[] = '<div class="entry-edit-head">';
		$html[] = '<h4 class="icon-head head-edit-form fieldset-legend">'.$this->getTabLabel().'</h4>';
		$html[] = '</div>';
		$html[] = '<fieldset>';
		$html[] = '<legend>'.$this->getTabLabel().'</legend>';
		$html = array_merge($html, $this->getHtml());
		$html[] = '</fieldset>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}