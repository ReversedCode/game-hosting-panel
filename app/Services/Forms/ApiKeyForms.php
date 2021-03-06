<?php
/**
 * Created by PhpStorm.
 * User: Hugo
 * Date: 7/22/2019
 * Time: 2:02 AM
 */

namespace App\Services\Forms;

use App\ApiKey;
use App\Forms\ApiKeyForm;
use Kris\LaravelFormBuilder\Form;

class ApiKeyForms extends ServiceForm
{
	public function create(): Form
    {
		return $this->formBuilder->create(ApiKeyForm::class, [
			'method' => 'POST',
			'url'    => route('api-keys.store'),
		]);
	}

	public function edit(ApiKey $key): Form
    {
		return $this->formBuilder->create(ApiKeyForm::class, [
			'method' => 'PATCH',
			'url'    => route('api-keys.update', $key),
			'model'  => $key,
		]);
	}
}
