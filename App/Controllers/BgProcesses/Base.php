<?php

namespace App\Controllers\BgProcesses;

class Base extends \MvcCore\Controller
{
	protected $viewEnabled = FALSE;

	/** @var \App\Models\BgProcess */
	protected $bgProcess;

	/**
	 * Miliseconds to sleep between every removing step 
	 * to let database to do other things.
	 * @var int
	 */
	protected $stepSleepMs = 200;

	public function SetBgProcess ($bgProcess) {
		$this->bgProcess = $bgProcess;
		return $this;
	}

	public function SetStepSleepMs ($stepSleepMs) {
		$this->stepSleepMs = $stepSleepMs;
		return $this;
	}

	protected function stepSleep () {
		if ($this->stepSleepMs > 0)
			usleep($this->stepSleepMs * 1000);
	}
}