<?php
/**
 * Created V/26/06/2015
 * Updated M/27/02/2018
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

class Luigifab_Urlnosql_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract {

	public function initControllerRouters($observer) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled'))
			$observer->getData('front')->addRouter('urlnosql', $this);
	}

	public function match(Zend_Controller_Request_Http $request) {

		$params = trim($request->getPathInfo(), '/');
		$params = explode('/', $params);

		if (count($params) === 1) {

			// Array ( [0] => 300003-abc.html )
			$params = $params[0];

			// recherche de l'id dans l'url (insensible à la casse)
			// l'id étant l'id du produit dans Magento :)
			preg_match('#^([0-9]+)[a-z0-9\-]*'.Mage::helper('catalog/product')->getProductUrlSuffix().'$#i', $params, $id);

			if (!empty($id[1]) && is_numeric($id[1])) {
				// Array ( [0] => 300003-abc.html [1] => 300003 )
				$id = intval($id[1]);
			}
		}

		if (empty($id)) {

			$params  = '///';
			$rewrite = Mage::getResourceModel('core/url_rewrite_collection')
				->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
				->addFieldToFilter('request_path', substr($request->getPathInfo(), 1))
				->addFieldToFilter('product_id', array('gt' => 0))
				->setPageSize(1)
				->getFirstItem();

			if (!empty($rewrite->getData('product_id')))
				$id = $rewrite->getData('product_id');
		}

		// action
		if (!empty($id) && is_numeric($id)) {

			$candidates = array();

			$product = Mage::getModel('catalog/product')->load($id);
			$product = (!empty($product->getId())) ? $product : null;

			// si l'url est l'url d'un produit désactivé
			// c'est la fin des haricots la tout de suite maintenant
			if (is_object($product) && ($product->getData('status') != Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) {
				return false;
			}


			// LE PRODUIT EXISTE MAIS N'EST PAS VISIBLE
			// si l'url est l'url d'un produit non visible associé à un produit parent
			// cherche les éventuels ids parents
			if (is_object($product) && ($product->getData('visibility') == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)) {
				$candidates = array_merge(
					Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id),
					Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($id, Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED)
				);
			}

			// LE PRODUIT EXISTE ET EST VISIBLE
			// si l'url est l'url d'un produit visible
			// conserve le produit
			else if (is_object($product)) {
				$candidates = array($product);
			}

			// LE PRODUIT N'EXISTE PAS
			// si l'url est l'url d'un produit qui n'existe pas ou plus
			// cherche les éventuels ids de remplacement
			else if (!is_object($product) && !empty($oldids = Mage::getStoreConfig('urlnosql/general/oldids'))) {

				// https://mariadb.com/kb/en/mariadb/regular-expressions-overview/#word-boundaries
				// https://dev.mysql.com/doc/refman/8.0/en/regexp.html
				$products = Mage::getResourceModel('catalog/product_collection');
				$products->addAttributeToFilter($oldids, array('regexp' => '[[:<:]]'.$id.'[[:>:]]'));

				if (version_compare(Mage::getVersion(), '1.6', '<')) {
					$products->getSelect()->reset(Zend_Db_Select::WHERE);
					$products->getSelect()->where('_table_oldids.value regexp "[[:<:]]'.$id.'[[:>:]]"');
				}
				else {
					//$products->getSelect()->reset(Zend_Db_Select::WHERE);
					//$products->getSelect()->where('at_oldids.value regexp "[[:<:]]'.$id.'[[:>:]]"');
				}

				$candidates = $products->getAllIds();
			}


			// 4 8 15 16 23 42... SI NOUS AVONS DES CANDIDATS
			// soit dans le ou les ids produits parents (dans le cas d'un produit non visible)
			// soit dans le produit chargé initialement
			// soit dans le ou les ids produits de remplacement
			while (!empty($candidates)) {

				$product = array_shift($candidates); // un id ou un objet produit (du premier au dernier)
				$product = (is_object($product)) ? $product : Mage::getModel('catalog/product')->load($product);

				// le produit existe (le contraire est possible via l'attribut oldids)
				// le produit est activé (le contraire est possible via l'attribut oldids ou via les produits associés)
				// le produit est visible (le contraire est possible via l'attribut oldids ou via les produits associés)
				if (!empty($product->getId()) &&
				    ($product->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) &&
				    ($product->getData('visibility') != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)) {

					if (strpos($product->getProductUrl(), '/'.$params) === false) {
						header('Location: '.$product->getProductUrl(), true, 301);
						exit(0); // stop redirection 301
					}
					else {
						$request->setModuleName('catalog')->setControllerName('product')->setActionName('view');
						$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $params);
						$request->setParam('category_ids', $product->getCategoryIds());
						$request->setParam('id', $id);
						return true;
					}
				}

				$product->reset();
			}
		}

		return false;
	}
}