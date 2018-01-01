<?php
/**
 * Created M/30/06/2015
 * Updated M/28/02/2017
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

class Luigifab_Urlnosql_Model_Rewrite_AttrProdUrlkey extends Mage_Catalog_Model_Product_Attribute_Backend_Urlkey {

	public function beforeSave($object) {
		return (Mage::getStoreConfigFlag('urlnosql/general/enabled')) ? $this : parent::beforeSave($object);
	}

	public function afterSave($object) {
		return (Mage::getStoreConfigFlag('urlnosql/general/enabled')) ? $this : parent::afterSave($object);
	}
}