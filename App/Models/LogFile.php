<?php

namespace App\Models;

class LogFile extends \App\Models\Base
{
	const NOT_PROCESSED = 0;
	const PROCESSING = 1;
	const PROCESSED = 2;

	/** @var int|NULL */
	protected $idGeneralLog;
	/** @var string */
	protected $fileName;
	/** @var string */
	protected $hash;
	/** @var int|NULL */
	protected $linesCount;
	/** @var int|NULL */
	protected $fileSize;
	/** @var \DateTime|NULL */
	protected $created;
	/** @var int|NULL */
	protected $processed;

	/** @return int|NULL */
	public function GetIdGeneralLog () {
		return $this->idGeneralLog;
	}
	/** @return string */
	public function GetFileName () {
		return $this->fileName;
	}
	/** @return string */
	public function GetFullPath () {
		return self::GetDataDir() . '/' . $this->fileName;
	}
	/** @return string */
	public function GetHash () {
		return $this->hash;
	}
	/** @return int|NULL */
	public function GetLinesCount () {
		return $this->linesCount;
	}
	/**
	 * @param int|NULL $linesCount 
	 * @return \App\Models\LogFile
	 */
	public function SetLinesCount ($linesCount) {
		$this->linesCount = $linesCount;
		return $this;
	}
	/** @return int|NULL */
	public function GetFileSize () {
		return $this->fileSize;
	}
	/** @return \DateTime|NULL */
	public function GetCreated () {
		return $this->created;
	}
	/** @return int|NULL */
	public function GetProcessed () {
		return $this->processed;
	}
	/**
	 * @param int $processed 
	 * @return \App\Models\LogFile
	 */
	public function SetProcessed ($processed) {
		$this->processed = $processed;
		return $this;
	}

	/** @return bool */
	public function IsSavedInDatabase () {
		$dbData = self::GetConnection()
			->Prepare([
				"SELECT					",
				"	t.`id_general_log`,	",
				"	t.`lines_count`,	",
				"	t.`processed`		",
				"FROM general_logs t	",
				"WHERE t.hash = :hash;	",
			])
			->FetchOne([':hash' => $this->hash])
			->ToArray();
		if ($dbData !== NULL) 
			return TRUE;
		return FALSE;
	}

	/** @return array */
	public function GetUsers () {
		return self::GetConnection()
			->Prepare([
				"SELECT 						",
				"	c.id_user, u.user_name		",
				"FROM connections c				",
				"LEFT JOIN users u ON			",
				"	u.id_user = c.id_user		",
				"WHERE 							",
				"	c.id_general_log = :id AND	",
				"	c.id_user IS NOT NULL		",
				"GROUP BY c.id_user				",
				"ORDER BY u.user_name ASC;		",
			])
			->FetchAll([':id' => $this->idGeneralLog])
			->ToScalars(
				'user_name', 'string', 'id_user', 'int'
			);
	}

	/** @return bool */
	public function Save ($createNew = null, $flags = 0) {
		if ($createNew || $this->idGeneralLog === NULL) {
			return $this->Insert($flags);
		} else {
			return $this->Update($flags);
		}
	}

	/** @return bool */
	public function Insert ($flags = 0) {
		$result = TRUE;
		$this->created->setTimezone(new \DateTimeZone('UTC'));
		$this->processed = self::NOT_PROCESSED;
		$data = $this->GetValues(
			self::PROPS_PROTECTED |
			self::PROPS_CONVERT_CAMELCASE_TO_UNDERSCORES
		);
		$params = [];
		$sqlItems = [];
		foreach ($data as $columnName => $value) {
			if ($columnName === 'id_general_log') continue;
			$params[":{$columnName}"] = self::convertToScalar($value);
			$sqlItems[] = "`{$columnName}`";
		}
		$db = self::GetConnection();
		try {
			$db->BeginTransaction(self::TRANS_READ_WRITE, 'log_file_insert');
			$db
				->Prepare([
					"INSERT INTO `general_logs` (		",
					implode(", ", $sqlItems),
					") VALUES (							",
					implode(", ", array_keys($params)),
					");									",
				])
				->Execute($params);
			$this->idGeneralLog = $db->LastInsertId('general_logs', 'int');
			$db->Commit();
			$this->initialValues = array_merge([], $this->initialValues, [
				'idGeneralLog'	=> $this->idGeneralLog,
				'linesCount'	=> $this->linesCount,
			]);
		} catch (\Throwable $e) {
			$db->RollBack();
			\MvcCore\Debug::Exception($e);
			$result = FALSE;
		}
		return $result;
	}
	
