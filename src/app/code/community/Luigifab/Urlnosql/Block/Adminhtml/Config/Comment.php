<?php
/**
 * Created S/22/08/2015
 * Updated L/18/05/2020
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

class Luigifab_Urlnosql_Block_Adminhtml_Config_Comment extends Mage_Adminhtml_Block_System_Config_Form_Field {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$html = parent::render($element);
		$html = str_replace(['{{', '}}'], ['<a href="'.$this->getUrl('*/system_config/edit', ['section' => 'urlnosql']).'">', '</a>'], $html);

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled'))
			$html = str_replace('<select', '<select disabled="disabled"', $html);

		return $html;
	}
}