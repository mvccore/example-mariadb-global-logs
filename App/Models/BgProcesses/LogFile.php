<?php

namespace App\Models\BgProcesses;

class LogFile extends \App\Models\BgProcess
{
	protected $controller = '\App\Controllers\BgProcesses\LogFile';
	protected $action = 'Index';
}