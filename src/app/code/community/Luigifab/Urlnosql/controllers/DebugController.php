<?php
/**
 * Created D/15/11/2020
 * Updated S/23/12/2023
 *
 * Copyright 2015-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020-2023 | Fabrice Creuzot <fabrice~cellublue~com>
 * https://github.com/luigifab/openmage-urlnosql
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

	public function preDispatch() {

		Mage::register('turpentine_nocache_flag', true, true);
		$this->getResponse()
			->setHeader('Cache-Control', 'no-cache, must-revalidate', true)
			->setHeader('X-Robots-Tag', 'noindex, nofollow', true);

		$isBot = true;
		$userAgent = getenv('HTTP_USER_AGENT');

		if (!empty($userAgent) && (mb_stripos($userAgent, 'bot') === false)) {
			$isBot = false;
			// @see Mage_Log_Model_Visitor
			$ignoreAgents = Mage::getConfig()->getNode('global/ignore_user_agents');
			if (!empty($ignoreAgents)) {
				$ignoreAgents = $ignoreAgents->asArray();
				foreach ($ignoreAgents as $ignoreAgent) {
					if (mb_stripos($userAgent, $ignoreAgent) !== false) {
						$isBot = true;
						break;
					}
				}
			}
		}

		if ($isBot) {
			$this->setFlag('', Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH, true);
			$this->getResponse()->setHttpResponseCode(404);
		}

		return parent::preDispatch();
	}

	public function indexAction() {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled') && Mage::getStoreConfigFlag('urlnosql/general/debug_enabled')) {

			$pass = Mage::getStoreConfig('urlnosql/general/debug_password');
			if (!empty($pass) && ($this->getRequest()->getParam('pass') != $pass)) {
				$link = '';
				$text = 'invalid pass';
			}
			else {
				$text = Mage::getSingleton('core/session')->getData('urlnosql');

				if (empty(Mage::getSingleton('core/cookie')->get('urlnosql'))) {
					$link = ' - <a href="'.Mage::getUrl('*/*/start', ['pass' => $pass]).'">start</a>';
					if (empty($text))
						$link .= ' - <a href="'.Mage::getUrl('*/*/clear', ['pass' => $pass]).'" style="color:#666;">clear</a>';
					else
						$link .= ' - <a href="'.Mage::getUrl('*/*/clear', ['pass' => $pass]).'">clear</a>';
				}
				else {
					$link = ' - <a href="'.Mage::getUrl('*/*/stop', ['pass' => $pass]).'">stop</a>';
					if (empty($text))
						$link .= ' - <a href="'.Mage::getUrl('*/*/clear', ['pass' => $pass]).'" style="color:#666;">clear</a>';
					else
						$link .= ' - <a href="'.Mage::getUrl('*/*/clear', ['pass' => $pass]).'">clear</a>';
				}

				if (empty($text))
					$text = 'no data';
				else
					$text = str_replace([BP, '#{', '}#'], ['', '<b>', '</b>'], htmlspecialchars(print_r($text, true)));
			}
		}
		else {
			$link = '';
			$text = 'disabled';
		}

		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Content-Type', 'text/html; charset=utf-8', true)
			->setBody('<html lang="en"><head><title>urlnosql</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="robots" content="noindex,nofollow"></head><body><pre style="white-space:pre-wrap;"><b>'.date('c').'</b>'.$link.'<br><br>'.$text.'</pre></body></html>');
	}

	public function startAction() {

		$pass = Mage::getStoreConfig('urlnosql/general/debug_password');
		if (Mage::getStoreConfigFlag('urlnosql/general/debug_enabled') && (empty($pass) || ($this->getRequest()->getParam('pass') == $pass))) {
			Mage::getSingleton('core/cookie')->set('urlnosql', 1, true);
			$this->_redirect('*/*/index', ['pass' => $pass]);
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function clearAction() {

		$pass = Mage::getStoreConfig('urlnosql/general/debug_password');
		if (Mage::getStoreConfigFlag('urlnosql/general/debug_enabled') && (empty($pass) || ($this->getRequest()->getParam('pass') == $pass))) {
			Mage::getSingleton('core/session')->setData('urlnosql', null);
			$this->_redirect('*/*/index', ['pass' => $pass]);
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function stopAction() {

		$pass = Mage::getStoreConfig('urlnosql/general/debug_password');
		if (Mage::getStoreConfigFlag('urlnosql/general/debug_enabled') && (empty($pass) || ($this->getRequest()->getParam('pass') == $pass))) {
			Mage::getSingleton('core/cookie')->delete('urlnosql');
			$this->_redirect('*/*/index', ['pass' => $pass]);
		}
		else {
			$this->_redirect('*/*/index');
		}
	}
}