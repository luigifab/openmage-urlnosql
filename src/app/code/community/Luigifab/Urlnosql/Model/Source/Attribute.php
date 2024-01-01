<?php
/**
 * Created M/01/03/2016
 * Updated J/20/10/2022
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

class Luigifab_Urlnosql_Model_Source_Attribute {

	protected $_options;

	public function toOptionArray() {

		if (empty($this->_options)) {

			$this->_options = [['label' => '', 'value' => '']];
			$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
				->addFieldToFilter('is_unique', 1)
				->addFieldToFilter('is_global', 1)
				->addFieldToFilter('frontend_input', 'text')
				->setOrder('attribute_code', 'asc');

			foreach ($attributes as $attribute)
				$this->_options[] = ['value' => $attribute->getData('attribute_code'), 'label' => $attribute->getData('attribute_code')];
		}

		return $this->_options;
	}
}