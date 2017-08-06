<?php
/**
 * Created S/22/08/2015
 * Updated M/28/02/2017
 *
 * Copyright 2015-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Urlnosql_Block_Adminhtml_Rewrite_Urlkey extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Attribute_Urlkey {

	//protected function _construct() {          // NO!
	//	$this->setModuleName('Mage_Adminhtml'); // NO!
	//}

	public function getElementHtml() {

		if (Mage::getStoreConfigFlag('urlnosql/general/enabled') && is_object(Mage::registry('current_product'))) {

			$html = str_replace(array('disabled="disabled"', 'id=""', '<label', '<input'), array('', '', '<label style="color:gray;" ', '<input disabled="disabled" '), parent::getElementHtml()).' <p class="note" style="width:auto;">'.$this->__('Disabled if you use <strong>urlnosql</strong> module (see {{Product url rewrite}}).').'</p>';

			$html = str_replace('{{', '<a href="'.$this->getUrl('*/*/edit', array('section' => 'urlnosql')).'">', $html);
			$html = str_replace('}}', '</a>', $html);

			return $html;
		}
		else {
			return parent::getElementHtml();
		}
	}
}