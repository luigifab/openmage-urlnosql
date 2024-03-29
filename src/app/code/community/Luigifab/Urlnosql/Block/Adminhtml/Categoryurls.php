<?php
/**
 * Created M/16/05/2023
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

class Luigifab_Urlnosql_Block_Adminhtml_Categoryurls extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface {

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
		$category = Mage::registry('current_category');
		return is_object($category) && !empty($category->getId()) && ($category->getLevel() > 1);
	}

	public function _toHtml() {

		$category = Mage::registry('current_category');
		$storeId  = $this->getRequest()->getParam('store');
		$storeId  = empty($storeId) ? Mage::app()->getDefaultStoreView()->getId() : Mage::app()->getStore($storeId)->getId();

		$html = [];
		$html[] = '<div class="entry-edit">';
		$html[] = '<div class="entry-edit-head"><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getTabLabel().'</h4></div>';
		$html[] = '<fieldset><legend>'.$this->getTabLabel().'</legend>';
		$html[] = '<p>'.$this->__('List of addresses:').'</p>';
		$html[] = '<ul style="margin:0 1em 1em; list-style:inside;">';

		$current = substr(Mage::getSingleton('core/locale')->getLocaleCode(), 0, 2); // not mb_substr
		$stores  = Mage::getResourceModel('core/store_collection')->addFieldToFilter('is_active', 1)->setOrder('store_id', 'asc'); // without admin
		$single  = count($stores) == 1;

		foreach ($stores as $sid => $store) {

			if (!str_contains($category->getPath(), '/'.$store->getRootCategoryId().'/'))
				continue;

			Mage::app()->setCurrentStore($store);
			$categoryStore = Mage::getResourceModel('catalog/category_collection')
			    ->setStore($store)
			    ->addIdFilter($category->getId())
			    ->addAttributeToSelect('url_key')
			    ->addAttributeToSelect('is_active')
			    ->addUrlRewriteToResult()
			    ->getFirstItem();

			$url    = $categoryStore->getUrl();
			$marker = !$single && ($storeId == $sid);
			$locale = substr(Mage::getStoreConfig('general/locale/code', $sid), 0, 2); // not mb_substr

			$disabled = empty($categoryStore->getData('is_active'));
			$html[] = '<li>'.
				($disabled ? '<em>' : '').
				($marker ? '<strong>' : '').
					(($locale != $current) ?
						$this->__('(%d) <span lang="%s">%s</span>:', $sid, $locale, $store->getData('name')) :
						$this->__('(%d) %s:', $sid, $store->getData('name'))
					).' <a href="'.$url.'">'.$url.'</a>'.
				($marker ? '</strong>' : '').
				' (<a href="'.$store->getUrl('catalog/category/view', ['id' => $categoryStore->getId()]).'">id</a>)'.
				($disabled ? ' '.$this->__('(category disabled)').'</em>': '').
			'</li>';
		}

		Mage::app()->setCurrentStore(0);

		$html[] = '</ul>';
		$html[] = '</fieldset>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}