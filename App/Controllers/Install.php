<?php

namespace App\Controllers;

use App\Models;

class Install extends \App\Controllers\Base
{
	protected $installModel;

	public function IndexAction () {
		$this->view->title = 'Instalation';

		$this->installModel = new \App\Models\Install;
		$this->view->configInstalledBefore = $this->installModel->IsConfigInstalled();
		$this->view->dataInstalledBefore = $this->installModel->IsDataDirInstalled();
		$this->view->cliInstalledBefore = $this->installModel->IsCliDirInstalled();
		$this->view->dbInstalledBefore = $this->installModel->IsDbInstalled();

		if (!$this->installModel->IsConfigInstalled()) {
			try {
				$this->view->configInstalledAfter = $this->installModel->InstallConfig();
			} catch (\Exception $e) {
				$this->view->configInstallErrorMsg = $e->getMessage();
				$this->view->configInstallStack = $e->getTraceAsString();
				$this->view->configInstalledAfter = FALSE;
			}
		}
		if (!$this->installModel->IsDataDirInstalled()) {
			try {
				$this->view->dataInstalledAfter = $this->installModel->InstallDataDir();
			} catch (\Exception $e) {
				$this->view->dataInstallErrorMsg = $e->getMessage();
				$this->view->dataInstallStack = $e->getTraceAsString();
				$this->view->dataInstalledAfter = FALSE;
			}
		}
		if (!$this->installModel->IsCliDirInstalled()) {
			try {
				$this->view->cliInstalledAfter = $this->installModel->InstallCliDir();
			} catch (\Exception $e) {
				$this->view->cliInstallErrorMsg = $e->getMessage();
				$this->view->cliInstallStack = $e->getTraceAsString();
				$this->view->cliInstalledAfter = FALSE;
			}
		}
		if (!$this->installModel->IsDbInstalled()) {
			try {
				$this->view->dbInstalledAfter = $this->installModel->InstallDb();
			} catch (\Exception $e) {
				$this->view->dbInstallErrorMsg = $e->getMessage();
				$this->view->dbInstallCmd = $e->getPrevious() ? $e->getPrevious()->getMessage() : NULL;
				$this->view->dbInstallStack = $e->getTraceAsString();
				$this->view->dbInstalledAfter = FALSE;
			}
		}
	}
}