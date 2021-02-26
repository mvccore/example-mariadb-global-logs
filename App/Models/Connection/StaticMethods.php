<?php

namespace App\Models\Connection;

/**
 * @method static \MvcCore\Ext\Models\Db\Connection GetConnection(string|int|array|\stdClass|NULL $connectionNameOrConfig = NULL, bool $strict = TRUE)
 */
trait StaticMethods {
	
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

}