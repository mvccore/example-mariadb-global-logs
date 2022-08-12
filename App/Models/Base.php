<?php

namespace App\Models;

class Base extends \MvcCore\Ext\Models\Db\Models\MySql {

	private static $_appRoot = NULL;
	private static $_sysCfgRelPath = '~/mysql_global_logs.ini';
	private static $_cliDir = '~/Var/Cli';
	private static $_dataDir = '~/Data';
	private static $_cacheDir = '~/Var/Cache';

	/** @return \MvcCore\Ext\Models\Db\Connection */
	public static function GetConnection ($connectionNameOrConfig = NULL, $strict = true) {
		/** @var \MvcCore\Ext\Models\Db\Connection $conn */
		$conn = parent::GetConnection(0, $strict);
		return $conn;
	}

	public static function GetSysConfigRelPath () {
		return static::$_sysCfgRelPath;
	}
	public static function GetDataDir () {
		return self::getAppRootDir() . mb_substr(self::$_dataDir, 1);
	}

	protected static function getCliDir () {
		return self::getAppRootDir() . mb_substr(self::$_cliDir, 1);
	}
	protected static function getCacheDir () {
		return self::getAppRootDir() . mb_substr(self::$_cacheDir, 1);
	}
	protected static function getAppRootDir () {
		return self::$_appRoot ?: (
			self::$_appRoot = \MvcCore\Application::GetInstance()->GetRequest()->GetAppRoot()
		);
	}
}
