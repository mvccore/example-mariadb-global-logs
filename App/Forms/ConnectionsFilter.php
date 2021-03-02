<?php

namespace App\Forms;

use \MvcCore\Ext\Forms\Fields;

class		ConnectionsFilter 
extends		\MvcCore\Ext\Form 
implements	\MvcCore\Ext\Controllers\DataGrids\Forms\IFilterForm {

	use \MvcCore\Ext\Controllers\DataGrids\Forms\FilterForm;

	public function Init ($submit = FALSE) {
		parent::Init();

		$filter = (new Fields\SubmitButton)
			->SetValue('filter');

		$this->AddFields($filter);
	}

}