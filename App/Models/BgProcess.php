<?php

namespace App\Models;

class BgProcess extends \App\Models\Base
{
	/** @var int|NULL */
	protected $id = NULL;
	/** @var int */
	protected $idGeneralLog;
	/** @var string|NULL */
	protected $hash = NULL;
	/** @var float|NULL */
	protected $progress = NULL;
	/** @var string */
	protected $controller;
	/** @var string|NULL */
	protected $action = NULL;
	/** @var string|NULL */
	protected $params = NULL;
	/** @var \DateTime|NULL */
	protected $created = NULL;
	/** @var \DateTime|NULL */
	protected $started = NULL;
	/** @var \DateTime|NULL */
	protected $finished = NULL;
	/** @var int|NULL */
	protected $result = NULL;
	/** @var string|NULL */
	protected $message = NULL;
	
	/** @var array|NULL */
	private $_paramsUnserialized = NULL;
	
	/**
	 * Boolean about if it is already registered shutwodn handler 
	 * to start background process after request end or not.
	 * @var bool
	 */
	private static $_shutdownHandlerRegistered = FALSE;

	/** @return int|NULL */
	public function GetId () {
		return $this->id;
	}
	
	/** @return string|NULL */
	public function GetHash () {
		return $this->hash;
	}
	
	/** @return int|NULL */
	public function GetIdGeneralLog () {
		return $this->idGeneralLog;
	}

	/**
	 * @param int|NULL $idGeneralLog
	 * @return \App\Models\BgProcess
	 */
	public function SetIdGeneralLog ($idGeneralLog) {
		$this->idGeneralLog = $idGeneralLog;
		return $this;
	}
	
	/** @return float|NULL */
	public function GetProgress () {
		return $this->progress;
	}
	/**
	 * @param float|NULL $progress
	 * @return \App\Models\BgProcess
	 */
	public function SetProgress ($progress) {
		$this->progress = $progress;
		return $this;
	}
	
	/** @return string */
	public function GetController () {
		return $this->controller;
	}
	/**
	 * @param string $controller
	 * @return \App\Models\BgProcess
	 */
	public function SetController ($controller) {
		$this->controller = $controller;
		return $this;
	}
	
	/** @return string|NULL */
	public function GetAction () {
		return $this->action;
	}
	/**
	 * @param string|NULL $action
	 * @return \App\Models\BgProcess
	 */
	public function SetAction ($action) {
		$this->action = $action;
		return $this;
	}
	
	/** @return array */
	public function GetParams () {
		if ($this->_paramsUnserialized === NULL) {
			$this->_paramsUnserialized = [];
			if ($this->params !== NULL) 
				$this->_paramsUnserialized = unserialize($this->params);
		}
		return $this->_paramsUnserialized;
	}
	/**
	 * @param array $params
	 * @return \App\Models\BgProcess
	 */
	public function SetParams ($params) {
		$this->_paramsUnserialized = $params;
		$this->params = serialize($params);
		return $this;
	}

	/** @return \DateTime|NULL */
	public function GetCreated () {
		return $this->created;
	}
	
	/** @return \DateTime|NULL */
	public function GetStarted () {
		return $this->started;
	}
	/**
	 * @param \DateTime|NULL $started
	 * @return \App\Models\BgProcess
	 */
	public function SetStarted ($started) {
		$this->started = $started;
		return $this;
	}
	
	/** @return \DateTime|NULL */
	public function GetFinished () {
		return $this->finished;
	}
	/**
	 * @param \DateTime|NULL $finished
	 * @return \App\Models\BgProcess
	 */
	public function SetFinished ($finished) {
		$this->finished = $finished;
		return $this;
	}
	
	/** @return int|NULL */
	public function GetResult () {
		return $this->result;
	}
	/**
	 * @param int|NULL $result
	 * @return \App\Models\BgProcess
	 */
	public function SetResult ($result) {
		$this->result = $result;
		return $this;
	}
	
	/** @return string|NULL */
	public function GetMessage () {
		return $this->message;
	}
	/**
	 * @param string|NULL $message
	 * @return \App\Models\BgProcess
	 */
	public function SetMessage ($message) {
		$this->message = $message;
		return $this;
	}
	
	/** @return \App\Models\LogFile|NULL */
	public function GetGeneralLog () {
		return \App\Models\LogFile::GetById($this->idGeneralLog);
	}


	/**
	 * @param int $idGeneralLog
	 * @param array $params 
	 * @return \App\Models\BgProcess
	 */
	public static function CreateNew ($idGeneralLog, $params = []) {
		return (new static)
			->SetIdGeneralLog($idGeneralLog)
			->SetParams($params);
	}

