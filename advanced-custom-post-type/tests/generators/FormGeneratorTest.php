<?php

namespace ACPT\Tests;

use ACPT\Constants\FormAction;
use ACPT\Core\Generators\Form\FormGenerator;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Form\FormModel;

class FormGeneratorTest extends AbstractTestCase
{
	/**
	 * @test
	 * @throws \Exception
	 */
	public function canRenderTheForm()
	{
		$formModel = FormModel::hydrateFromArray([
			'name' => 'form',
			'label' => 'label',
			'action' => FormAction::PHP,
			'key' => 'abcd1234'
		]);

		$textField = FormFieldModel::hydrateFromArray([
			'key' => 'abcd1234',
			'group' => "Standard fields",
			'name' => "textual",
			'type' => FormFieldModel::TEXT_TYPE,
			'isRequired' => false,
			'sort' => 1,
			'label' => "Text label",
			'description' => 'Text field description',
			'extra' => [
				"placeholder" => "this is a placeholder",
				"defaultValue" => "default",
			],
			'settings' => []
		]);

		$urlField = FormFieldModel::hydrateFromArray([
			'key' => 'abcd56789',
			'group' => "Standard fields",
			'name' => "url",
			'type' => FormFieldModel::URL_TYPE,
			'isRequired' => false,
			'sort' => 2,
			'label' => "URL label",
			'description' => 'url field description',
			'extra' => [
				"placeholder" => "this is a placeholder",
				"defaultValue" => "https://acpt.io",
				"labelDefaultValue" => "ACPT",
				"labelPlaceholder" => "This is the anchor text",
			],
			'settings' => []
		]);

		$formModel->addField($textField);
		$formModel->addField($urlField);

		$generator = new FormGenerator($formModel);
		$generated = $generator->render();

		$parsed = $this->parseHtml($generated);
		$forms = $parsed->getElementsByTagName("form");

		$this->assertEquals(1, $forms->count());

		$form = $forms->item(0);

		// form
		$this->assertEquals("abcd1234", $form->getAttribute("id"));
		$this->assertEquals("post", $form->getAttribute("method"));
		$this->assertEquals("multipart/form-data", $form->getAttribute("enctype"));
		$this->assertEquals("", $form->getAttribute("action"));

		// text field
		$textInput = $parsed->getElementById($textField->getName());
		$textInputLabel = $form->getElementsByTagName("label")->item(0);
		$textInputDescription = $parsed->getElementById($textField->getName()."_description");

		$this->assertEquals($textField->getName(), $textInputLabel->getAttribute("for"));
		$this->assertEquals("acpt-form-label", $textInputLabel->getAttribute("class"));
		$this->assertEquals($textField->getLabel(), $textInputLabel->textContent);

		$this->assertEquals($textField->getDescription(), $textInputDescription->textContent);

		$this->assertEquals($textField->getName(), $textInput->getAttribute("id"));
		$this->assertEquals($textField->getName(), $textInput->getAttribute("name"));
		$this->assertEquals("text", $textInput->getAttribute("type"));
		$this->assertEquals("acpt-form-control", $textInput->getAttribute("class"));
		$this->assertEquals($textField->getExtra()['defaultValue'], $textInput->getAttribute("value"));
		$this->assertEquals($textField->getExtra()['placeholder'], $textInput->getAttribute("placeholder"));

		// URL field
		$urlInput = $parsed->getElementById($urlField->getName());
		$urlInputLabel = $form->getElementsByTagName("label")->item(1);
		$urlInputDescription = $parsed->getElementById($urlField->getName()."_description");

		$this->assertEquals($urlField->getName(), $urlInputLabel->getAttribute("for"));
		$this->assertEquals("acpt-form-label", $urlInputLabel->getAttribute("class"));
		$this->assertEquals($urlField->getLabel(), $urlInputLabel->textContent);

		$this->assertEquals($urlField->getDescription(), $urlInputDescription->textContent);

		$this->assertEquals($urlField->getName(), $urlInput->getAttribute("id"));
		$this->assertEquals($urlField->getName(), $urlInput->getAttribute("name"));
		$this->assertEquals("url", $urlInput->getAttribute("type"));
		$this->assertEquals("acpt-form-control", $urlInput->getAttribute("class"));
		$this->assertEquals($urlField->getExtra()['defaultValue'], $urlInput->getAttribute("value"));
		$this->assertEquals($urlField->getExtra()['placeholder'], $urlInput->getAttribute("placeholder"));

		$urlLabelInput = $parsed->getElementById($urlField->getName()."_label");
		$this->assertEquals($urlField->getName()."_label", $urlLabelInput->getAttribute("id"));
		$this->assertEquals($urlField->getName()."_label", $urlLabelInput->getAttribute("name"));
		$this->assertEquals("text", $urlLabelInput->getAttribute("type"));
		$this->assertEquals("acpt-form-control", $urlLabelInput->getAttribute("class"));
		$this->assertEquals($urlField->getExtra()['labelDefaultValue'], $urlLabelInput->getAttribute("value"));
		$this->assertEquals($urlField->getExtra()['labelPlaceholder'], $urlLabelInput->getAttribute("placeholder"));
	}
}
