<?php

namespace App\Models\BgProcesses\LogFiles;

class Processing extends \App\Models\Base
{
	/** @var int */
	protected $idGeneralLog;
	/** @var int */
	protected $linesCount;
	/** @var int */
	protected $bgProcessId;
	/** @var int */
	protected $currentLine;
	/** @var int */
	protected $queriesLimitToFlush;

	/** @var \int[] Keys are string values, values are numeric database ids. */
	protected $databases = [];
	/** @var \int[] Keys are string values, values are numeric database ids. */
	protected $users = [];
	/** @var \int[] Keys are string values, values are numeric database ids. */
	protected $queryTypes = [];

	/** @var \int[] Keys are thread ids ints, values are connection ids ints. */
	protected $threadIs2ConnIds = [];
	/** @var \int[] Keys are thread ids ints, values are request counts ints. */
	protected $connIs2ReqNumbers = [];
	/** @var \int[] Keys are thread ids ints, values are queries counts ints. */
	protected $connIds2QueriesCounts = [];

	/** @var \bool[] Keys are connection ids ints, values are booleans. */
	protected $connIds2UpdateCounts = [];
	/** @var \string[] Keys are connection ids ints, values are strings. */
	protected $connIds2UpdateDisconns = [];
	/** @var array Values are queries table data to insert */
	protected $queries = [];
	/** @var int Data length to flush. */
	protected $queriesCount = 0;

	/**
	 * @param int $idGeneralLog 
	 * @param int $linesCount
	 * @param int $bgProcessId
	 * @param int $queriesCountToFlush
	 */
	public function __construct ($idGeneralLog, $linesCount, $bgProcessId, $queriesCountToFlush = 10) {
		$this->idGeneralLog = $idGeneralLog;
		$this->linesCount = $linesCount;
		$this->bgProcessId = $bgProcessId;
		$this->currentLine = 0;
		$this->queriesLimitToFlush = $queriesCountToFlush - 1;
	}

	/**
	 * @return void
	 */
	public function LoadAllCollections () {
		$db = self::GetConnection();
		$this->databases = $db
			->Prepare(implode("\n", [
				"SELECT					",
				"	d.`id_database`,	",
				"	d.`database_name`	",
				"FROM `databases` d		",
				"ORDER BY				",
				"	d.`id_database` ASC;",
			]))
			->Execute()
			->FetchAllToScalars(
				'database_name', 'id_database', NULL, 'int'
			);
		$this->users = $db
			->Prepare(implode("\n", [
				"SELECT				",
				"	u.`id_user`,	",
				"	u.`user_name`	",
				"FROM `users` u		",
				"ORDER BY			",
				"	u.`id_user` ASC;",
			]))
			->Execute()
			->FetchAllToScalars(
				'user_name', 'id_user', NULL, 'int'
			);
		$this->queryTypes = $db
			->Prepare(implode("\n", [
				"SELECT						",
				"	qt.`id_query_type`,		",
				"	qt.`query_type_name`	",
				"FROM `query_types` qt		",
				"ORDER BY					",
				"	qt.`id_query_type` ASC;	",
			]))
			->Execute()
			->FetchAllToScalars(
				'query_type_name', 'id_query_type', NULL, 'int'
			);
	}

	/**
	 * @param int $currentLine 
	 * @return \App\Models\BgProcesses\LogFiles\Processing
	 */
	public function SetCurrentLine ($currentLine) {
		$this->currentLine = $currentLine;
		return $this;
	}

	/**
	 * @param string|NULL $user 
	 * @param string|NULL $database 
	 * @param int $idThread 
	 * @param string $dateTimeStr
	 * @return void
	 */
	public function AddConnection ($user, $database, $idThread, $dateTimeStr) {
		$idUser = $user !== NULL
			? $this->getIdUser($user)
			: NULL;
		$idDatabase = $database !== NULL
			? $this->getIdDatabase($database)
			: NULL;
		$db = self::GetConnection();
		$db->BeginTransaction(
			'connection_insert', TRUE,
			\MvcCore\Ext\DbConnection::ISOLATION_LEVEL_REPEATABLE_READ
		);
		$db
			->Prepare(implode("\n", [
				"INSERT INTO `connections` (	",
				"	`id_general_log`, `id_user`,",
				"	`id_database`, `id_thread`,	",
				"	`connected`				",
				") VALUES (						",
				"	:id_general_log, :id_user,	",
				"	:id_database, :id_thread,	",
				"	:connected					",
				");								",
			]))
			->Execute([
				":id_general_log"	=> $this->idGeneralLog,
				":id_user"			=> $idUser,
				":id_database"		=> $idDatabase,
				":id_thread"		=> $idThread,
				":connected"		=> $dateTimeStr,
			]);
		$idConn = $db->LastInsertId('connections', 'int');
		$db->Commit();
		$this->threadIs2ConnIds[$idThread] = $idConn;
	}

