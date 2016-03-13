<?php
/**
 * Created M/25/08/2015
 * Updated M/08/03/2016
 * Version 5
 *
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>, Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Urlnosql_Block_Adminhtml_Config_Example extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1') {

			$oldids = Mage::getStoreConfig('urlnosql/general/oldids');
			$product = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect(array_merge(Mage::getSingleton('catalog/config')->getProductAttributes(), array($oldids)))
				->addAttributeToSort('created_at', 'desc')
				->setPage(0, 1)
				->getFirstItem();

			if ($product->getId() > 0) {

				Mage::register('current_product', $product);

				$html  = '<div class="entry-edit-head collapseable"><strong>'.$element->getLegend().'</strong></div>'."\n";
				$html .= '<fieldset class="'.$this->_getFieldsetCss().'">'."\n";
				$html .=  '<legend>'.$element->getLegend().'</legend>'."\n";
				$html .=  implode("\n", Mage::getBlockSingleton('urlnosql/adminhtml_info')->getHtml())."\n";
				$html .= '</fieldset>';

				return $html;
			}
		}
	}
}