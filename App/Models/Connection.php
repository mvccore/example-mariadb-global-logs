<?php

namespace App\Models;

class Connection extends \App\Models\Base {
	
	/**
	 * @order 0
	 * @var int
	 */
	protected $idConnection;
	/**
	 * @order NULL
	 * @var int
	 */
	protected $idGeneralLog;
	/**
	 * @order NULL
	 * @var int
	 */
	protected $idUser;
	/**
	 * @order 7
	 * @var string
	 */
	protected $user;
	/**
	 * @order NULL
	 * @var int
	 */
	protected $idDatabase;
	/**
	 * @order 8
	 * @var string
	 */
	protected $database;
	/**
	 * @order 2
	 * @var int
	 */
	protected $idThread;
	/**
	 * @order 3
	 * @var \DateTime
	 */
	protected $connected;
	/**
	 * @order 4
	 * @var \DateTime
	 */
	protected $disconnected;
	/**
	 * @order 5
	 * @var int
	 */
	protected $requestsCount;
	/**
	 * @order 6
	 * @var int
	 * */
	protected $queriesCount;
	/**
	 * @order 1
	 * @var int
	 */
	protected $mark;
	/**
	 * @order NULL
	 * @var int
	 * */
	protected $selectsCount;
	/**
	 * @order NULL
	 * @var int
	 * */
	protected $insertsCount;
	/**
	 * @order NULL
	 * @var int
	 * */
	protected $updatesCount;
	/**
	 * @order NULL
	 * @var int
	 * */
	protected $deletesCount;


	/** @var \string[]|NULL */
	protected static $orderFields = NULL;


	/** @return int */
	public function GetIdConnection(){
		return $this->idConnection;
	}
	/**
	 * @param int $idConnection 
	 * @return \App\Models\Connection
	 */
	public function SetIdConnection($idConnection){
		$this->idConnection = $idConnection;
		return $this;
	}
	/** @return int */
	public function GetIdGeneralLog(){
		return $this->idGeneralLog;
	}
	/**
	 * @param int $idGeneralLog 
	 * @return \App\Models\Connection
	 */
	public function SetIdGeneralLog($idGeneralLog){
		$this->idGeneralLog = $idGeneralLog;
		return $this;
	}
	/** @return int */
	public function GetIdUser(){
		return $this->idUser;
	}
	/**
	 * @param int $idUser 
	 * @return \App\Models\Connection
	 */
	public function SetIdUser($idUser){
		$this->idUser = $idUser;
		return $this;
	}
	/** @return string */
	public function GetUser(){
		return $this->user;
	}
	/**
	 * @param string $user 
	 * @return \App\Models\Connection
	 */
	public function SetUser($user){
		$this->user = $user;
		return $this;
	}
	/** @return int */
	public function GetIdDatabase(){
		return $this->idDatabase;
	}
	/**
	 * @param int $idDatabase
	 * @return \App\Models\Connection
	 */
	public function SetIdDatabase($idDatabase){
		$this->idDatabase = $idDatabase;
		return $this;
	}
	/** @return string */
	public function GetDatabase(){
		return $this->database;
	}
	/**
	 * @param string $database 
	 * @return \App\Models\Connection
	 */
	public function SetDatabase($database){
		$this->database = $database;
		return $this;
	}
	/** @return int */
	public function GetIdThread(){
		return $this->idThread;
	}
	/**
	 * @param int $idThread 
	 * @return \App\Models\Connection
	 */
	public function SetIdThread($idThread){
		$this->idThread = $idThread;
		return $this;
	}
	/** @return \DateTime|NULL */
	public function GetConnected(){
		return $this->connected;
	}
	/**
	 * @param \DateTime|NULL $connected 
	 * @return \App\Models\Connection
	 */
	public function SetConnected($connected){
		$this->connected = $connected;
		return $this;
	}
	/** @return \DateTime|NULL */
	public function GetDisconnected(){
		return $this->disconnected;
	}
	/**
	 * @param \DateTime|NULL $connected 
	 * @return \App\Models\Connection
	 */
	public function SetDisconnected($disconnected){
		$this->disconnected = $disconnected;
		return $this;
	}
	/** @return int */
	public function GetRequestsCount(){
		return $this->requestsCount;
	}
	/**
	 * @param int $requestsCount 
	 * @return \App\Models\Connection
	 */
	public function SetRequestsCount($requestsCount){
		$this->requestsCount = $requestsCount;
		return $this;
	}
	/** @return int */
	public function GetQueriesCount(){
		return $this->queriesCount;
	}
	/**
	 * @param int $queriesCount 
	 * @return \App\Models\Connection
	 */
	public function SetQueriesCount($queriesCount){
		$this->queriesCount = $queriesCount;
		return $this;
	}
	/** @return int */
	public function GetMark(){
		return $this->mark;
	}
	/**
	 * @param int $mark 
	 * @return \App\Models\Connection
	 */
	public function SetMark($mark){
		$this->mark = $mark;
		return $this;
	}
	/** @return int */
	public function GetSelectsCount() {
		return $this->selectsCount;	
	}
	/** @return int */
	public function GetInsertsCount() {
		return $this->insertsCount;	
	}
	/** @return int */
	public function GetUpdatesCount() {
		return $this->updatesCount;	
	}
	/** @return int */
	public function GetDeletesCount() {
		return $this->deletesCount;	
	}

