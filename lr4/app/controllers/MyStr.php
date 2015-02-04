<?php
class MyStr {
	public static function jsionEscape($str) {
		return str_replace('"', '\"', str_replace('\\', '\\\\', $str));
	}
}