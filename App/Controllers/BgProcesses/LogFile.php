<?php

namespace App\Controllers\BgProcesses;

class LogFile extends \App\Controllers\BgProcesses\Base
{
	const THREAD_CONNECTED = 1;
	const THREAD_DISCONNECTED = 2;
	const THREAD_IGNORED = 3;

	/** @var \App\Models\LogFile */
	protected $generalLog;
	/** @var \App\Models\BgProcesses\LogFiles\Processing */
	protected $model;
	/** @var \string[] */
	protected $ignoredUsers;

	/** @var string|NULL */
	protected $dateTimeStr = NULL;
	/** @var bool[]|NULL Keys are thread id ints, values are booleans about connection state. */
	protected $idThreads = [];
	/** @var int|NULL */
	protected $idThreadCurrent = NULL;
	/** @var \string[]|NULL */
	protected $currentRequestLines = NULL;
	/** @var int */
	protected $srcLineNum = 0;

	/**
	 * @see http://r-webdog.czx/tec/mariadb-global-logs/?controller=bg-processes/dispatcher&action=execute&id_bg_process=1
	 */
	public function IndexAction () {
		$this->generalLog = $this->bgProcess->GetGeneralLog();

		$sysCfg = $this->GetSystemConfig();
		$this->ignoredUsers = isset($sysCfg->ignore->users) && is_array($sysCfg->ignore->users)
			? $sysCfg->ignore->users
			: [];

		$linesCount = \App\Models\LogFile::GetFileLinesCount($this->generalLog->GetFullPath());
		$this->generalLog
			->SetLinesCount($linesCount)
			->SetProcessed(\App\Models\LogFile::PROCESSING)
			->Save();
		
		$this->model = new \App\Models\BgProcesses\LogFiles\Processing(
			$this->generalLog->GetIdGeneralLog(),
			$linesCount,
			$this->bgProcess->GetId(),
			100
		);
		$this->model->LoadAllCollections();
		
		try {
			$this->parseLogFile();

			$this->generalLog
				->SetProcessed(\App\Models\LogFile::PROCESSED)
				->Save();
			
			$now = new \DateTime('now');
			$now->setTimezone(new \DateTimeZone('UTC'));
			$this->bgProcess
				->SetResult(1)
				->SetProgress(100.0)
				->SetFinished($now)
				->Save();

		} catch (\Throwable $e) {
			$this->bgProcess
				->SetResult(-1)
				->SetMessage(
					$e->getMessage() . PHP_EOL.PHP_EOL . $e->getTraceAsString()
				)
				->Save();
			throw $e;
		}
	}

	protected function parseLogFile () {
		$handle = fopen($this->generalLog->GetFullPath(), 'r+');
		$this->dateTimeStr = NULL;
		$this->srcLineNum = 0;
		while ($buffer = fgets($handle)) {
			
			// separate datetime if there is any:
			if (preg_match("#^[\d]{6}[ ]{1,2}[\d]{1,2}\:[\d]{2}\:[\d]{2}\t\s*([\d]+)\s#", $buffer)) {
				$this->dateTimeStr = substr(date('Y'), 0, 2) 
					. mb_substr($buffer, 0, 2)
					. '-' . mb_substr($buffer, 2, 2)
					. '-' . mb_substr($buffer, 4, 2)
					. mb_substr($buffer, 6, 9);
				$buffer = "\t" . mb_substr($buffer, 15);
			}
			
			// if line begins with double tab:
			if (mb_substr($buffer, 0, 2) === "\t\t") {
				if (preg_match_all("#^\t\t\s*([0-9]+)\sConnect\t(.*)#", $buffer, $matches)) {
					$this
						->closeCurrentRequestIfNecessary()
						->parseLogLineConnection($matches);
				
				} else if (preg_match_all("#^\t\t\s*([0-9]+)\sQuery\t(.*)#", $buffer, $matches)) {
					$this
						->closeCurrentRequestIfNecessary()
						->parseLogLineQuery($matches);
				
				} else if (preg_match_all("#^\t\t\s*([0-9]+)\sExecute\t(.*)#", $buffer, $matches)) {
					$this
						->closeCurrentRequestIfNecessary()
						->parseLogLineQuery($matches);
				
				} else if (preg_match_all("#^\t\t\s*([0-9]+)\sBinlog Dump\t(.*)#", $buffer, $matches)) {
					$this->closeCurrentRequestIfNecessary();
					// do not parse slave binlog dumps
				
				} else if (preg_match_all("#^\t\t\s*([0-9]+)\sPrepare\t(.*)#", $buffer, $matches)) {
					$this->closeCurrentRequestIfNecessary();
					// do not parse prepare statements
				
				} else if (preg_match_all("#^\t\t\s*([0-9]+)\sClose stmt\t(.*)#", $buffer, $matches)) {
					$this->closeCurrentRequestIfNecessary();
					// do not parse close statements
				
				} else if (preg_match_all("#^\t\t\s*([0-9]+)\sQuit\t#", $buffer, $matches)) {
					$this
						->closeCurrentRequestIfNecessary()
						->parseLogLineQuit($matches);
				
				} else {
					if ($this->dateTimeStr === NULL) continue; // log file heading
					// line could be only next query line:
					$this->addCurrentRequestLine($buffer);
				}

			} else {
				if ($this->dateTimeStr === NULL) continue; // log file heading
				// line could be only next query line:
				$this->addCurrentRequestLine($buffer);
			}

			$this->srcLineNum++;
			$this->model->SetCurrentLine($this->srcLineNum);
		}
		fclose($handle);
		$this->model
			->SetCurrentLine($this->srcLineNum)
			->FlushData();
	}

