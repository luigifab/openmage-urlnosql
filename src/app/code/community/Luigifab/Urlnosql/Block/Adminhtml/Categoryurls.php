<?php
/**
 * Created M/16/05/2023
 * Updated J/21/09/2023
 *
 * Copyright 2015-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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
		return $this->__('Category URL rewrite');
	}

	public function getTabTitle() {
		return '';
	}

	public function isHidden() {
		return false;
	}

	public function canShowTab() {
		return is_object(Mage::registry('current_category'));
	}

	public function _toHtml($title = true, $category = null) {

		if (!is_object($category))
			$category = clone Mage::registry('current_category');

		$storeId = $this->getRequest()->getParam('store');
		$storeId = empty($storeId) ? Mage::app()->getDefaultStoreView()->getId() : Mage::app()->getStore($storeId)->getId();

		$html = [];

		if ($title === true) {
			$html[] = '<div class="entry-edit">';
			$html[] = '<div class="entry-edit-head"><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getTabLabel().'</h4></div>';
			$html[] = '<fieldset><legend>'.$this->getTabLabel().'</legend>';
		}
		else {
			$html[] = '<div class="section-config">';
			$html[] = '<div class="entry-edit-head collapseable"><strong>'.$title.'</strong></div>';
			$html[] = '<fieldset class="config"><legend>'.$title.'</legend>';
		}

		// génération des URLs
		// pour toutes les vues magasins activées
		$html[] = '<p>'.$this->__('List of addresses:').'</p>';
		$html[] = '<ul style="margin:0 1em 1em; list-style:inside;">';

		$current = substr(Mage::getSingleton('core/locale')->getLocaleCode(), 0, 2); // not mb_substr
		$stores  = Mage::getResourceModel('core/store_collection')->addFieldToFilter('is_active', 1)->setOrder('store_id', 'asc'); // without admin
		$single  = $stores->getSize() == 1;

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

			if (empty($categoryStore->getData('is_active'))) {
				$html[] = '<li><em>'.
					($marker ? '<strong>' : '').
						$this->__('(%d) <span lang="%s">%s</span>:', $sid, $locale, $store->getData('name')).
						' '.$this->__('(category disabled)').
					($marker ? '</strong>' : '').
				'</em></li>';
			}
			else if ($locale != $current) {
				$html[] = '<li>'.
					($marker ? '<strong>' : '').
						$this->__('(%d) <span lang="%s">%s</span>:', $sid, $locale, $store->getData('name')).
						' <a href="'.$url.'">'.$url.'</a>'.
					($marker ? '</strong>' : '').
				'</li>';
			}
			else {
				$html[] = '<li>'.
					($marker ? '<strong>' : '').
						$this->__('(%d) %s:', $sid, $store->getData('name')).
						' <a href="'.$url.'">'.$url.'</a>'.
					($marker ? '</strong>' : '').
				'</li>';
			}
		}

		Mage::app()->setCurrentStore(0);

		$html[] = '</ul>';
		$html[] = '</fieldset>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}