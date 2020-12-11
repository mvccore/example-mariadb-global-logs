<?php

namespace App\Models;

class Cli extends \App\Models\Base
{
	/**
	 * @param string $scriptName
	 * @param \string[] $args
	 * @return string
	 */
	public static function RunScript ($scriptName, $args = []) {
		$isWin = mb_substr(mb_strtolower(PHP_OS), 0, 3) === 'win';
		if ($isWin) {
			$cmd = $scriptName . '.cmd';
		} else {
			$cmd = 'sh ' . $scriptName . '.sh';
		}
		if (count($args) > 0) {
			foreach ($args as $arg) {
				if (
					mb_strpos($arg, ' ') !== FALSE && 
					(mb_substr($arg, 0, 1) !== '"' || mb_substr($arg, 0, 1) !== "'")
				)
					$arg = '"' . $arg . '"';
				$cmd .= ' ' . $arg;
			}
		}
		if (!$isWin) 
			$cmd = 'sh -c "' . $cmd . '" 2>&1';
		return self::System($cmd, self::getCliDir());
	}

	/**
	 * Execute system command.
	 * @param string $cmd
	 * @param string|NULL $dirPath
	 * @return bool|string
	 */
	public static function System ($cmd, $dirPath = NULL) {
		if (!function_exists('system')) return FALSE;
		$dirPathPresented = $dirPath !== NULL && mb_strlen($dirPath) > 0;
		$cwd = '';
		if ($dirPathPresented) {
			$cwd = getcwd();
			chdir($dirPath);
		}
		ob_start();
		system($cmd);
		$sysOut = ob_get_clean();
		if ($dirPathPresented) chdir($cwd);
		return trim($sysOut);
	}
}