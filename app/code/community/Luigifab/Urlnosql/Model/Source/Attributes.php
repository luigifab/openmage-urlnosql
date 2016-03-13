<?php
/**
 * Created M/01/03/2016
 * Updated M/08/03/2016
 * Version 2
 *
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>, Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/urlnosql
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

class Luigifab_Urlnosql_Model_Source_Attributes {

	public function toOptionArray() {

		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
			->addFieldToFilter('is_unique', 1)
			->addFieldToFilter('is_global', 1)
			->addFieldToFilter('frontend_input', 'text')
			->setOrder('attribute_code', 'ASC');

		$options = array(array('label' => '', 'value' => ''));

		foreach ($attributes as $attribute)
			array_push($options, array('label' => $attribute->getData('attribute_code'), 'value' => $attribute->getData('attribute_code')));

		return $options;
	}
}