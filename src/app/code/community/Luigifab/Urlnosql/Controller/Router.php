<?php
/**
 * Created V/26/06/2015
 * Updated S/05/12/2020
 *
 * Copyright 2015-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020      | Fabrice Creuzot <fabrice~cellublue~com>
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

	public static function saveDebug(array $debug) {

		if (!empty($debug) && !empty($_COOKIE['urlnosql']) && Mage::getStoreConfigFlag('urlnosql/general/debug_enabled')) {

			array_unshift($debug, '#{'.gmdate('c').'}#');
			array_unshift($debug, getenv('REQUEST_URI'));

			if (empty(session_id()))
				$session = Mage::getSingleton('core/session', ['name' => Mage_Core_Controller_Front_Action::SESSION_NAMESPACE])->start();
			else
				$session = Mage::getSingleton('core/session');

			$data  = $session->getData('urlnosql');
			if (empty($data)) $data = [];
			array_unshift($data, $debug);
			$session->setData('urlnosql', array_slice($data, 0, 10));
		}
	}

	public function initControllerRouters($observer) {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled'))
			$observer->getData('front')->addRouter('urlnosql', $this);
	}

	public function match(Zend_Controller_Request_Http $request, bool $fromUs = false, array $debug = []) {

		// recherche
		// au début $params est un array
		$params = trim($request->getPathInfo(), '/');
		$params = (array) explode('/', $params); // (yes)

		if (count($params) == 1) {
			// Array ( [0] => 300003-abc.html )
			$params = $params[0];
			// recherche de l'id dans l'url (insensible à la casse)
			// l'id étant l'id du produit
			preg_match('#^(\d+)[\w%\-]*'.Mage::helper('catalog/product')->getProductUrlSuffix().'$#i', $params, $id);
			// Array ( [0] => 300003-abc.html [1] => 300003 )
			if (!empty($id[1]) && is_numeric($id[1]))
				$id = (int) $id[1];
		}
		else if ($fromUs === true) {
			// Array ( [0] => catalog [1] => product [2] => view [3] => id [4] => 7 )
			$id = array_search('id', $params);
			$id = empty($params[$id + 1]) ? false : $params[$id + 1];
			$params = '///';
		}

		// action
		// dorénavant $params est un string
		if (!empty($id) && is_numeric($id)) {

			$candidates = [];
			$oldids  = Mage::getStoreConfig('urlnosql/general/oldids');
			$storeId = Mage::app()->getStore()->getId();
			$product = $this->getProduct($id, $storeId);
			$product = empty($product->getId()) ? null : $product;

			$debug[] = 'ROUTER';

			// LE PRODUIT EXISTE
			if (is_object($product)) {

				$debug[] = ($txt = 'Product #'.$product->getId()).' found';

				// produit désactivé ou non visible
				// cherche les éventuels ids parents et les éventuels ids de remplacement
				if (($product->getData('status') != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) ||
				    ($product->getData('visibility') == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)) {

					if (!empty($oldids)) {
						$candidates = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;
						$candidates = array_merge(
							Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id),
							Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($id, $candidates),
							// https://mariadb.com/kb/en/mariadb/regular-expressions-overview/#word-boundaries
							// https://dev.mysql.com/doc/refman/8.0/en/regexp.html
							Mage::getResourceModel('catalog/product_collection')
								->addAttributeToFilter($oldids, ['regexp' => '[[:<:]]'.$id.'[[:>:]]'])
								->addStoreFilter()
								->getAllIds());
					}
					else {
						$candidates = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;
						$candidates = array_merge(
							Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id),
							Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($id, $candidates));
					}

					$debug[] = $txt.' not enabled/visible';
					$debug[] = $txt.' searching other products in:';
					$debug[] = $candidates;
				}
				// produit visible
				// conserve le produit
				else {
					$candidates = [$product];
					$debug[] = $txt.' enabled/visible';
				}
			}
			// LE PRODUIT N'EXISTE PAS
			// cherche les éventuels ids de remplacement
			else if (!empty($oldids)) {

				// https://mariadb.com/kb/en/mariadb/regular-expressions-overview/#word-boundaries
				// https://dev.mysql.com/doc/refman/8.0/en/regexp.html
				$candidates = Mage::getResourceModel('catalog/product_collection')
					->addAttributeToFilter($oldids, ['regexp' => '[[:<:]]'.$id.'[[:>:]]'])
					->addStoreFilter()
					->getAllIds();

				$debug[] = 'Product not found';
				$debug[] = 'Searching other products in:';
				$debug[] = $candidates;
			}

			// 4 8 15 16 23 42... SI NOUS AVONS DES CANDIDATS
			// soit dans le ou les ids produits parents (dans le cas d'un produit non visible)
			// soit dans le produit chargé initialement
			// soit dans le ou les ids produits de remplacement
			while (!empty($candidates)) {

				$product = array_shift($candidates); // un id ou un objet produit (du premier au dernier)
				$product = is_object($product) ? $product : $this->getProduct($product, $storeId);

				$debug[] = ($txt = 'Checking product #'.$product->getId());

				// le produit existe (le contraire est possible via l'attribut oldids)
				// le produit est activé (le contraire est possible via l'attribut oldids ou via les produits associés)
				// le produit est visible (le contraire est possible via l'attribut oldids ou via les produits associés)
				if (($product->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) &&
				    ($product->getData('visibility') != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)) {

					$url = $product->getProductUrl();
					$debug[] = $txt.' product enabled/visible';
					$debug[] = $txt.' the url must be: '.$url;

					if (mb_strpos($url, '/'.$params) === false) {
						$debug[] = ' 301';
						self::saveDebug($debug);
						header('Location: '.$url, true, 301);
						exit(0); // stop redirection 301
					}

					$debug[] = ' nothing to do';
					self::saveDebug($debug);
					Mage::register('urlnosql', $url);

					$request->setModuleName('catalog')->setControllerName('product')->setActionName('view');
					$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $params);
					$request->setParam('category_ids', $product->getCategoryIds());
					$request->setParam('id', $id);
					return true;
				}
			}
		}

		self::saveDebug($debug);
		return false;
	}

	private function getProduct(int $productId, int $storeId) {

		return Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect(array_filter(preg_split('#\s+#', Mage::getStoreConfig('urlnosql/general/attributes').' status visibility')))
			->addIdFilter($productId)
			->addStoreFilter($storeId)
			->setPageSize(1)
			->getFirstItem()
			->setStoreId($storeId);
	}
}