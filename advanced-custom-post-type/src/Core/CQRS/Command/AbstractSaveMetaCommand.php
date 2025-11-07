<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Constants\Cookies;
use ACPT\Constants\ExtraFields;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\Fields\AbstractField;
use ACPT\Core\Helper\Id;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Validators\MetaDataValidator;
use ACPT\Utils\Checker\ValidationRulesChecker;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\Data\Sanitizer;
use ACPT\Utils\PHP\Arrays;
use ACPT\Utils\PHP\Cookie;
use ACPT\Utils\PHP\GeoLocation;
use ACPT\Utils\PHP\Session;
use ACPT\Utils\Wordpress\Files;
use ACPT\Utils\Wordpress\Transient;

abstract class AbstractSaveMetaCommand implements LogFormatInterface
{
	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var array
	 */
	protected $files;

	/**
	 * AbstractSaveMetaCommand constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->data = $data;
		$this->files = [];
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 *
	 * @return bool
	 */
	protected function hasField(MetaFieldModel $fieldModel)
	{
	    $key = $fieldModel->getDbName();

	    if(isset($this->WooCommerceLoopIndex) and $this->WooCommerceLoopIndex !== null){
            $key .= "_".$this->WooCommerceLoopIndex;
        }

	    $key .= "_id";

		return (
			isset($this->data[$key]) and
			$this->data[$key] === $fieldModel->getId()
		);
	}

