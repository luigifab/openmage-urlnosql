<?php
/**
 * Created L/29/06/2015
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

class Luigifab_Urlnosql_Model_Rewrite_Sitemap extends Mage_Sitemap_Model_Mysql4_Catalog_Product {

	private $storeId = 0;

	public function getCollection($storeId) {
		$this->storeId = $storeId;
		return parent::getCollection($storeId);
	}

	// Magento 1.4 à 1.7
	protected function _prepareProduct(array $productRow) {

		$product = parent::_prepareProduct($productRow);

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$data = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect(array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/attributes'))))
				->addAttributeToFilter('entity_id', $product->getId())
				->setPageSize(1)
				->getFirstItem()
				->setStoreId($this->storeId);

			$url = $data->getProductUrl();
			$url = substr($url, strrpos($url, '/') + 1);

			$product->setData('url', $url);
			$product->setData('sku', $data->getData('sku'));
		}

		return $product;
	}

	// Magento 1.8 et +
	protected function _loadEntities() {

		$entities = array();
		$query    = $this->_getWriteAdapter()->query($this->_select);

		while ($row = $query->fetch()) {

			$entity = $this->_prepareObject($row);

			if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

				$data = Mage::getResourceModel('catalog/product_collection')
					->addAttributeToSelect(array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/attributes'))))
					->addAttributeToFilter('entity_id', $entity->getId())
					->setPageSize(1)
					->getFirstItem()
					->setStoreId($this->storeId);

				$url = $data->getProductUrl();
				$url = substr($url, strrpos($url, '/') + 1);

				$entity->setData('url', $url);
				$entity->setData('sku', $data->getData('sku'));
			}

			$entities[$entity->getId()] = $entity;
		}

		return $entities;
	}

	public function specialCheckRewrite() {
		return true;
	}
}