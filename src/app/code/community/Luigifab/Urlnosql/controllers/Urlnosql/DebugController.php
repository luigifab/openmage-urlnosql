<?php
/**
 * Created D/15/11/2020
 * Updated V/20/11/2020
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

require_once(str_replace('/controllers/Urlnosql/', '/controllers/', __FILE__));

class Luigifab_Urlnosql_Urlnosql_DebugController extends Luigifab_Urlnosql_DebugController {

	protected $_sessionNamespace = Mage_Adminhtml_Controller_Action::SESSION_NAMESPACE;

	public function preDispatch() {
		$this->getLayout()->setArea('adminhtml');
		Mage::dispatchEvent('adminhtml_controller_action_predispatch_start', []);
		Mage_Core_Controller_Varien_Action::preDispatch();
	}
}