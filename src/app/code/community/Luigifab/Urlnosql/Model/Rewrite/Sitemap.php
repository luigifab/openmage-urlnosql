<?php
/**
 * Created L/29/06/2015
 * Updated M/06/12/2022
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

class Luigifab_Urlnosql_Model_Rewrite_Sitemap extends Mage_Sitemap_Model_Resource_Catalog_Product {

	protected $_storeId;

	public function getCollection($storeId) {
		$this->_storeId = $storeId;
		return parent::getCollection($storeId);
	}

	protected function _getEntityUrl($row, $entity) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$product = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect(array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/attributes'))))
				->addIdFilter($entity->getId())
				->addStoreFilter($this->_storeId)
				->setPageSize(1)
				->getFirstItem()
				->setStoreId($this->_storeId);

			$entity->setData('sku', $product->getData('sku'));

			$url = $product->getProductUrl();
			return mb_substr($url, mb_strrpos($url, '/') + 1);
		}

		return parent::_getEntityUrl($row, $entity);
	}
}