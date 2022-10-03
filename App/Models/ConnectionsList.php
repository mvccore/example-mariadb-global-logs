<?php

namespace App\Models;

class		ConnectionsList
extends		\App\Models\Base
implements	\MvcCore\Ext\Controllers\DataGrids\Models\IGridModel,
			\MvcCore\Ext\Controllers\DataGrids\Models\IGridColumns {
	
	//use \App\Models\Connection\Props;

	use \MvcCore\Ext\Controllers\DataGrids\Models\GridColumns,
		\MvcCore\Ext\Controllers\DataGrids\Models\GridModel;

	/** @var int */
	protected $idGeneralLog;

	/** @var \MvcCore\Ext\Models\Db\Connections\MySql */
	protected $db;

	
	/** @return int */
	public function GetIdGeneralLog () {
		/** @var \App\Models\Connection $this */
		return $this->idGeneralLog;
	}
	/**
	 * @param  int $idGeneralLog 
	 * @return \App\Models\Connection
	 */
	public function SetIdGeneralLog ($idGeneralLog) {
		/** @var \App\Models\Connection $this */
		$this->idGeneralLog = $idGeneralLog;
		return $this;
	}


	protected function load () {
		if ($this->offset === NULL) $this->offset = 0;
		if ($this->limit === NULL) $this->limit = PHP_INT_MAX;
		
		$this->db = self::GetConnection();

		list ($countSql, $countParams) = $this->completeSqlAndParamsCount();
		list ($pageDataSql, $pageDataParams) = $this->completeSqlAndParamsPageData();

		$this->totalCount = $this->db
			->Prepare($countSql)
			->FetchOne($countParams)
			->ToScalar('total_count', 'int');
		
		$this->pageData = $this->db
			->Prepare($pageDataSql)
			->StreamAll($pageDataParams)
			->ToInstances(
				'\App\Models\Connection',
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE
			);
	}

	protected function completeSqlAndParamsCount () {
		list($sqlConditions, $params) = $this->completeConditionSqlAndParams(1);
		$sql = [
			"SELECT COUNT(								",
			"	c.`id_connection`						",
			") AS `total_count`							",
			"FROM (										",
			"	SELECT									",
			"		c.*,								",
			"		d.`database_name` AS `database`,	",
			"		u.`user_name` AS `user`				",
			"	FROM `connections` c					",
			"	LEFT JOIN `databases` d ON				",
			"		d.`id_database` = c.`id_database`	",
			"	LEFT JOIN `users` u ON					",
			"		u.`id_user` = c.`id_user`			",
			") c										",
			"{$sqlConditions[0]}						",
		];
		return [$sql, $params];
	}

	protected function completeSqlAndParamsPageData () {
		$queryTypes = $this->db
			->Prepare([
				"SELECT 						",
				"	q.`id_query_type`,			",
				"	q.`query_type_name`			",
				"FROM query_types q				",
				"WHERE 							",
				"	q.`query_type_name` IN (	",
				"	'select','insert',			",
				"	'update','delete'			",
				");								",
			])
			->FetchAll()
			->ToScalars(
				'id_query_type', 'int', 
				'query_type_name', 'string'
			);
		
		list($sqlConditions, $params) = $this->completeConditionSqlAndParams(
			1 + count($queryTypes)
		);

		// sorting:
		$sortSql = $this->getSortingSql(TRUE, NULL, $this->db->GetConfig()->driver);

		
		// offset and limit:
		$limitSql = " LIMIT {$this->offset}, {$this->limit} ";

		$selects = isset($queryTypes['select']);
		$inserts = isset($queryTypes['insert']);
		$updates = isset($queryTypes['update']);
		$deletes = isset($queryTypes['delete']);
		
		$selectsColumnSql = $selects ? "IFNULL(selects.`selects_count`, 0)" : "0" ;
		$insertsColumnSql = $inserts ? "IFNULL(inserts.`inserts_count`, 0)" : "0" ;
		$updatesColumnSql = $updates ? "IFNULL(updates.`updates_count`, 0)" : "0" ;
		$deletesColumnSql = $deletes ? "IFNULL(deletes.`deletes_count`, 0)" : "0" ;

		$sql = [
			"SELECT														",
			"	c.*,													",
			"	d.`database_name` AS `database`,						",
			"	u.`user_name` AS `user`,								",
			"	{$selectsColumnSql} AS `selects_count`,					",
			"	{$insertsColumnSql} AS `inserts_count`,					",
			"	{$updatesColumnSql} AS `updates_count`,					",
			"	{$deletesColumnSql} AS `deletes_count`					",

			"FROM `connections` c										",

			"LEFT JOIN `databases` d ON									",
			"	d.`id_database` = c.`id_database`						",
			"LEFT JOIN `users` u ON										",
			"	u.`id_user` = c.`id_user`								",
		];

		if ($selects) {
			$sqlCondition = array_shift($sqlConditions);
			$sql = array_merge($sql, [
				"LEFT JOIN (											",
				"	SELECT 												",
				"		q.`id_connection`,								",
				"		COUNT(q.`id_query_type`) AS `selects_count`		",
				"	FROM `queries` q									",
				"	JOIN (												",
				"		SELECT c.`id_connection`						",
				"		FROM `connections` c							",
				"		{$sqlCondition} {$sortSql} {$limitSql}			",
				"	) conns ON											",
				"		conns.`id_connection` = q.`id_connection` AND	",
				"		q.`id_query_type` = ".$queryTypes['select']."	",
				"	GROUP BY											",
				"		q.`id_connection`								",
				") selects ON											",
				"	selects.`id_connection` = c.`id_connection`			",
			]);
		}
		
		if ($inserts) {
			$sqlCondition = array_shift($sqlConditions);
			$sql = array_merge($sql, [
				"LEFT JOIN (											",
				"	SELECT 												",
				"		q.`id_connection`,								",
				"		COUNT(q.`id_query_type`) AS `inserts_count`		",
				"	FROM `queries` q									",
				"	JOIN (												",
				"		SELECT c.`id_connection`						",
				"		FROM `connections` c							",
				"		{$sqlCondition} {$sortSql} {$limitSql}			",
				"	) conns ON											",
				"		conns.`id_connection` = q.`id_connection` AND	",
				"		q.`id_query_type` = ".$queryTypes['insert']."	",
				"	GROUP BY											",
				"		q.`id_connection`								",
				") inserts ON											",
				"	inserts.`id_connection` = c.`id_connection`			",
			]);
		}
		
		if ($updates) {
			$sqlCondition = array_shift($sqlConditions);
			$sql = array_merge($sql, [
				"LEFT JOIN (											",
				"	SELECT 												",
				"		q.`id_connection`,								",
				"		COUNT(q.`id_query_type`) AS `updates_count`		",
				"	FROM `queries` q									",
				"	JOIN (												",
				"		SELECT c.`id_connection`						",
				"		FROM `connections` c							",
				"		{$sqlCondition} {$sortSql} {$limitSql}			",
				"	) conns ON											",
				"		conns.`id_connection` = q.`id_connection` AND	",
				"		q.`id_query_type` = ".$queryTypes['update']."	",
				"	GROUP BY											",
				"		q.`id_connection`								",
				") updates ON											",
				"	updates.`id_connection` = c.`id_connection`			",
		]);
		}
		
		if ($deletes) {
			$sqlCondition = array_shift($sqlConditions);
			$sql = array_merge($sql, [
				"LEFT JOIN (											",
				"	SELECT 												",
				"		q.`id_connection`,								",
				"		COUNT(q.`id_query_type`) AS `deletes_count`		",
				"	FROM `queries` q									",
				"	JOIN (												",
				"		SELECT c.`id_connection`						",
				"		FROM `connections` c							",
				"		{$sqlCondition} {$sortSql} {$limitSql}			",
				"	) conns ON											",
				"		conns.`id_connection` = q.`id_connection` AND	",
				"		q.`id_query_type` = ".$queryTypes['delete']."	",
				"	GROUP BY											",
				"		q.`id_connection`								",
				") deletes ON											",
				"	deletes.`id_connection` = c.`id_connection`			",
			]);
		}

		$sqlCondition = array_shift($sqlConditions);
		$sql = array_merge($sql, [
			"{$sqlCondition} {$sortSql} {$limitSql};					",
		]);
		
		//x([implode("\n", $sql), $params]);
		return [$sql, $params];
	}
	
	protected function completeConditionSqlAndParams ($collectionsCount) {
		$conditionsSqls = [];
		$params = [];
		$driver = $this->db->GetConfig()->driver;
		for ($i = 0; $i < $collectionsCount; $i++) {
			list ($conditionSqlLocal, $params) = $this->getConditionSqlAndParams(
				FALSE, 'c', $params, $driver, ":param_{$i}_", "``"
			);
			$params[":id_gen_log_{$i}"] = $this->idGeneralLog;
			$conditionSql = " WHERE c.`id_general_log` = :id_gen_log_{$i} ";
			if ($conditionSqlLocal) 
				$conditionSql .= "AND {$conditionSqlLocal} ";
			$conditionsSqls[$i] = $conditionSql;
		}
		//x([$conditionsSqls, $params]);
		return [$conditionsSqls, $params];
	}

}