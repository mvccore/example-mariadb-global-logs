<?php

namespace App\Models;

class		ConnectionsList
extends		\App\Models\Base
implements	\MvcCore\Ext\Controllers\DataGrids\Models\IGridModel,
			\MvcCore\Ext\Controllers\DataGrids\Models\IGridColumns{

	use \App\Models\Connection\Props,
		\App\Models\Connection\GettersSetters;

	use \MvcCore\Ext\Controllers\DataGrids\Models\GridModel,
		\MvcCore\Ext\Controllers\DataGrids\Models\GridColumns;

	/**
	 * @var int
	 */
	protected $idGeneralLog = NULL;

	/**
	 * @param  int $idGeneralLog 
	 * @return \App\Models\ConnectionsList
	 */
	public function SetIdGeneralLog ($idGeneralLog) {
		$this->idGeneralLog = $idGeneralLog;
		return $this;
	}
	
	protected function load (): void {
		if ($this->offset === NULL) $this->offset = 0;
		if ($this->limit === NULL) $this->limit = PHP_INT_MAX;

		list ($countSql, $countParams) = $this->completeSqlAndParamsCount();
		list ($pageDataSql, $pageDataParams) = $this->completeSqlAndParamsPageData();

		$conn = self::GetConnection();

		$this->totalCount = $conn
			->Prepare($countSql)
			->FetchOne($countParams)
			->ToScalar('total_count', 'int');
		
		$this->pageData = $conn
			->Prepare($pageDataSql)
			->StreamAll($pageDataParams)
			->ToInstances(
				\App\Models\Connection::class,
				self::PROPS_PROTECTED |
				self::PROPS_CONVERT_UNDERSCORES_TO_CAMELCASE
			);
	}

	protected function completeSqlAndParamsCount () {
		list($sqlConditions, $params) = $this->completeSqlConditionsAndParams();
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
		list($sqlConditions, $params) = $this->completeSqlConditionsAndParams(TRUE);
		
		// ordering:
		$orderSqlItems = [];
		foreach ($this->ordering as $columnName => $direction)
			$orderSqlItems[] = "`{$columnName}` {$direction}";
		$orderSql = count($orderSqlItems) > 0
			? " ORDER BY " . implode(", ", $orderSqlItems) . " "
			: "";

		$queryTypes = self::GetConnection()
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
		
		// offset and limit:
		$limitSql = " LIMIT {$this->offset}, {$this->limit} ";

		$sql = [
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
			
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `selects_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		{$sqlConditions[0]} {$orderSql} {$limitSql}			",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = ".$queryTypes['select']."		",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") selects ON												",
			"	selects.`id_connection` = c.`id_connection`				",
			
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `inserts_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		{$sqlConditions[1]} {$orderSql} {$limitSql}			",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = ".$queryTypes['insert']."		",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") inserts ON												",
			"	inserts.`id_connection` = c.`id_connection`				",
			
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `updates_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		{$sqlConditions[2]} {$orderSql} {$limitSql}			",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = ".$queryTypes['update']."		",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") updates ON												",
			"	updates.`id_connection` = c.`id_connection`				",
			
			"LEFT JOIN (												",
			"	SELECT 													",
			"		q.`id_connection`,									",
			"		COUNT(q.`id_query_type`) AS `deletes_count`			",
			"	FROM `queries` q										",
			"	JOIN (													",
			"		SELECT c.`id_connection`							",
			"		FROM `connections` c								",
			"		{$sqlConditions[3]} {$orderSql} {$limitSql}			",
			"	) conns ON												",
			"		conns.`id_connection` = q.`id_connection` AND		",
			"		q.`id_query_type` = ".$queryTypes['delete']."		",
			"	GROUP BY												",
			"		q.`id_connection`									",
			") deletes ON												",
			"	deletes.`id_connection` = c.`id_connection`				",
			
			"{$sqlConditions[4]} {$orderSql} {$limitSql};				",
		];
		return [$sql, $params];
	}
	
	protected function completeSqlConditionsAndParams ($pageDataSql = FALSE) {
		$conditionsSqls = [];
		$params = [];
		$length = $pageDataSql ? 5 : 1;
		for ($i = 0; $i < $length; $i++) {
			$conditionSqlItems = [];

			$conditionSqlItems[] = "c.`id_general_log` = :id_gen_log{$i}";
			$params[":id_gen_log{$i}"] = $this->idGeneralLog;

			$filterSql = [];
			$index = 0;
			foreach ($this->filtering as $columnName => $rawValues) {
				if (count($rawValues) === 1) {
					$rawValue = $rawValues[0];
					$lastChar = mb_substr($rawValue, -1, 1);
					if ($lastChar === '%') { // dev feature:-)
						$conditionSqlItems[] = "c.`{$columnName}` LIKE :param_{$i}_{$index}";
					} else {
						$conditionSqlItems[] = "c.`{$columnName}` = :param_{$i}_{$index}";
					}
					$params[":param_{$i}_{$index}"] = $rawValue;
					$index++;
				} else {
					$paramsNames = [];
					foreach ($rawValues as $rawValue) {
						$paramsNames[] = ":param_{$i}_{$index}";
						$params[":param_{$i}_{$index}"] = $rawValue;
						$index++;
					}
					$paramsNamesStr = implode(", ", $paramsNames);
					$conditionSqlItems[] = "c.`{$columnName}` IN ({$paramsNamesStr})";
				}
			}

			$conditionsSqls[$i] = " WHERE " . implode(" AND ", $conditionSqlItems) . " ";
		}

		return [$conditionsSqls, $params];
	}
}