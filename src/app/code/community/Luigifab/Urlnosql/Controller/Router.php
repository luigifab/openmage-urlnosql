<?php
/**
 * Created V/26/06/2015
 * Updated V/09/10/2020
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

class Luigifab_Urlnosql_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract {

	public function initControllerRouters($observer) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled'))
			$observer->getData('front')->addRouter('urlnosql', $this);
	}

	public function match(Zend_Controller_Request_Http $request, bool $fromUs = false) {

		// recherche
		// au début $params est un array
		$params = trim($request->getPathInfo(), '/');
		$params = (array) explode('/', $params); // (yes)

		if (count($params) === 1) {
			// Array ( [0] => 300003-abc.html )
			$params = $params[0];
			// recherche de l'id dans l'url (insensible à la casse)
			// l'id étant l'id du produit
			preg_match('#^(\d+)[\w%\-]*'.Mage::helper('catalog/product')->getProductUrlSuffix().'$#i', $params, $id);
			if (!empty($id[1]) && is_numeric($id[1])) {
				// Array ( [0] => 300003-abc.html [1] => 300003 )
				$id = (int) $id[1];
			}
		}
		else if ($fromUs === true) {
			// Array ( [0] => catalog [1] => product [2] => view [3] => id [4] => 7 )
			$id = array_search('id', $params);
			$id = empty($params[$id + 1]) ? false : $params[$id + 1];
			$params  = '///';
		}
		else {
			$params  = '///';
		}

		// vérifie les réécritures d'urls
		if (empty($id)) {
			$rewrite = Mage::getResourceModel('core/url_rewrite_collection')
				->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
				->addFieldToFilter('request_path', mb_substr($request->getPathInfo(), 1))
				->addFieldToFilter('product_id', ['gt' => 0])
				->setPageSize(1)
				->getFirstItem();
			$id = $rewrite->getData('product_id');
		}

		// action
		// dorénavant $params est un string
		if (!empty($id) && is_numeric($id)) {

			$storeId = Mage::app()->getStore()->getId();
			$candidates = [];

			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($id);
			$product = empty($product->getId()) ? null : $product;

			// LE PRODUIT EXISTE
			if (is_object($product)) {
				// produit désactivé
				// c'est la fin des haricots la tout de suite maintenant
				if ($product->getData('status') != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
					return false;
				}
				// produit non visible
				// cherche les éventuels ids parents
				if ($product->getData('visibility') == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
					$candidates = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;
					$candidates = array_merge(
						Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id),
						Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($id, $candidates));
				}
				// produit visible
				// conserve le produit
				else {
					$candidates = [$product];
				}
			}
			// LE PRODUIT N'EXISTE PAS
			// cherche les éventuels ids de remplacement
			else if (!empty($oldids = Mage::getStoreConfig('urlnosql/general/oldids'))) {
				// https://mariadb.com/kb/en/mariadb/regular-expressions-overview/#word-boundaries
				// https://dev.mysql.com/doc/refman/8.0/en/regexp.html
				$candidates = Mage::getResourceModel('catalog/product_collection')
					->addAttributeToFilter($oldids, ['regexp' => '[[:<:]]'.$id.'[[:>:]]'])
					->addStoreFilter()
					->getAllIds();
			}

			// 4 8 15 16 23 42... SI NOUS AVONS DES CANDIDATS
			// soit dans le ou les ids produits parents (dans le cas d'un produit non visible)
			// soit dans le produit chargé initialement
			// soit dans le ou les ids produits de remplacement
			while (!empty($candidates)) {

				$product = array_shift($candidates); // un id ou un objet produit (du premier au dernier)
				$product = is_object($product) ? $product : Mage::getModel('catalog/product')->setStoreId($storeId)->load($product);

				// le produit existe (le contraire est possible via l'attribut oldids)
				// le produit est activé (le contraire est possible via l'attribut oldids ou via les produits associés)
				// le produit est visible (le contraire est possible via l'attribut oldids ou via les produits associés)
				if (($product->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) &&
				    ($product->getData('visibility') != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)) {

					if (mb_stripos($product->getProductUrl(), '/'.$params) !== false) {
						$request->setModuleName('catalog')->setControllerName('product')->setActionName('view');
						$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $params);
						$request->setParam('category_ids', $product->getCategoryIds());
						$request->setParam('id', $id);
						return true;
					}

					header('Location: '.$product->getProductUrl(), true, 301);
					exit(0); // stop redirection 301
				}
			}
		}

		return false;
	}
}