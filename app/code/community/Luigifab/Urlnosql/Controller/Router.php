<?php
/**
 * Created V/26/06/2015
 * Updated M/22/09/2015
 * Version 3
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

class Luigifab_Urlnosql_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract {

	public function initControllerRouters($observer) {

		if (Mage::getStoreConfig('urlnosql/general/enabled') === '1') {
			$router = new Luigifab_Urlnosql_Controller_Router();
			$this->match(Mage::app()->getRequest());
		}
	}

	public function match(Zend_Controller_Request_Http $request) {

		$params = trim($request->getPathInfo(), '/');
		$params = explode('/', $params);

		if (count($params) === 1) {

			preg_match('#^([0-9]+)[a-z0-9\-]*'.Mage::helper('catalog/product')->getProductUrlSuffix().'$#', $params[0], $result);

			if (isset($result[1]) && is_numeric($result[1])) {

				if (Mage::getStoreConfig('urlnosql/general/check') === '1') {

					$product = Mage::getModel('catalog/product')->load(intval($result[0]));
					//Mage::register('current_product', $product); NO!

					if ($product->getData('visibility') == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {

						$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

						if (isset($parentIds[0]) && is_numeric($parentIds[0])) {
							$product->load($parentIds[0]);
							header('Location: '.$product->getProductUrl(), true, 301);
							exit;
						}
					}
					else if (strpos($product->getProductUrl(), $params[0]) !== false) {
						$request->setModuleName('catalog')->setControllerName('product')->setActionName('view')->setParam('id', $product->getId());
						$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $params[0]);
						return true;
					}
					else if ($product->getId() > 0) {
						header('Location: '.$product->getProductUrl(), true, 301);
						exit;
					}
				}
				else {
					$request->setModuleName('catalog')->setControllerName('product')->setActionName('view')->setParam('id', intval($result[0]));
					$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $params[0]);
					return true;
				}
			}
		}

		return false;
	}
}