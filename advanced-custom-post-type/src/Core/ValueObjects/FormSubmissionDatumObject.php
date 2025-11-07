<?php

namespace ACPT\Core\ValueObjects;

use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Utils\Data\Sanitizer;

class FormSubmissionDatumObject implements \JsonSerializable
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * FormSubmissionDatumObject constructor.
	 *
	 * @param $name
	 * @param $type
	 * @param $value
	 */
	public function __construct(
		$name,
		$type,
		$value
	)
	{
		$this->name = $name;
		$this->setType($type);
		$this->value = Sanitizer::sanitizeRawData($type, $value);
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	private function setType($type)
	{
		$allowed = [
			FormFieldModel::ACCEPTANCE_TYPE,
			FormFieldModel::ADDRESS_TYPE,
			FormFieldModel::ADDRESS_MULTI_TYPE,
			FormFieldModel::BARCODE_TYPE,
			FormFieldModel::BUTTON_TYPE,
			FormFieldModel::CAPTCHA_TYPE,
			FormFieldModel::CHECKBOX_TYPE,
			FormFieldModel::COLOR_TYPE,
			FormFieldModel::COUNTRY_TYPE,
			FormFieldModel::CURRENCY_TYPE,
			FormFieldModel::DATE_TYPE,
			FormFieldModel::DATE_TIME_TYPE,
			FormFieldModel::DATE_RANGE_TYPE,
			FormFieldModel::EMAIL_TYPE,
			FormFieldModel::EMBED_TYPE,
			FormFieldModel::FILE_TYPE,
			FormFieldModel::HIDDEN_TYPE,
			FormFieldModel::HTML_TYPE,
			FormFieldModel::ID_TYPE,
			FormFieldModel::ICON_TYPE,
			FormFieldModel::LENGTH_TYPE,
			FormFieldModel::LIST_TYPE,
			FormFieldModel::NUMBER_TYPE,
			FormFieldModel::PASSWORD_TYPE,
			FormFieldModel::PHONE_TYPE,
			FormFieldModel::POST_TYPE,
			FormFieldModel::POST_OBJECT_TYPE,
			FormFieldModel::QR_CODE_TYPE,
			FormFieldModel::RADIO_TYPE,
			FormFieldModel::RANGE_TYPE,
			FormFieldModel::RATING_TYPE,
			FormFieldModel::REPEATER_TYPE,
			FormFieldModel::SELECT_TYPE,
			FormFieldModel::TERM_OBJECT_TYPE,
			FormFieldModel::TEXT_TYPE,
			FormFieldModel::TEXTAREA_TYPE,
			FormFieldModel::TIME_TYPE,
			FormFieldModel::TOGGLE_TYPE,
			FormFieldModel::TURNSTILE_TYPE,
			FormFieldModel::URL_TYPE,
            FormFieldModel::USER_TYPE,
            FormFieldModel::WEIGHT_TYPE,
			FormFieldModel::WORDPRESS_POST_THUMBNAIL,
			FormFieldModel::WORDPRESS_POST_TITLE,
			FormFieldModel::WORDPRESS_POST_CONTENT,
			FormFieldModel::WORDPRESS_POST_EXCERPT,
			FormFieldModel::WORDPRESS_POST_DATE,
			FormFieldModel::WORDPRESS_POST_AUTHOR,
			FormFieldModel::WORDPRESS_POST_TAXONOMIES,
			FormFieldModel::WORDPRESS_TERM_NAME,
			FormFieldModel::WORDPRESS_TERM_DESCRIPTION,
			FormFieldModel::WORDPRESS_TERM_SLUG,
			FormFieldModel::WORDPRESS_USER_EMAIL,
			FormFieldModel::WORDPRESS_USER_BIO,
			FormFieldModel::WORDPRESS_USER_FIRST_NAME,
			FormFieldModel::WORDPRESS_USER_LAST_NAME,
			FormFieldModel::WORDPRESS_USER_USERNAME,
			FormFieldModel::WORDPRESS_USER_PASSWORD,
		];

		if(!in_array($type, $allowed)){
			throw new \InvalidArgumentException("`".$type."` is not an allowed field type");
		}

		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

    /**
     * @return mixed
     */
    public function getValueAsString()
    {
        if(is_array($this->value)){
            return json_encode($this->value);
        }

        return $this->value;
    }

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'name' => $this->getName(),
			'type' => $this->getType(),
			'value' => $this->getValue(),
		];
	}
}