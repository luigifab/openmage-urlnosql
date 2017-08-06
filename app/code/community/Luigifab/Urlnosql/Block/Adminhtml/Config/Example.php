<?php
/**
 * Created M/25/08/2015
 * Updated M/28/02/2017
 *
 * Copyright 2015-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * https://www.luigifab.info/magento/urlnosql
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

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$oldids = Mage::getStoreConfig('urlnosql/general/oldids');

			$products = Mage::getResourceModel('catalog/product_collection');
			$products->addAttributeToSelect(array_merge(Mage::getSingleton('catalog/config')->getProductAttributes(), array($oldids)));
			$products->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
			$products->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
			$products->addAttributeToSort('created_at', 'desc');
			$products->setPageSize(1);

			if (!empty($products->getFirstItem()->getId())) {

				Mage::register('current_product', $products->getFirstItem());

				$html  = '<div class="entry-edit-head collapseable"><strong>'.$element->getData('legend').'</strong></div>'."\n";
				$html .= '<fieldset class="'.$this->_getFieldsetCss().'">'."\n";
				$html .=  '<legend>'.$element->getData('legend').'</legend>'."\n";
				$html .=  implode("\n", Mage::getBlockSingleton('urlnosql/adminhtml_info')->getHtml())."\n";
				$html .= '</fieldset>';

				return $html;
			}
		}
	}
}