	protected function parseLogLineConnection ($matches) {
		$matches = array_map(function ($value) { return $value[0]; }, $matches);
		list (, $idThreadStr, $connDescription) = $matches;
		
		$idThread = intval($idThreadStr);
		$connDescriptionParts = explode('as anonymous on', $connDescription);
		list ($user, $database) = array_map('trim', $connDescriptionParts);
		if (mb_strlen($user) === 0) $user = NULL;
		if (mb_strlen($database) === 0) $database = NULL;
		
		if (
			isset($this->threadIds[$idThread]) && 
			$this->threadIds[$idThread] === self::THREAD_DISCONNECTED
		) throw new \Exception(
			"Another thread with id `{$idThread}` has been already parsed (line: {$this->srcLineNum})."
		);

		$ignoredThread = in_array($user, $this->ignoredUsers, TRUE);
		if (!$ignoredThread) {
			$this->model->AddConnection(
				$user, $database, $idThread, $this->dateTimeStr
			);
		}
		$this->threadIds[$idThread] = $ignoredThread
			? self::THREAD_IGNORED
			: self::THREAD_CONNECTED;
		$this->idThreadCurrent = $idThread;
	}

	protected function parseLogLineQuery ($matches) {
		$matches = array_map(function ($value) { return $value[0]; }, $matches);
		list (, $idThreadStr, $queryStr) = $matches;
		
		$idThread = intval($idThreadStr);
		
		if (!isset($this->threadIds[$idThread])) {
			// insert new connection - there was no start, general log file could be trimmed
			$this->model->AddConnection(
				NULL, NULL, $idThread, $this->dateTimeStr
			);
			$this->threadIds[$idThread] = self::THREAD_CONNECTED;
		} else if (
			isset($this->threadIds[$idThread]) && 
			$this->threadIds[$idThread] === self::THREAD_DISCONNECTED) {
			throw new \Exception(
				"Another thread with id `{$idThread}` has been already parsed (line: {$this->srcLineNum})."
			);
		}
		$this->idThreadCurrent = $idThread;

		if ($this->threadIds[$idThread] !== self::THREAD_IGNORED) {
			$this->model->AddConnectionRequestCount($idThread);
			$this->addCurrentRequestLine($queryStr);
		}
	}

	/**
	 * @param string $rawLine 
	 */
	protected function addCurrentRequestLine ($rawLine) {
		if ($this->threadIds[$this->idThreadCurrent] === self::THREAD_IGNORED) 
			return $this;
		if ($this->currentRequestLines === NULL) {
			$this->currentRequestLines = [[$rawLine, $this->srcLineNum]];
		} else {
			$this->currentRequestLines[] = [$rawLine, $this->srcLineNum];
		}
		return $this;
	}

	/**
	 * @throws \Exception 
	 * @return \App\Controllers\BgProcesses\LogFile
	 */
	protected function closeCurrentRequestIfNecessary () {
		if ($this->currentRequestLines === NULL) 
			return $this;
		if ($this->threadIds[$this->idThreadCurrent] === self::THREAD_IGNORED) 
			return $this;

		$rawLines = [];
		foreach ($this->currentRequestLines as $requestLineData) 
			$rawLines[] = $requestLineData[0];
		$rawRequestSql = implode("", $rawLines);

		$rawQueries = \Libs\Sql::Explode($rawRequestSql);
		$queries = [];
		$lineNumbersIndex = 0;
		foreach ($rawQueries as $rawQuery) {
			if (!isset($this->currentRequestLines[$lineNumbersIndex]))
				throw new \Exception("Error when defining query begin line (index: {$lineNumbersIndex}, line: {$this->srcLineNum}, query: `{$rawQuery}`).");
			$lineBegin = $this->currentRequestLines[$lineNumbersIndex][1];
			$lineNumbersIndex += substr_count($rawQuery, "\n");
			if (!isset($this->currentRequestLines[$lineNumbersIndex])) 
				throw new \Exception("Error when defining query end line (index: {$lineNumbersIndex}, line: {$this->srcLineNum}, query: `{$rawQuery}`).");
			$lineEnd = $this->currentRequestLines[$lineNumbersIndex][1];
			$rawQueryTrimmed = trim($rawQuery, "; \t\n\r\0\x0B");
			if (mb_strlen($rawQueryTrimmed) > 0) 
				$queries[] = [$rawQuery, $lineBegin, $lineEnd];
		}
		
		foreach ($queries as $queryData) {
			list($rawQuery, $lineBegin, $lineEnd) = $queryData;
			$this->model->AddQuery(
				$this->idThreadCurrent,
				$this->dateTimeStr,
				$lineBegin,
				$lineEnd,
				$rawQuery
			);
		}
		
		$this->currentRequestLines = NULL;

		return $this;
	}

	protected function parseLogLineQuit ($matches) {
		$matches = array_map(function ($value) { return $value[0]; }, $matches);
		list (, $idThreadStr, ) = $matches;

		$idThread = intval($idThreadStr);

		if (
			isset($this->threadIds[$idThread]) && 
			$this->threadIds[$idThread] === self::THREAD_DISCONNECTED
		) throw new \Exception(
			"Thread with id `{$idThread}` has been closed already (line: {$this->srcLineNum})."
		);
		
		if ($this->threadIds[$idThread] !== self::THREAD_IGNORED) {
			$this->model->AddDisconnection(
				$idThread, $this->dateTimeStr
			);
		}
			
		$this->threadIds[$idThread] = self::THREAD_DISCONNECTED;
		$this->idThreadCurrent = NULL;
	}
}
