<?php
/**
 * Created L/03/08/2015
 * Updated S/16/12/2023
 *
 * Copyright 2015-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020-2023 | Fabrice Creuzot <fabrice~cellublue~com>
 * https://github.com/luigifab/openmage-urlnosql
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

class Luigifab_Urlnosql_Block_Adminhtml_Producturls extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface {

	public function getTabLabel() {
		return $this->__('URL rewrite');
	}

	public function getTabTitle() {
		return '';
	}

	public function isHidden() {
		return false;
	}

	public function canShowTab() {
		$product = Mage::registry('current_product');
		return is_object($product) && !empty($product->getId());
	}

	public function _toHtml() {

		$product = clone Mage::registry('current_product');
		$storeId = $this->getRequest()->getParam('store');
		$storeId = empty($storeId) ? Mage::app()->getDefaultStoreView()->getId() : Mage::app()->getStore($storeId)->getId();

		$productId  = $product->getId();
		$attributes = array_filter(preg_split('#\s+#', 'entity_id '.Mage::getStoreConfig('urlnosql/general/attributes')));
		$ignores    = array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/ignore')));
		$oldids     = Mage::getStoreConfig('urlnosql/general/oldids');

		$html = [];
		$html[] = '<div class="entry-edit">';
		$html[] = '<div class="entry-edit-head"><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getTabLabel().'</h4></div>';
		$html[] = '<fieldset><legend>'.$this->getTabLabel().'</legend>';

		if (!empty($oldids) && !empty($product->getData($oldids))) {
			$html[] = '<p>'.$this->__('Format: <strong>www.example.org/%s%s</strong>',
				str_replace('_', '', implode('-', $attributes)), $this->helper('catalog/product')->getProductUrlSuffix());
			$html[] = '<br />'.$this->__('This product replaces the following deleted products (via the <em>%s</em> attribute): %s.',
				$oldids, str_replace([' ', ',', ', ,', ',  ,', ',,'], ', ', $product->getData($oldids))).'</p>';
		}
		else {
			$html[] = '<p>'.$this->__('Format: <strong>www.example.org/%s%s</strong>',
				str_replace('_', '', implode('-', $attributes)), $this->helper('catalog/product')->getProductUrlSuffix()).'</p>';
		}

		$css = $this->getSkinUrl('images/error_msg_icon.gif');
		$css = 'style="margin-left:8px; padding-left:19px; background:url(\''.$css.'\') no-repeat left center;"';

		$html[] = '<p>'.$this->__('Address description for the store view #%d:', $storeId).'</p>';
		$html[] = '<ul style="margin:0 1em 1em; line-height:110%; list-style:inside;">';

		foreach ($attributes as $attribute) {

			$source = $product->getResource()->getAttribute($attribute);

			// @see https://stackoverflow.com/a/30519730
			if (is_object($source)) {
				$value = $product->getResource()->getAttributeRawValue($productId, $attribute, $storeId);
				if (in_array($source->getData('frontend_input'), ['select', 'multiselect']))
					$value = $product->getResource()->getAttribute($attribute)->setStoreId($storeId)->getSource()->getOptionText($value);
			}
			else {
				$value = $product->getData($attribute);
			}

			if (!empty($value))
				$value = $this->helper('urlnosql')->normalizeChars(Mage::getStoreConfig('general/locale/code', $storeId), $value);

			if (($attribute != 'entity_id') && !is_object($source)) {
				$html[] = '<li>'.$this->__('%s <span %s>Warning! This attribute does not exist.</span>', $attribute, $css).'</li>';
			}
			else if (($attribute != 'entity_id') && empty($source->getData('used_in_product_listing'))) {

				$url = $this->getUrl('*/catalog_product_attribute/edit', ['attribute_id' => $source->getId()]);
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
		$html[] = '<p>'.$this->__('List of addresses:').'</p>';
		$html[] = '<ul style="margin:0 1em 1em; list-style:inside;">';

		$current = substr(Mage::getSingleton('core/locale')->getLocaleCode(), 0, 2); // not mb_substr
		$stores  = Mage::getResourceModel('core/store_collection')->addFieldToFilter('is_active', 1)->setOrder('store_id', 'asc'); // without admin
		$enabled = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
		$single  = count($stores) == 1;
		$wsites  = $product->getWebsiteIds();

		foreach ($stores as $sid => $store) {

			if (!in_array($store->getWebsiteId(), $wsites))
				continue;

			$url    = $product->setStoreId($sid)->getProductUrl();
			$marker = !$single && ($storeId == $sid);
			$locale = substr(Mage::getStoreConfig('general/locale/code', $sid), 0, 2); // not mb_substr

			$disabled = $product->getResource()->getAttributeRawValue($productId, 'status', $sid) != $enabled;
			$html[] = '<li>'.
				($disabled ? '<em>' : '').
				($marker ? '<strong>' : '').
					(($locale != $current) ?
						$this->__('(%d) <span lang="%s">%s</span>:', $sid, $locale, $store->getData('name')) :
						$this->__('(%d) %s:', $sid, $store->getData('name'))
					).' <a href="'.$url.'">'.$url.'</a>'.
				($marker ? '</strong>' : '').
				' (<a href="'.$store->getUrl('catalog/product/view', ['id' => $product->getId()]).'">id</a>)'.
				($disabled ? ' '.$this->__('(product disabled)').'</em>': '').
			'</li>';
		}

		$html[] = '</ul>';
		$html[] = '</fieldset>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}