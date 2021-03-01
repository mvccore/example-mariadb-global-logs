<?php

namespace App\Controllers;

class Queries extends \App\Controllers\Base
{
	/** @var \App\Models\Connection */
	protected $connection;
	/** @var \App\Models\LogFile */
	protected $generalLog;
	/** @var \MvcCore\Ext\Models\Db\Readers\Streams\Iterator */
	protected $queries;

	public function IndexAction () {
		$idConnection = $this->GetParam('id_connection', '0-9', NULL, 'int');
		$this->connection = \App\Models\Connection::GetById($idConnection);
		if ($this->connection === NULL) throw new \Exception(
			"Connection with id: `{$idConnection}` doesn't exist."
		);
		$this->generalLog = $this->connection->GetGeneralLog();
		$this->queriesStream = $this->connection->GetQueriesStream();
		
		$this->view->generalLog = $this->generalLog;
		$this->view->connection = $this->connection;
		$this->view->queriesStream = $this->queriesStream;

		$this->view->heading = $this->generalLog->GetFileName() . ' - Queries';
		$this->view->title = $this->generalLog->GetFileName();
		
		$this->view->backLink = $this->Url(
			'Connections:Index', [
				'id_general_log' => $this->generalLog->GetIdGeneralLog()
			]
		);

		$this->view->Js('varHead')
			->Append(self::$staticPath . '/js/Queries.js');
	}
}