	/**
	 * @param int $idThread 
	 * @param string $dateTimeStr 
	 * @return void
	 */
	public function AddDisconnection($idThread, $dateTimeStr) {
		$connId = $this->threadIs2ConnIds[$idThread];
		$this->connIds2UpdateDisconns[$connId] = $dateTimeStr;
	}

	/**
	 * @param int $idThread 
	 * @return void
	 */
	public function AddConnectionRequestCount ($idThread) {
		$connId = $this->threadIs2ConnIds[$idThread];
		if (!isset($this->connIs2ReqNumbers[$connId])) {
			$this->connIs2ReqNumbers[$connId] = 1;
		} else {
			$this->connIs2ReqNumbers[$connId] += 1;
		}
		$this->connIds2UpdateCounts[$connId] = TRUE;
	}

	/**
	 * @param int $idThread 
	 * @param string $dateTimeStr 
	 * @param int $srcLineBegin 
	 * @param int $srcLineEnd 
	 * @param string $queryStr 
	 * @return void
	 */
	public function AddQuery ($idThread, $dateTimeStr, $srcLineBegin, $srcLineEnd, $queryStr) {
		$connId = $this->threadIs2ConnIds[$idThread];
		if (!isset($this->connIds2QueriesCounts[$connId])) {
			$this->connIds2QueriesCounts[$connId] = 1;
		} else {
			$this->connIds2QueriesCounts[$connId] += 1;
		}
		$this->connIds2UpdateCounts[$connId] = TRUE;

		$requestNumber = $this->connIs2ReqNumbers[$connId];
		$idQueryType = $this->recognizeQueryType($queryStr);
		$this->queries[] = [
			$connId, $idQueryType, $requestNumber, $dateTimeStr, $srcLineBegin, $srcLineEnd, $queryStr
		];

		$this->queriesCount += 1;
		if ($this->queriesCount === $this->queriesLimitToFlush) 
			$this->FlushData();
	}

	/**
	 * Execute connection counts updates, 
	 * execute connections disconnect datetimes updates and
	 * excute queries inserts.
	 * @return void
	 */
	public function FlushData () {
		$db = self::GetConnection();

		// Update connection table with new requests and queries counts:
		if (count($this->connIds2UpdateCounts) > 0) {
			$countsUpdatesSql = [];
			$countsUpdatesParams = [];
			foreach (array_keys($this->connIds2UpdateCounts) as $i => $connId) {
				$countsUpdatesSql[] = implode("\n", [
					"UPDATE `connections`					",
					"SET									",
					"	`requests_count` = :req_cnt{$i},	",
					"	`queries_count` = :query_cnt{$i}	",
					"WHERE `id_connection` = :id_conn{$i};	",
				]);
				$countsUpdatesParams[":req_cnt{$i}"] = $this->connIs2ReqNumbers[$connId];
				$countsUpdatesParams[":query_cnt{$i}"] = $this->connIds2QueriesCounts[$connId];
				$countsUpdatesParams[":id_conn{$i}"] = $connId;
			}
			$db
				->Prepare(implode("\n", $countsUpdatesSql))
				->Execute($countsUpdatesParams)
				->RowCount();
		}

		// Update connection table with possible disconnections:
		if (count($this->connIds2UpdateDisconns) > 0) {
			$disConnUpdatesSql = [];
			$disConnUpdatesParams = [];
			foreach (array_keys($this->connIds2UpdateDisconns) as $i => $connId) {
				$disConnUpdatesSql[] = implode("\n", [
					"UPDATE `connections`					",
					"SET `disconnected` = :disconn{$i}		",
					"WHERE `id_connection` = :id_conn{$i};	",
				]);
				$disConnUpdatesParams[":disconn{$i}"] = $this->connIds2UpdateDisconns[$connId];
				$disConnUpdatesParams[":id_conn{$i}"] = $connId;
			}
			$db
				->Prepare(implode("\n", $disConnUpdatesSql))
				->Execute($disConnUpdatesParams)
				->RowCount();
		}

		// Insert new qieries:
		if (count($this->queries) > 0) {
			$insertQueriesSql = [];
			$insertQueriesParams = [];
			foreach ($this->queries as $i => $queryData) {
				list(
					$connId, $idQueryType, $requestNumber, 
					$dateTimeStr, $srcLineBegin, $srcLineEnd, $queryStr
				) = $queryData;
				
				$insertQueriesSql[] = implode("\n", [
					"INSERT INTO `queries` (					",
					"	`id_connection`, `id_query_type`,		",
					"	`request_number`, `executed`,			",
					"	`source_line_begin`,					",
					"	`source_line_end`, `query_text`			",
					") VALUES (									",
					"	:id_connection{$i}, :id_query_type{$i},	",
					"	:request_number{$i}, :executed{$i},		",
					"	:source_line_begin{$i},					",
					"	:source_line_end{$i}, :query_text{$i}	",
					");											",
				]);
				$insertQueriesParams[":id_connection{$i}"]		= $connId;
				$insertQueriesParams[":id_query_type{$i}"]		= $idQueryType;
				$insertQueriesParams[":request_number{$i}"]		= $requestNumber;
				$insertQueriesParams[":executed{$i}"]			= $dateTimeStr;
				$insertQueriesParams[":source_line_begin{$i}"]	= $srcLineBegin;
				$insertQueriesParams[":source_line_end{$i}"]	= $srcLineEnd;
				$insertQueriesParams[":query_text{$i}"]			= $queryStr;
			}
			$db
				->Prepare(implode("\n", $insertQueriesSql))
				->Execute($insertQueriesParams)
				->RowCount();
		}

		// Update progress:
		$progress = floatval($this->currentLine) / floatval($this->linesCount) * 100.0;
		$db
			->Prepare(implode("\n", [
				"UPDATE `bg_processes`		",
				"SET `progress` = :val		",
				"WHERE `id_bg_process` = :id;",

			]))
			->Execute([
				':val'	=> number_format($progress, 6, '.', ''),
				':id'	=> $this->bgProcessId,
			])
			->RowCount();

		$this->connIds2UpdateCounts = [];
		$this->connIds2UpdateDisconns = [];
		$this->queries = [];
		$this->queriesCount = 0;
	}

