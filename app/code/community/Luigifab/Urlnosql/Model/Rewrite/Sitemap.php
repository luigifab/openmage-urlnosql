<?php
/**
 * Created L/29/06/2015
 * Updated D/06/09/2015
 * Version 6
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

class Luigifab_Urlnosql_Model_Rewrite_Sitemap extends Mage_Sitemap_Model_Mysql4_Catalog_Product {

	private $storeId = 0;

	public function getCollection($storeId) {
		$this->storeId = $storeId;
		return parent::getCollection($storeId);
	}

	// Magento 1.8 et +
	protected function _loadEntities() {

		$entities = array();
		$query = $this->_getWriteAdapter()->query($this->_select);

		while ($row = $query->fetch()) {

			$entity = $this->_prepareObject($row);

			if (Mage::getStoreConfig('urlnosql/general/enabled') === '1') {

				$product = Mage::getResourceModel('catalog/product_collection');
				$product->addAttributeToSelect(explode(' ', trim(Mage::getStoreConfig('urlnosql/general/attributes'))));
				$product->addAttributeToFilter('entity_id', $entity->getId());
				$product = $product->getFirstItem()->setStoreId($this->storeId);

				$url = $product->getProductUrl();
				$url = substr($url, strrpos($url, '/') + 1);

				$entity->setUrl($url);
				$entity->setSku($product->getSku());
			}

			$entities[$entity->getId()] = $entity;
		}

		return $entities;
	}

	// Magento 1.4 à 1.7
	protected function _prepareProduct(array $productRow) {

		$product = parent::_prepareProduct($productRow);

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1') {

			$data = Mage::getResourceModel('catalog/product_collection');
			$data->addAttributeToSelect(explode(' ', trim(Mage::getStoreConfig('urlnosql/general/attributes'))));
			$data->addAttributeToFilter('entity_id', $product->getId());
			$data = $data->getFirstItem()->setStoreId($this->storeId);

			$url = $data->getProductUrl();
			$url = substr($url, strrpos($url, '/') + 1);

			$product->setUrl($url);
			$product->setSku($data->getSku());
		}

		return $product;
	}
}