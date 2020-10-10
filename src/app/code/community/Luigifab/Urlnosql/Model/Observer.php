<?php
/**
 * Created L/01/01/2018
 * Updated M/29/09/2019
 *
 * Copyright 2015-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
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

class Luigifab_Urlnosql_Model_Observer {

	// EVENT controller_front_init_before (global)
	public function redirectToRewrite(Varien_Event_Observer $observer) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled') && Mage::getStoreConfigFlag('urlnosql/general/redirect')) {

			$request = $observer->getData('front')->getRequest();
			if (empty($request->getPost()) && !Mage::app()->getStore()->isAdmin()) {

				$path = trim($request->getPathInfo(), '/').'/';
				if (mb_stripos($path, 'catalog/product/view') !== false) {
					$router = new Luigifab_Urlnosql_Controller_Router();
					$router->match($request, true);
				}
				else {
					$rewrite = Mage::getResourceModel('core/url_rewrite_collection')
						->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
						->addFieldToFilter('target_path', $path)
						->setPageSize(1)
						->getFirstItem();

					if (!empty($rewrite->getData('request_path'))) {
						header('Location: '.Mage::getBaseUrl().$rewrite->getData('request_path'), true, 301);
						exit(0); // stop redirection 301
					}
				}
			}
		}
	}

	// EVENT catalog_controller_product_init_before (frontend)
	public function updateCategoryId(Varien_Event_Observer $observer) {

		$request = $observer->getData('controller_action')->getRequest();
		$params  = $observer->getData('params');
		$ids     = $request->getParam('category_ids');

		if (!empty($ids) && is_array($ids)) {

			$lastCategoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
			$categories = Mage::getResourceModel('catalog/category_collection')
				->setStore(Mage::app()->getStore())
				->addAttributeToFilter('path', ['like' => '%/'.Mage::app()->getStore()->getRootCategoryId().'/%'])
				->addAttributeToFilter('entity_id', ['in' => $ids])
				->addAttributeToFilter('is_active', 1)
				->addAttributeToSort('level', 'desc')
				->addAttributeToSort('entity_id', 'desc');

			if (!empty($lastCategoryId) && in_array($lastCategoryId, $categories->getAllIds()))
				$id = $lastCategoryId;
			else if (!empty($cid = $categories->getFirstItem()))
				$id = $cid->getId();

			if (!empty($id)) {
				Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($id);
				$request->setParam('category', $id);
				$params->setData('category_id', $id);
				Mage::register('current_entity_key', $categories->getItemById($id)->getPath());
			}
		}
	}
}