	/**
	 * Save a meta field
	 *
	 * @param MetaFieldModel $fieldModel
	 * @param string|int $elementId
	 * @param string $belongsTo
	 *
	 * @throws \Exception
	 */
	protected function saveField(MetaFieldModel $fieldModel, $elementId, $belongsTo)
	{
		$data = $this->data;
		$idName = $fieldModel->getDbName();
		$fieldType = $fieldModel->getType();
        $allowHtml = false;
        $allowDangerousContent = false;
		$key = $idName;

        if(isset($this->WooCommerceLoopIndex) and $this->WooCommerceLoopIndex !== null){
            $key .= "_".$this->WooCommerceLoopIndex;
        }

		// handling files from comment forms
        if(isset($this->files[$key])){
			$rawFile = $this->files[$key];

			if(!empty($rawFile['tmp_name'])){
				$fileUploaded = Files::uploadFile($rawFile['tmp_name'], $rawFile['name']);

				if($fileUploaded !== false){
					$fileUploadedUrl = $fileUploaded['url'];
					$fileUploadedId  = $fileUploaded['attachmentId'];

					Meta::save(
						$elementId,
						$belongsTo,
						$idName,
						$fileUploadedUrl
					);

					Meta::save(
						$elementId,
						$belongsTo,
						$idName.'_id',
						$fileUploadedId
					);

					Meta::save(
						$elementId,
						$belongsTo,
						$idName.'_type',
						$fieldModel->getType()
					);
				}
			}

		} elseif(isset($data[$key])){
			$rawValue = $data[$key];

			// validation
			try {
				MetaDataValidator::validate($fieldType, $rawValue, $fieldModel->isRequired());
			} catch (\Exception $exception){
			    $this->logExceptionAndExit($exception);
			}

			// min and max attributes
            try {
                $fieldModel->validateAgainstMaxAndMin($rawValue);
            } catch (\Exception $exception){
                $this->logExceptionAndExit($exception);
            }

			// validation against rules
			if($fieldModel !== null and !empty($fieldModel->getValidationRules())){
				$validationRulesChecker = new ValidationRulesChecker($rawValue, $fieldModel->getValidationRules());
				$validationRulesChecker->validate();

				if(!$validationRulesChecker->isValid()){
					foreach ($validationRulesChecker->getErrors() as $error){
                        $this->logExceptionAndExit(new \Exception($error));
					}
				}
			}

			// ID field
            if($fieldType === MetaFieldModel::ID_TYPE){
                $idStrategy = $fieldModel->getAdvancedOption('id_strategy') ?? Id::UUID_V1;

                if(!Id::isValid($rawValue, $idStrategy)){
                    $error = "ID format is not valid";
                    $this->logExceptionAndExit(new \Exception($error));
                }
            }

            // Relational field
			if($fieldType === MetaFieldModel::POST_TYPE){

                $rawValues = is_array($rawValue) ? $rawValue : explode(",", $rawValue);
                $numberOfElements = count($rawValues);
                $this->checkMinMaxBlocks($data, $idName, $numberOfElements);

				$command = new HandleRelationsCommand($fieldModel, $rawValue, $elementId, $belongsTo);
				$command->execute();
			} else {

				$value = $rawValue;

				if(is_array($value)){

					if($fieldType === MetaFieldModel::REPEATER_TYPE){

						$numberOfElements = count(array_values($value)[0]);
                        $this->checkMinMaxBlocks($data, $idName, $numberOfElements);

						foreach ($value as $blockName => $fields){
							$value[$blockName] = Arrays::reindex($fields);
						}

					} elseif($fieldType === MetaFieldModel::FLEXIBLE_CONTENT_TYPE){

						$numberOfBlocks = count($value);
                        $this->checkMinMaxBlocks($data, $idName, $numberOfBlocks);

						foreach ($value as $blockName => $fields){
							$value[$blockName] = Arrays::reindex($fields);
						}
					} else {
						$value = Arrays::reindex($value);
					}
				}

				// Other relational fields
                if(
                    $fieldType === MetaFieldModel::POST_OBJECT_MULTI_TYPE or
                    $fieldType === MetaFieldModel::TERM_OBJECT_MULTI_TYPE or
                    $fieldType === MetaFieldModel::USER_MULTI_TYPE
                ){
                    if(!is_array($value)){
                        $value = (explode(",",$value));
                    }

                    $numberOfElements = count($value);
                    $this->checkMinMaxBlocks($data, $idName, $numberOfElements);
                }

				// Password field
                if($fieldType === MetaFieldModel::PASSWORD_TYPE){
                    $algo = $fieldModel->getAdvancedOption("algorithm") ?? PASSWORD_DEFAULT;

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
                }

                // Date fields
                if(
                    $fieldType === MetaFieldModel::DATE_TIME_TYPE or
                    $fieldType === MetaFieldModel::DATE_TYPE or
                    $fieldType === MetaFieldModel::TIME_TYPE or
                    $fieldType === MetaFieldModel::DATE_RANGE_TYPE
                ){
                    switch ($fieldType){
                        case MetaFieldModel::DATE_TIME_TYPE:
                            $defaultFormat = "Y-m-d H:i:s";
                            break;

                        default:
                        case MetaFieldModel::DATE_RANGE_TYPE:
                        case MetaFieldModel::DATE_TYPE:
                            $defaultFormat = "Y-m-d";
                            break;

                        case MetaFieldModel::TIME_TYPE:
                            $defaultFormat = "H:i:s";
                            break;
                    }

                    $format = $data[$idName."_format"] ?? $defaultFormat;
                    $dateTimeObject = \DateTime::createFromFormat($format, $value);

                    // For quick and bulk edit
                    if(!$dateTimeObject instanceof \DateTime){
                        $dateTimeObject = \DateTime::createFromFormat("Y-m-d\TH:i", $value);
                    }

                    if($dateTimeObject instanceof \DateTime){
                        $value = $dateTimeObject->format($defaultFormat);
                    }
                }

                // Textual fields
                if($fieldType === MetaFieldModel::TEXT_TYPE or $fieldType === MetaFieldModel::TEXTAREA_TYPE){
                    $allowHtml = $fieldModel->getAdvancedOption("allow_html") ?? false;
                }

                // HTML fields
                if($fieldType === MetaFieldModel::HTML_TYPE){
                    $allowDangerousContent = $fieldModel->getAdvancedOption("allow_dangerous_content") ?? false;
                }

				Meta::save(
					$elementId,
					$belongsTo,
					$idName,
					Sanitizer::sanitizeRawData($fieldType, $this->convertMetaDataToDBFormat($value), $allowHtml, $allowDangerousContent)
				);

				// Address field: add coordinates
				if($fieldType === MetaFieldModel::ADDRESS_TYPE){
					$address = Sanitizer::sanitizeRawData($fieldType, $this->convertMetaDataToDBFormat($value));

					if(
						!empty($address) and
						empty($data[$idName.'_lat']) and
						empty($data[$idName.'_lng'])
					){
						$coordinates = GeoLocation::getCoordinates($address);
						$data[$idName.'_lat'] = $coordinates['lat'];
						$data[$idName.'_lng'] = $coordinates['lng'];

						if(empty($data[$idName.'_city'])){
							$city = GeoLocation::getCity($coordinates['lat'], $coordinates['lng']);

							if(!empty($city)){
								$data[$idName.'_city'] = $city;
							}
						}

                        if(empty($data[$idName.'_country'])){
                            $country = GeoLocation::getCountry($coordinates['lat'], $coordinates['lng']);

                            if(!empty($city)){
                                $data[$idName.'_country'] = $country;
                            }
                        }
					}
				}

				// Image fields: set post thumbnail
				if(
                    $fieldType === MetaFieldModel::IMAGE_TYPE and
					$fieldModel->getAdvancedOption('set_thumbnail') == 1 and
					$belongsTo === MetaTypes::CUSTOM_POST_TYPE
				){
					if(isset($data[$idName.'_attachment_id']) and !empty($data[$idName.'_attachment_id'])){
						set_post_thumbnail( (int)$elementId, (int)$data[$idName.'_attachment_id'] );
					} else {
						delete_post_meta( (int)$elementId, '_thumbnail_id' );
					}
				}

                // Gallery & Image slider fields: set post thumbnail
                if(
                    ($fieldType === MetaFieldModel::GALLERY_TYPE || $fieldType === MetaFieldModel::IMAGE_SLIDER_TYPE) and
                    $fieldModel->getAdvancedOption('set_thumbnail') == 1 and
                    $belongsTo === MetaTypes::CUSTOM_POST_TYPE
                ){
                    if(isset($data[$idName.'_attachment_id']) and !empty($data[$idName.'_attachment_id'])){
                        $firstImageId = explode(",", $data[$idName.'_attachment_id']);

                        if(!empty($firstImageId)){
                            set_post_thumbnail( (int)$elementId, (int)$firstImageId[0] );
                        }
                    } else {
                        delete_post_meta( (int)$elementId, '_thumbnail_id' );
                    }
                }

				// Term fields: sync terms with current post
				if(
					($fieldType === MetaFieldModel::TERM_OBJECT_TYPE or $fieldType === MetaFieldModel::TERM_OBJECT_MULTI_TYPE) and
					$fieldModel->getAdvancedOption('sync_taxonomy') == 1 and
					$belongsTo === MetaTypes::CUSTOM_POST_TYPE
				){
					if(!is_array($value)){
						$value = [$value];
					}

					$terms = [];
					$taxonomy = null;

					foreach ($value as $termId){
						if(!empty($termId)){
							$term = get_term($termId);

							if($term instanceof \WP_Term){
								$terms[] = $term->name;
								$taxonomy = $term->taxonomy;
							}
						}
					}

					if(!empty($terms) and !empty($taxonomy)){
						wp_set_post_terms( (int)$elementId, $terms, $taxonomy );
					}
				}

				// Extra fields
				foreach (ExtraFields::ALLOWED_VALUES as $extra){
					if(isset($data[$key.'_'.$extra])){
						Meta::save(
							$elementId,
							$belongsTo,
							$idName.'_'.$extra,
							Sanitizer::sanitizeRawData(MetaFieldModel::TEXT_TYPE, $data[$key.'_'.$extra] )
						);
					}
				}
			}

		} else {

			// blank the field only if it already exists
			$metaFieldToBeBlanked = Meta::fetch($elementId, $belongsTo, $idName);

			if($metaFieldToBeBlanked !== null){
				Meta::save(
					$elementId,
					$belongsTo,
					$idName,
					''
				);

				// blank the extra fields
                foreach (ExtraFields::ALLOWED_VALUES as $extra){

                    // blank the extra field only if it already exists
                    $metaFieldToBeBlanked = Meta::fetch($elementId, $belongsTo, $idName.'_'.$extra);
                    if($metaFieldToBeBlanked !== null){
                        Meta::save(
                            $elementId,
                            $belongsTo,
                            $idName.'_'.$extra,
                            ''
                        );
                    }
                }
			}
		}

		if(!empty($errors)){
			Transient::set( "acpt_plugin_error_msg_".$elementId, $errors, 60 );
			add_filter( 'redirect_post_location', [$this, 'addNoticeQueryVar'], 99 );
		}

		do_action("acpt/save_meta_field", $this, $fieldModel, $elementId, $belongsTo);
	}

