<?php
/**
 * Created V/26/06/2015
 * Updated D/24/01/2021
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

class Luigifab_Urlnosql_Model_Rewrite_Producturl extends Mage_Catalog_Model_Product_Url {

	public function getUrl(Mage_Catalog_Model_Product $product, $params = []) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$storeId    = empty($product->getStoreId()) ? Mage::app()->getStore()->getId() : $product->getStoreId();
			$attributes = array_filter(preg_split('#\s+#', 'entity_id '.Mage::getStoreConfig('urlnosql/general/attributes')));
			$ignores    = array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/ignore')));
			$values     = [];

			foreach ($attributes as $attribute) {

				$source = $product->getResource()->getAttribute($attribute);

				// https://stackoverflow.com/a/30519730
				if (is_object($source)) {
					$value = $product->getResource()->getAttributeRawValue($product->getId(), $attribute, $storeId);
					if (in_array($source->getData('frontend_input'), ['select', 'multiselect']))
						$value = $product->getResource()->getAttribute($attribute)->setStoreId($storeId)->getSource()->getOptionText($value);
				}
				else {
					$value = $product->getData($attribute);
				}

				if (!empty($value))
					$value = Mage::helper('urlnosql')->normalizeChars(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId), $value);
				if (!empty($value) && !in_array($value, $ignores))
					$values[] = $value;
			}

			return Mage::app()->getStore($storeId)->getBaseUrl().
				preg_replace('#-{2,}#', '-', implode('-', $values)). // est vide si le produit n'existe pas
				Mage::helper('catalog/product')->getProductUrlSuffix($storeId);
		}

		return parent::getUrl($product, $params);
	}
}