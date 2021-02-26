<?php

namespace App\Models\Connection;

/**
 * @method static \MvcCore\Ext\Models\Db\Connection GetConnection(string|int|array|\stdClass|NULL $connectionNameOrConfig = NULL, bool $strict = TRUE)
 * @method static int|float|string|NULL convertToScalar(bool|int|float|string|\DateTimeInterface|\DateInterval|\bool[]|\int[]|\float[]|\string[]|\DateTimeInterface[]|\DateInterval[]|NULL $value, array $formatArgs = [])
 */
trait ManipulationMethods {
	
	/**
	 * @return bool
	 */
	public function Save ($createNew = NULL, $flags = 0) {
		/** @var $this \App\Models\Connection */
		if ($createNew || $this->idConnection === NULL) {
			return $this->Insert($flags);
		} else {
			return $this->Update($flags);
		}
	}
	
	/** @return bool */
	public function Insert ($flags = 0) {
		/** @var $this \App\Models\Connection */
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
		} catch (\Throwable $e) {
			if ($db->InTransaction()) $db->RollBack();
			\MvcCore\Debug::Exception($e);
			$result = FALSE;
		}
		return $result;
	}

	/** @return bool */
	public function Update ($flags = 0) {
		/** @var $this \App\Models\Connection */
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
}