	/** @return \string[] */
	public static function GetOrderFields () {
		if (self::$orderFields === NULL) {
			/** @var $props \ReflectionProperty[] */
			$props = (new \ReflectionClass(\App\Models\Connection::class))
				->getProperties(\ReflectionProperty::IS_PROTECTED);
			$orderFields = [];
			$currentClass = get_called_class();
			foreach ($props as $prop) {
				if (
					$prop->isStatic() ||
					$prop->getDeclaringClass()->getName() !== $currentClass
				) continue;
				preg_match_all("#@order ([^\n]+)\n#", $prop->getDocComment(), $matches);
				if ($matches && $matches[1]) {
					$rawSequence = mb_strtolower(trim($matches[1][0]));
					if (is_numeric($rawSequence)) {
						$sequence = intval($rawSequence);
						$orderFields[$sequence] = $prop->getName();
					}
				} else {
					$orderFields[] = $prop->getName();
				}
			}
			ksort($orderFields);
			foreach ($orderFields as $orderField) {
				$orderFieldDbKey = \MvcCore\Tool::GetUnderscoredFromPascalCase(
					$orderField
				);
				self::$orderFields[$orderFieldDbKey] = str_replace('_',' ',$orderFieldDbKey);
			}
		}
		return self::$orderFields;
	}

