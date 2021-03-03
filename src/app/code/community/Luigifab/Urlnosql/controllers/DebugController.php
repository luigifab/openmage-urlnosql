<?php
/**
 * Created D/15/11/2020
 * Updated M/02/02/2021
 *
 * Copyright 2015-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020-2021 | Fabrice Creuzot <fabrice~cellublue~com>
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

class Luigifab_Urlnosql_DebugController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled') && Mage::getStoreConfigFlag('urlnosql/general/debug_enabled')) {

			$passwd = Mage::getStoreConfig('urlnosql/general/debug_password');
			if (!empty($passwd) && ($this->getRequest()->getParam('pass') != $passwd)) {
				$link = '';
				$text = 'invalid pass';
			}
			else {
				if (empty(Mage::getSingleton('core/cookie')->get('urlnosql')))
					$link = ' - <a href="'.Mage::getUrl('*/*/start', ['pass' => $passwd]).'">start</a>';
				else
					$link = ' - <a href="'.Mage::getUrl('*/*/stop', ['pass' => $passwd]).'">stop</a>';

				$text = print_r(Mage::getSingleton('core/session')->getData('urlnosql'), true);
				$text = str_replace([Mage::getBaseDir(), '#{', '}#'], ['', '<b>', '</b>'], htmlspecialchars($text));
			}
		}
		else {
			$link = '';
			$text = 'disabled';
		}

		$this->getResponse()->setBody(
			'<html lang="en"><head><title>urlnosql</title>'.
			'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
			'<meta name="robots" content="noindex,nofollow"></head><body><pre style="white-space:pre-wrap;">'.
			date('c').$link.'<br><br>'.$text.
			'</pre></body></html>');
	}

	public function startAction() {

		$passwd = Mage::getStoreConfig('urlnosql/general/debug_password');
		if (Mage::getStoreConfigFlag('urlnosql/general/debug_enabled') && (empty($passwd) || ($this->getRequest()->getParam('pass') == $passwd))) {
			Mage::getSingleton('core/cookie')->set('urlnosql', 1);
			$this->_redirect('*/*/index', ['pass' => $passwd]);
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function stopAction() {

		$passwd = Mage::getStoreConfig('urlnosql/general/debug_password');
		if (Mage::getStoreConfigFlag('urlnosql/general/debug_enabled') && (empty($passwd) || ($this->getRequest()->getParam('pass') == $passwd))) {
			Mage::getSingleton('core/cookie')->delete('urlnosql');
			$this->_redirect('*/*/index', ['pass' => $passwd]);
		}
		else {
			$this->_redirect('*/*/index');
		}
	}
}