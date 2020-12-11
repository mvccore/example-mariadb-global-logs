<?php

namespace MvcCore\Ext\DbConnections;

class Command
{
	/** @var bool|NULL */
	protected static $debugingEnabled = NULL;

	/** @var \MvcCore\Ext\DbConnection */
	protected $connection = NULL;
	/** @var \PDOStatement */
	protected $cmd = NULL;
	/** @var array */
	protected $params = NULL;
	/** @var bool */
	protected $selecting = FALSE;
	/** @var bool */
	protected $result = FALSE;
	/** @var bool */
	protected $itemsAsObjects = FALSE;
	/** @var \Throwable|NULL */
	protected $error = NULL;

	public function __construct (\MvcCore\Ext\DbConnection $connection, \PDOStatement $cmd) {
		$this->connection = $connection;
		$this->cmd = $cmd;
		$sqlTrimmed = trim($cmd->queryString, "; \t\n\r\0\x0B");
		preg_match("#\s#", $sqlTrimmed, $matches, PREG_OFFSET_CAPTURE);
		if ($matches && $matches[0]) {
			$firstWhiteSpacePos = $matches[0][1];
			$firstWord = mb_strtolower(mb_substr($sqlTrimmed, 0, $firstWhiteSpacePos));
			$this->selecting = $firstWord === 'select';
		}
	}

	/**
	 * Return connection wrapper instance.
	 * @return \MvcCore\Ext\DbConnection
	 */
	public function GetConnection () {
		return $this->connection;
	}

	/**
	 * Return internal \PDO connection instance.
	 * @return \PDO
	 */
	public function GetPdoConnection () {
		return $this->connection->GetPdoConnection();
	}

	/**
	 * Return internal \PDOStatement instance.
	 * @return \PDOStatement
	 */
	public function GetStatement () {
		return $this->cmd;
	}
	
	/**
	 * Return execution params.
	 * @return array
	 */
	public function GetParams () {
		return $this->params;
	}

	/**
	 * @param array $params
	 * @param bool $logError
	 * @param bool $execAgainOnDisconnect
	 * @throws \Throwable
	 * @return \MvcCore\Ext\DbConnections\Command
	 */
	public function Execute ($params = NULL, $logError = TRUE, $execAgainOnDisconnect = TRUE) {
		$this->params = $params;
		$this->error = NULL;
		try {
			$dbErrorMsg = NULL;
			set_error_handler(function ($phpErrLevel, $errMessage) use (& $dbErrorMsg) {
				// $phpErrLevel is always with value `2` as warning
				$dbErrorMsg = $errMessage;
			});
			$this->result = $this->cmd->execute($params);
			restore_error_handler();
			if ($this->result) {
				$this->params = [];
			} else {
				$errInfo = $this->cmd->errorInfo();
				throw new \Exception($errInfo[2] ?: $dbErrorMsg, intval($errInfo[0]));
			}
		} catch (\Throwable $e) {
			$this->error = $e;
			$this->result = FALSE;
		}
		if (!$this->result) {
			$connection = $this->connection;
			$reconnTriesCount = $connection->GetReconnectionTriesCount();
			if (
				mb_strpos(mb_strtolower($this->error->getMessage()), 'server has gone away') !== FALSE &&
				$reconnTriesCount < $connection::RETRY_ATTEMPTS
			) {
				$connection->Connect();
				if ($this->selecting || $execAgainOnDisconnect) {
					$this->cmd = $connection->GetPdoConnection()->prepare($this->cmd->queryString);
					return $this->Execute($params, $logError, $execAgainOnDisconnect);
				}
				return $this;
			} else {
				return $connection::LogAndThrownError(
					$this->error, $logError, $this->cmd->queryString, $this->params
				);
			}
		}
		return $this;
	}

	/**
	 * Return `\PDOStatement::execute()` result boolean.
	 * @return boolean
	 */
	public function GetExecuteResult () {
		return $this->result;
	}

