<?php

namespace ACPT\Constants;

class ExtraFields
{
	const ATTACHMENT_ID = 'attachment_id';
	const CITY = 'city';
    const COUNTRY = 'country';
    const CURRENCY = 'currency';
    const DIAL = 'dial';
    const FORMAT = 'format';
    const FORGED_BY = 'forged_by';
    const ID = 'id';
    const LABEL = 'label';
	const LAT = 'lat';
	const LENGTH = 'length';
	const LNG = 'lng';
	const BARCODE_VALUE = 'barcode_value';
	const QR_CODE_VALUE = 'qr_code_value';
	const TYPE = 'type';
	const WEIGHT = 'weight';

	const ALLOWED_VALUES = [
		self::ATTACHMENT_ID,
		self::BARCODE_VALUE,
        self::CITY,
		self::COUNTRY,
		self::CURRENCY,
		self::DIAL,
		self::FORMAT,
        self::FORGED_BY,
		self::ID,
		self::LAT,
		self::LABEL,
		self::LENGTH,
		self::LNG,
		self::QR_CODE_VALUE,
		self::TYPE,
		self::WEIGHT,
	];
}


