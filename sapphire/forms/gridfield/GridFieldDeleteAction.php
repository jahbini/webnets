<?php
/**
 * This class is an GridField Component that add Delete action for Objects in the GridField.
 * See {@link GridFieldRemoveButton} for detaching an item from the current relationship instead.
 */
class GridFieldDeleteAction implements GridField_ColumnProvider, GridField_ActionProvider {
	
	
	/**
	 * If this is set to true, this actionprovider will remove the object from the list, instead of 
	 * deleting. In the case of a has one, has many or many many list it will uncouple the item from 
	 * the list.
	 *
	 * @var boolean
	 */
	protected $removeRelation = false;
	
	/**
	 *
	 * @param boolean $unlinkRelation - true if removing the item from the list, but not deleting it
	 */
	public function __construct($unlinkRelation = false) {
		$this->removeRelation = $unlinkRelation;
	}
	
	/**
	 * Add a column 'Delete'
	 * 
	 * @param type $gridField
	 * @param array $columns 
	 */
	public function augmentColumns($gridField, &$columns) {
		if(!in_array('Actions', $columns))
			$columns[] = 'Actions';
	}
	
	/**
	 * Return any special attributes that will be used for FormField::createTag()
	 *
	 * @param GridField $gridField
	 * @param DataObject $record
	 * @param string $columnName
	 * @return array
	 */
	public function getColumnAttributes($gridField, $record, $columnName) {
		return array('class' => 'col-buttons');
	}
	
	/**
	 * Add the title 
	 * 
	 * @param GridField $gridField
	 * @param string $columnName
	 * @return array
	 */
	public function getColumnMetadata($gridField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}
	
	/**
	 * Which columns are handled by this component
	 * 
	 * @param type $gridField
	 * @return type 
	 */
	public function getColumnsHandled($gridField) {
		return array('Actions');
	}
	
	/**
	 * Which GridField actions are this component handling
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getActions($gridField) {
		return array('deleterecord', 'unlinkrelation');
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @param DataObject $record
	 * @param string $columnName
	 * @return string - the HTML for the column 
	 */
	public function getColumnContent($gridField, $record, $columnName) {
		if($this->removeRelation) {
			$field = Object::create('GridField_FormAction', $gridField, 'UnlinkRelation'.$record->ID, false, "unlinkrelation", array('RecordID' => $record->ID))
				->addExtraClass('gridfield-button-unlink')
				->setAttribute('title', _t('GridAction.UnlinkRelation', "Unlink"))
				->setAttribute('data-icon', 'chain--minus');
		} else {
			if(!$record->canDelete()) {
				return;
			}
			$field = Object::create('GridField_FormAction', $gridField,  'DeleteRecord'.$record->ID, false, "deleterecord", array('RecordID' => $record->ID))
				->addExtraClass('gridfield-button-delete')
				->setAttribute('title', _t('GridAction.Delete', "Delete"))
				->setAttribute('data-icon', 'decline');
		}
		return $field->Field();
	}
	
	/**
	 * Handle the actions and apply any changes to the GridField
	 *
	 * @param GridField $gridField
	 * @param string $actionName
	 * @param mixed $arguments
	 * @param array $data - form data
	 * @return void
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'deleterecord' || $actionName == 'unlinkrelation') {
			$item = $gridField->getList()->byID($arguments['RecordID']);
			if(!$item) {
				return;
			}
			if($actionName == 'deleterecord' && !$item->canDelete()) {
				throw new ValidationException(_t('GridFieldAction_Delete.DeletePermissionsFailure',"No delete permissions"),0);
			}
			$gridField->getList()->remove($item);
		} 
	}
}