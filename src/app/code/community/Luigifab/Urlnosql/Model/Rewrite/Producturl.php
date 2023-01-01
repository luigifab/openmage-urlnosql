<?php
/**
 * Created V/26/06/2015
 * Updated L/03/10/2022
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

class Luigifab_Urlnosql_Model_Rewrite_Producturl extends Mage_Catalog_Model_Product_Url {

	protected static $_cache = [];

	public function __construct() {

		if (empty(self::$_cache) && Mage::app()->useCache('block_html')) {
			self::$_cache = @json_decode(Mage::app()->loadCache('urlnosql_urls'), true);
			register_shutdown_function([$this, 'destruct']);
			if (!is_array(self::$_cache))
				self::$_cache = [];
		}

		if (!isset(self::$_cache['enabled']))
			self::$_cache['enabled'] = Mage::getStoreConfigFlag('urlnosql/general/enabled');
	}

	public function destruct() {

		// une seule fois via register_shutdown_function
		if (!empty(self::$_cache) && Mage::app()->useCache('block_html'))
			Mage::app()->saveCache(json_encode(self::$_cache), 'urlnosql_urls',
				[Mage_Core_Model_Config::CACHE_TAG, Mage_Core_Block_Abstract::CACHE_GROUP]);
	}

	public function getUrl(Mage_Catalog_Model_Product $product, $params = []) {

		if (self::$_cache['enabled']) {

			$storeId   = empty($product->getStoreId()) ? Mage::app()->getStore()->getId() : $product->getStoreId();
			$productId = $product->getId();

			if (!empty(self::$_cache[$storeId][$productId]))
				return self::$_cache[$storeId][$productId];

			$attributes = array_filter(preg_split('#\s+#', 'entity_id '.Mage::getStoreConfig('urlnosql/general/attributes')));
			$ignores    = array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/ignore')));
			$values     = [];

			foreach ($attributes as $attribute) {

				$source = $product->getResource()->getAttribute($attribute);

				// https://stackoverflow.com/a/30519730
				if (is_object($source)) {
					$value = $product->getResource()->getAttributeRawValue($productId, $attribute, $storeId);
					if (in_array($source->getData('frontend_input'), ['select', 'multiselect']))
						$value = $product->getResource()->getAttribute($attribute)->setStoreId($storeId)->getSource()->getOptionText($value);
				}
				else {
					$value = $product->getData($attribute);
				}

				if (!empty($value))
					$value = Mage::helper('urlnosql')->normalizeChars(Mage::getStoreConfig('general/locale/code', $storeId), $value);
				if (!empty($value) && !in_array($value, $ignores))
					$values[] = $value;
			}

			self::$_cache[$storeId][$productId] = Mage::app()->getStore($storeId)->getBaseUrl().
				preg_replace('#-{2,}#', '-', implode('-', $values)). // est vide si le produit n'existe pas
				Mage::helper('catalog/product')->getProductUrlSuffix($storeId);

			return self::$_cache[$storeId][$productId];
		}

		return parent::getUrl($product, $params);
	}
}