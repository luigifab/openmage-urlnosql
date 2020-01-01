<?php
/**
 * Created M/25/08/2015
 * Updated M/20/08/2019
 *
 * Copyright 2015-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Urlnosql_Block_Adminhtml_Config_Example extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$oldids  = Mage::getStoreConfig('urlnosql/general/oldids');
			$product = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect(array_merge(Mage::getSingleton('catalog/config')->getProductAttributes(), [$oldids]))
				->addAttributeToFilter('visibility', ['neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE])
				->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
				->addAttributeToSort('created_at', 'desc')
				->setPageSize(1)
				->getFirstItem();

			if (!empty($product->getId()))
				return Mage::getBlockSingleton('urlnosql/adminhtml_info')->_toHtml($element->getData('legend'), $product);
		}
	}
}