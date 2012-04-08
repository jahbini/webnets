<?php
/**
 * @package forms
 * @subpackage fields-formattedinput
 */

/**
 * Text field with PointyEmail Validation.
 * @package forms
 * @subpackage fields-formattedinput
 */
class PointyEmailField extends TextField {
	
	function jsValidation() {
		$formID = $this->form->FormName();
		$error = _t('PointyEmailField.VALIDATIONJS', 'Please enter an email address.');
		$jsFunc =<<<JS
Behaviour.register({
	"#$formID": {
		validatePointyEmailField: function(fieldName) {
			var el = _CURRENT_FORM.elements[fieldName];
			if(!el || !el.value) return true;

			newstr =el.value.replace(/^[a-zA-Z0-9 ]*<\s*([^>]*)\s*>$/, '$1');
		 	if(newstr.match(/^([a-zA-Z0-9_+\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|local|[0-9]{1,3})(\]?)$/)) {
		 		return true;
		 	} else {
				validationError(el, "$error","validation");
		 		return false;
		 	} 	
		}
	}
});
JS;

		Requirements::customScript($jsFunc, 'func_validatePointyEmailField');

		//return "\$('$formID').validatePointyEmailField('$this->name');";
		return <<<JS
if(typeof fromAnOnBlur != 'undefined'){
	if(fromAnOnBlur.name == '$this->name')
		$('$formID').validatePointyEmailField('$this->name');
}else{
	$('$formID').validatePointyEmailField('$this->name');
}
JS;
	}
	
	function validate($validator){
		$this->value = trim($this->value);
		$matchme = $this->value;
		if ( ereg('^[a-zA-Z0-9 ]*<([^>]*)>$', $this->value, $values)) {
			 $matchme = $values[1];
		}
		if($matchme && !ereg('^([a-zA-Z0-9_+\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|local|[0-9]{1,3})(\]?)$', $matchme)){
 			$validator->validationError(
 				$this->name,
				_t('EmailField.VALIDATION', "Please enter an email address."),
				"validation"
			);
			return false;
		} else{
			return true;
		}
	}
}
?>
