<?php
/**
 * Created L/01/01/2018
 * Updated V/24/06/2022
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

class Luigifab_Urlnosql_Model_Observer {

	// EVENT controller_front_init_before (global)
	public function redirectToRewrite(Varien_Event_Observer $observer) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled')) {

			$request = $observer->getData('front')->getRequest();
			$suffix  = Mage::helper('catalog/product')->getProductUrlSuffix();
			$path    = trim($request->getPathInfo(), '/');

			if (empty($_POST) && (mb_stripos($path, $suffix) !== false) && (preg_match('#^(\d+)[\w%\-]*'.$suffix.'$#i', $path) !== 1)) {

				// si ancienne url dans request_path ou target_path
				// cherche l'id produit, en plusieurs fois si nécessaire
				$debug = ['OBSERVER', 'Searching '.$path.' in core_url_rewrite'];
				$where = ['request_path', 'target_path', 'request_path', 'target_path'];
				$paths = [['like' => $path], ['like' => $path], ['like' => '/'.$path], ['like' => '/'.$path]];
				$skips = [];
				$idx   = 0;

				while (!empty($where) && ($idx < 10)) {

					$debug[]  = '-- loading from database --';
					$rewrites = Mage::getResourceModel('core/url_rewrite_collection')
						->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
						->addFieldToFilter($where, $paths);

					if (!empty($skips))
						$rewrites->addFieldToFilter('url_rewrite_id', ['nin' => $skips]);

					$where = [];
					$paths = [];

					foreach ($rewrites as $rewrite) {

						$id = $rewrite->getData('id_path');
						$debug[] = 'Checking "'.$id.'" ('.trim($rewrite->getData('request_path'), '/').' - '.trim($rewrite->getData('target_path'), '/').')';

						if (strncasecmp($id, 'product/', 8) === 0) {
							$id = explode('/', $id);
							$debug[] = 'Product #'.$id[1].' found!';
							$router = new Luigifab_Urlnosql_Controller_Router();
							$router->match($request->setPathInfo('catalog/product/view/id/'.$id[1]), true, $debug);
							return; // inutile de continuer
						}
						if (strncasecmp($id, 'category/', 9) === 0) {
							$debug[] = 'Category found!';
							Luigifab_Urlnosql_Controller_Router::saveDebug($debug);
							return; // inutile de continuer
						}

						// si c'est une boucle de redirection
						$skips[] = $rewrite->getId();

						$where[] = 'request_path';
						$where[] = 'target_path';
						$where[] = 'request_path';
						$where[] = 'target_path';

						$paths[] = ['like' => ($path = trim($rewrite->getData('request_path'), '/'))];
						$paths[] = ['like' => $path];
						$paths[] = ['like' => '/'.$path];
						$paths[] = ['like' => '/'.$path];

						$where[] = 'request_path';
						$where[] = 'target_path';
						$where[] = 'request_path';
						$where[] = 'target_path';

						$paths[] = ['like' => ($path = trim($rewrite->getData('target_path'), '/'))];
						$paths[] = ['like' => $path];
						$paths[] = ['like' => '/'.$path];
						$paths[] = ['like' => '/'.$path];
					}

					$idx++;
				}

				$debug[] = 'No products found!';
				Luigifab_Urlnosql_Controller_Router::saveDebug($debug);
			}
		}
	}

	// EVENT controller_action_predispatch_catalog_product_view (frontend)
	public function checkProductUrl(Varien_Event_Observer $observer) {

		if (empty(Mage::registry('urlnosql')) && Mage::getStoreConfigFlag('urlnosql/general/redirect')) {
			$router = new Luigifab_Urlnosql_Controller_Router();
			$router->match($observer->getData('controller_action')->getRequest(), true);
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