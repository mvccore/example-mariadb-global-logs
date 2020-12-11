<?php

namespace MvcCore\Ext;

class DbConnection
{
	const RETRY_ATTEMPTS = 3;

	const ISOLATION_LEVEL_REPEATABLE_READ	= 1;
	const ISOLATION_LEVEL_READ_COMMITTED	= 2;
	const ISOLATION_LEVEL_READ_UNCOMMITTED	= 4;
	const ISOLATION_LEVEL_SERIALIZABLE		= 8;

	/** @var \PDO */
	protected $pdoConnection = NULL;
	/** @var string */
	protected $dsn;
	/** @var string */
	protected $username;
	/** @var string */
	protected $passwd;
	/** @var array */
	protected $options;
	/** @var bool */
	protected $inTransaction = FALSE;
	/** @var string */
	protected $transactionName = NULL;
	/** @var bool */
	protected $autocommit = TRUE;
	/** @var bool */
	protected $multiStatements = FALSE;
	/** @var int */
	protected $reconnectionTriesCount = 0;
	
	/**
	 * Creates a PDO instance representing a connection to a database.
	 * @param string $dsn
	 * @param string $username
	 * @param string $passwd
	 * @param array $options
	 */
	public function __construct ($dsn, $username = '', $passwd = '', array $options = []) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->passwd = $passwd;
		$this->options = $options;
		$this->reconnectionTriesCount = 0;
		$this->Connect();
		$multiStatementsConstName = '\PDO::MYSQL_ATTR_MULTI_STATEMENTS';
		if (defined($multiStatementsConstName)) {
			$multiStatementsConst = constant($multiStatementsConstName);
			$this->multiStatements = $this->options[$multiStatementsConst];
		}
	}
	
	/**
	 * @return \PDO
	 */
	public function Connect () {
		try {
			if (!isset($this->options[\PDO::ATTR_EMULATE_PREPARES]))
				$this->options[\PDO::ATTR_EMULATE_PREPARES]			= TRUE;
			if (!isset($this->options[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]))
				$this->options[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]	= TRUE;
			if (!isset($this->options[\PDO::ATTR_ERRMODE]))
				$this->options[\PDO::ATTR_ERRMODE]					= TRUE;
			if (!isset($this->options[\PDO::ERRMODE_EXCEPTION]))
				$this->options[\PDO::ERRMODE_EXCEPTION]				= TRUE;
			$this->pdoConnection = new \PDO(
				$this->dsn, $this->username, $this->passwd, $this->options
			);
		} catch (\Throwable $e) {
			$this->reconnectionTriesCount += 1;
			$this->reconnectOrThrownException($e);
		}
		return $this->pdoConnection;
	}

	/**
	 * Return internal \PDO instance.
	 * @return \PDO
	 */
	public function GetPdoConnection () {
		return $this->pdoConnection;
	}

	/**
	 * Get initial config values.
	 * @return array
	 */
	public function GetPdoInitConfig () {
		return [
			'dsn'		=> $this->dsn,
			'username'	=> $this->username,
			'passwd'	=> $this->passwd,
			'options'	=> $this->options,
		];
	}

	/**
	 * Get how many tries to reconnect into database will be called internally, 
	 * (if connection is lost) before thrown a final exception.
	 * @return int
	 */
	public function GetReconnectionTriesCount () {
		return $this->reconnectionTriesCount;
	}
	
	/**
	 * Get boolean about if \PDO connection is multi statement.
	 * @return bool
	 */
	public function IsMultiStatements () {
		return $this->multiStatements;
	}

	
	/**
	 * Initiates a transaction.
	 * @param string $name Lowercase underscored string name for SQL comment enclosed by `/\* ` and ` *\/`.
	 * @param int $isolation `NULL` by default.
	 * @param bool $write `TRUE` by default.
	 * @param bool $withConsistentSnapshot `TRUE` by default.
	 * @return bool
	 */
	public function BeginTransaction ($name = NULL, $write = TRUE, $isolation = NULL, $withConsistentSnapshot = TRUE) {
		$withConsistentSnapshot = (
			$isolation == static::ISOLATION_LEVEL_REPEATABLE_READ && 
			$withConsistentSnapshot
		);
		if ($this->inTransaction) return FALSE;

		// the code below is not compatible with mysql 5.5 and lower:
		$sqlItems = [];

		$startTransSettingsSeparator = '';
		$snapshotStr = '';
		$writeStr = '';
		
		if ($withConsistentSnapshot) 
			$snapshotStr = ' WITH CONSISTENT SNAPSHOT';

		if ($write) {
			if ($this->autocommit) {
				$this->autocommit = FALSE;
				$sqlItems[] = 'SET SESSION autocommit = 0;';
			}
			$writeStr = ' READ WRITE';
			if ($withConsistentSnapshot)
				$startTransSettingsSeparator = ',';
		} else {
			$writeStr = ' READ ONLY';
			if ($withConsistentSnapshot)
				$startTransSettingsSeparator = ',';
		}

		$properties = implode($startTransSettingsSeparator, [$snapshotStr, $writeStr]);

		// this only applies to the next unstarted transaction
		// afterwards the isolation is reverted
		switch ($isolation) {
			case static::ISOLATION_LEVEL_REPEATABLE_READ:
				$sqlItems[] = 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;';
				break;
			case static::ISOLATION_LEVEL_READ_COMMITTED:
				$sqlItems[] = 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED;';
				break;
			case static::ISOLATION_LEVEL_READ_UNCOMMITTED:
				$sqlItems[] = 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;';
				break;
			case static::ISOLATION_LEVEL_SERIALIZABLE:
				$sqlItems[] = 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;';
				break;
		}

		if ($name !== NULL) {
			$this->transactionName = str_replace(' ', '_', mb_strtolower($name));
			$sqlItems[] = "/* trans_start:{$this->transactionName} */";
		}
		// examples:"START TRANSACTION WITH CONSISTENT SNAPSHOT, READ WRITE;" or
		//			"START TRANSACTION READ WRITE;" or
		//			"START TRANSACTION READ ONLY;" or ...
		$sqlItems[] = "START TRANSACTION{$properties};";
		
		if ($this->multiStatements) {
			$this->pdoConnection->exec(implode('', $sqlItems));
		} else {
			foreach ($sqlItems as $sqlItem)
				$this->pdoConnection->exec($sqlItem);
		}

		$this->inTransaction = TRUE;

		return TRUE;
	}

	/**
	 * Commits a transaction.
	 * @param bool|NULL $chain
	 * @return bool
	 */
	public function Commit ($chain = NULL) {
		if (!$this->inTransaction) return FALSE;
		$sqlItems = [];
		$chaining = '';
		if ($chain === TRUE) {
			$chaining = ' AND CHAIN';
		} else if ($chain === FALSE) {
			$chaining = ' AND NO CHAIN';
		}
		if ($this->transactionName !== NULL) 
			$sqlItems[] = "/* trans_commit:{$this->transactionName} */";
		$sqlItems[] = "COMMIT{$chaining};";
		if (!$chain && !$this->autocommit) {
			$this->autocommit = TRUE;
			$sqlItems[] = 'SET SESSION autocommit = 1;';
		}
		if ($this->multiStatements) {
			$this->pdoConnection->exec(implode('', $sqlItems));
		} else {
			foreach ($sqlItems as $sqlItem)
				$this->pdoConnection->exec($sqlItem);
		}
		// it is possible to still be in a transaction if mysql settings defaults to chaining
		$this->inTransaction = $chain ? TRUE : FALSE;
		return TRUE;
	}

	/**
	 * Rolls back a transaction.
	 * @param bool|NULL $chain
	 * @param \Throwable|NULL $e
	 * @return bool
	 */
	public function RollBack ($chain = NULL, $e = NULL) {
		if (!$this->inTransaction) return FALSE;
		$sqlItems = [];
		$chaining = '';
		if ($chain === TRUE) {
			$chaining = ' AND CHAIN';
		} else if ($chain === FALSE) {
			$chaining = ' AND NO CHAIN';
		}
		if ($this->transactionName !== NULL) 
			$sqlItems[] = "/* trans_rollback:{$this->transactionName} */";
		$sqlItems[] = "ROLLBACK{$chaining};";
		if (!$chain && !$this->autocommit) {
			$this->autocommit = TRUE;
			$sqlItems[] = 'SET SESSION autocommit = 1;';
		}
		if ($this->multiStatements) {
			$this->pdoConnection->exec(implode('', $sqlItems));
		} else {
			foreach ($sqlItems as $sqlItem)
				$this->pdoConnection->exec($sqlItem);
		}
		// it is possible to still be in a transaction if mysql settings defaults to chaining
		$this->pdoConnection->exec(implode('', $sqlItems));
		$this->inTransaction = $chain ? TRUE : FALSE;
		if ($e !== NULL) {
			\MvcCore\Debug::Log($e, \MvcCore\IDebug::DEBUG);
		} else {
			try {
				$errorMsg = "Transaction rollback.";
				if ($this->transactionName !== NULL)
					$errorMsg .= ' ' . $this->transactionName;
				throw new \Exception($errorMsg);
			} catch (\Throwable $e2) {
				\MvcCore\Debug::Log($e2, \MvcCore\IDebug::DEBUG);
			}
		}
		return TRUE;
	}

	/**
	 * Fetch the SQLSTATE associated with the last operation on the database handle.
	 * @return int|string|NULL
	 */
	public function ErrorCode () {
		$rawCode = $this->pdoConnection->errorCode();
		if (is_numeric($rawCode)) return intval($rawCode);
		return trim($rawCode) === '' ? NULL : $rawCode;
	}

	/**
	 * Fetch extended error information associated with the last operation on the database handle.
	 * @return array
	 */
	public function ErrorInfo () {
		return $this->pdoConnection->errorInfo();
	}

	/**
	 * Execute an SQL statement and return the number of affected rows.
	 * @param string $statement
	 * @return int Affected rows count.
	 */
	public function Execute ($statement) {
		return $this->invokeWithReconnectionFallback('exec', [$statement], FALSE);
	}

	/**
	 * Retrieve a database connection attribute.
	 * @param int $attribute
	 * @return mixed
	 */
	public function GetAttribute ($attribute) {
		return $this->pdoConnection->getAttribute($attribute);
	}

	/**
	 * Return an array of available PDO drivers.
	 * @return array
	 */
	public static function GetAvailableDrivers () {
		return \PDO::getAvailableDrivers();
	}

	/**
	 * Checks if inside a transaction.
	 * @return bool
	 */
	public function InTransaction () {
		return $this->inTransaction;
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 * @param string|NULL $sequenceName
	 * @param string|NULL $targetType
	 * @return int|float|string|NULL
	 */
	public function LastInsertId ($sequenceName = NULL, $targetType = NULL) {
		$result = $this->pdoConnection->lastInsertId($sequenceName);
		if ($result !== NULL && $targetType !== NULL)
			settype($result, $targetType);
		return $result;
	}

	/**
	 * Prepares a statement for execution and returns a statement object.
	 * @param string $statement
	 * @param array|NULL $driverOptions
	 * @throws \Throwable
	 * @return \MvcCore\Ext\DbConnections\Command
	 */
	public function Prepare ($statement, $driverOptions = NULL) {
		return $driverOptions === NULL
			? $this->invokeWithReconnectionFallback('prepare', [$statement], TRUE)
			: $this->invokeWithReconnectionFallback('prepare', [$statement, $driverOptions], TRUE);
	}

	/**
	 * Executes an SQL statement, returning a result set as a PDOStatement object.
	 * @param string $statement
	 * @return \MvcCore\Ext\DbConnections\Command
	 */
	public function Query ($statement) {
		return $this->invokeWithReconnectionFallback('query', [$statement], TRUE);
	}

	/**
	 * Quotes a string for use in a query.
	 * @param string $string
	 * @param int $parameter_type
	 * @return string
	 */
	public function Quote ($string , $parameter_type = \PDO::PARAM_STR) {
		return $this->pdoConnection->quote($string, $parameter_type);
	}

	/**
	 * Set an attribute.
	 * @param int $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function SetAttribute ($attribute , $value) {
		return $this->pdoConnection->setAttribute($attribute , $value);
	}

	/**
	 * Try to invoke methods `prepare()` or `query()` on internal `\PDO` instance
	 * and if there has been any exception or any error thrown with message like:
	 * `... server has gone away ...` (MySQL server connection has been dropped),
	 * try to reconnect from PHP and try to process given method with arguments
	 * again 3 times (by `self::RETRY_ATTEMPTS`).
	 * @param string $method
	 * @param array $args
	 * @throws \Throwable
	 * @return \MvcCore\Ext\DbConnections\Command|int
	 */
	protected function invokeWithReconnectionFallback ($method, $args, $returnCmd = TRUE) {
		$exception = NULL;
		/** @var $cmd \PDOStatement */
		try {
			$dbErrorMsg = NULL;
			set_error_handler(function ($phpErrLevel, $errMessage) use (& $dbErrorMsg) {
				// $phpErrLevel is always with value `2` as warning
				$dbErrorMsg = $errMessage;
			});
			$cmd = call_user_func_array([$this->pdoConnection, $method], $args);
			restore_error_handler();
			if (!$cmd) {
				$errInfo = $this->cmd->errorInfo();
				throw new \Exception($errInfo[2] ?: $dbErrorMsg, intval($errInfo[0]));
			}
		} catch (\Throwable $e) {
			$exception = $e;
			$cmd = FALSE;
		}
		if (!$cmd) {
			$connTriesCount = $this->reconnectionTriesCount;
			if (
				mb_strpos($exception->getMessage(), 'server has gone away') !== FALSE &&
				$connTriesCount < self::RETRY_ATTEMPTS
			) {
				$this->Connect();
				return $this->invokeWithReconnectionFallback($method, $args, $returnCmd);
			} else {
				return static::LogAndThrownError(
					$exception, TRUE, $args[0], []
				);
			}
		}
		if ($returnCmd) {
			return new \MvcCore\Ext\DbConnections\Command($this, $cmd);
		} else {
			return $cmd->rowCount();
		}
	}

	/**
	 * @param bool $logErrors 
	 * @throws \Throwable 
	 */
	public static function LogAndThrownError (\Throwable $error, $logError, $queryString, $params) {
		if ($logError) \MvcCore\Debug::Log($error);
		$isDev = \MvcCore\Application::GetInstance()->GetEnvironment()->IsDevelopment();
		$sqlWithValues = '';
		if ($isDev) {
			$paramsToReplace = array_merge([], $params);
			array_walk($paramsToReplace, function (& $value, $key) {
				if ($value === NULL) {
					$value = 'NULL';
				} else if (is_string($value)) {
					$value = "'{$value}'";
				}
			});
			krsort($paramsToReplace);
			$sqlWithValues = strtr($queryString, $paramsToReplace);
			x($error->getMessage() . PHP_EOL . $sqlWithValues);
		}
		throw $error;
	}

	/**
	 * @param \Throwable $e
	 * @param string $method
	 * @param array $args
	 * @throws \Throwable
	 * @return mixed
	 */
	protected function reconnectOrThrownException (\Throwable $e, $method = NULL, $args = NULL) {
		if (
			$e instanceof \PDOException &&
			mb_strpos($e->getMessage(), 'server has gone away') !== FALSE &&
			$this->reconnectionTriesCount < self::RETRY_ATTEMPTS
		) {
			$this->Connect();
			if ($method !== NULL && is_callable([$this->pdoConnection, $method])) {
				return call_user_func_array([$this->pdoConnection, $method], $args);
			}
		} else {
			throw $e;
		}
		return NULL;
	}
}