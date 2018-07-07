<?php
/**
 * Created V/26/06/2015
 * Updated L/21/05/2018
 *
 * Copyright 2015-2018 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Urlnosql_Model_Rewrite_Product extends Mage_Catalog_Model_Product_Url {

	public function getUrl(Mage_Catalog_Model_Product $product, $params = array()) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$storeId    = (!empty($product->getStoreId())) ? $product->getStoreId() : Mage::app()->getStore()->getId();
			$attributes = array_filter(preg_split('#\s#', trim('entity_id '.Mage::getStoreConfig('urlnosql/general/attributes'))));
			$ignores    = array_filter(preg_split('#\s#', Mage::getStoreConfig('urlnosql/general/ignore')));

			$data = array();

			foreach ($attributes as $attribute) {

				$source = $product->getResource()->getAttribute($attribute);
				$model  = Mage::getResourceModel('catalog/product');

				// il faudrait peut être prendre en charge Mage::getStoreConfigFlag('catalog/frontend/flat_catalog_product')
				// https://stackoverflow.com/a/30519730
				if (is_object($source) && in_array($source->getData('frontend_input'), array('select', 'multiselect'))) {
					$value = $model->getAttributeRawValue($product->getId(), $attribute, $storeId);
					$value = $model->getAttribute($attribute)->setStoreId($storeId)->getSource()->getOptionText($value);
				}
				else if (is_object($source)) {
					$value = $model->getAttributeRawValue($product->getId(), $attribute, $storeId);
				}
				else {
					$value = $product->getData($attribute);
				}

				$value = Mage::helper('urlnosql')->normalizeChars($value);

				if (!empty($value) && !in_array($value, $ignores))
					array_push($data, $value);
			}

			return Mage::app()->getStore($storeId)->getBaseUrl().
				preg_replace('#\-{2,}#', '-', implode('-', $data)).             // est vide si le produit n'existe pas
				Mage::helper('catalog/product')->getProductUrlSuffix($storeId);
		}
		else {
			return parent::getUrl($product, $params);
		}
	}
}