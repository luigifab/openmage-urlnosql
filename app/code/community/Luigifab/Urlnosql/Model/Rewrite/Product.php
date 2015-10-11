<?php
/**
 * Created V/26/06/2015
 * Updated S/12/09/2015
 * Version 11
 *
 * Copyright 2015 | Fabrice Creuzot <fabrice.creuzot~label-park~com>, Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Urlnosql_Model_Rewrite_Product extends Mage_Catalog_Model_Product_Url {

	public function getUrl(Mage_Catalog_Model_Product $product, $params = array()) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1') {

			$storeId    = ($product->getStoreId() > 0) ? $product->getStoreId() : Mage::app()->getStore()->getStoreId();
			$attributes = array_filter(explode(' ', trim('entity_id '.Mage::getStoreConfig('urlnosql/general/attributes'))));
			$ignores    = array_filter(explode(' ', trim(Mage::getStoreConfig('urlnosql/general/ignore'))));
			$data = array();

			foreach ($attributes as $attribute) {

				$source = $product->getResource()->getAttribute($attribute);

				// $product->getData($attribute) = '' si un attribut liste déroulante n'a pas de valeur (backend_type = int)
				// getAttributeRawValue uniquement si on demande la valeur pour une autre vue magasin
				if (is_object($source) && ($source->getBackendType() == 'varchar'))
					$value = ($storeId == Mage::app()->getStore()->getStoreId()) ? $product->getData($attribute) :
						Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $attribute, $storeId);
				else
					$value = ($product->getData($attribute) == '') ? '' : $source->setStoreId($storeId)->getFrontend()->getValue($product);

				$value = Mage::helper('urlnosql')->normalizeChars(strtolower($value));
				$value = preg_replace('#[^a-z0-9\-]#', '', $value);

				if ((strlen($value) > 0) && !in_array($value, $ignores))
					array_push($data, $value);
			}

			$data = implode('-', $data);
			return Mage::app()->getStore($storeId)->getBaseUrl().$data.Mage::helper('catalog/product')->getProductUrlSuffix($storeId);
		}
		else {
			return parent::getUrl($product, $params);
		}
	}
}