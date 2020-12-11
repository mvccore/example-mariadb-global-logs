#!/bin/bash

id_bg_process=$1

cmd="php -d max_execution_time=0 -d memory_limit=-1 ../../index.php controller=bg-processes/dispatcher action=execute id_bg_process=$id_bg_process"
bash -c "exec nohup setsid ${cmd} > /dev/null 2>&1 &"

echo 1
