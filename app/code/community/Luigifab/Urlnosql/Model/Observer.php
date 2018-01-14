<?php
/**
 * Created L/01/01/2018
 * Updated L/01/01/2018
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

class Luigifab_Urlnosql_Model_Observer {

	// EVENT catalog_controller_product_init_before (frontend)
	public function updateCategoryId($observer) {

		$request = $observer->getData('controller_action')->getRequest();
		$params  = $observer->getData('params'); // Magento 1.7 et +
		$ids = $request->getParam('category_ids');

		if (!empty($ids) && is_array($ids)) {

			$lastCategoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
			$categories = Mage::getResourceModel('catalog/category_collection')
				->setStore(Mage::app()->getStore())
				->addAttributeToFilter('path', array('like' => '%/'.Mage::app()->getStore()->getRootCategoryId().'/%'))
				->addAttributeToFilter('entity_id', array('in' => $ids))
				->addAttributeToFilter('is_active', 1)
				->addAttributeToSort('level', 'desc')
				->addAttributeToSort('entity_id', 'desc');

			if (!empty($lastCategoryId) && in_array($lastCategoryId, $categories->getAllIds()))
				$id = $lastCategoryId;
			else if (!empty($cid = $categories->getFirstItem()))
				$id = $cid->getId();

			if (!empty($id)) {

				// Magento 1.4 et +
				Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($id);
				// Magento 1.4
				$request->setParam('category', $id);

				if (is_object($params)) {
					// Magento 1.7 et +
					$params->setData('category_id', $id);
					// Magento 1.8 et + (une histoire de cache)
					Mage::register('current_entity_key', $categories->getItemById($id)->getPath());
				}
			}
		}
	}
}