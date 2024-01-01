<?php
/**
 * Created V/26/06/2015
 * Updated D/26/11/2023
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

class Luigifab_Urlnosql_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$msg = $this->checkRewrites();
		if ($msg !== true)
			return sprintf('<p class="box">%s %s <span class="right">Stop russian war. <b>🇺🇦 Free Ukraine!</b> | <a href="https://github.com/luigifab/%3$s">github.com</a> | <a href="https://www.%4$s">%4$s</a> - ⚠ IPv6</span></p><p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%5$s</strong><br />%6$s</p>',
				'Luigifab/Urlnosql', $this->helper('urlnosql')->getVersion(), 'openmage-urlnosql', 'luigifab.fr/openmage/urlnosql',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('There is conflict (<em>%s</em>).', $msg));

		return sprintf('<p class="box">%s %s <span class="right">Stop russian war. <b>🇺🇦 Free Ukraine!</b> | <a href="https://github.com/luigifab/%3$s">github.com</a> | <a href="https://www.%4$s">%4$s</a> - ⚠ IPv6</span></p>',
			'Luigifab/Urlnosql', $this->helper('urlnosql')->getVersion(), 'openmage-urlnosql', 'luigifab.fr/openmage/urlnosql');
	}

	protected function checkRewrites() {

		$rewrites = [
			['block' => 'adminhtml/catalog_form_renderer_attribute_urlkey'],
			['model' => 'catalog/indexer_url'],
			['model' => 'catalog/product_api'],
			['model' => 'catalog/product_api_v2'],
			['model' => 'catalog/product_attribute_backend_urlkey'],
			['model' => 'catalog/product_url'],
			['model' => 'catalog/url'],
			['model' => 'sitemap_resource/catalog_product'],
		];

		foreach ($rewrites as $rewrite) {
			foreach ($rewrite as $type => $class) {
				if (($type == 'model') && (mb_stripos(Mage::getConfig()->getModelClassName($class), 'luigifab') === false))
					return $class;
				if (($type == 'block') && (mb_stripos(Mage::getConfig()->getBlockClassName($class), 'luigifab') === false))
					return $class;
				if (($type == 'helper') && (mb_stripos(Mage::getConfig()->getHelperClassName($class), 'luigifab') === false))
					return $class;
			}
		}

		return true;
	}
}