	/** @return bool */
	public function Update ($flags = 0) {
		$data = $this->GetTouched(
			self::PROPS_PROTECTED |
			self::PROPS_CONVERT_CAMELCASE_TO_UNDERSCORES
		);
		if (count($data) === 0) 
			return FALSE;
		$params = [':id_general_log' => $this->idGeneralLog];
		$colsSql = [];
		foreach ($data as $columnName => $value) {
			if ($columnName === 'id_general_log') continue;
			$params[":{$columnName}"] = self::convertToScalar($value);
			$colsSql[] = "`{$columnName}` = :{$columnName}";
		};
		$result = self::GetConnection()
			->Prepare([
				"UPDATE `general_logs`					",
				"SET " . implode(", ", $colsSql) . "	",
				"WHERE									",
				"	`id_general_log` = :id_general_log;	",
			])
			->Execute($params)
			->GetExecResult();
		$this->initialValues = array_merge([], $this->initialValues, $data);
		return $result;
	}

	/**
	 * @param string $fullPath 
	 * @param string $newLineChar 
	 * @return int
	 */
	public static function GetFileLinesCount ($fullPath, $newLineChar = "\n") {
		$f = fopen($fullPath, 'rb');
		$lines = 0;
		while (!feof($f)) 
			$lines += substr_count(fread($f, 8192), $newLineChar);
		fclose($f);
		return $lines;
	}

	/**
	 * @param int $logFileId
	 * @return \App\Models\LogFile
	 */
	public static function GetById ($logFileId) {
		return self::GetConnection()
			->Prepare(implode("\n", [
				"SELECT *									",
				"FROM `general_logs`						",
				"WHERE `id_general_log` = :id_general_log;	",
			]))
			->FetchOne([':id_general_log' => $logFileId])
			->ToInstance(
				get_called_class(),
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE |
				self::PROPS_INITIAL_VALUES
			);
	}

	/** @return \App\Models\LogFile[] */
	public static function ListAllWithDbInfo () {
		$rawHddData = self::listAllFromHdd();
		$rawDbData = self::listAllFromDb();
		$rawData = array_merge([], $rawHddData, $rawDbData);
		$result = [];
		foreach ($rawData as $rawItem)
			$result[] = (new static)
				->SetValues(
					$rawItem, 
					self::PROPS_PROTECTED |
					self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE |
					self::PROPS_INITIAL_VALUES
				);
		return $result;
	}

	/**
	 * @param string $hash 
	 * @return \App\Models\LogFile
	 */
	public static function GetFileByHash ($hash) {
		$result = NULL;
		$dataDirFullPath = self::GetDataDir();
		$di = new \DirectoryIterator($dataDirFullPath);
		foreach ($di as $item) {
			if ($item->isDot() || $item->isDir()) continue;
			$fileName = $item->getFilename();
			$fullPath = $dataDirFullPath . '/' . $fileName;
			$hashLocal = self::getHashFromFile($fullPath);
			if ($hashLocal !== $hash) continue;
			$fileName = $item->getFilename();
			$fullPath = $dataDirFullPath . '/' . $fileName;
			$created = (new \DateTime())->setTimestamp(filectime($fullPath));
			$result = (new static)
				->SetValues([
					'id_general_log'	=> NULL,
					'file_name'			=> $fileName,
					'hash'				=> $hash,
					'lines_count'		=> NULL,
					'file_size'			=> filesize($fullPath),
					'created'			=> $created->format('Y-m-d H:i:s'),
					'processed'			=> self::NOT_PROCESSED,
				], (
					self::PROPS_PROTECTED |
					self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE |
					self::PROPS_INITIAL_VALUES
				)
			);
		}
		return $result;
	}
	
	/** @return \App\Models\LogFile[] Keys are hash values */
	protected static function listAllFromHdd () {
		$result = [];
		$dataDirFullPath = self::GetDataDir();
		$di = new \DirectoryIterator($dataDirFullPath);
		foreach ($di as $item) {
			if ($item->isDot() || $item->isDir()) continue;
			$fileName = $item->getFilename();
			$fullPath = $dataDirFullPath . '/' . $fileName;
			$hash = self::getHashFromFile($fullPath);
			$result[$hash] = [
				'id_general_log'	=> NULL,
				'file_name'			=> $fileName,
				'hash'				=> $hash,
				'lines_count'		=> NULL,
				'file_size'			=> filesize($fullPath),
				'created'			=> (new \DateTime())->setTimestamp(filectime($fullPath))->format('Y-m-d H:i:s'),
				'processed'			=> self::NOT_PROCESSED,
			];
		}
		return $result;
	}
	
	/** @return \App\Models\LogFile[] Keys are hash values */
	protected static function listAllFromDb () {
		return self::GetConnection()
			->Prepare([
				"SELECT *					",
				"FROM `general_logs` gl		",
				"ORDER BY `file_name` ASC;	",
			])
			->FetchAll()
			->ToArrays('hash');
	}

	protected static function getHashFromFile ($fullPath) {
		// return sha1_file($fullPath); // this is too slow for very large files
		return sha1(serialize([
			'fullPath'			=> $fullPath,
			'createDate'		=> filectime($fullPath),
			'modificationDate'	=> filemtime($fullPath),
			'fileSize'			=> filesize($fullPath),
		]));
	}
}
