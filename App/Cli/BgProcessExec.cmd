@set id_bg_process=%1

:: @set COMPUTERNAME=TOM
@set bin_dir=%cd%
@set app_root_dir=%bin_dir:\=/%/../..

@Fork.exe php.cmd -d max_execution_time=0 -d memory_limit=-1 "%app_root_dir%/index.php" controller=bg-processes/dispatcher action=execute id_bg_process=%id_bg_process%

:: @Fork.Debug.exe php.cmd -d max_execution_time=0 -d memory_limit=-1 "%app_root_dir%/index.php" controller=bg-processes/dispatcher action=execute id_bg_process=%id_bg_process%
