<?php
	ob_start();
	var_export($_SERVER);
	$out = ob_get_clean();
	file_put_contents(__DIR__ . '/out.log', $out);