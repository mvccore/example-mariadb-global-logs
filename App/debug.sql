-- clear parsed global log data with id: 2

DELETE `mysql_global_logs`.`queries` 
FROM `mysql_global_logs`.`queries` 
JOIN `mysql_global_logs`.`connections` ON
	`mysql_global_logs`.`queries`.`id_connection` = `mysql_global_logs`.`connections`.`id_connection`
WHERE `mysql_global_logs`.`connections`.`id_general_log` = 2;

DELETE FROM `mysql_global_logs`.`connections` WHERE  `id_general_log`=2;

DELETE FROM `mysql_global_logs`.`bg_processes` WHERE  `id_bg_process`=2;
ALTER TABLE `bg_processes` AUTO_INCREMENT=1;
	
DELETE FROM `mysql_global_logs`.`general_logs` WHERE  `id_general_log`=2;
ALTER TABLE `general_logs` AUTO_INCREMENT=1;