	/**
	 * This function normalize the raw data before saving in DB.
	 * Is needed to convert relationship values from strings (1,37,47) to arrays [1, 37, 47]
	 *
	 * @param $rawValue
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	protected function convertMetaDataToDBFormat($rawValue)
	{
		if(is_array($rawValue)){
			foreach ($rawValue as $fieldLabel => $fieldValue){
				if(is_array($fieldValue)){

					foreach ($fieldValue as $index => $nestedValue){

						// post inside a nested block
						if(isset($nestedValue['blocks']) and is_array($nestedValue['blocks'])){
							foreach($nestedValue['blocks'] as $blockIndex => $blockFields){
								if(is_array($blockFields)){
									foreach($blockFields as $fieldName => $fieldValues){
										if(is_array($fieldValues)){
											foreach($fieldValues as $fIndex => $fieldValue){
												if(is_array($fieldValue)){
													foreach($fieldValue as $cIndex => $field){
														if(isset($field['type']) and $field['type'] === MetaFieldModel::POST_TYPE){
															$rawValueToArray = explode(",", $field['value']);
															$rawValue[$fieldLabel][$index]["blocks"][$blockIndex][$fieldName][$fIndex][$cIndex]['value'] = $rawValueToArray;
														}
													}
												}
											}
										}
									}
								}
							}
						} elseif(isset($nestedValue['original_name'])){

							// post inside a repeater
							if(isset($nestedValue['type']) and $nestedValue['type'] === MetaFieldModel::POST_TYPE){
								$rawValueToArray = is_array($rawValue[$fieldLabel][$index]['value']) ? $rawValue[$fieldLabel][$index]['value'] : explode(",", $rawValue[$fieldLabel][$index]['value']);
								$rawValue[$fieldLabel][$index]['value'] = $rawValueToArray;
							}

							// post inside a nested repeater
							if(isset($nestedValue['type']) and is_array($nestedValue) and $nestedValue['type'] === MetaFieldModel::REPEATER_TYPE){
								$key = array_keys($nestedValue)[0];
								$secondLevelNestedValues = $nestedValue[$key];

								foreach ($secondLevelNestedValues as $cindex => $secondLevelNestedValue){
									if(isset($rawValue[$fieldLabel][$index][$key][$cindex]['type']) and $rawValue[$fieldLabel][$index][$key][$cindex]['type'] === MetaFieldModel::POST_TYPE){
										$rawValueToArray = explode(",", $rawValue[$fieldLabel][$index][$key][$cindex]['value']);
										$rawValue[$fieldLabel][$index][$key][$cindex]['value'] = $rawValueToArray;
									}
								}
							}
						}
					}

					// post inside a block
					if($fieldLabel === 'blocks'){
						if(is_array($fieldValue)){
							foreach($fieldValue as $blockIndex => $blockFields){
								if(is_array($blockFields)){
									foreach($blockFields as $fieldName => $fieldValues){
										if(is_array($fieldValues)){
											foreach($fieldValues as $fIndex => $fieldValue){
												if(is_array($fieldValue)){
													foreach($fieldValue as $cIndex => $field){
														if(isset($field['type']) and $field['type'] === MetaFieldModel::POST_TYPE){
															$rawValueToArray = explode(",", $field['value']);
															$rawValue["blocks"][$blockIndex][$fieldName][$fIndex][$cIndex]['value'] = $rawValueToArray;
														}

														// post inside a 2nd level block
														if(isset($field['blocks'])){
															foreach($field['blocks'] as $bblockIndex => $bblockFields){
																foreach($bblockFields as $ffieldName => $ffieldValues){
																	foreach($ffieldValues as $ffIndex => $ffieldValue){
																		foreach($ffieldValue as $ccIndex => $ffield){
																			if(isset($ffield['type']) and $ffield['type'] === MetaFieldModel::POST_TYPE){
																				$rawValueToArray = explode(",", $ffield['value']);
																				$rawValue["blocks"][$blockIndex][$fieldName][$fIndex][$cIndex]["blocks"][$bblockIndex][$ffieldName][$ffIndex][$ccIndex]['value'] = $rawValueToArray;
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $rawValue;
	}

    /**
     * @return array
     */
    public function logFormat(): array
    {
        return [
            "class"  => static::class,
            'data' => $this->data,
            'files' => $this->files,
        ];
    }

