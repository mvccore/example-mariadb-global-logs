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
		return self::GetConnection()
			->Prepare([
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
			])
			->FetchOne([
				':id'	=> $id
			])
			->ToInstance(
				get_called_class(),
				self::PROPS_INHERIT |
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE |
				self::PROPS_INITIAL_VALUES
			);
	}
	
	/**
	 * @param int $idGeneralLog 
	 * @param string $controller 
	 * @param string $controller 
	 * @return \App\Models\BgProcess|NULL
	 */
	public static function GetByLogIdCtrlAndAction ($idGeneralLog, $controller, $action) {
		return self::GetConnection()
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
			->FetchOne([
				':hash' => self::_getHash($idGeneralLog, $controller, $action),
			])
			->ToInstance(
				get_called_class(),
				self::PROPS_INHERIT |
				self::PROPS_PROTECTED | 
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE |
				self::PROPS_INITIAL_VALUES
			);
	}

	/**
	 * @param \int[]|\string[] $generalLogsIds
	 * @param string $controller 
	 * @param string $controller 
	 * @return \App\Models\BgProcess[] Keys are general log ids.
	 */
	public static function GetByLogsIdsCtrlAndAction ($generalLogsIds, $controller, $action) {
		$hashes = [];
		foreach ($generalLogsIds as $generalLogId)
			$hashes[] = self::_getHash(intval($generalLogId), $controller, $action);
		$hashesStr = implode("','", $hashes);
		return self::GetConnection()
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
			->FetchAll()
			->ToInstances(
				get_called_class(),
				self::PROPS_INHERIT |
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE,
				'id_general_log',
				'int'
			);
	}

	/**
	 * @return bool
	 */
	public function Save ($createNew = null, $flags = 0) {
		if ($createNew || $this->id === NULL) {
			return $this->Insert($flags);
		} else {
			return $this->Update($flags);
		}
	}

	/** @return bool */
	public function Insert ($flags = 0) {
		$result = TRUE;
		$this->hash = self::_getHash($this->idGeneralLog, $this->controller, $this->action);
		
		$data = $this->GetValues(
			self::PROPS_INHERIT |
			self::PROPS_PROTECTED |
			self::PROPS_CONVERT_CAMELCASE_TO_UNDERSCORES |
			self::PROPS_GET_SCALAR_VALUES
		);

		$params = [];
		$sqlItems = [];
		foreach ($data as $columnName => $scalarValue) {
			if (mb_substr($columnName, 0, 1) === '_' || $columnName === 'id') continue;
			if ($columnName == 'progress') {
				$scalarValue = number_format($scalarValue, 2, '.', '');
			} else {
				$scalarValue = $scalarValue;
			}
			$params[":{$columnName}"] = $scalarValue;
			$sqlItems[] = "`{$columnName}`";
		}

		$db = self::GetConnection();
		try {
			$db->BeginTransaction(
				self::TRANS_ISOLATION_REPEATABLE_READ |
				self::TRANS_READ_WRITE,
				'bg_process_insert'
			);
			$result = $db
				->Prepare([
					"INSERT INTO `bg_processes`	(		",
					implode(",",$sqlItems)."			",
					") VALUES (							",
					implode(",",array_keys($params))."	",
					");									",
				])
				->Execute($params)
				->GetExecResult();
			$this->id = $db->LastInsertId('bg_processes', 'int');
			$db->Commit();
			$createdRawValue = $db
				->Prepare([
					"SELECT `created`				",
					"FROM `bg_processes`		",
					"WHERE `id_bg_process` = :id;	",
				])
				->FetchOne([':id'	=> $this->id])
				->ToScalar('created');
			$createdDateTime = static::parseToDateTime('DateTime', $createdRawValue, ['Y-m-d H:i:s', 'UTC']);
			if ($createdDateTime !== FALSE) 
				$this->created = $createdDateTime;
			$this->initialValues = array_merge([], $this->initialValues, [
				'id'		=> $this->id,
				'created'	=> $this->created,
				'hash'		=> $this->hash,
			]);
		} catch (\Throwable $e) {
			if ($db->InTransaction()) $db->RollBack();
			\MvcCore\Debug::Exception($e);
			$result = FALSE;
		}
		return $result;
	}
	
	/** @return bool */
	public function Update ($flags = 0) {
		$data = $this->GetTouched(
			self::PROPS_INHERIT |
			self::PROPS_PROTECTED |
			self::PROPS_CONVERT_CAMELCASE_TO_UNDERSCORES |
			self::PROPS_GET_SCALAR_VALUES
		);
		if (count($data) === 0) 
			return FALSE;

		$params = [];
		$colsSql = [];
		foreach ($data as $columnName => $scalarValue) {
			if (mb_substr($columnName, 0, 1) === '_' || $columnName === 'id') continue;
			$params[":{$columnName}"] = $scalarValue;
			$colsSql[] = "`{$columnName}` = :{$columnName}";
		};
		$params[':id'] = $this->id;

		$result = self::GetConnection()
			->Prepare([
				"UPDATE `bg_processes`			",
				"SET " . implode(", ", $colsSql),
				"WHERE `id_bg_process` = :id;	",
			])
			->Execute($params)
			->GetExecResult();

		$this->initialValues = array_merge([], $this->initialValues, $data);

		return $result;
	}

	/**
	 * @return bool
	 */
	public function Delete ($flags = 0) {
		return self::GetConnection()
			->Prepare([
				"DELETE FROM `bg_processes`			",
				"WHERE `id_bg_process` = :id_bg_process;",
			])
			->Execute([
				":id_bg_process"	=> $this->id,
			])
			->GetExecResult();
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