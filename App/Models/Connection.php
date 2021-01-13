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
				self::$orderFields[$orderFieldDbKey] = str_replace(
					'_',' ',$orderFieldDbKey
				);
			}
		}
		return self::$orderFields;
	}

	/**
	 * @param int $idGeneralLog 
	 * @param string $orderField 
	 * @param string $direction 
	 * @param int $offset 
	 * @param int $limit 
	 * @return \MvcCore\Ext\Models\Db\Readers\Streams\Iterator
	 */
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
			->StreamAll($params)
			->ToInstances(
				get_called_class(),
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE
			);
	}
	
	/**
	 * @param int $idGeneralLog 
	 * @param int $offset 
	 * @param int $limit 
	 * @return int
	 */
	public static function GetCount (
		$idGeneralLog,
		$offset = 0, $limit = 100
	) {
		return self::GetConnection()
			->Prepare([
				"SELECT	COUNT(c.`id_connection`) AS cnt	",
				"FROM `connections` c					",
				"WHERE c.`id_general_log` = :id_gen_log;",
			])
			->FetchOne([':id_gen_log' => $idGeneralLog])
			->ToScalar('cnt', 'int');
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
			->FetchOne([':id_conn' => $idConnection])
			->ToInstance(
				get_called_class(),
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE |
				self::PROPS_INITIAL_VALUES
			);
	}

	/**
	 * @return bool
	 */
	public function Save ($createNew = NULL, $flags = 0) {
		if ($createNew || $this->idConnection === NULL) {
			return $this->Insert($flags);
		} else {
			return $this->Update($flags);
		}
	}
	
	/** @return bool */
	public function Insert ($flags = 0) {
		$result = TRUE;
		$data = $this->GetValues(
			self::PROPS_PROTECTED |
			self::PROPS_CONVERT_CAMELCASE_TO_UNDERSCORES
		);
		unset($data['user'], $data['database']);
		
		$params = [];
		$sqlItems = [];
		foreach ($data as $columnName => $value) {
			if (mb_substr($columnName, 0, 1) === '_' || $columnName === 'idConnection') continue;
			$params[":{$columnName}"] = self::convertToScalar($value);
			$sqlItems[] = "`{$columnName}`";
		}

		$db = self::GetConnection();
		try {
			$db->BeginTransaction(
				self::TRANS_ISOLATION_REPEATABLE_READ |
				self::TRANS_READ_WRITE,
				'connection_insert'
			);
			$result = $db
				->Prepare([
					"INSERT INTO `connections` (		",
					implode(', ', $sqlItems)."			",
					") VALUES (							",
					implode(",",array_keys($params))."	",
					");									",
				])
				->Execute($params)
				->GetExecResult();
			$this->idConnection = $db->LastInsertId('connections', 'int');
			$db->Commit();
			$this->initialValues = array_merge([], $this->initialValues, [
				'idConnection' => $this->idConnection,
			]);
		} catch (\Exception $e) {
			if ($db->InTransaction()) $db->RollBack();
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
		unset($data['user'],$data['database']);
		if (count($data) === 0) 
			return FALSE;
		
		$params = [];
		$colsSql = [];
		foreach ($data as $columnName => $value) {
			if (mb_substr($columnName, 0, 1) === '_' || $columnName === 'idConnection') continue;
			$params[":{$columnName}"] = self::convertToScalar($value);
			$colsSql[] = "`{$columnName}` = :{$columnName}";
		};
		$params[':id'] = $this->idConnection;

		$result = self::GetConnection()
			->Prepare([
				"UPDATE `connections`			",
				"SET " . implode(", ", $colsSql),
				"WHERE `id_connection` = :id;	",
			])
			->Execute($params)
			->GetExecResult();

		$this->initialValues = array_merge([], $this->initialValues, $data);

		return $result;
	}

	/** @return \App\Models\LogFile */
	public function GetGeneralLog () {
		return \App\Models\LogFile::GetById($this->idGeneralLog);
	}

	/**
	 * @return \MvcCore\Ext\Models\Db\Readers\Streams\Iterator
	 */
	public function GetQueriesStream () {
		/** @var $rawQueries \App\Models\Query[] */
		return self::GetConnection()
			->Prepare([
				"SELECT										",
				"	q.*,									",
				"	t.`query_type_name`						",
				"FROM `queries` q							",
				"LEFT JOIN `query_types` t ON				",
				"	t.`id_query_type` = q.`id_query_type`	",
				"WHERE	q.`id_connection` = :id_conn		",
				"ORDER BY									",
				"	q.`id_query` ASC,						",
				"	q.`request_number` ASC,					",
				"	q.`executed` ASC;						",
			])
			->StreamAll([':id_conn' => $this->idConnection])
			->ToInstances(
				get_class(new \App\Models\Query),
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE
			);
	}
}