	/**
	 * @param int $id 
	 * @return \App\Models\BgProcess|NULL
	 */
	public static function GetById ($id) {
		$db = self::GetConnection();
		$rawData = $db
			->prepare(implode("\n", [
				"SELECT 							",
				"	bgp.`id_bg_process` AS `id`,	",
				"	bgp.`id_general_log`,			",
				"	bgp.`hash`,						",
				"	bgp.`progress`,					",
				"	bgp.`controller`,				",
				"	bgp.`action`,					",
				"	bgp.`params`,					",
				"	bgp.`created`,					",
				"	bgp.`started`,					",
				"	bgp.`finished`,					",
				"	bgp.`result`,					",
				"	bgp.`message`					",
				"FROM `bg_processes` bgp			",
				"WHERE bgp.`id_bg_process` = :id;	",
			]))
			->execute([
				':id'	=> $id
			])
			->fetchOneToAssocArray();
		if ($rawData === NULL) return NULL;
		/** @var $bgProcess \App\Models\BgProcess */
		$bgProcess = (new static())->SetUp(
			$rawData, 
			\MvcCore\IModel::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, 
			TRUE
		);
		return $bgProcess;
	}
	
	/**
	 * @param int $idGeneralLog 
	 * @param string $controller 
	 * @param string $controller 
	 * @return \App\Models\BgProcess|NULL
	 */
	public static function GetByLogIdCtrlAndAction ($idGeneralLog, $controller, $action) {
		$db = self::GetConnection();
		$hash = self::_getHash($idGeneralLog, $controller, $action);
		$rawData = $db
			->Prepare(implode("\n", [
				"SELECT 						",
				"	bgp.`id_bg_process` AS `id`,",
				"	bgp.`id_general_log`,		",
				"	bgp.`hash`,					",
				"	bgp.`progress`,				",
				"	bgp.`controller`,			",
				"	bgp.`action`,				",
				"	bgp.`params`,				",
				"	bgp.`created`,				",
				"	bgp.`started`,				",
				"	bgp.`finished`,				",
				"	bgp.`result`,				",
				"	bgp.`message`				",
				"FROM `bg_processes` bgp		",
				"WHERE							",
				"	bgp.`hash` = :hash;			",
			]))
			->Execute([
				':hash' => $hash,
			])
			->fetchOneToAssocArray();
		if ($rawData === NULL) return NULL;
		/** @var $bgProcess \App\Models\BgProcess */
		$bgProcess = (new static())->SetUp(
			$rawData, 
			\MvcCore\IModel::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, 
			TRUE
		);
		return $bgProcess;
	}

	/**
	 * @param \int[]|\string[] $generalLogsIds
	 * @param string $controller 
	 * @param string $controller 
	 * @return \App\Models\BgProcess[] Keys are general log ids.
	 */
	public static function GetByLogsIdsCtrlAndAction ($generalLogsIds, $controller, $action) {
		$db = self::GetConnection();
		$hashes = [];
		foreach ($generalLogsIds as $generalLogId)
			$hashes[] = self::_getHash(intval($generalLogId), $controller, $action);
		$hashesStr = implode("','", $hashes);
		$rawData = $db
			->Prepare(implode("\n", [
				"SELECT 							",
				"	bgp.`id_bg_process` AS `id`,	",
				"	bgp.`id_general_log`,			",
				"	bgp.`hash`,						",
				"	bgp.`progress`,					",
				"	bgp.`controller`,				",
				"	bgp.`action`,					",
				"	bgp.`params`,					",
				"	bgp.`created`,					",
				"	bgp.`started`,					",
				"	bgp.`finished`,					",
				"	bgp.`result`,					",
				"	bgp.`message`					",
				"FROM `bg_processes` bgp			",
				"WHERE								",
				"	bgp.`hash` IN ('{$hashesStr}');	",
			]))
			->Execute()
			->FetchAllToAssocArrays('id_general_log');
		if (!$rawData) return [];
		$result = [];
		foreach ($rawData as $rawItem) {
			/** @var $item \App\Models\BgProcess */
			$item = (new static())->SetUp(
				$rawItem, 
				\MvcCore\IModel::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, 
				TRUE
			);
			$result[$item->GetIdGeneralLog()] = $item;
		}
		return $result;
	}

	/**
	 * @return int
	 */
	public function Save () {
		if ($this->id === NULL) {
			return $this->id = $this->insert();
		} else {
			return $this->update();
		}
	}

