<?php

namespace App\Controllers;

class Base extends \MvcCore\Controller {
	
	protected $renderMode = \MvcCore\IView::RENDER_WITHOUT_OB_CONTINUOUSLY;

	public function Init () {
		parent::Init();
		$sysCfg = \MvcCore\Config::GetConfigSystem();
		if ($sysCfg === NULL) {
			$defaultCfgPath = \App\Models\Install::GetSysConfigRelPathDefault();
			$sysCfg = \MvcCore\Config::GetConfig($defaultCfgPath);
			\MvcCore\Config::ClearConfigCache();
			$cfgPath = \App\Models\Base::GetSysConfigRelPath();
			\MvcCore\Config::SetConfigCache($cfgPath, $sysCfg);
		}
		if (isset($sysCfg->app->locale))
			\MvcCore\Ext\Tools\Locale::SetLocale(LC_ALL, $sysCfg->app->locale);
		if (isset($sysCfg->app->timezone))
			date_default_timezone_set($sysCfg->app->timezone);
		// check instalation and redirect if necessary:
		self::_checkInstall();
	}

	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {
			$this->_preDispatchSetUpBundles();
			/** @var \MvcCore\Ext\Views\Helpers\FormatDateHelper $formatDateHelper */
			$formatDateHelper = $this->view->GetHelper('FormatDate');
			$formatDateHelper
				->SetDefaultFormatMask('yyyy-MM-dd HH:mm:ss');
			$this->view->basePath = $this->GetRequest()->GetBasePath();
		}
	}

	private function _checkInstall () {
		$currentRoute = $this->router->GetCurrentRoute();
		if ($currentRoute->GetController() == 'Install') return;
		$installModel = new \App\Models\Install;
		if (!$installModel->IsEverythingInstalled()) {
			self::Redirect($this->Url('Install:Index'));
		}
	}

	private function _preDispatchSetUpBundles () {
		$cfg = $this->GetConfigSystem();
		\MvcCore\Ext\Views\Helpers\Assets::SetGlobalOptions(
			(array) $cfg->assets
		);
		$static = self::$staticPath;
		$this->view->Css('fixedHead')
			->AppendRendered($static . '/css/fonts.css')
			->AppendRendered($static . '/css/all.css');
		$this->view->Js('fixedHead')
			->Append($static . '/js/ajax.min.js');
	}
}
