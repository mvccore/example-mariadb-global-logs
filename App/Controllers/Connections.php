<?php

namespace App\Controllers;

class Connections extends \App\Controllers\Base
{
	/** @var \App\Models\LogFile */
	protected $generalLog;

	public function Init () {
		parent::Init();
		if ($this->actionName !== 'index') return NULL;
		$this->completeParams();
		$this->createGrid();
	}
	
	public function MarkAction () {
		$idConnection = $this->GetParam('id_connection', '0-9', NULL, 'int');
		$mark = $this->GetParam('mark', '0-9', '0', 'int');

		$connection = \App\Models\Connection::GetById($idConnection);
		$connection->SetMark($mark === 0 ? 1 : 0);
		$connection->Save();

		return $this->JsonResponse([
			'success'	=> TRUE,
			'id'		=> $idConnection,
			'mark'		=> $connection->GetMark()
		]);
	}

	public function IndexAction () {
		$this->view->heading = $this->generalLog->GetFileName() . ' - Connections';
		$this->view->title = $this->generalLog->GetFileName();
		
		$this->view->backLink = $this->Url('Index:Index');

		$this->view->Js('varFoot')
			->Append(self::$staticPath . '/js/Marking.js');
	}

	protected function completeParams () {
		$idGeneralLog = $this->GetParam('id_general_log', '0-9', NULL, 'int');
		$this->generalLog = \App\Models\LogFile::GetById($idGeneralLog);
		if (
			$this->generalLog === NULL || (
				$this->generalLog !== NULL && 
				$this->generalLog->GetProcessed() === \App\Models\LogFile::NOT_PROCESSED
			)
		) throw new \Exception(
			"General log with id: {$idGeneralLog} doesn't exist or it's not processed yet."
		);
	}
	
	protected function createGrid () {
		$model = (new \App\Models\ConnectionsList)
			->SetIdGeneralLog($this->generalLog->GetIdGeneralLog());
		//xxx($model->GetConfigColumns());
		$this->grid = (new \MvcCore\Ext\Controllers\DataGrid($this, 'grid'))
			->SetCssClasses('connections')
			->SetModel($model)
			->SetMultiSorting(TRUE)
			->SetMultiFiltering(TRUE)
			->SetItemsPerPage(10)
			->SetCountScales([10,100,1000,10000,0])
			->SetAllowedCustomUrlCountScale(TRUE)
			/*->SetTranslator(function($key, $replacements = []) {
				if (mb_substr($key, 0, 1) === '_')
					return mb_substr($key, 1);
				return "_{$key}";
			})
			->SetTranslateUrlNames(TRUE)*/
			->SetConfigRendering(
				(new \MvcCore\Ext\Controllers\DataGrids\Configs\Rendering)
					->SetRenderControlPaging(\MvcCore\Ext\Controllers\IDataGrid::CONTROL_DISPLAY_IF_NECESSARY)
					->SetControlPagingOuterPagesDisplayRatio(2.0)
					->SetRenderControlPagingFirstAndLast(TRUE)
					->SetRenderControlPagingPrevAndNext(TRUE)
			);
	}
}
