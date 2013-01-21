<?php
add_action( 'init', 'ninja_forms_register_field_textbox' );

function ninja_forms_register_field_textbox(){
	$args = array(
		'name' => 'Textbox',
		'sidebar' => 'template_fields',
		'edit_function' => 'ninja_forms_field_text_edit',
		'edit_options' => array(
			array(
				'type' => 'checkbox',
				'name' => 'datepicker',
				'label' => 'Datepicker',
			),
			array(
				'type' => 'checkbox',
				'name' => 'email',
				'label' => 'Is this an email address?',
			),
			array(
				'type' => 'checkbox',
				'name' => 'send_email',
				'label' => 'Send a copy of the form to this address?',
			),
		),
		'display_function' => 'ninja_forms_field_text_display',
		'save_function' => '',
		'group' => 'standard_fields',
		'edit_label' => true,
		'edit_label_pos' => true,
		'edit_req' => true,
		'edit_custom_class' => true,
		'edit_help' => true,
		'edit_meta' => false,
		'edit_conditional' => true,
		'conditional' => array(
			'value' => array(
				'type' => 'text',
			),
		),
	);

	ninja_forms_register_field( '_text', $args );
}

function ninja_forms_field_text_edit( $field_id, $data ){
	$plugin_settings = get_option( 'ninja_forms_settings' );

	if( isset( $plugin_settings['currency_symbol'] ) ){
		$currency_symbol = $plugin_settings['currency_symbol'];
	}else{
		$currency_symbol = "$";
	}	

	if( isset( $plugin_settings['date_format'] ) ){
		$date_format = $plugin_settings['date_format'];
	}else{
		$date_format = "$";
	}
	$custom = '';
	// Default Value
	if( isset( $data['default_value'] ) ){
		$default_value = $data['default_value'];
	}else{
		$default_value = '';
	}
	if( $default_value == 'none' ){
		$default_value = '';
	}

	?>
	<div class="description description-thin">
		<span class="field-option">
		<label for="">
			<?php _e( 'Default Value' , 'ninja-forms'); ?>
		</label><br />
			<select id="default_value_<?php echo $field_id;?>" name="" class="widefat ninja-forms-_text-default-value">
				<option value="" <?php if( $default_value == ''){ echo 'selected'; $custom = 'no';}?>><?php _e('None', 'ninja-forms'); ?></option>
				<option value="_user_firstname" <?php if($default_value == '_user_firstname'){ echo 'selected'; $custom = 'no';}?>><?php _e('User Firstname (If logged in)', 'ninja-forms'); ?></option>
				<option value="_user_lastname" <?php if($default_value == '_user_lastname'){ echo 'selected'; $custom = 'no';}?>><?php _e('User Lastname (If logged in)', 'ninja-forms'); ?></option>
				<option value="_user_display_name" <?php if($default_value == '_user_display_name'){ echo 'selected'; $custom = 'no';}?>><?php _e('User Display Name (If logged in)', 'ninja-forms'); ?></option>
				<option value="_user_email" <?php if($default_value == '_user_email'){ echo 'selected'; $custom = 'no';}?>><?php _e('User Email (If logged in)', 'ninja-forms'); ?></option>
				<option value="_custom" <?php if($custom != 'no'){ echo 'selected';}?>><?php _e('Custom', 'ninja-forms'); ?> -></option>
			</select>
		</span>
	</div>
	<div class="description description-thin">

		<label for="" id="default_value_label_<?php echo $field_id;?>" style="<?php if($custom == 'no'){ echo 'display:none;';}?>">
			<span class="field-option">
			<?php _e( 'Default Value' , 'ninja-forms'); ?><br />
			<input type="text" class="widefat code" name="ninja_forms_field_<?php echo $field_id;?>[default_value]" id="ninja_forms_field_<?php echo $field_id;?>_default_value" value="<?php echo $default_value;?>" />
			</span>
		</label>

	</div>


	<?php
	$custom = '';
	// Field Mask
	if( isset( $data['mask'] ) ){
		$mask = $data['mask'];
	}else{
		$mask = '';
	}
	?>
	<div class="description description-thin">
		<span class="field-option">
		<label for="">
			<?php _e( 'Input Mask' , 'ninja-forms'); ?>
		</label><br />
			<select id="mask_<?php echo $field_id;?>"  name="" class="widefat ninja-forms-_text-mask">
				<option value="" <?php if($mask == ''){ echo 'selected'; $custom = 'no';}?>><?php _e('None', 'ninja-forms'); ?></option>
				<option value="(999) 999-9999" <?php if($mask == '(999) 999-9999'){ echo 'selected'; $custom = 'no';}?>><?php _e('Phone - (555) 555-5555', 'ninja-forms'); ?></option>
				<option value="date" <?php if($mask == 'date'){ echo 'selected'; $custom = 'no';}?>><?php _e('Date', 'ninja-forms'); ?> - <?php echo $date_format;?></option>
				<option value="currency" <?php if($mask == 'currency'){ echo 'selected'; $custom = 'no';}?>><?php _e('Currency', 'ninja-forms'); ?> - <?php echo $currency_symbol;?></option>
				<option value="_custom" <?php if($custom != 'no'){ echo 'selected';}?>><?php _e('Custom', 'ninja-forms'); ?> -></option>
			</select>

		</span>
	</div>
	<div class="description description-thin">
		<span class="field-option">
		<label for=""  id="mask_label_<?php echo $field_id;?>" style="<?php if($custom == 'no'){ echo 'display:none;';}?>">
			<?php _e( 'Custom Mask Definition' , 'ninja-forms'); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" name="" class="ninja-forms-mask-help">Help</a><br />
			<input type="text" id="ninja_forms_field_<?php echo $field_id;?>_mask" name="ninja_forms_field_<?php echo $field_id;?>[mask]" class="widefat code" value="<?php echo $mask; ?>" />
		</label>
		</span>
	</div>
	<?php
}

