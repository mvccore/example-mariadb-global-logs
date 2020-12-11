<?php

namespace App\Views\Helpers;

class FormatBytesHelper {
	protected static $units = ['B', 'KB', 'MB', 'GB', 'TB'];
	/**
	 * @param int $bytes
	 * @param int $precision
	 * @return string
	 */
	public function FormatBytes ($bytes, $precision = 0) {
		

		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count(self::$units) - 1); 

		$bytes /= pow(1024, $pow);
		// alternative:
		//$bytes /= (1 << (10 * $pow)); 

		return round($bytes, $precision) . ' ' . self::$units[$pow]; 
	}
}