	/**
	 * @param string $user 
	 * @return int
	 */
	protected function getIdUser ($user) {
		if (isset($this->users[$user]))
			return $this->users[$user];
		$db = self::GetConnection();
		$db->BeginTransaction(
			'user_insert', TRUE, 
			\MvcCore\Ext\DbConnection::ISOLATION_LEVEL_SERIALIZABLE
		);
		$db
			->Prepare(implode("\n", [
				"INSERT INTO `users` (`user_name`)	",
				"VALUES (:user_name);				",
			]))
			->Execute([':user_name' => $user]);
		$idUser = $db->LastInsertId('users', 'int');
		$db->Commit();
		$this->users[$user] = $idUser;
		return $idUser;
	}

	/**
	 * @param string $database 
	 * @return int
	 */
	protected function getIdDatabase ($database) {
		if (isset($this->databases[$database]))
			return $this->databases[$database];
		$db = self::GetConnection();
		$db->BeginTransaction(
			'database_insert', TRUE, 
			\MvcCore\Ext\DbConnection::ISOLATION_LEVEL_SERIALIZABLE
		);
		$db
			->Prepare(implode("\n", [
				"INSERT INTO `databases` (`database_name`)	",
				"VALUES (:database_name);					",
			]))
			->Execute([':database_name' => $database]);
		$idDatabase = $db->LastInsertId('databases', 'int');
		$db->Commit();
		$this->databases[$database] = $idDatabase;
		return $idDatabase;
	}

	/**
	 * @param string $queryType 
	 * @return int
	 */
	protected function getIdQueryType ($queryType) {
		if (isset($this->queryTypes[$queryType]))
			return $this->queryTypes[$queryType];
		$db = self::GetConnection();
		$db->BeginTransaction(
			'query_type_insert', TRUE,
			\MvcCore\Ext\DbConnection::ISOLATION_LEVEL_SERIALIZABLE
		);
		$db
			->Prepare(implode("\n", [
				"INSERT INTO `query_types` (`query_type_name`)	",
				"VALUES (:query_type_name);						",
			]))
			->Execute([':query_type_name' => $queryType]);
		$idQueryType = $db->LastInsertId('query_types', 'int');
		$db->Commit();
		$this->queryTypes[$queryType] = $idQueryType;
		return $idQueryType;
	}

	
	/**
	 * @param string $queryString 
	 * @return int|NULL
	 */
	protected function recognizeQueryType ($queryString) {
		$queryStringTrimmed = trim(\Libs\Sql::RemoveComments($queryString), "; \t\n\r\0\x0B");
		preg_match("#\s#", $queryStringTrimmed, $matches, PREG_OFFSET_CAPTURE);
		if ($matches && $matches[0]) {
			$firstWhiteSpacePos = $matches[0][1];
			$firstWord = mb_strtolower(mb_substr($queryStringTrimmed, 0, $firstWhiteSpacePos));
			if ($firstWord === 'null')
				throw new \Exception($queryString);
			if (preg_match("#^([a-z]+)$#", $firstWord)) {
				return $this->getIdQueryType($firstWord);
			} else {
				throw new \Exception($queryString);
			}
		} else {
			$firstWord = mb_strtolower($queryStringTrimmed);
			if ($firstWord === 'null')
				throw new \Exception($queryString);
			if (preg_match("#^([a-z]+)$#", $firstWord)) {
				return $this->getIdQueryType($firstWord);
			} else {
				throw new \Exception($queryString);
			}
		}
	}

}