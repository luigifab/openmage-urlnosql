<?php
/**
 * Created V/26/06/2015
 * Updated D/06/10/2019
 *
 * Copyright 2015-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020-2022 | Fabrice Creuzot <fabrice~cellublue~com>
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

class Luigifab_Urlnosql_Model_Rewrite_Indexerurl extends Mage_Catalog_Model_Indexer_Url {

	protected function _registerProductEvent(Mage_Index_Model_Event $event) {
		return Mage::getStoreConfigFlag('urlnosql/general/enabled') ? $this : parent::_registerProductEvent($event);
	}
}