	/** @return int */
	protected function update () {
		$updatedRows = 0;
		$touchedProperties = $this->GetTouched(TRUE, FALSE);
		$updateSetItems = [];
		$params = [];
		array_walk($touchedProperties, function ($value, $propKey) use (& $updateSetItems, & $params) {
			if (mb_substr($propKey, 0, 1) === '_' || $propKey === 'id') return;
			$underScoreKey = \MvcCore\Tool::GetUnderscoredFromPascalCase($propKey);
			$updateSetItems[] = "`{$underScoreKey}` = :{$underScoreKey}";
			if ($value instanceof \DateTime) {
				$value->setTimezone(new \DateTimeZone('UTC'));
				$scalarValue = $value->format('Y-m-d H:i:s');
			} else if ($propKey == 'progress') {
				$scalarValue = number_format($value, 2, '.', '');
			} else {
				$scalarValue = $value;
			}
			$params[':' . $underScoreKey] = $scalarValue;
		});
		$params[':id'] = $this->id;
		$setSectionSql = implode(", ", $updateSetItems);
		$updatedRows = self::GetConnection()
			->Prepare(implode("\n", [
				"UPDATE `bg_processes`			",
				"SET {$setSectionSql}			",
				"WHERE `id_bg_process` = :id;	",
			]))
			->Execute($params)
			->RowCount();
		$this->initialValues = array_merge(
			$this->initialValues, $touchedProperties
		);
		return $updatedRows;
	}

	/** @return int|NULL */
	protected function insert () {
		$idBgProcess = NULL;
		$this->hash = self::_getHash($this->idGeneralLog, $this->controller, $this->action);
		$touchedProperties = $this->GetTouched(TRUE, FALSE);
		$insertSqlColumns = [];
		$insertSqlValues = [];
		$insertParams = [];
		array_walk($touchedProperties, function ($value, $propKey) use (& $insertSqlColumns, & $insertSqlValues, & $insertParams) {
			if (mb_substr($propKey, 0, 1) === '_' || $propKey === 'id') return;
			$underScoreKey = \MvcCore\Tool::GetUnderscoredFromPascalCase($propKey);
			$insertSqlColumns[] = "`{$underScoreKey}`";
			$insertSqlValues[] = ":{$underScoreKey}";
			if ($value instanceof \DateTime) {
				$value->setTimezone(new \DateTimeZone('UTC'));
				$scalarValue = $value->format('Y-m-d H:i:s');
			} else if ($propKey == 'progress') {
				$scalarValue = number_format($value, 2, '.', '');
			} else {
				$scalarValue = $value;
			}
			$insertParams[":{$underScoreKey}"] = $scalarValue;
		});
		$insertSql  = "INSERT INTO `bg_processes` (" . implode(', ', $insertSqlColumns) . ") "
					. "VALUES (" . implode(', ', $insertSqlValues) . ");";
		$db = self::GetConnection();
		try {
			$db->BeginTransaction('bg_process_insert', TRUE);
			$db
				->Prepare($insertSql)
				->Execute($insertParams);
			$idBgProcess = $db->LastInsertId('bg_processes', 'int');
			$db->Commit();
			$createdRawValue = $db
				->Prepare(implode("\n", [
					"SELECT `created`				",
					"FROM `bg_processes`		",
					"WHERE `id_bg_process` = :id;	",
				]))
				->Execute([ ':id'	=> $idBgProcess ])
				->FetchOneToScalar('created');
			list($converted, $createdDateTime) = static::convertToType($createdRawValue, 'DateTime');
			if ($converted) $this->created = $createdDateTime;
			$this->initialValues = array_merge($this->initialValues, [
				'id'		=> $idBgProcess,
				'created'	=> $this->created,
				'hash'		=> $this->hash,
			]);
		} catch (\Exception $e) {
			if ($db->InTransaction()) $db->RollBack();
			\MvcCore\Debug::Exception($e);
		}
		return $idBgProcess;
	}

	/**
	 * @return int
	 */
	public function Delete () {
		$deletedRows = 0;
		$db = self::GetConnection();
		try {
			$db->BeginTransaction('bg_process_delete', TRUE);
			$deletedRows = $db
				->Prepare(implode("\n", [
					"DELETE FROM `bg_processes`			",
					"WHERE `id_bg_process` = :id_bg_process;",
				]))
				->Execute([
					":id_bg_process"	=> $this->id,
				])
				->RowCount();
			$db->Commit();
		} catch (\Exception $e) {
			$db->RollBack();
			\MvcCore\Debug::Exception($e);
		}
		return $deletedRows;
	}

	/**
	 * @throws \Exception 
	 * @return bool
	 */
	public function Start () {
		if ($this->id === NULL)
			throw new \Exception("Save background process first.");
		if ($this->started !== NULL)
			throw new \Exception("Background process with id: `{$this->id}` has been started already.");
		$this
			->SetProgress(0.0)
			->SetStarted(new \DateTime('now'))
			->Save();
		$rawStartOutput = \App\Models\Cli::RunScript(
			'BgProcessExec', 
			[$this->id]
		);
		$startOutput = intval($rawStartOutput);
		if ($startOutput === 1) {
			return TRUE;
		} else {
			throw new \Exception(
				"Error when starting background process with id: `{$this->id}`: "
				. $rawStartOutput
			);
		}
	}

	private static function _getHash ($idGeneralLog, $controller, $action) {
		return hash('sha256', serialize([
			'idGeneralLog'	=> $idGeneralLog,
			'controller'	=> $controller,
			'action'		=> $action,
		]));
	}
}