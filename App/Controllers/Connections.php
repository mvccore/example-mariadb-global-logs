<?php

namespace App\Controllers;

class Connections extends \App\Controllers\Base {

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
		$model = new \App\Models\ConnectionsList;
		$model->SetIdGeneralLog($this->generalLog->GetIdGeneralLog());
		//xxx($model->GetConfigColumns());
		$this->grid = (new \MvcCore\Ext\Controllers\DataGrid($this, 'grid'))
			->AddCssClasses('connections')
			->SetSortingMode(
				//\MvcCore\Ext\Controllers\IDataGrid::SORT_DISABLED
				\MvcCore\Ext\Controllers\IDataGrid::SORT_MULTIPLE_COLUMNS
			)
			->SetFilteringMode(
				//\MvcCore\Ext\Controllers\IDataGrid::FILTER_DISABLED
				\MvcCore\Ext\Controllers\IDataGrid::FILTER_MULTIPLE_COLUMNS |
				\MvcCore\Ext\Controllers\IDataGrid::FILTER_ALLOW_NULL |
				\MvcCore\Ext\Controllers\IDataGrid::FILTER_ALLOW_EQUALS |
				\MvcCore\Ext\Controllers\IDataGrid::FILTER_ALLOW_RANGES |
				\MvcCore\Ext\Controllers\IDataGrid::FILTER_ALLOW_LIKE_ANYWHERE
			)
			->SetModel($model)
			->SetRowClass('\App\Models\Connection')
			->SetItemsPerPage(100)
			->SetCountScales([100,1000,10000,0])
			->SetAllowedCustomUrlCountScale(TRUE)
			/*->SetTranslator(function($key, $replacements = []) {
				return "_{$key}";
			})
			->SetTranslateUrlNames(TRUE)*/
			->SetConfigRendering(
				(new \MvcCore\Ext\Controllers\DataGrids\Configs\Rendering)
					->SetTemplateTableBody($this->controllerName . '/grid-table-body')
					->SetRenderFilterForm(TRUE)
					->SetRenderTableHeadFiltering(TRUE)
					->SetRenderControlPaging(\MvcCore\Ext\Controllers\IDataGrid::CONTROL_DISPLAY_IF_NECESSARY)
					->SetRenderControlPagingFirstAndLast(TRUE)
			);
		$filterForm = (new \App\Forms\ConnectionsFilter($this->grid))
			->SetGeneralLog($this->generalLog);
		$this->grid->SetControlFilterForm($filterForm);
	}
}
