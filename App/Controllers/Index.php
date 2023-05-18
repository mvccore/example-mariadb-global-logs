<?php

namespace App\Controllers;

use App\Models;

class Index extends Base {
	
	/** @return void */
	public function IndexAction () {
		$this->view->title = 'MariaDB General Logs';
		$this->view->logFiles = \App\Models\LogFile::ListAllWithDbInfo();
		$this->view->dataDir = \App\Models\Base::GetDataDir();
		$this->view->logsProcessingProgressesUrl = $this->Url(':LogsProcessingProgresses');
		$form = $this->getListProcessingStartForm();
		$form->Init();
		$form->PreDispatch();
		$this->view->errors = $form->GetErrors();
		$formCsrf = $form->GetCsrf();
		list ($this->view->csrfName, $this->view->csrfValue) = [
			$formCsrf->name, $formCsrf->value
		];
		$this->view->Js('varFoot')
			->Append(self::$staticPath . '/js/LogsList.js');
	}

	/** @return void */
	public function ProcessingStartAction () {
		$form = $this->getListProcessingStartForm();
		list($result, $errors, $data) = $form->Submit();
		if ($result !== \MvcCore\Ext\IForm::RESULT_ERRORS) {
			try {
				$hash = $this->GetParam('hash', 'a-zA-Z0-9');
				/** @var \App\Models\LogFile $logFile */
				$logFile = \App\Models\LogFile::GetFileByHash($hash);
				if (!$logFile->IsSavedInDatabase()) 
					$logFile->Save();
				$logFile
					->SetProcessed(\App\Models\LogFile::PROCESSING)
					->Save();
		
				$logFileParsingProcess = \App\Models\BgProcesses\LogFile::CreateNew(
					$logFile->GetIdGeneralLog(), []
				);
				$logFileParsingProcess->Save();
				$logFileParsingProcess->Start();
			} catch (\Throwable $e) {
				\MvcCore\Debug::Log($e);
				$form->AddError($e->getMessage());
			}
		}
		$form->SubmittedRedirect();
	}

	public function LogsProcessingProgressesAction () {
		$logsFilesIdsRaw = $this->GetParam('logs_files_ids', '0-9,');
		$logsFilesIds = explode(',', $logsFilesIdsRaw);
		$bgProcesses = \App\Models\BgProcess::GetByLogsIdsCtrlAndAction(
			$logsFilesIds, '\App\Controllers\BgProcesses\LogFile', 'Index'
		);
		$result = [];
		foreach ($logsFilesIds as $logFileIdStr) {
			$logFileId = intval($logFileIdStr);
			if (isset($bgProcesses[$logFileId])) {
				/** @var \App\Models\BgProcess $bgProcess */
				$bgProcess = $bgProcesses[$logFileId];
				$progress = $bgProcess->GetProgress();
				$progressFormatted = number_format($progress ?: 0.0, 2, '.', '');
				$done = !($progress < 100.0);
				if ($done) {
					$progressText = 'Processed';
				} else {
					$progressText = $this->getBgProcessProgressText(
						$bgProcess, $progress, $progressFormatted
					);
				}
				$generalLog = $bgProcess->GetGeneralLog();
				$itemData = [
					'done'		=> $done,
					'progress'	=> $progressText,
					'fileSize'	=> number_format(
						$generalLog->GetFileSize(),
						0, '.', ' '
					),
				];
				if ($done)
					$itemData['linkCode'] = 
						'<a href="'
							. $this->Url(
								'Connections:Index', 
								['id_general_log' => $bgProcess->GetIdGeneralLog()]
							)
						. '">' . $generalLog->GetFileName() . '</a>';
				$result[$logFileId] = $itemData;
			} else {
				$result[$logFileId] = [
					'done'		=> FALSE,
					'progress'	=> 'Processing',
					'fileSize'	=> NULL,
				];
			}
		}
		$this->JsonResponse([
			'success'	=> TRUE,
			'data'		=> $result,
		]);
	}

	/**
	 * @param \App\Models\BgProcess $bgProcess 
	 * @param float $progress 
	 * @param string $progressFormatted 
	 * @return string
	 */
	protected function getBgProcessProgressText ($bgProcess, $progress, $progressFormatted) {
		$processingText = 'Processing';
		if ($progress === 0.0 || $progress === NULL) return $processingText;
		$now = new \DateTime('now');
		$secondInterval = new \DateInterval('PT1S');
		$secondInterval->invert = 1;
		$prevSec = clone $now;
		$prevSec->add($secondInterval);
		$started = $bgProcess->GetStarted() ?: $prevSec;
		$now->setTimezone(new \DateTimeZone('UTC'));
		$spentTime = $now->diff($started);
		$spentSeconds = $now->getTimestamp() - $started->getTimestamp();
		$totalSeconds = intval(floatval($spentSeconds) / floatval($progress) * 100.0);
		$etaTotalSeconds = $totalSeconds - $spentSeconds;
		$etaHours = intval(floor($etaTotalSeconds / 3600));
		if ($etaHours < 0) return $processingText;
		$etaMinutes = intval(floor(((floatval($etaTotalSeconds) / 3600.0) - $etaHours) * 60.0));
		$etaSeconds = intval((floatval($etaTotalSeconds / 60.0) - ($etaHours * 60) - $etaMinutes) * 60.0);
		$etaTime = new \DateInterval("PT{$etaHours}H{$etaMinutes}M{$etaSeconds}S");
		$spendTimeFormated = $spentTime->format('%H:%I:%S');
		$estimatedTimeFormated = $etaTime->format('%H:%I:%S');
		return "Processing: {$progressFormatted}%, spent: {$spendTimeFormated}, estimated: {$estimatedTimeFormated}";
	}
	
	protected function getListProcessingStartForm () {
		$successAndErrorUrl = $this->Url(':Index', ['absolute' => TRUE]);
		return (new \MvcCore\Ext\Form($this))
			->SetId('logs_list')
			->SetSuccessUrl($successAndErrorUrl)
			->SetErrorUrl($successAndErrorUrl);
	}


	/**
	 * Render not found action.
	 * @return void
	 */
	public function NotFoundAction(){
		$this->ErrorAction();
	}

	/**
	 * Render possible server error action.
	 * @return void
	 */
	public function ErrorAction () {
		$code = $this->response->GetCode();
		if ($code === 200) $code = 404;
		$this->view->title = "Error {$code}";
		$this->view->message = $this->request->GetParam('message', FALSE);
		$this->Render('error');
	}
}
