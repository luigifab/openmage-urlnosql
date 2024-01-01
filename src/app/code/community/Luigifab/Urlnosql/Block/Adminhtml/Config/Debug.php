<?php
/**
 * Created D/15/11/2020
 * Updated S/03/12/2022
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

class Luigifab_Urlnosql_Block_Adminhtml_Config_Debug extends Mage_Adminhtml_Block_System_Config_Form_Field {

	public function render(Varien_Data_Form_Element_Abstract $element) {
		$element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue()->unsPath();
		return parent::render($element);
	}

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

		$storeId = Mage::app()->getDefaultStoreView()->getId();
		$passwd  = Mage::getStoreConfig('urlnosql/general/debug_password');
		$element->setValue(sprintf('<a href="%s">debug front</a> / <a href="%s">debug back</a>',
			preg_replace('#/key/[^/]+/#', '/', $this->getUrl('urlnosql/debug/index', ['pass' => $passwd, '_store' => $storeId])),
			preg_replace('#/key/[^/]+/#', '/', $this->getUrl('*/urlnosql_debug/index', ['pass' => $passwd]))));

		return sprintf('<span lang="en" id="%s">%s</span>', $element->getHtmlId(), $element->getValue());
	}
}