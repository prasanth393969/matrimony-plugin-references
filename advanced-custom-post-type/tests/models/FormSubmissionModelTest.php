<?php

namespace ACPT\Tests;

use ACPT\Constants\FormAction;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Form\FormModel;
use ACPT\Core\Models\Form\FormSubmissionModel;
use ACPT\Core\ValueObjects\FormSubmissionDatumObject;

class FormSubmissionModelTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function wrongFormSubmissionData()
	{
		try {
			$form = FormModel::hydrateFromArray([
				'name' => 'test',
				'label' => 'test',
				'key' => 'abcb1234',
				'action' => FormAction::FILL_META,
			]);

			$formSubmission = FormSubmissionModel::hydrateFromArray([
				'formId' => $form->getId(),
				'action' => $form->getAction(),
				'callback' => '',
				'createdAt' => new \DateTime(),
			]);

			$datum1 = new FormSubmissionDatumObject('text', 'not-allowed-type', 'this is a test');

			$formSubmission->addDatum($datum1);
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), "`not-allowed-type` is not an allowed field type");
		}
	}

	/**
	 * @test
	 */
	public function formSubmissionToArray()
	{
		$form = FormModel::hydrateFromArray([
			'name' => 'test',
			'label' => 'test',
			'key' => 'abcb1234',
			'action' => FormAction::FILL_META,
		]);

		$formSubmission = FormSubmissionModel::hydrateFromArray([
			'formId' => $form->getId(),
			'action' => $form->getAction(),
			'callback' => '',
            'createdAt' => new \DateTime(),
		]);

		$datum1 = new FormSubmissionDatumObject('text', FormFieldModel::TEXT_TYPE, 'this is a test');
		$datum2 = new FormSubmissionDatumObject('email', FormFieldModel::EMAIL_TYPE, 'mauro@acpt.io');
		$datum3 = new FormSubmissionDatumObject('textarea', FormFieldModel::TEXTAREA_TYPE, 'lorem ipsum dolor facium silor.');
		$datum4 = new FormSubmissionDatumObject('select-multi', FormFieldModel::SELECT_TYPE, ['foo', 'bar', 'fsf']);
		$datum5 = new FormSubmissionDatumObject('number', FormFieldModel::NUMBER_TYPE, 123);

		$formSubmission->addDatum($datum1);
		$formSubmission->addDatum($datum2);
		$formSubmission->addDatum($datum3);
		$formSubmission->addDatum($datum4);
		$formSubmission->addDatum($datum5);

		$formSubmissionArray = json_decode(json_encode($formSubmission), true);

		$this->assertEquals($formSubmissionArray['formId'], $form->getId());
		$this->assertEquals($formSubmissionArray['action'], $form->getAction());
		$this->assertEquals($formSubmissionArray['callback'], '');
		$this->assertIsArray($formSubmissionArray['browser']);
		$this->assertEquals($formSubmissionArray['data'][0]['name'], 'text');
		$this->assertEquals($formSubmissionArray['data'][0]['type'], FormFieldModel::TEXT_TYPE);
		$this->assertEquals($formSubmissionArray['data'][1]['name'], 'email');
		$this->assertEquals($formSubmissionArray['data'][1]['type'], FormFieldModel::EMAIL_TYPE);
		$this->assertEquals($formSubmissionArray['data'][2]['name'], 'textarea');
		$this->assertEquals($formSubmissionArray['data'][2]['type'], FormFieldModel::TEXTAREA_TYPE);
		$this->assertEquals($formSubmissionArray['data'][3]['name'], 'select-multi');
		$this->assertEquals($formSubmissionArray['data'][3]['type'], FormFieldModel::SELECT_TYPE);
		$this->assertEquals($formSubmissionArray['data'][4]['name'], 'number');
		$this->assertEquals($formSubmissionArray['data'][4]['type'], FormFieldModel::NUMBER_TYPE);

		$formSubmission->removeDatum($datum3);

		$formSubmissionArray = json_decode(json_encode($formSubmission), true);

		$this->assertCount(4, $formSubmissionArray['data']);
	}
}