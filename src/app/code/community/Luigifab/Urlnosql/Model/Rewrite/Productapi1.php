<?php
/**
 * Created L/25/01/2021
 * Updated V/12/02/2021
 *
 * Copyright 2015-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020-2021 | Fabrice Creuzot <fabrice~cellublue~com>
 * https://www.luigifab.fr/openmage/urlnosql
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

class Luigifab_Urlnosql_Model_Rewrite_Productapi1 extends Mage_Catalog_Model_Product_Api {

	private $_cache = [];

	protected function _getProduct($productId, $store = null, $identifierType = null) {

		$storeId  = $this->_getStoreId($store);
		$cacheKey = $productId.$storeId.$identifierType;
		if (empty($this->_cache[$cacheKey]))
			$this->_cache[$cacheKey] = parent::_getProduct($productId, $storeId, $identifierType);

		return $this->_cache[$cacheKey];
	}

	protected function _getStoreId($store = null) {
		return is_numeric($store) ? $store : parent::_getStoreId($store);
	}

	public function info($productId, $store = null, $attributes = null, $identifierType = null) {

		$result = parent::info($productId, $store, $attributes, $identifierType);

		if (is_array($result) && Mage::getStoreConfigFlag('urlnosql/general/enabled')) {
			$result['product_url'] = $this->_getProduct($productId, $store, $identifierType)->getProductUrl();
			$result['url_path']    = mb_substr($result['product_url'], mb_strrpos($result['product_url'], '/') + 1);
		}

		return $result;
	}
}