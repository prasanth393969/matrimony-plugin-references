<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Validators\ArgumentsArrayValidator;
use ACPT\Utils\Data\NestedValues;
use ACPT\Utils\Data\Sanitizer;
use ACPT\Utils\PHP\GeoLocation;
use ACPT\Utils\Wordpress\WPAttachment;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorSVG;

class SaveMetaFieldValueCommand extends AbstractMetaFieldValueCommand implements CommandInterface
{
	/**
	 * @inheritDoc
	 * @throws \Exception
	 */
	public function execute()
	{
		$metaFieldModelType = $this->fieldModel->getType();
        $savedFieldValue = $this->get($this->fieldModel->getDbName());
		$savedFieldType = $this->get($this->fieldModel->getDbName().'_type');
		$savedFieldId = $this->get($this->fieldModel->getDbName().'_id');
		$value = $this->args['value'];

        // check is value is changed, if not, do nothing and return true
        if($savedFieldValue == $value){
            return true;
        }

		// save _type
		if($metaFieldModelType != $savedFieldType){
			if(!$this->set($this->fieldModel->getDbName().'_type', $metaFieldModelType)){
				return false;
			}
		}

		// save _id
		if($savedFieldId != $this->fieldModel->getId()){
            if(!$this->set($this->fieldModel->getDbName().'_id', $this->fieldModel->getId())){
                return false;
            }
        }

		switch ($metaFieldModelType){

            // PASSWORD_TYPE
            case MetaFieldModel::PASSWORD_TYPE:

                $algo = $this->fieldModel->getAdvancedOption("algorithm") ?? PASSWORD_DEFAULT;

                switch ($algo){
                    default:
                    case "PASSWORD_DEFAULT":
                        $value = password_hash($value, PASSWORD_DEFAULT);
                        break;
                    case "PASSWORD_BCRYPT":
                        $value = password_hash($value, PASSWORD_BCRYPT);
                        break;
                    case "PASSWORD_ARGON2I":
                        $value = password_hash($value, PASSWORD_ARGON2I);
                        break;
                    case "PASSWORD_ARGON2ID":
                        $value = password_hash($value, PASSWORD_ARGON2ID);
                        break;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData(MetaFieldModel::PASSWORD_TYPE, $value))){
                    return false;
                }

                break;

			// ADDRESS_TYPE
			case MetaFieldModel::ADDRESS_TYPE:

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData(MetaFieldModel::ADDRESS_TYPE, $value))){
                    return false;
                }

                $coordinates = GeoLocation::getCoordinates($value);

                if(
                    !empty($coordinates) and
                    $coordinates['lat'] !== null and
                    $coordinates['lng'] !== null
                ){
                    $savedFieldLat = $this->get($this->fieldModel->getDbName().'_lat');
                    $savedFieldLng = $this->get($this->fieldModel->getDbName().'_lng');
                    $savedFieldCity = $this->get($this->fieldModel->getDbName().'_city');
                    $savedFieldCountry = $this->get($this->fieldModel->getDbName().'_country');

                    if($savedFieldLat !== $coordinates['lat'] and !$this->set( $this->fieldModel->getDbName().'_lat', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $coordinates['lat']))){
                        return false;
                    }

                    if($savedFieldLng !== $coordinates['lng'] and !$this->set( $this->fieldModel->getDbName().'_lng', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $coordinates['lng']))){
                        return false;
                    }

                    $city = GeoLocation::getCity($coordinates['lat'], $coordinates['lng']);
                    $country = GeoLocation::getCountry($coordinates['lat'], $coordinates['lng']);

                    if(!empty($city) and $savedFieldCity !== $city){
                        if(!$this->set( $this->fieldModel->getDbName().'_city', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $city))){
                            return false;
                        }
                    }

                    if(!empty($country) and $savedFieldCountry !== $country){
                        if(!$this->set( $this->fieldModel->getDbName().'_country', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $country))){
                            return false;
                        }
                    }
                }

				break;

            // AUDIO_TYPE
            case MetaFieldModel::AUDIO_TYPE:

                $wpAttachment = (is_numeric($value)) ? WPAttachment::fromId($value) : WPAttachment::fromUrl($value);
                $isAudio = $wpAttachment->isAudio();
                $idFile = $wpAttachment->getId();
                $urlFile = $wpAttachment->getSrc();

                if(!$isAudio){
                    return false;
                }

                $savedFieldValue = $this->get( $this->fieldModel->getDbName());
                $saved_file_id = $this->get( $this->fieldModel->getDbName().'_attachment_id');

                if($saved_file_id !== $idFile){
                    if(!$this->set( $this->fieldModel->getDbName().'_attachment_id', $idFile)){
                        return false;
                    }
                }

                if($savedFieldValue != $urlFile){
                    if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $urlFile))){
                        return false;
                    }
                }

                break;
			
			// CURRENCY_TYPE
			case MetaFieldModel::CURRENCY_TYPE:
			
				$mandatory_keys = [
					'amount' => [
						'required' => true,
						'type' => 'float|double|integer',
					],
					'unit' => [
						'required' => true,
						'type' => 'string',
					],
				];
			
				$validator = new ArgumentsArrayValidator();
			
				if(!$validator->validate($mandatory_keys, $value)){
					return false;
				}
			
				$amount = $value['amount'];
				$unit = $value['unit'];

				$saved_field_unit = $this->get( $this->fieldModel->getDbName().'_currency');

                if($savedFieldValue == $amount){
                    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData(MetaFieldModel::CURRENCY_TYPE, $amount))){
                    return false;
                }
			
				if($saved_field_unit !== $unit){
					if(!$this->set( $this->fieldModel->getDbName().'_currency', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $unit))){
						return false;
					}
				}
			
				break;
			
			// DATE_RANGE_TYPE
			case MetaFieldModel::DATE_RANGE_TYPE:

			    $savedFieldValue = explode(" - ", $savedFieldValue);

			    if($savedFieldValue == $value){
			        return true;
                }

				if(!is_array($value)){
					return false;
				}

				if(count($value) !== 2){
				    return false;
                }

				$from = $value[0];
				$to = $value[1];

                $date1 = new \DateTime($from);
                $date2 = new \DateTime($to);

                if ($date1 > $date2){
                    return false;
                }

				$value = implode(" - ", $value);

				if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
					return false;
				}
			
				break;

			// EMBED_TYPE
			case MetaFieldModel::EMBED_TYPE:
			
				$embed = (new \WP_Embed())->shortcode([
					'width' => 180,
					'height' => 135,
				], $value);
			
				if(!Strings::contains('<iframe', $embed)){
					return false;
				}
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				if($savedFieldValue != $value){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
						return false;
					}
				}
			
				break;
			
			// FILE_TYPE
			case MetaFieldModel::FILE_TYPE:
			
				$mandatory_keys = [
					'url' => [
						'required' => false,
						'type' => 'string',
					],
                    'id' => [
                        'required' => false,
                        'type' => 'string',
                    ],
					'label' => [
						'required' => false,
						'type' => 'string',
					],
				];
			
				$validator = new ArgumentsArrayValidator();
			
				if(!$validator->validate($mandatory_keys, $value)){
					return false;
				}

				$url = $value['url'] ?? null;
				$id = $value['id'] ?? null;

				if(!$url and !$id){
				    return false;
                }

				if($url !== null){
                    $wpAttachment = WPAttachment::fromUrl($url);
                }

                if($id !== null){
                    $wpAttachment = WPAttachment::fromId($id);
                }

                if(!isset($wpAttachment)){
                    return false;
                }

                if($wpAttachment->isEmpty()){
                    return false;
                }

                $idFile = $wpAttachment->getId();
                $urlFile = $wpAttachment->getSrc();
                $isImage = $wpAttachment->isImage();
                $isVideo = $wpAttachment->isVideo();
                $isAudio = $wpAttachment->isAudio();

				$label = (isset($value['label']) and !empty($value['label'])) ? $value['label'] : $urlFile;
			
				if($isImage or $isVideo or $isAudio){
					return false;
				}
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				$saved_field_label = $this->get( $this->fieldModel->getDbName().'_label');
				$saved_file_id = $this->get( $this->fieldModel->getDbName().'_attachment_id');
			
				if($idFile !== $saved_file_id){
					if(!$this->set( $this->fieldModel->getDbName().'_attachment_id', $idFile)){
						return false;
					}
				}
			
				if($savedFieldValue !== $urlFile){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $urlFile))){
						return false;
					}
				}
			
				if($saved_field_label !== $label){
					if(!$this->set( $this->fieldModel->getDbName().'_label', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $label))){
						return false;
					}
				}
			
				break;
			
			// GALLERY_TYPE
			case MetaFieldModel::IMAGE_SLIDER_TYPE:
			case MetaFieldModel::AUDIO_MULTI_TYPE:
			case MetaFieldModel::GALLERY_TYPE:

				if(!is_array($value)){
					return false;
				}

				// Max 2 images allowed for image slider
				if($metaFieldModelType === MetaFieldModel::IMAGE_SLIDER_TYPE and count($value) > 2){
                    return false;
                }
			
				$idFiles = [];
				$files = [];

				foreach ($value as $file){
                    $wpAttachment = (is_numeric($file)) ? WPAttachment::fromId($file) : WPAttachment::fromUrl($file);
					$isImage = $wpAttachment->isImage();
					$isAudio = $wpAttachment->isAudio();
					$idFile = $wpAttachment->getId();
                    $urlFile = $wpAttachment->getSrc();

					if(
                        ($isAudio and $metaFieldModelType === MetaFieldModel::AUDIO_MULTI_TYPE) or
                        ($isImage and $metaFieldModelType === MetaFieldModel::GALLERY_TYPE) or
                        ($isImage and $metaFieldModelType === MetaFieldModel::IMAGE_SLIDER_TYPE)
                    ){
                        $idFiles[] = $idFile;
                        $files[] = $urlFile;
                    }
				}

				if(empty($idFiles)){
					return false;
				}
			
				$idFiles = implode(",", $idFiles);
				$savedFileIds = $this->get( $this->fieldModel->getDbName().'_attachment_id');
			
				if($savedFileIds !== $idFiles){
					if(!$this->set( $this->fieldModel->getDbName().'_attachment_id', $idFiles)){
						return false;
					}
				}
			
				$savedFieldValue = $this->get($this->fieldModel->getDbName());
			
				if($savedFieldValue != $value){
					if(!$this->set($this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $files))){
						return false;
					}
				}
			
				break;
			
			// IMAGE_TYPE
			case MetaFieldModel::IMAGE_TYPE:

                $wpAttachment = (is_numeric($value)) ? WPAttachment::fromId($value) : WPAttachment::fromUrl($value);
				$isImage = $wpAttachment->isImage();
				$idFile = $wpAttachment->getId();
                $urlFile = $wpAttachment->getSrc();

				if(!$isImage){
					return false;
				}
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				$saved_file_id = $this->get( $this->fieldModel->getDbName().'_attachment_id');
			
				if($saved_file_id !== $idFile){
					if(!$this->set( $this->fieldModel->getDbName().'_attachment_id', $idFile)){
						return false;
					}
				}
			
				if($savedFieldValue != $urlFile){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $urlFile))){
						return false;
					}
				}
			
				break;
			
			// LENGTH_TYPE
			case MetaFieldModel::LENGTH_TYPE:
			
				$mandatory_keys = [
					'length' => [
						'required' => true,
						'type' => 'float|double|integer',
					],
					'unit' => [
						'required' => true,
						'type' => 'string',
					],
				];
			
				$validator = new ArgumentsArrayValidator();
			
				if(!$validator->validate($mandatory_keys, $value)){
					return false;
				}
			
				$length = $value['length'];
				$unit = $value['unit'];
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				$saved_field_unit = $this->get( $this->fieldModel->getDbName().'_length');

                if($savedFieldValue == $length){
                    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData(MetaFieldModel::WEIGHT_TYPE, $length))){
                    return false;
                }
			
				if($saved_field_unit !== $unit){
					if(!$this->set( $this->fieldModel->getDbName().'_length', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $unit))){
						return false;
					}
				}
			
				break;
			
			case MetaFieldModel::NUMBER_TYPE:
			case MetaFieldModel::RANGE_TYPE:

				$min = $this->fieldModel->getAdvancedOption('min');
				$max = $this->fieldModel->getAdvancedOption('max');
			
				if($min !== null and (int)$value < (int)$min){
					return false;
				}
			
				if($max !== null and (int)$value > (int)$max){
					return false;
				}

				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				if($savedFieldValue != $value){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
						return false;
					}
				}
			
				break;

			// LIST_TYPE
			case MetaFieldModel::LIST_TYPE:
				if(!is_array($value)){
					return false;
				}

				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				if($savedFieldValue != $value){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
						return false;
					}
				}

				break;

            // BARCODE_TYPE
            case MetaFieldModel::BARCODE_TYPE:

                if(!isset($value['value'])){
                    return false;
                }

                if(!is_string($value['value'])){
                    return false;
                }

                $val = $value['value'];
                $savedBarcodeValue = $this->get( $this->fieldModel->getDbName().'_barcode_value');

                if($savedFieldValue == $val){
                    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $val))){
                    return false;
                }

                // Generate Barcode
                try {
                    $format = $value['format'] ?? "code128";
                    $color = $value['color'] ?? "#000000";

                    $allowedFormats = [
                        "code128",
                        "ean13",
                        "ean8",
                        "ean5",
                        "ean2",
                        "upc",
                        "code39",
                        "itf14",
                        "msi",
                        "pharmacode",
                    ];

                    if(!in_array($format, $allowedFormats)){
                        return false;
                    }

                    $generator = new BarcodeGeneratorSVG();
                    $f = $generator::TYPE_CODE_128;

                    switch ($format){
                        case "code128":
                            $f = $generator::TYPE_CODE_128;
                            break;

                        case "ean13":
                            $f = $generator::TYPE_EAN_13;
                            break;

                        case "ean8":
                            $f = $generator::TYPE_EAN_8;
                            break;

                        case "ean5":
                            $f = $generator::TYPE_EAN_5;
                            break;

                        case "ean2":
                            $f = $generator::TYPE_EAN_2;
                            break;

                        case "upc":
                            $f = $generator::TYPE_UPC_A;
                            break;

                        case "code39":
                            $f = $generator::TYPE_CODE_39;
                            break;

                        case "itf14":
                            $f = $generator::TYPE_ITF_14;
                            break;

                        case "msi":
                            $f = $generator::TYPE_MSI;
                            break;

                        case "pharmacode":
                            $f = $generator::TYPE_PHARMA_CODE;
                            break;
                    }

                    $svg = $generator->getBarcode($val, $f, 3, 100, $color);
                    $barcodeValueObject = json_encode([
                        'svg' => htmlentities($svg),
                        'format' => $format,
                        'color' => $color,
                        'bgColor' => '#ffffff',
                    ]);

                    // barcode_value
                    if($savedBarcodeValue == $barcodeValueObject){
                        return true;
                    }

                    if(!$this->set( $this->fieldModel->getDbName().'_barcode_value', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $barcodeValueObject))){
                        return false;
                    }

                } catch (\Exception $exception){

                    do_action("acpt/error", $exception);

                    return false;
                }

                break;

            // QR_CODE_TYPE
            case MetaFieldModel::QR_CODE_TYPE:

                if(!Strings::isUrl($value)){
                    return false;
                }

                $savedQRCodeValue = $this->get( $this->fieldModel->getDbName().'_qr_code_value');

                if($savedFieldValue == $value){
                    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
                    return false;
                }

                // Generate QR Code
                try {
                    $writer = new PngWriter();
                    $qrCode = new QrCode(
                        $value,
                        new Encoding('UTF-8'),
                        new ErrorCorrectionLevelLow(),
                        200,
                        0,
                        new RoundBlockSizeModeMargin(),
                        new Color(0, 0, 0),
                        new Color(255, 255, 255)
                    );

                    $qrCodeObject = $writer->write($qrCode);

                    $QRCodeValueObject = json_encode([
                        'img' => $qrCodeObject->getDataUri(),
                        'resolution' => 200,
                        'colorLight' => '#ffffff',
                        'colorDark' => '#000000',
                    ]);

                    // qr_code_value
                    if($savedQRCodeValue == $QRCodeValueObject){
                        return true;
                    }

                    if(!$this->set( $this->fieldModel->getDbName().'_qr_code_value', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $QRCodeValueObject))){
                        return false;
                    }

                } catch (\Exception $exception){

                    do_action("acpt/error", $exception);

                    return false;
                }

                break;

			// REPEATER_TYPE
			case MetaFieldModel::REPEATER_TYPE:
			
				if(!is_array($value)){
					return false;
				}

                $values = NestedValues::addOrUpdateRawValue(
                    $this->fieldModel,
                    (empty($savedFieldValue) or !is_array($savedFieldValue)) ? [] : $savedFieldValue,
                    $value
                );

				if($savedFieldValue == $values){
				    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $values))){
                    return false;
                }

				break;
			
			// FLEXIBLE_CONTENT_TYPE
			case MetaFieldModel::FLEXIBLE_CONTENT_TYPE:
			
				if(!is_array($value)){
					return false;
				}
			
				if(!isset($value['blocks'])){
					return false;
				}

                $values = [
                    'blocks' => []
                ];

                foreach ($value['blocks'] as $block_index => $block){
                    if(is_array($block)){
                        foreach ($block as $block_name => $block_values){
                            if(is_array($block_values)){
                                $values['blocks'][(int)$block_index] = NestedValues::formatBlockValues(
                                    $this->fieldModel,
                                    $block_name,
                                    $block_values
                                );
                            }
                        }
                    }
                }

                if($savedFieldValue == $values){
                    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $values))){
                    return false;
                }

				break;
			
			// POST_TYPE
			case MetaFieldModel::POST_TYPE:

                try {
                    $rawValue = Sanitizer::sanitizeRawData($metaFieldModelType, $value);

                    if(!is_array($rawValue)){
                        $rawValue = [$rawValue];
                    }

                    $rawValue = implode(",", $rawValue);

                    $command = new HandleRelationsCommand($this->fieldModel, $rawValue, $this->location, $this->belongsTo);
                    $command->execute();

                    return true;
                } catch (\Exception $exception){

                    do_action("acpt/error", $exception);

                    return false;
                }

			// SELECT_TYPE
			case MetaFieldModel::RADIO_TYPE:
			case MetaFieldModel::SELECT_TYPE:
			
				if(in_array($value, $this->fieldModel->getOptionValues())){
					$savedFieldValue = $this->get( $this->fieldModel->getDbName());
					if($savedFieldValue != $value){
						if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
							return false;
						}
					}
			
					return true;
				}
			
				return false;
			
				break;
			
			// SELECT_MULTI_TYPE
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
			
				if(!is_array($value)){
					return false;
				}
			
				$optionValues = $this->fieldModel->getOptionValues();
			
				foreach ($value as $item){
					if(!in_array($item, $optionValues)){
						return false;
					}
				}
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				if($savedFieldValue != $value){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
						return false;
					}
				}
			
				break;

			// TABLE_TYPE
			case MetaFieldModel::TABLE_TYPE:

				if(!is_array($value)){
					return false;
				}

				$generator = new TableFieldGenerator(json_encode($value));

				if(!$generator->theJSONIsValid()){
					return false;
				}

				if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
					return false;
				}

				break;
			
			// VIDEO_TYPE
			case MetaFieldModel::VIDEO_TYPE:

                $wpAttachment = (is_numeric($value)) ? WPAttachment::fromId($value) : WPAttachment::fromUrl($value);
				$isVideo = $wpAttachment->isVideo();
				$idFile = $wpAttachment->getId();
				$urlFile = $wpAttachment->getSrc();

				if(!$isVideo){
					return false;
				}
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				$saved_file_id = $this->get( $this->fieldModel->getDbName().'_attachment_id');
			
				if($saved_file_id !== $idFile){
					if(!$this->set( $this->fieldModel->getDbName().'_attachment_id', $idFile)){
						return false;
					}
				}
			
				if($savedFieldValue != $urlFile){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $urlFile))){
						return false;
					}
				}
			
				break;
			
			// WEIGHT_TYPE
			case MetaFieldModel::WEIGHT_TYPE:
			
				$mandatory_keys = [
					'weight' => [
						'required' => true,
						'type' => 'float|double|integer',
					],
					'unit' => [
						'required' => true,
						'type' => 'string',
					],
				];
			
				$validator = new ArgumentsArrayValidator();
			
				if(!$validator->validate($mandatory_keys, $value)){
					return false;
				}
			
				$weight = $value['weight'];
				$unit = $value['unit'];
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				$saved_field_unit = $this->get( $this->fieldModel->getDbName().'_weight');

                if($savedFieldValue == $weight){
                    return true;
                }

                if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData(MetaFieldModel::WEIGHT_TYPE, $weight))){
                    return false;
                }
			
				if($saved_field_unit !== $unit){
					if(!$this->set( $this->fieldModel->getDbName().'_weight', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $unit))){
						return false;
					}
				}
			
				break;

			// COUNTRY_TYPE
			case MetaFieldModel::COUNTRY_TYPE:
				$mandatory_keys = [
					'value' => [
						'required' => true,
						'type' => 'string',
					],
					'country' => [
						'required' => true,
						'type' => 'string',
					],
				];

				$validator = new ArgumentsArrayValidator();

				if(!$validator->validate($mandatory_keys, $value)){
					return false;
				}

				$countryValue = $value['value'];
				$countryCode = $value['country'];

				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				$savedFieldCountry = $this->get( $this->fieldModel->getDbName().'_country');

				if($savedFieldValue !== $countryValue){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $countryValue))){
						return false;
					}
				}

				if($savedFieldCountry !== $countryCode){
					if(!$this->set( $this->fieldModel->getDbName().'_country', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $countryCode))){
						return false;
					}
				}

				break;

			// URL_TYPE
			case MetaFieldModel::URL_TYPE:

				if(is_string($value)){
					$value = [
						'url' => $value,
						'label' => $value,
					];
				}
			
				$mandatory_keys = [
					'url' => [
						'required' => true,
						'type' => 'string',
					],
					'label' => [
						'required' => false,
						'type' => 'string',
					],
				];
			
				$validator = new ArgumentsArrayValidator();
			
				if(!$validator->validate($mandatory_keys, $value)){
					return false;
				}
			
				$url = $value['url'];
				$label = (isset($value['label']) and !empty($value['label'])) ? $value['label'] : $url;

                if($savedFieldValue !== $url){
                    if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $url))){
                        return false;
                    }
                }

                $savedFieldLabel = $this->get( $this->fieldModel->getDbName().'_label');

				if($savedFieldLabel !== $label){
					if(!$this->set( $this->fieldModel->getDbName().'_label', Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $label))){
						return false;
					}
				}
			
				break;
			
			// RATING_TYPE
			case MetaFieldModel::RATING_TYPE:
				if(!is_numeric($value)){
					return false;
				}
			
				if($value < 1){
					return false;
				}
			
				if($value > 10){
					return false;
				}
			
				$savedFieldValue = $this->get( $this->fieldModel->getDbName());
				if($savedFieldValue != $value){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
						return false;
					}
				}
				break;
			
			// Default behaviour
			default:
				$savedFieldValue = $this->get($this->fieldModel->getDbName());
				if($savedFieldValue != $value){
					if(!$this->set( $this->fieldModel->getDbName(), Sanitizer::sanitizeRawData($metaFieldModelType, $value))){
						return false;
					}
				}
		}

		return true;
	}
}