function ninja_forms_field_text_save(){

}

function ninja_forms_field_text_display( $field_id, $data ){
	global $current_user;
	$field_class = ninja_forms_get_field_class( $field_id );

	if(isset($data['default_value'])){
		$default_value = $data['default_value'];
	}else{
		$default_value = '';
	}

	get_currentuserinfo();
	$user_ID = $current_user->ID;
	$user_firstname = $current_user->user_firstname;
    $user_lastname = $current_user->user_lastname;
    $user_display_name = $current_user->display_name;
    $user_email = $current_user->user_email;

	switch( $default_value ){
		case '_user_firstname':
			$default_value = $user_firstname;
			break;
		case '_user_lastname':
			$default_value = $user_lastname;
			break;
		case '_user_display_name':
			$default_value = $user_display_name;
			break;
		case '_user_email':
			$default_value = $user_email;
			break;
	}

	if(isset($data['label_pos'])){
		$label_pos = $data['label_pos'];
	}else{
		$label_pos = "left";
	}

	if(isset($data['label'])){
		$label = $data['label'];
	}else{
		$label = '';
	}

	if($label_pos == 'inside'){
		$default_value = $label;
	}

	if( isset( $data['mask'] ) ){
		$mask = $data['mask'];
	}else{
		$mask = '';
	}

	switch( $mask ){
		case '': 
			$mask_class = '';
			break;
		case 'date':
			$mask_class = 'ninja-forms-date';
			break;
		case 'currency':
			$mask_class =  'ninja-forms-currency';
			break;
		default:
			$mask_class = 'ninja-forms-mask';
			break;
	}

	if( isset( $data['datepicker'] ) AND $data['datepicker'] == 1 ){
		$mask_class = 'ninja-forms-datepicker';
	}

	?>
	<input id="ninja_forms_field_<?php echo $field_id;?>" title="<?php echo $mask;?>" name="ninja_forms_field_<?php echo $field_id;?>" type="text" class="<?php echo $field_class;?> <?php echo $mask_class;?>" value="<?php echo $default_value;?>" />
	<?php

}