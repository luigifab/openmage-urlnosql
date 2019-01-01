<?php
/**
 * Created V/26/06/2015
 * Updated L/16/07/2018
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

class Luigifab_Urlnosql_Model_Rewrite_Indexer extends Mage_Catalog_Model_Indexer_Url {

	protected function _registerProductEvent(Mage_Index_Model_Event $event) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled'))
			return $this;
		else
			return parent::_registerProductEvent($event);
	}

	protected function _processEvent(Mage_Index_Model_Event $event) {

		$data = $event->getNewData();
		if (!empty($data['catalog_url_reindex_all']))
			$this->reindexAll();

		$urlModel = Mage::getSingleton('catalog/url');

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			if (!empty($data['rewrite_category_ids'])) {

				if (version_compare(Mage::getVersion(), '1.5', '>='))
					$urlModel->clearStoreInvalidRewrites(); // Maybe some categories were moved

				foreach ($data['rewrite_category_ids'] as $categoryId)
					$urlModel->refreshCategoryRewrite($categoryId);
			}
		}
		else {
			$dataObject = $event->getDataObject();
			if ($dataObject instanceof Varien_Object && $dataObject->hasData('save_rewrites_history'))
				$urlModel->setShouldSaveRewritesHistory($dataObject->getData('save_rewrites_history')); // Force rewrites history saving

			if (!empty($data['rewrite_product_ids'])) {

				if (version_compare(Mage::getVersion(), '1.5', '>='))
					$urlModel->clearStoreInvalidRewrites(); // Maybe some products were moved or removed from website

				foreach ($data['rewrite_product_ids'] as $productId)
					$urlModel->refreshProductRewrite($productId);
			}

			if (!empty($data['rewrite_category_ids'])) {

				if (version_compare(Mage::getVersion(), '1.5', '>='))
					$urlModel->clearStoreInvalidRewrites(); // Maybe some categories were moved

				foreach ($data['rewrite_category_ids'] as $categoryId)
					$urlModel->refreshCategoryRewrite($categoryId);
			}
		}
	}

	public function specialCheckRewrite() {
		return true;
	}
}