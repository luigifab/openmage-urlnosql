<?php
/**
 * Created S/22/08/2015
 * Updated S/01/05/2020
 *
 * Copyright 2015-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Urlnosql_Block_Adminhtml_Rewrite_Urlkey extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Attribute_Urlkey {

	//protected function _construct() {
	//	$this->setModuleName('Mage_Adminhtml');
	//}

	public function getElementHtml() {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled') && !empty(Mage::registry('current_product'))) {
			$html = $this->__('See {{Product URL rewrite}}.');
			$html = str_replace(['{{', '}}'], ['<a href="'.$this->getUrl('*/system_config/edit', ['section' => 'urlnosql']).'">', '</a>'], $html);
		}

		return $html ?? parent::getElementHtml();
	}
}