	public static function GetList (
		$idGeneralLog,
		$orderField = 'connected', $direction = 'asc', 
		$offset = 0, $limit = 100
	) {
		$params = [
			':id_gen_log1' => $idGeneralLog,
			':id_gen_log2' => $idGeneralLog,
			':id_gen_log3' => $idGeneralLog,
			':id_gen_log4' => $idGeneralLog,
			':id_gen_log5' => $idGeneralLog,
		];
		$direction = strtoupper($direction);
		$sql = implode("\n", [
			"SELECT														",
			"	c.*,													",
			"	d.`database_name` AS `database`,						",
			"	u.`user_name` AS `user`,								",
			"	IFNULL(selects.`selects_count`, 0) AS `selects_count`,	",
			"	IFNULL(inserts.`inserts_count`, 0) AS `inserts_count`,	",
			"	IFNULL(updates.`updates_count`, 0) AS `updates_count`,	",
			"	IFNULL(deletes.`deletes_count`, 0) AS `deletes_count`	",
			"FROM `connections` c										",
			"LEFT JOIN `databases` d ON									",
			"	d.`id_database` = c.`id_database`						",
			"LEFT JOIN `users` u ON										",
			"	u.`id_user` = c.`id_user`								",
			"															",
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `selects_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		WHERE	c.`id_general_log` = :id_gen_log1			",
			"		ORDER BY `{$orderField}` {$direction}				",
			"		LIMIT {$offset}, {$limit}							",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = 1								",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") selects ON												",
			"	selects.`id_connection` = c.`id_connection`				",
			"															",
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `inserts_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		WHERE	c.`id_general_log` = :id_gen_log2			",
			"		ORDER BY `{$orderField}` {$direction}				",
			"		LIMIT {$offset}, {$limit}							",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = 10								",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") inserts ON												",
			"	inserts.`id_connection` = c.`id_connection`				",
			"															",
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `updates_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		WHERE	c.`id_general_log` = :id_gen_log3			",
			"		ORDER BY `{$orderField}` {$direction}				",
			"		LIMIT {$offset}, {$limit}							",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = 11								",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") updates ON												",
			"	updates.`id_connection` = c.`id_connection`				",
			"															",
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `deletes_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		WHERE	c.`id_general_log` = :id_gen_log4			",
			"		ORDER BY `{$orderField}` {$direction}				",
			"		LIMIT {$offset}, {$limit}							",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = 12								",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") deletes ON												",
			"	deletes.`id_connection` = c.`id_connection`				",
			"															",
			"WHERE														",
			"	c.`id_general_log` = :id_gen_log5						",
			"ORDER BY `{$orderField}` {$direction}						",
			"LIMIT {$offset}, {$limit};									",
		]);

		return self::GetConnection()
			->Prepare($sql)
			->Execute($params)
			->FetchAllToInstances(
				get_called_class(),
				self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, 
				FALSE
			);
	}
	
	public static function GetCount (
		$idGeneralLog,
		$offset = 0, $limit = 100
	) {
		$params = [':id_gen_log' => $idGeneralLog];
		return self::GetConnection()
			->Prepare(implode("\n", [
				"SELECT	COUNT(c.`id_connection`) 		",
				"FROM `connections` c					",
				"WHERE c.`id_general_log` = :id_gen_log;",
			]))
			->Execute($params)
			->FetchColumn(0, 'int');
	}

	/**
	 * @param int $idConnection 
	 * @return \App\Models\Connection|\MvcCore\Model
	 */
	public static function GetById ($idConnection) {
		return self::GetConnection()
			->Prepare(implode("\n", [
				"SELECT									",
				"	c.*,								",
				"	d.`database_name` AS `database`,	",
				"	u.`user_name` AS `user`				",
				"FROM `connections` c					",
				"LEFT JOIN `databases` d ON				",
				"	d.`id_database` = c.`id_database`	",
				"LEFT JOIN `users` u ON					",
				"	u.`id_user` = c.`id_user`			",
				"WHERE									",
				"	c.`id_connection` = :id_conn;		",
			]))
			->Execute([':id_conn' => $idConnection])
			->FetchOneToInstance(
				get_called_class(),
				self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE, 
				TRUE
			);
	}

	/**
	 * @return int
	 */
	public function Save () {
		if ($this->idConnection === NULL) {
			return $this->idConnection = $this->insert();
		} else {
			return $this->update();
		}
	}

	/** @return int */
	protected function update () {
		$updatedRows = 0;
		$touchedProperties = $this->GetTouched(FALSE, FALSE);
		if (count($touchedProperties) === 0) return 0;
		unset($touchedProperties['user'],$touchedProperties['database']);
		$updateSetItems = [];
		$params = [];
		array_walk($touchedProperties, function ($value, $propKey) use (& $updateSetItems, & $params) {
			if (mb_substr($propKey, 0, 1) === '_' || $propKey === 'idConnection') return;
			$underScoreKey = \MvcCore\Tool::GetUnderscoredFromPascalCase($propKey);
			$updateSetItems[] = "`{$underScoreKey}` = :{$underScoreKey}";
			if ($value instanceof \DateTime) {
				$value->setTimezone(new \DateTimeZone('UTC'));
				$scalarValue = $value->format('Y-m-d H:i:s');
			} else {
				$scalarValue = $value;
			}
			$params[':' . $underScoreKey] = $scalarValue;
		});
		$params[':id'] = $this->idConnection;
		$setSectionSql = implode(", ", $updateSetItems);
		$updatedRows = self::GetConnection()
			->Prepare(implode("\n", [
				"UPDATE `connections`			",
				"SET {$setSectionSql}			",
				"WHERE `id_connection` = :id;	",
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
		$idConnection = NULL;
		$touchedProperties = $this->GetTouched(FALSE, FALSE);
		unset($touchedProperties['user'],$touchedProperties['database']);
		$insertSqlColumns = [];
		$insertSqlValues = [];
		$insertParams = [];
		array_walk($touchedProperties, function ($value, $propKey) use (& $insertSqlColumns, & $insertSqlValues, & $insertParams) {
			if (mb_substr($propKey, 0, 1) === '_' || $propKey === 'idConnection') return;
			$underScoreKey = \MvcCore\Tool::GetUnderscoredFromPascalCase($propKey);
			$insertSqlColumns[] = "`{$underScoreKey}`";
			$insertSqlValues[] = ":{$underScoreKey}";
			if ($value instanceof \DateTime) {
				$value->setTimezone(new \DateTimeZone('UTC'));
				$scalarValue = $value->format('Y-m-d H:i:s');
			} else {
				$scalarValue = $value;
			}
			$insertParams[":{$underScoreKey}"] = $scalarValue;
		});
		$insertSql  = "INSERT INTO `connections` (" . implode(', ', $insertSqlColumns) . ") "
					. "VALUES (" . implode(', ', $insertSqlValues) . ");";
		$db = self::GetConnection();
		try {
			$db->BeginTransaction('connection_insert', TRUE);
			$db
				->Prepare($insertSql)
				->Execute($insertParams);
			$idConnection = $db->LastInsertId('connections', 'int');
			$db->Commit();
			$this->initialValues = array_merge($this->initialValues, [
				'idConnection' => $idConnection,
			]);
		} catch (\Exception $e) {
			if ($db->InTransaction()) $db->RollBack();
			\MvcCore\Debug::Exception($e);
		}
		return $idConnection;
	}

	/** @return \App\Models\LogFile */
	public function GetGeneralLog () {
		return \App\Models\LogFile::GetById($this->idGeneralLog);
	}

	public function GetGroupedQueries () {
		$db = self::GetConnection();
		$params = [':id_conn' => $this->idConnection];
		/** @var $rawQueries \App\Models\Query[] */
		$rawQueries = $db
			->Prepare(implode("\n", [
				"SELECT										",
				"	q.*,									",
				"	t.`query_type_name`						",
				"FROM `queries` q							",
				"LEFT JOIN `query_types` t ON				",
				"	t.`id_query_type` = q.`id_query_type`	",
				"WHERE	q.`id_connection` = :id_conn		",
				"ORDER BY q.`id_query` ASC;					",
			]))
			->Execute($params)
			->FetchAllToInstances(
				\App\Models\Query::class,
				self::KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE,
				FALSE
			);
		$result = [];
		foreach ($rawQueries as $rawQuery) {
			$executed = $rawQuery->GetExecuted()->format('Y-m-d H:i:s');
			$requestNum = $rawQuery->GetRequestNumber();
			if (!isset($result[$executed]))
				$result[$executed] = [];
			if (!isset($result[$executed][$requestNum]))
				$result[$executed][$requestNum] = [];
			$items = & $result[$executed][$requestNum];
			$items[] = $rawQuery;
		}
		return $result;
	}
}