	/**
	 * Returns an array containing all of the result set rows.
	 * @param int $how
	 * @param mixed $class_name
	 * @param array $ctorArgs
	 * @return array
	 */
	public function FetchAll ($how = NULL, $fullClassName = NULL, $ctorArgs = NULL) {
		if ($how === NULL && $fullClassName === NULL && $ctorArgs === NULL) {
			return $this->cmd->fetchAll();
		} else if ($fullClassName === NULL && $ctorArgs === NULL) {
			return $this->cmd->fetchAll($how);
		} else if ($ctorArgs === NULL) {
			return $this->cmd->fetchAll($how, $fullClassName);
		} else {
			return $this->cmd->fetchAll($how, $fullClassName, $ctorArgs);
		}
	}

	/**
	 * Fetches the next row from a result set.
	 * @param int $how
	 * @param int $orientation
	 * @param int $offset
	 * @return mixed
	 */
	public function Fetch ($how = NULL, $orientation = NULL, $offset = NULL) {
		if ($how === NULL && $orientation === NULL && $offset === NULL) {
			return $this->cmd->fetch();
		} else if ($orientation === NULL && $offset === NULL) {
			return $this->cmd->fetch($how);
		} else if ($offset === NULL) {
			return $this->cmd->fetch($how, $orientation);
		} else {
			return $this->cmd->fetch($how, $orientation, $offset);
		}
	}

	/**
	 * Returns a single column from the next row of a result set.
	 * @param int $columnNumber
	 * @return mixed
	 */
	public function FetchColumn ($columnNumber = 0, $targetType = NULL) {
		$result = $this->cmd->fetchColumn($columnNumber);
		if ($result !== NULL && $targetType !== NULL) 
			settype($result, $targetType);
		return $result;
	}

	/**
	 * Fetch single row into associative array.
	 * @param bool $asObject
	 * @return array||NULL
	 */
	public function FetchOneToAssocArray () {
		$rawResult = $this->fetch(\PDO::FETCH_ASSOC);
		$result = [];
		if (!$rawResult) return NULL;
		foreach ($rawResult as $rawKey => $rawValue) {
			if (is_numeric($rawKey)) continue;
			$result[$rawKey] = $rawValue;
		}
		return $result;
	}
	
	/**
	 * Fetch singlůe row into instances created by first argument
	 * and set up given instances by given flags in second argument.
	 * @param string      $fullClassName			Collection instance class full name
	 * @param int	      $keysConversionFlags		`\MvcCore\IModel::KEYS_CONVERSION_*` flags to process array keys conversion before set up into properties.
	 * @param bool        $completeInitialValues	Complete protected array `initialValues` to be able to compare them by calling method `GetTouched()` anytime later.
	 * @return \MvcCore\Model|\MvcCore\IModel|mixed
	 */
	public function FetchOneToInstance ($fullClassName, $keysConversionFlags = NULL, $completeInitialValues = TRUE) {
		$rawResult = $this->fetch(\PDO::FETCH_ASSOC);
		if (!$rawResult) return NULL;
		$type = new \ReflectionClass($fullClassName);
		$result = $type->newInstanceWithoutConstructor();
		$result->SetUp($rawResult, $keysConversionFlags, $completeInitialValues);
		return $result;
	}

	/**
	 * Fetch single row into associative array.
	 * @return \stdClass
	 */
	public function FetchOneToStdClass () {
		return (object) $this->fetchOneToAssocArray();
	}

	/**
	 * Fetch result into `array` of `array`s, keyed by first argument.
	 * If first argument is not provided, result is keyed with numbers as it is.
	 * @param string|NULL $key
	 * @param string|NULL $keyType
	 * @return \array[]|\stdClass[]
	 */
	public function FetchAllToAssocArrays ($key = NULL, $keyType = NULL) {
		$rawResults = $this->fetchAll(\PDO::FETCH_ASSOC);
		$result = [];
		$retypeKey = $keyType !== NULL;
		foreach ($rawResults as $rawKey1 => $rawItem) {
			$item = [];
			foreach ($rawItem as $rawKey2 => $rawValue) {
				if (is_numeric($rawKey2)) continue;
				$item[$rawKey2] = $rawValue;
			}
			$itemKey = $key === NULL ? $rawKey1 : $item[$key];
			if ($retypeKey)
				settype($itemKey, $keyType);
			if ($this->itemsAsObjects) {
				$result[$itemKey] = (object) $item;
			} else {
				$result[$itemKey] = $item;
			}
		}
		return $result;
	}
	
