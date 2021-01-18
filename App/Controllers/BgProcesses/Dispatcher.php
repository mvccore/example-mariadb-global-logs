<?php

namespace App\Controllers\BgProcesses;

class Dispatcher extends \App\Controllers\Base
{
	/** @var bool */
	protected $viewEnabled = FALSE;

	/** @var bool */
	protected $isWin;

	/** @var \App\Models\BgProcess */
	protected $bgProcess;
	
	/** @var \App\Controllers\BgProcesses\Base */
	protected $execController;

	public function Init () {
		parent::Init();
		$this->isWin = strtolower(substr(PHP_OS, 0, 3)) === 'win';
		$bgProcessId = $this->GetParam('id_bg_process', '0-9', NULL, 'int');
		$errorMsg = NULL;
		if ($bgProcessId === NULL) {
			$errorMsg = "Background process id param `{$bgProcessId}` is not valid.";
		} else {
			$this->bgProcess = \App\Models\BgProcess::GetById($bgProcessId);
			if ($this->bgProcess === NULL)
				$errorMsg = "Background process with id `{$bgProcessId}` doesn't exist.";
		}
		if ($errorMsg !== NULL) {
			try {
				throw new \Exception($errorMsg);
			} catch (\Throwable $e) {
				\MvcCore\Debug::Log($e);
			}
			$this->Terminate();
		}
	}

	public function ExecuteAction () {
		try {

			$execCtrlFullName = '\\' . trim($this->bgProcess->GetController(), '\\');
			$this->execController = $execCtrlFullName::CreateInstance();
			
			$this->AddChildController($this->execController);
			$this->execController
				->SetBgProcess($this->bgProcess)
				->SetLayout(NULL);

			$execCtrlClassNameDcArr = explode('/', \MvcCore\Tool::GetDashedFromPascalCase(
				trim(str_replace('\\', '/', $execCtrlFullName), '/')
			));
			array_shift($execCtrlClassNameDcArr);
			array_shift($execCtrlClassNameDcArr);
			
			$this->execController->controllerName = implode('/', $execCtrlClassNameDcArr);
			$this->execController->actionName = \MvcCore\Tool::GetDashedFromPascalCase(
				$this->bgProcess->GetAction()
			);

			$bgProcessParams = $this->bgProcess->GetParams();
			$this->request->SetParams($bgProcessParams);
			
			$this->execController->Dispatch();

			unset($this->execController);

		} catch (\Throwable $e) {
			$ctrlSerialized = serialize($this->execController);
			$date = date('y-m-d_H-i-s_v', time());
			$dumpFullPath = $this->request->GetAppRoot().'/Var/Logs/exec_ctrl_'.$date.'.dump';
			file_put_contents($dumpFullPath, $ctrlSerialized);
			if ($this->request->IsCli()) {
				\MvcCore\Debug::Log($e);
			} else {
				\MvcCore\Debug::Exception($e);
			}
		}

		$this->Terminate();
	}

	public function __sleep() {
		$nonSerializedProps = [
			'application', 'request', 'response', 'router', 'view', 'environment', 'parentController'
		];
		$type = new \ReflectionClass($this);
		/** @var $props \ReflectionProperty[] */
		$props = $type->getProperties(
			\ReflectionProperty::IS_PRIVATE | 
			\ReflectionProperty::IS_PROTECTED | 
			\ReflectionProperty::IS_PUBLIC
		);
		$result = [];
		foreach ($props as $prop) {
			if ($prop->isStatic()) continue;
			$propName = $prop->getName();
			if (!in_array($propName, $nonSerializedProps, TRUE)) 
				$result[] = $propName;
		}
		return $result;
	}
}
