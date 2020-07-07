<?php
/**
 * Created M/01/03/2016
 * Updated M/20/08/2019
 *
 * Copyright 2015-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
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

class Luigifab_Urlnosql_Model_Source_Attribute {

	public function toOptionArray() {

		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
		$attributes->addFieldToFilter('is_unique', 1);
		$attributes->addFieldToFilter('is_global', 1);
		$attributes->addFieldToFilter('frontend_input', 'text');
		$attributes->setOrder('attribute_code', 'asc');

		$options = [['label' => '', 'value' => '']];

		foreach ($attributes as $attribute)
			$options[] = ['value' => $attribute->getData('attribute_code'), 'label' => $attribute->getData('attribute_code')];

		return $options;
	}
}