	/**
	 * Fetch result into array of instances created by first argument
	 * and set up given instances by given flags in second argument
	 * @param string      $fullClassName			Collection instance class full name
	 * @param int	      $keysConversionFlags		`\MvcCore\IModel::KEYS_CONVERSION_*` flags to process array keys conversion before set up into properties.
	 * @param bool        $completeInitialValues	Complete protected array `initialValues` to be able to compare them by calling method `GetTouched()` anytime later.
	 * @param string|NULL $key						Key column for result collection.
	 * @param string|NULL $keyType					Key type for result collection.
	 * @return \MvcCore\Model[]|\MvcCore\IModel[]
	 */
	public function FetchAllToInstances ($fullClassName, $keysConversionFlags = NULL, $completeInitialValues = FALSE, $key = NULL, $keyType = NULL) {
		$rawResults = $this->fetchAll(\PDO::FETCH_ASSOC);
		$result = [];
		$retypeKey = $keyType !== NULL;
		$type = new \ReflectionClass($fullClassName);
		foreach ($rawResults as $rawKey1 => $rawItem) {
			$item = $type->newInstanceWithoutConstructor();
			$item->SetUp($rawItem, $keysConversionFlags, $completeInitialValues);
			$itemKey = $key === NULL ? $rawKey1 : $rawItem[$key];
			if ($retypeKey)
				settype($itemKey, $keyType);
			$result[$itemKey] = $item;
		}
		return $result;
	}

	/**
	 * Fetch result into `array` of `array`s, keyed by first argument.
	 * If first argument is not provided, result is keyed with numbers as it is.
	 * @param string|NULL $columnName
	 * @param string|NULL $valueType
	 * @return int|float|string|bool|NULL
	 */
	public function FetchOneToScalar ($columnName = NULL, $valueType = NULL) {
		if ($columnName === NULL) {
			$rawResult = $this->fetch();
			if (isset($rawResult[0])) {
				$value = $rawResult[0];
				if ($valueType !== NULL)
					settype($value, $valueType);
				return $value;
			}
			return NULL;
		} else {
			$rawResult = $this->fetch(\PDO::FETCH_ASSOC);
			if (is_array($rawResult) && array_key_exists($columnName, $rawResult)) {
				$value = $rawResult[$columnName];
				if ($valueType !== NULL)
					settype($value, $valueType);
				return $value;
			}
			return NULL;
		}
	}

	/**
	 * Fetch result into `array` of `array`s, keyed by first argument.
	 * If first argument is not provided, result is keyed with numbers as it is.
	 * @param string|NULL $keyColumn
	 * @param string|NULL $valueColumn
	 * @param string|NULL $keyType
	 * @param string|NULL $valueType
	 * @return array|\int[]|\float[]|\string[]|\bool[]
	 */
	public function FetchAllToScalars ($keyColumn = NULL, $valueColumn = NULL, $keyType = NULL, $valueType = NULL) {
		$rawResults = $this->fetchAll(\PDO::FETCH_ASSOC);
		$result = [];
		$retypeKey = $keyType !== NULL;
		$retypeValue = $valueType !== NULL;
		foreach ($rawResults as $rawKey1 => $rawItem) {
			$item = [];
			foreach ($rawItem as $rawKey2 => $rawValue) {
				if (is_numeric($rawKey2)) continue;
				$item[$rawKey2] = $rawValue;
			}
			$itemKey = $keyColumn === NULL
				? $rawKey1
				: $item[$keyColumn];
			if ($retypeKey)
				settype($itemKey, $keyType);
			$itemValue = NULL;
			if ($valueColumn === NULL) {
				$itemKeys = array_fill_keys(array_keys($item), 1);
				unset($itemKeys[$keyColumn]);
				$itemKeys = array_keys($itemKeys);
				$itemValue = $item[$itemKeys[0]];
			} else {
				$itemValue = $item[$valueColumn];
			}
			if ($retypeValue)
				settype($itemValue, $valueType);
			$result[$itemKey] = $itemValue;
		}
		return $result;
	}

