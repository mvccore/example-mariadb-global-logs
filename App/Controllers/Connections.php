<?php

namespace App\Controllers;

class Connections extends \App\Controllers\Base
{
	/** @var \App\Models\LogFile */
	protected $generalLog;
	/** @var \App\Models\Connection[] */
	protected $connections;
	/** @var int */
	protected $totalCount;
	/** @var array */
	protected $orderFields;
	/** @var string */
	protected $orderField;
	/** @var string */
	protected $direction;
	/** @var int */
	protected $offset;
	/** @var int */
	protected $limit;

	public function IndexAction () {
		$this->completeParams();
		$this->totalCount = \App\Models\Connection::GetCount(
			$this->generalLog->GetIdGeneralLog(), 
			$this->offset, $this->limit
		);
		$this->connections = \App\Models\Connection::GetList(
			$this->generalLog->GetIdGeneralLog(), 
			$this->orderField, $this->direction, 
			$this->offset, $this->limit
		);
		$this->setUpViewProps();
		$this->setUpPaging();
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

		$this->orderFields = \App\Models\Connection::GetOrderFields();
		
		$rawOrderField = $this->GetParam('order', 'a-z_', 'connected');
		$this->orderField = in_array($rawOrderField, array_keys($this->orderFields), TRUE)
			? $rawOrderField
			: 'connected';

		$rawDir = $this->GetParam('dir', 'ascde', 'asc');
		$this->direction = $rawDir == 'asc' ? 'asc' : 'desc';

		$this->limit = $this->GetParam('limit', '0-9', 1000, 'int');
		if ($this->limit === 0) $this->limit = PHP_INT_MAX;
		
		$page = $this->GetParam('page', '0-9', 1, 'int');
		$this->offset = ($page - 1) * $this->limit;
	}

	protected function setUpViewProps () {
		$this->view->generalLog = $this->generalLog;
		$this->view->connections = $this->connections;
		
		$this->view->orderFields = $this->orderFields;
		$this->view->currentOrder = $this->orderField;
		$this->view->currentDir = $this->direction;

		$this->view->heading = $this->generalLog->GetFileName() . ' - Connections';
		$this->view->title = $this->generalLog->GetFileName();
		
		$this->view->backLink = $this->Url('Index:Index');

		$this->view->Js('varFoot')
			->Append(self::$staticPath . '/js/Marking.js');
	}

	protected function setUpPaging () {
		$pageMargin = 5;
		$pagesCount = intval(ceil($this->totalCount / $this->limit));
		$currentPage = intdiv($this->offset, $this->limit) + 1;
		$displayPrev = $this->offset - $this->limit > 0;
		$displayFirst = $this->offset > $pageMargin * $this->limit;
		$displayNext = $this->offset + $this->limit < $this->totalCount;
		$displayLast = $this->offset < ($pagesCount * $this->limit) - (($pageMargin + 1) * $this->limit);
		
		$links = [];
		
		if ($displayPrev) {
			$links[] = ['prev', $this->getPagingUrl($this->offset - $this->limit)];
			$links[] = ['&hellip;'];
		}
		if ($displayFirst) {
			$links[] = [1, $this->getPagingUrl(0)];
			$links[] = ['&hellip;'];
		}
		
		$beginIndex = max($currentPage - $pageMargin, 1);
		$endIndex = min($currentPage + $pageMargin + 1, $pagesCount + 1);
		for ($pageIndex = $beginIndex; $pageIndex < $endIndex; $pageIndex++) {
			if ($pageIndex === $currentPage) {
				$links[] = [$currentPage];
			} else {
				$links[] = [$pageIndex, $this->getPagingUrl(($pageIndex - 1) * $this->limit)];
			}		
		}

		if ($displayLast) {
			$links[] = ['&hellip;'];
			$links[] = [$pagesCount, $this->getPagingUrl(($pagesCount - 1) * $this->limit)];
		}
		if ($displayNext) {
			$links[] = ['&hellip;'];
			$links[] = ['next', $this->getPagingUrl($this->offset + $this->limit)];
		}
		$this->view->paging = $pagesCount > 1;
		$this->view->pagingLinks = $links;
	}

	protected function getPagingUrl ($offset) {
		$page = intdiv($offset, $this->limit) + 1;
		return $this->Url('self', ['page' => $page]);
	}
}
