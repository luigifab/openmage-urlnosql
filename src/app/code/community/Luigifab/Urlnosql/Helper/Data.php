<?php
/**
 * Created V/26/06/2015
 * Updated V/14/05/2021
 *
 * Copyright 2015-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2015-2016 | Fabrice Creuzot <fabrice.creuzot~label-park~com>
 * Copyright 2020-2021 | Fabrice Creuzot <fabrice~cellublue~com>
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

class Luigifab_Urlnosql_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Urlnosql')->version;
	}

	public function _(string $data, ...$values) {
		$text = $this->__(' '.$data, ...$values);
		return ($text[0] == ' ') ? $this->__($data, ...$values) : $text;
	}

	public function escapeEntities($data, bool $quotes = false) {
		return htmlspecialchars($data, $quotes ? ENT_SUBSTITUTE | ENT_COMPAT : ENT_SUBSTITUTE | ENT_NOQUOTES);
	}

	public function normalizeChars(string $locale, string $value) {

		$opts = transliterator_list_ids();
		$code = str_replace('_', '-', strtolower($locale)).'_Latn/BGN';

		if (in_array($code, $opts))
			$value = transliterator_transliterate($code.'; Any-Latin; Latin-ASCII; [^\u001F-\u007f] remove; Lower()', $value);
		else
			$value = transliterator_transliterate('Any-Latin; Latin-ASCII; [^\u001F-\u007f] remove; Lower()', $value);

		return trim(preg_replace('#[^\w\-]#', '', str_replace(['/', ' '], '-', $value)), '-');
	}
}