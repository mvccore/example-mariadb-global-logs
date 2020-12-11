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
			->Prepare(implode("\n", [
				"SELECT					",
				"	t.`id_general_log`,	",
				"	t.`lines_count`,	",
				"	t.`processed`		",
				"FROM general_logs t	",
				"WHERE t.hash = :hash;	",
			]))
			->Execute([':hash' => $this->hash])
			->FetchOneToAssocArray();
		if ($dbData !== NULL) {
			$this->SetUp($dbData, self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, TRUE);
			return TRUE;
		}
		return FALSE;
	}

	/** @return int|NULL */
	public function Save () {
		if ($this->idGeneralLog === NULL) {
			return $this->idGeneralLog = $this->insert();
		} else {
			return $this->update();
		}
	}

	/** @return int|NULL */
	protected function insert () {
		$result = NULL;
		$db = self::GetConnection();
		$this->created->setTimezone(new \DateTimeZone('UTC'));
		try {
			$db->BeginTransaction('log_file_insert', TRUE);
			$db->Prepare(implode("\n", [
					"INSERT INTO `general_logs` (		",
					"	file_name, hash, lines_count,	",
					"	file_size, created, processed	",
					") VALUES (							",
					"	:file_name, :hash, :lines_count,",
					"	:file_size, :created, :processed",
					");									",
				]))
				->Execute([
					':file_name'	=> $this->fileName, 
					':hash'			=> $this->hash, 
					':lines_count'	=> $this->GetLinesCount(),
					':file_size'	=> $this->fileSize, 
					':created'		=> $this->created->format('Y-m-d H:i:s'), 
					':processed'	=> self::NOT_PROCESSED
				]);
			$result = $db->LastInsertId('general_logs', 'int');
			$this->initialValues['idGeneralLog'] = $result;
			$this->initialValues['linesCount'] = $this->linesCount;
			$db->Commit();
		} catch (\Throwable $e) {
			$db->RollBack();
			\MvcCore\Debug::Exception($e);
			$result = NULL;
		}
		return $result;
	}
	
	/** @return int */
	protected function update () {
		$touched = $this->GetTouched(FALSE, FALSE);
		if (count($touched) === 0) return 0;
		$colsSqlItems = [];
		$params = [];
		array_walk($touched, function ($value, $propKey) use (& $colsSqlItems, & $params) {
			if (mb_substr($propKey, 0, 1) == '_' || $propKey === 'id_general_log') return;
			$colName = \MvcCore\Tool::GetUnderscoredFromPascalCase($propKey);
			$colsSqlItems[] = "{$colName} = :{$colName}";
			if ($value instanceof \DateTime) {
				$value->setTimezone(new \DateTimeZone('UTC'));
				$scalarValue = $value->format('Y-m-d H:i:s');
			} else {
				$scalarValue = $value;
			}
			$params[":{$colName}"] = $scalarValue;
		});
		$colsSql = implode(", ", $colsSqlItems);
		$params[':id_general_log'] = $this->idGeneralLog;
		$updatedRows = self::GetConnection()
			->Prepare(implode("\n", [
				"UPDATE `general_logs`					",
				"SET {$colsSql}							",
				"WHERE									",
				"	`id_general_log` = :id_general_log;	",
			]))
			->Execute($params)
			->RowCount();
		$this->initialValues = array_merge(
			[], $this->initialValues, $touched
		);
		return $updatedRows;
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
		$rawData = self::GetConnection()
			->Prepare(implode("\n", [
				"SELECT *									",
				"FROM `general_logs`						",
				"WHERE `id_general_log` = :id_general_log;	",
			]))
			->Execute([':id_general_log' => $logFileId])
			->FetchOneToAssocArray();
		if ($rawData === NULL) return NULL;
		/** @var $result \App\Models\LogFile */
		$result = (new static)
			->SetUp($rawData, self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, TRUE);
		return $result;
	}

	/** @return \App\Models\LogFile[] */
	public static function ListAllWithDbInfo () {
		$rawHddData = self::listAllFromHdd();
		$rawDbData = self::listAllFromDb();
		$rawData = array_merge([], $rawHddData, $rawDbData);
		$result = [];
		foreach ($rawData as $rawItem)
			$result[] = (new static)
				->SetUp($rawItem, self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, TRUE);
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
			$result = (new static)->SetUp([
				'id_general_log'	=> NULL,
				'file_name'			=> $fileName,
				'hash'				=> $hash,
				'lines_count'		=> NULL,
				'file_size'			=> filesize($fullPath),
				'created'			=> filectime($fullPath),
				'processed'			=> self::NOT_PROCESSED,
			], self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE);
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
			->Prepare(implode("\n", [
				"SELECT *					",
				"FROM `general_logs` gl		",
				"ORDER BY `file_name` ASC;	",
			]))
			->Execute()
			->FetchAllToAssocArrays('hash');
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