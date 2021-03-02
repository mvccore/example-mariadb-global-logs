<?php

namespace App\Models;

/**
 * @method static \MvcCore\Ext\Models\Db\Connection GetConnection(string|int|array|\stdClass|NULL $connectionNameOrConfig = NULL, bool $strict = TRUE)
 */
class Connection extends \App\Models\Base {
	
	use \App\Models\Connection\Props,
		\App\Models\Connection\GettersSetters,
		\App\Models\Connection\StaticMethods,
		\App\Models\Connection\ManipulationMethods;

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