<?php
/**
 * Created V/26/06/2015
 * Updated J/26/07/2018
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

class Luigifab_Urlnosql_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		if (($msg = $this->checkRewrites()) === true) {
			return sprintf('<p class="box">Luigifab/Urlnosql %s <a href="https://www.%s" style="float:right;">%2$s</a></p>',
				$this->helper('urlnosql')->getVersion(), 'luigifab.info/magento/urlnosql');
		}
		else {
			return sprintf('<p class="box">Luigifab/Urlnosql %s <a href="https://www.%s" style="float:right;">%2$s</a></p>'.
				'<p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%s</strong><br />%s</p>',
				$this->helper('urlnosql')->getVersion(), 'luigifab.info/magento/urlnosql',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('There is conflict (<em>%s</em>).', $msg));
		}
	}

	private function checkRewrites() {

		$rewrites = array(
			array('block', 'adminhtml/catalog_form_renderer_attribute_urlkey'),
			array('model', 'catalog/indexer_url'),
			array('model', 'catalog/product_attribute_backend_urlkey'),
			array('model', 'catalog/product_url'),
			array('model', 'catalog/url'),
			array('model', 'sitemap_mysql4/catalog_product')
		);

		foreach ($rewrites as $rewrite) {
			if ($rewrite[0] == 'model') {
				if (!method_exists(Mage::getModel($rewrite[1]), 'specialCheckRewrite'))
					return $rewrite[1];
			}
			else if ($rewrite[0] == 'resource') {
				if (!method_exists(Mage::getResourceModel($rewrite[1]), 'specialCheckRewrite'))
					return $rewrite[1];
			}
			else if ($rewrite[0] == 'block') {
				if (!method_exists(Mage::getBlockSingleton($rewrite[1]), 'specialCheckRewrite'))
					return $rewrite[1];
			}
		}

		return true;
	}
}