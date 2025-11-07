<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Constants\MetaTypes;
use ACPT\Includes\ACPT_DB;

class DeleteMetaFieldValueCommand extends AbstractMetaFieldValueCommand implements CommandInterface
{
	/**
	 * @throws \Exception
	 */
	public function execute()
	{
		global $wpdb;

		$this->fieldModel->setFindLabel($this->location);
		$this->fieldModel->setBelongsToLabel($this->belongsTo);

		switch ($this->belongsTo){

			case MetaTypes::MEDIA:
			case MetaTypes::CUSTOM_POST_TYPE:
				$sql = "
	                DELETE FROM `{$wpdb->prefix}postmeta`
	                WHERE meta_key LIKE %s AND post_id = %d
	            ";
				$args = [
					$this->fieldModel->getDbName().'%',
					$this->location
				];
				break;

			case MetaTypes::TAXONOMY:
				$sql = "
	                DELETE FROM `{$wpdb->prefix}termmeta`
	                WHERE meta_key LIKE %s AND term_id = %d
	            ";
				$args = [
					$this->fieldModel->getDbName().'%',
					$this->location
				];
				break;

			case MetaTypes::USER:
				$sql = "
	                DELETE FROM `{$wpdb->prefix}usermeta`
	                WHERE meta_key LIKE %s AND user_id = %d
	            ";
				$args = [
					$this->fieldModel->getDbName().'%',
					$this->location
				];
				break;

			case MetaTypes::COMMENT:
				$sql = "
	                DELETE FROM `{$wpdb->prefix}commentmeta`
	                WHERE meta_key LIKE %s AND comment_id = %d
	            ";
				$args = [
					$this->fieldModel->getDbName().'%',
					$this->location
				];
				break;

			case MetaTypes::OPTION_PAGE:
				$sql = "
	                DELETE FROM `{$wpdb->prefix}options`
	                WHERE option_name LIKE %s 
	            ";
				$args = [
					$this->fieldModel->getDbName().'%',
				];
				break;
		}

		if(isset($sql) and isset($args)){
			ACPT_DB::executeQueryOrThrowException($sql, $args);
		}
	}
}