    /**
     * @param $data
     * @param $idName
     * @param $numberOfElements
     */
    private function checkMinMaxBlocks($data, $idName, $numberOfElements)
    {
        $minimumBlocks = isset($data[$idName.'_min_blocks']) ? $data[$idName.'_min_blocks'] : null;
        $maximumBlocks = isset($data[$idName.'_max_blocks']) ? $data[$idName.'_max_blocks'] : null;

        if($minimumBlocks and ($numberOfElements < $minimumBlocks )){
            $error = 'There was an error during saving data. Minimum number of elements is : ' . $minimumBlocks;
            $this->logExceptionAndExit(new \Exception($error));
        }

        if($maximumBlocks and ($numberOfElements > $maximumBlocks )){
            $error = 'There was an error during saving data. Maximum number of elements is : ' . $maximumBlocks;
            $this->logExceptionAndExit(new \Exception($error));
        }
    }

    /**
     * @param \Exception $error
     * @param string     $level
     */
    private function logExceptionAndExit(\Exception $error, $level = 'warning')
    {
        do_action("acpt/error", $error);

        Cookie::set(Cookies::ACPT_ERRORS, [
            [
                'level' => $level,
                'message' => $error->getMessage(),
            ]
        ]);

        $location = $_SERVER['HTTP_REFERER'];
        wp_safe_redirect($location);
        exit();
    }
}