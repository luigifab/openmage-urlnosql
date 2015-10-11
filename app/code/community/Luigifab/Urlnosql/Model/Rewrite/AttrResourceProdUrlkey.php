<?php
/**
 * Created M/30/06/2015
 * Updated S/22/08/2015
 * Version 3
 *
 * Copyright 2015 | Fabrice Creuzot <fabrice.creuzot~label-park~com>, Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Urlnosql_Model_Rewrite_AttrResourceProdUrlkey extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Urlkey {

	public function beforeSave($object) {
		return (Mage::getStoreConfig('urlnosql/general/enabled') === '1') ? $this : parent::beforeSave($object);
	}

	public function afterSave($object) {
		return (Mage::getStoreConfig('urlnosql/general/enabled') === '1') ? $this : parent::afterSave($object);
	}
}