	/**
	 * Fetch result into `array` of `\stdClass`es, keyed by first argument.
	 * If first argument is not provided, result is keyed with numbers as it is.
	 * @param string|NULL $key
	 * @param string|NULL $keyType
	 * @return \stdClass[]
	 */
	public function FetchAllToStdClasses ($key = NULL, $keyType = NULL) {
		$this->itemsAsObjects = TRUE;
		$result = $this->fetchAllToAssocArrays($key, $keyType);
		$this->itemsAsObjects = FALSE;
		return $result;
	}

	/**
	 * Bind a column to a PHP variable.
	 * @param mixed $column
	 * @param mixed $param
	 * @param int $type
	 * @param int $maxLength
	 * @param mixed $driverData
	 * @return bool
	 */
	public function BindColumn ($column, & $param, $type = NULL, $maxLength = NULL, $driverData = NULL) {
		return $this->cmd->bindColumn($column, $param, $type, $maxLength, $driverData);
	}

	/**
	 * Binds a parameter to the specified variable name.
	 * @param mixed $parameter
	 * @param mixed $variable
	 * @param int $dataType
	 * @param int $length
	 * @param mixed $driverOptions
	 * @return bool
	 */
	public function BindParam ($parameter, & $variable, $dataType = \PDO::PARAM_STR, $length = NULL, $driverOptions = NULL) {
		return $this->cmd->bindParam($parameter, $variable, $dataType, $length, $driverOptions);
	}

	/**
	 * Binds a value to a parameter.
	 * @param mixed $parameter
	 * @param mixed $value
	 * @param int $dataType
	 * @return bool
	 */
	public function BindValue ($parameter, $value, $dataType = \PDO::PARAM_STR) {
		return $this->cmd->bindValue($parameter, $value, $dataType);
	}

	/**
	 * Closes the cursor, enabling the statement to be executed again.
	 * @return bool
	 */
	public function CloseCursor () {
		return $this->cmd->closeCursor();
	}

	/**
	 * Returns the number of columns in the result set.
	 * @return int
	 */
	public function ColumnCount () {
		return $this->cmd->columnCount();
	}

	/**
	 * Dump an SQL prepared command.
	 * @return void
	 */
	public function DebugDumpParams () {
		return $this->cmd->debugDumpParams();
	}

	/**
	 * Fetch the SQLSTATE associated with the last operation on the statement handle.
	 * @return string
	 */
	public function ErrorCode () {
		return $this->cmd->errorCode();
	}

	/**
	 * Fetch extended error information associated with the last operation on the statement handle.
	 * @return array
	 */
	public function ErrorInfo () {
		return $this->cmd->errorInfo();
	}

	/**
	 * Fetches the next row and returns it as an object.
	 * @param string $fullClassName
	 * @param array $ctorArgs
	 * @return mixed
	 */
	public function FetchObject ($fullClassName = 'stdClass' , $ctorArgs = []) {
		return $this->cmd->fetchObject($fullClassName, $ctorArgs);
	}

	/**
	 * Retrieve a statement attribute.
	 * @param int $attribute
	 * @return mixed
	 */
	public function GetAttribute ($attribute) {
		return $this->cmd->getAttribute($attribute);
	}

	/**
	 * Returns metadata for a column in a result set.
	 * @param int $column
	 * @return array
	 */
	public function GetColumnMeta ($column) {
		return $this->cmd->getColumnMeta($column);
	}

	/**
	 * Advances to the next rowset in a multi-rowset statement handle.
	 * @return bool
	 */
	public function NextRowset () {
		return $this->cmd->nextRowset();
	}

	/**
	 * Returns the number of rows affected by the last SQL statement.
	 * @return int
	 */
	public function RowCount () {
		return $this->cmd->rowCount();
	}

	/**
	 * Set a statement attribute.
	 * @param int $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function SetAttribute ($attribute, $value) {
		return $this->cmd->setAttribute($attribute, $value);
	}

	/**
	 * Set the default fetch mode for this statement.
	 * @param int $mode \PDO::FETCH_COLUMN | \PDO::FETCH_CLASS | \PDO::FETCH_INTO
	 * @param int|string|object $colno|$classname|$object
	 * @param array $ctorargs
	 * @return bool
	 */
	public function SetFetchMode ($mode, $params = NULL) {
		return call_user_func_array([$this->cmd, 'setFetchMode'], func_get_args());
	}
}
