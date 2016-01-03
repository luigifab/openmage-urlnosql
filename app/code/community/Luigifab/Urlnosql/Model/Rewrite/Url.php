<?php
/**
 * Created V/26/06/2015
 * Updated S/22/08/2015
 * Version 4
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

class Luigifab_Urlnosql_Model_Rewrite_Url extends Mage_Catalog_Model_Url {

	public function refreshRewrites($storeId = null) {

		if (is_null($storeId)) {
			$stores = $this->getStores();
			foreach ($stores as $store)
				$this->refreshRewrites($store->getId());
			return $this;
		}

		if (version_compare(Mage::getVersion(), '1.5', '>='))
			$this->clearStoreInvalidRewrites($storeId);

		$this->refreshCategoryRewrite($this->getStores($storeId)->getRootCategoryId(), $storeId, false);

		if (Mage::getStoreConfig('urlnosql/general/enabled') !== '1')
			$this->refreshProductRewrites($storeId);

		$this->getResource()->clearCategoryProduct($storeId);

		return $this;
	}

	public function refreshCategoryRewrite($categoryId, $storeId = null, $refreshProducts = true) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1')
			$refreshProducts = false;

		return parent::refreshCategoryRewrite($categoryId, $storeId, $refreshProducts);
	}

	public function refreshProductRewrites($storeId) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1')
			return $this;
		else
			return parent::refreshProductRewrites($storeId);
	}

	public function getShouldSaveRewritesHistory($storeId = null) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1')
			return false;
		else
			return parent::getShouldSaveRewritesHistory($storeId);
	}

	protected function _saveRewriteHistory($rewriteData, $rewrite) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1')
			return $this;
		else
			return parent::_saveRewriteHistory($rewriteData, $rewrite);
	}
}