<?php

namespace App;

class Bootstrap {

	/**
	 * @return \MvcCore\Application
	 */
	public static function Init () {
		
		$app = \MvcCore\Application::GetInstance();

		// Patch core to use extended debug class:
		if (class_exists('\MvcCore\Ext\Debugs\Tracy')) {
			\MvcCore\Ext\Debugs\Tracy::$Editor = 'MSVS2019';
			$app->SetDebugClass('\MvcCore\Ext\Debugs\Tracy');
		}
		
		\MvcCore\Config::SetConfigSystemPath(
			\App\Models\Base::GetSysConfigRelPath()
		);

		// Set up application routes with custom names:
		
		\MvcCore\Router::GetInstance([
			'Index:Index'		=> [
				'match'			=> "#^/(index.php)?$#",
				'reverse'		=> '/',
			],
			'Install:Index'		=> "/install",
			'Connections:Index'	=> [
				'pattern'		=> "/connections/log-<id_general_log>[/<grid>]",
				'constraints'	=> ['grid' => '.*'],
			],
			'Queries:Index'		=> "/queries/connection-<id_connection>",
			'Editor:Index'		=> "/editor/log-<idGeneralLog>/<lineBegin>/<lineEnd>[/<linesRange>]",
		])->SetTrailingSlashBehaviour(-1);
		

		return $app;
	}
}
