<?php

namespace App\Models;

class Install extends Base
{
	private static $_sysCfgRelPathDefault = '/App/config_default.ini';
	private static $_cliDirDefault = '/App/Cli';
	private static $_installSqlCommands = '/App/install.sql';

	public static function GetSysConfigRelPathDefault () {
		return self::$_sysCfgRelPathDefault;
	}
	public function IsEverythingInstalled () {
		return (
			self::IsConfigInstalled() &&
			self::IsDataDirInstalled() &&
			self::IsCliDirInstalled() &&
			self::IsDbInstalled()
		);
	}
	public function IsConfigInstalled () {
		$sysCfg = \MvcCore\Config::GetSystem();
		if ($sysCfg === NULL) {
			\MvcCore\Config::ClearConfigCache();
			$sysCfg = \MvcCore\Config::GetSystem();
		}
		return $sysCfg !== NULL;
	}
	public function IsDataDirInstalled () {
		return is_dir(self::GetDataDir());
	}
	public function IsCliDirInstalled () {
		$sourceFullPath = self::getAppRootDir() . self::$_cliDirDefault;
		$targetFullPath = self::getCliDir();
		if (!is_dir($targetFullPath)) return FALSE;
		$srcDi = new \DirectoryIterator($sourceFullPath);
		$targetDi = new \DirectoryIterator($targetFullPath);
		$srcFilesCount = 0;
		$targetFilesCount = 0;
		foreach ($srcDi as $item) if (!$item->isDir() && !$item->isDot()) $srcFilesCount++;
		foreach ($targetDi as $item) if (!$item->isDir() && !$item->isDot()) $targetFilesCount++;
		return $srcFilesCount === $targetFilesCount;
	}
	public function IsDbInstalled () {
		try {
			$installScriptTablesCnt = 0;
			$cmds = $this->getDbInstallCommands();
			foreach ($cmds as $cmd) 
				if (mb_strpos($cmd, 'CREATE TABLE ') !== FALSE)
					$installScriptTablesCnt++;
			$dbCfg = \MvcCore\Config::GetSystem()->db;
			$dbTablesCnt = self::GetConnection($dbCfg)
				->Prepare([
					"SELECT COUNT(*) AS `cnt`			",
					"FROM information_schema.`TABLES` t	",
					"WHERE t.`TABLE_SCHEMA` = :db_name;	",
				])
				->FetchOne([':db_name' => $dbCfg->database])
				->ToScalar('cnt', 'int');
			
			$result = $dbTablesCnt === $dbTablesCnt;
		} catch (\Throwable $e) {
			$result = FALSE;
		}
		return $result;
	}

	public function InstallConfig () {
		$appRoot = self::getAppRootDir();
		$sourceFullPath = $appRoot . self::$_sysCfgRelPathDefault;
		$targetFullPath = $appRoot . \App\Models\Install::GetSysConfigRelPath();
		if (!file_exists($sourceFullPath)) {
			throw new \Exception(
				"Default config doesn't exist in location: {$sourceFullPath}."
			);
		}
		if (file_exists($targetFullPath)) {
			if (!unlink($targetFullPath)) {
				throw new \Exception(
					"Config file already exists and it's not possible ".
					"to overwrite it with default content."
				);
			}
		}
		if (!copy($sourceFullPath, $targetFullPath)) {
			throw new \Exception(
				"It's not possible to copy system config into location: `{$targetFullPath}`."
			);
		}
		return TRUE;
	}

	public function InstallCliDir () {
		$sourceFullPath = self::getAppRootDir() . self::$_cliDirDefault;
		$targetFullPath = self::getCliDir();
		if (!is_dir($targetFullPath) && !mkdir($targetFullPath, 0777, TRUE)) 
			throw new \Exception("Can't create directory `{$targetFullPath}`");
		$di = new \DirectoryIterator($sourceFullPath);
		foreach ($di as $item) {
			if ($item->isDir() || $item->isDot()) continue;
			$fileName = $item->getFilename();
			$sourceFileFullPath = $sourceFullPath . '/' . $fileName;
			$targetFileFullPath = $targetFullPath . '/' . $fileName;
			if (!copy($sourceFileFullPath, $targetFileFullPath)) 
				throw new \Exception(
					"Can't copy file to location `{$targetFileFullPath}`."
				);
		}
		return TRUE;
	}

	public function InstallDataDir () {
		$fullPath = self::GetDataDir();
		if (!mkdir($fullPath, 0777, TRUE)) 
			throw new \Exception("Can't create directory `{$fullPath}`");
		return TRUE;
	}

	public function InstallDb () {
		if (!$this->IsConfigInstalled()) throw new \Exception(
			"App config is not installed, it's not possible to install ".
			"database without credentials from app config."
		);
		$cmds = $this->getDbInstallCommands();
		$dbCfg = \MvcCore\Config::GetSystem()->db;
		$dbName = $dbCfg->database;
		$dbCfg->database = 'information_schema';
		$db = self::GetConnection($dbCfg);
		foreach ($cmds as $cmd) {
			if (mb_strpos($cmd, '%database_name%') !== FALSE)	
				$cmd = str_replace('%database_name%', $dbName, $cmd);
			try {
				$db->Prepare($cmd)->Execute();
			} catch (\Throwable $e) {
				try {
					throw new \Exception($cmd);
				} catch (\Throwable $prev) {
					throw new \Exception(
						"Database install command failure: `{$e->getMessage()}`",
						$e->getCode(),
						$prev
					);
				}
			}
		}
		return TRUE;
	}

	protected function getDbInstallCommands () {
		$rawCmds = file_get_contents(self::getAppRootDir() . self::$_installSqlCommands);
		$cmds = explode(';', $rawCmds);
		array_walk($cmds, function ($item, $key) use (& $cmds) {
			$item = trim($item);
			if (mb_strlen($item) > 0) {
				$item .= ';';
			} else {
				unset($cmds[$key]);
			}
		});
		return $cmds;
	}
}