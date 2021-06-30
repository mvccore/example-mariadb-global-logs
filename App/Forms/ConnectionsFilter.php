<?php

namespace App\Forms;

use \MvcCore\Ext\Forms\Fields;

class		ConnectionsFilter 
extends		\MvcCore\Ext\Form 
implements	\MvcCore\Ext\Controllers\DataGrids\Forms\IFilterForm {

	// PHP 5.4 compatible
	use \MvcCore\Ext\Controllers\DataGrids\Forms\FilterForm {
		\MvcCore\Ext\Controllers\DataGrids\Forms\FilterForm::__construct as private __constructFilterForm;
	}
	public function __construct (\MvcCore\IController $controller = NULL) {
		parent::__construct($controller);
		$this->__constructFilterForm($controller);
	}
	// PHP 5.4 compatible

	protected $id = 'connections-filter';

	/** @var \App\Models\LogFile|NULL */
	protected $generalLog = NULL;

	public function SetGeneralLog (\App\Models\LogFile $generalLog) {
		$this->generalLog = $generalLog;
		return $this;
	}

	public function Init ($submit = FALSE) {
		parent::Init($submit);

		$this->SetFormRenderMode(\MvcCore\Ext\IForm::FORM_RENDER_MODE_TABLE_STRUCTURE);

		$reqCountMin = (new Fields\Number)
			->SetValidators(['IntNumber'])
			->SetName('requestsCountMin')
			->SetLabel('Min. requests');
		
		$reqCountMax = (new Fields\Number)
			->SetValidators(['IntNumber'])
			->SetName('requestsCountMax')
			->SetLabel('Max. requests');
		
		$queriesCountMin = (new Fields\Number)
			->SetValidators(['IntNumber'])
			->SetName('queriesCountMin')
			->SetLabel('Min. queries');
		
		$queriesCountMax = (new Fields\Number)
			->SetValidators(['IntNumber'])
			->SetName('queriesCountMax')
			->SetLabel('Max. queries');

		$users = (new Fields\Select)
			->SetOptions($this->generalLog->GetUsers())
			->SetName('idUser')
			->SetLabel('Users')
			->SetMultiple(TRUE);

		$filter = (new Fields\SubmitButton)
			->SetName('filter')
			->SetValue('filter');

		$this->AddFields(
			$reqCountMin, $reqCountMax, 
			$queriesCountMin, $queriesCountMax, 
			$users, 
			$filter
		);
	}
	
	public function PreDispatch ($submit = FALSE){
		parent::PreDispatch($submit);
		if ($submit) return;
		$values = [];
		if (isset($this->filtering['requestsCount'])) {
			$reqCount = $this->filtering['requestsCount'];
			if (isset($reqCount['>=']))
				$values['requestsCountMin'] = $reqCount['>='][0];
			if (isset($reqCount['<=']))
				$values['requestsCountMax'] = $reqCount['<='][0];
		}
		if (isset($this->filtering['queriesCount'])) {
			$queriesCount = $this->filtering['queriesCount'];
			if (isset($queriesCount['>=']))
				$values['queriesCountMin'] = $queriesCount['>='][0];
			if (isset($queriesCount['<=']))
				$values['queriesCountMax'] = $queriesCount['<='][0];
		}
		if (isset($this->filtering['idUser'])) {
			$userIds = $this->filtering['idUser'];
			if (isset($userIds['=']))
				$values['idUser'] = array_map('intval', $userIds['=']);
		}
		if ($values)
			$this->SetValues($values);
	}

	public function Submit (array & $rawRequestParams = []) {
		list($result, $values, $errors) = parent::Submit($rawRequestParams);
		if ($result) {
			$values = [
				'requestsCount' => [
					'>='		=> $values['requestsCountMin'],
					'<='		=> $values['requestsCountMax'],
				],
				'queriesCount' => [
					'>='		=> $values['queriesCountMin'],
					'<='		=> $values['queriesCountMax'],
				],
				'idUser'		=> [
					'='			=> $values['idUser'],
				],
			];
		}
		return [$result, $values, $errors];
	}
}