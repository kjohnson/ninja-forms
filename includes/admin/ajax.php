<?php
add_action( 'wp_ajax_ninja_forms_save_metabox_state', 'ninja_forms_save_metabox_state' );
function ninja_forms_save_metabox_state(){
	$plugin_settings = get_option( 'ninja_forms_settings' );
	$page = $_REQUEST['page'];
	$tab = $_REQUEST['tab'];
	$slug = $_REQUEST['slug'];
	$state = $_REQUEST['state'];
	$plugin_settings['metabox_state'][$page][$tab][$slug] = $state;
	update_option( 'ninja_forms_settings', $plugin_settings );
	//$plugin_settings = get_option( 'ninja_forms_settings' );
	//echo "SETTING: ".$plugin_settings['metabox_state'][$page][$tab][$slug];
	die();
}

add_action('wp_ajax_ninja_forms_new_field', 'ninja_forms_new_field');
function ninja_forms_new_field(){
	global $wpdb, $ninja_forms_fields;

	$type = $_REQUEST['type'];
	$form_id = $_REQUEST['form_id'];
	
	if( isset( $ninja_forms_fields[$type]['name'] ) ){
		$type_name = $ninja_forms_fields[$type]['name'];
	}else{
		$type_name = '';
	}
	
	if( isset( $ninja_forms_fields[$type]['edit_options'] ) ){
		$edit_options = $ninja_forms_fields[$type]['edit_options'];
	}else{
		$edit_options = '';
	}
	
	$data = serialize(array('label' => $type_name));

	$order = 999;

	if($form_id != 0 AND $form_id != ''){
		$args = array(
			'type' => $type,
			'data' => $data,
		);
						
		$new_id = ninja_forms_insert_field( $form_id, $args );
		$new_html = ninja_forms_return_echo('ninja_forms_edit_field', $new_id);
		header("Content-type: application/json");
		$array = array ('new_id' => $new_id, 'new_type' => $type_name, 'new_html' => $new_html, 'edit_options' => $edit_options);
		echo json_encode($array);
		die();
	}
}

add_action('wp_ajax_ninja_forms_remove_field', 'ninja_forms_remove_field');
function ninja_forms_remove_field(){
	global $wpdb;
	$field_id = $_REQUEST['field_id'];
	$wpdb->query($wpdb->prepare("DELETE FROM ".NINJA_FORMS_FIELDS_TABLE_NAME." WHERE id = %d", $field_id));
	die();
}

add_action('wp_ajax_ninja_forms_delete_form', 'ninja_forms_delete_form');
function ninja_forms_delete_form( $form_id = '' ){
	global $wpdb;
	if( $form_id == '' ){
		$ajax = true;
		$form_id = $_REQUEST['form_id'];
	}else{
		$ajax = false;
	}
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".NINJA_FORMS_TABLE_NAME." WHERE id = %d", $form_id));
	$wpdb->query($wpdb->prepare("DELETE FROM ".NINJA_FORMS_FIELDS_TABLE_NAME." WHERE form_id = %d", $form_id));
	
	if( $ajax ){
		die();
	}
	
}

add_action('wp_ajax_ninja_forms_add_conditional', 'ninja_forms_add_conditional');
function ninja_forms_add_conditional(){
	global $wpdb, $ninja_forms_fields;
	
	$field_id = $_REQUEST['field_id'];
	$x = $_REQUEST['x'];	
	ninja_forms_field_conditional_output($field_id, $x);
	die();
}

add_action('wp_ajax_ninja_forms_add_cr', 'ninja_forms_add_cr');
function ninja_forms_add_cr(){
	global $wpdb, $ninja_forms_fields;

	$field_id = $_REQUEST['field_id'];
	$x = $_REQUEST['x'];
	$y = $_REQUEST['y'];
	$new_html = ninja_forms_return_echo('ninja_forms_field_conditional_cr_output', $field_id, $x, $y);
	header("Content-type: application/json");
	$array = array ('new_html' => $new_html, 'field_id' => $field_id, 'x' => $x, 'y' => $y);
	echo json_encode($array);
	die();
}

add_action('wp_ajax_ninja_forms_change_action', 'ninja_forms_change_action');
function ninja_forms_change_action(){
	global $wpdb, $ninja_forms_fields;
	
	$form_id = $_REQUEST['form_id'];
	$action_slug = $_REQUEST['action_slug'];
	$field_id = $_REQUEST['field_id'];
	$x = $_REQUEST['x'];
	$field_data = $_REQUEST['field_data'];

	$field_data = $field_data['ninja_forms_field_'.$field_id];

	$field_row = ninja_forms_get_field_by_id($field_id);
	$type = $field_row['type'];
	$reg_field = $ninja_forms_fields[$type];
	if( isset( $reg_field['conditional']['action'][$action_slug] ) ){
		$conditional = $reg_field['conditional']['action'][$action_slug];
	}else if( $action_slug == 'change_value'){
		$conditional = array( 'output' => 'text' );
	}else{
		$conditional = '';
	}

	header("Content-type: application/json");
	
	if( isset( $conditional['output'] ) ){
		$new_type = $conditional['output'];
	}else{
		$new_type = '';
	}

	$new_html = ninja_forms_return_echo( 'ninja_forms_field_conditional_action_output', $field_id, $x, $conditional, '', $field_data );
	$array = array('new_html' => $new_html, 'new_type' => $new_type );
	echo json_encode($array);

	die();

}

add_action('wp_ajax_ninja_forms_change_cr_field', 'ninja_forms_change_cr_field');
function ninja_forms_change_cr_field(){
	global $wpdb, $ninja_forms_fields;
	
	$field_id = $_REQUEST['field_id'];
	$field_value = $_REQUEST['field_value'];
	$x = $_REQUEST['x'];
	$y = $_REQUEST['y'];
	
	$field_row = ninja_forms_get_field_by_id($field_value);
	$type = $field_row['type'];
	$reg_field = $ninja_forms_fields[$type];
	$conditional = $reg_field['conditional'];
	header("Content-type: application/json");
	
	$new_html = '';
	
	if(isset($conditional['value']) AND is_array($conditional['value'])){
		$new_html = ninja_forms_return_echo('ninja_forms_field_conditional_cr_value_output', $field_id, $x, $y, $conditional);
		$array = array('new_html' => $new_html, 'new_type' => $conditional['value']['type'] );
		echo json_encode($array);
	}
	die();
}

add_action('wp_ajax_ninja_forms_add_list_option', 'ninja_forms_add_list_options');
function ninja_forms_add_list_options(){
	global $wpdb;
	$field_id = $_REQUEST['field_id'];
	$x = $_REQUEST['x'];
	$hidden_value = $_REQUEST['hidden_value'];
	ninja_forms_field_list_option_output($field_id, $x, '', $hidden_value);
	die();
}

add_action('wp_ajax_ninja_forms_insert_fav', 'ninja_forms_insert_fav');
function ninja_forms_insert_fav(){
	global $wpdb, $ninja_forms_fields;
	$fav_id = $_REQUEST['fav_id'];
	$form_id = $_REQUEST['form_id'];
	
	$fav_row = ninja_forms_get_fav_by_id($fav_id);

	$data = serialize($fav_row['data']);
	$type = $fav_row['type'];
	$type_name = $ninja_forms_fields[$type]['name'];
		
	if($form_id != 0 AND $form_id != ''){
		$args = array(
			'type' => $type,
			'data' => $data,
			'fav_id' => $fav_id,
		);
		$new_id = ninja_forms_insert_field( $form_id, $args );
		$new_html = ninja_forms_return_echo('ninja_forms_edit_field', $new_id);
		header("Content-type: application/json");
		$array = array ('new_id' => $new_id, 'new_type' => $type_name, 'new_html' => $new_html);
		echo json_encode($array);
	}
	die();
}

add_action('wp_ajax_ninja_forms_insert_def', 'ninja_forms_insert_def');
function ninja_forms_insert_def(){
	global $wpdb, $ninja_forms_fields;
	$def_id = $_REQUEST['def_id'];
	$form_id = $_REQUEST['form_id'];
	
	$def_row = ninja_forms_get_def_by_id($def_id);
		
	$data = serialize($def_row['data']);
	$type = $def_row['type'];
	$type_name = $ninja_forms_fields[$type]['name'];
		
	if($form_id != 0 AND $form_id != ''){
		$args = array(
			'type' => $type,
			'data' => $data,
			'def_id' => $def_id,
		);
		$new_id = ninja_forms_insert_field( $form_id, $args );
		$new_html = ninja_forms_return_echo('ninja_forms_edit_field', $new_id);
		header("Content-type: application/json");
		$array = array ('new_id' => $new_id, 'new_type' => $type_name, 'new_html' => $new_html);
		echo json_encode($array);
	}
	die();
}

add_action('wp_ajax_ninja_forms_add_fav', 'ninja_forms_add_fav');
function ninja_forms_add_fav(){
	global $wpdb;

	$field_data = $_REQUEST['field_data'];
	$field_id = $_REQUEST['field_id'];
	
	$field_row = ninja_forms_get_field_by_id($field_id);
	
	$field_type = $field_row['type'];
	$form_id = 1;
	
	$data = array();
	
	foreach($field_data as $key => $val){
		$key = str_replace('"', '', $key);	
		if(strpos($key, '[')){
			$key = str_replace(']', '', $key);
			$key = explode('[', $key);
			$multi = array(); 	
			$temp  =& $multi;	
			$x = 0;
			$count = count($key) - 1;
			foreach ($key as $item){ 
				$temp[$item] = array(); 
				if($x < $count){
					$temp =& $temp[$item];
				}else{
					$temp[$item] = $val;
				}
				$x++;
			}
			$data = ninja_forms_array_merge_recursive($data, $multi); 
		}else{
			$data[$key] = $val;
		}
	}
	
	$name = stripslashes($_REQUEST['fav_name']);
	$data['label'] = $name;
	$data = serialize($data);
	$wpdb->insert(NINJA_FORMS_FAV_FIELDS_TABLE_NAME, array('row_type' => 1, 'type' => $field_type, 'order' => 0, 'data' => $data, 'name' => $name));
	$fav_id = $wpdb->insert_id;
	$update_array = array('fav_id' => $fav_id);
	$wpdb->update( NINJA_FORMS_FIELDS_TABLE_NAME, $update_array, array( 'id' => $field_id ));
	
	$new_html = '<p class="button-controls" id="ninja_forms_insert_fav_field_'.$fav_id.'_p">
				<a class="button add-new-h2 ninja-forms-insert-fav-field" id="ninja_forms_insert_fav_field_'.$fav_id.'" name=""  href="#">'.__($name, 'ninja-forms').'</a>
			</p>';

	header("Content-type: application/json");
	$array = array ('fav_id' => $fav_id, 'fav_name' => $name, 'link_html' => $new_html);
	echo json_encode($array);

	die();
}

add_action('wp_ajax_ninja_forms_add_def', 'ninja_forms_add_def');
function ninja_forms_add_def(){
	global $wpdb;
	$field_data = $_REQUEST['field_data'];
	$field_id = $_REQUEST['field_id'];

	$field_row = ninja_forms_get_field_by_id($field_id);
	
	$field_type = $field_row['type'];
	$row_type = 0;
	
	$data = array();
	
	foreach($field_data as $key => $val){
		$key = str_replace('"', '', $key);	
		if(strpos($key, '[')){
			$key = str_replace(']', '', $key);
			$key = explode('[', $key);
			$multi = array(); 	
			$temp  =& $multi;	
			$x = 0;
			$count = count($key) - 1;
			foreach ($key as $item){ 
				$temp[$item] = array(); 
				if($x < $count){
					$temp =& $temp[$item];
				}else{
					$temp[$item] = $val;
				}
				$x++;
			}
			$data = ninja_forms_array_merge_recursive($data, $multi); 
		}else{
			$data[$key] = $val;
		}
	}
	
	$name = stripslashes($_REQUEST['def_name']);
	$data['label'] = $name;
	$data = serialize($data);
	$wpdb->insert(NINJA_FORMS_FAV_FIELDS_TABLE_NAME, array('row_type' => $row_type, 'type' => $field_type, 'data' => $data, 'name' => $name));
	$def_id = $wpdb->insert_id;
	$update_array = array('def_id' => $def_id);
	$wpdb->update( NINJA_FORMS_FIELDS_TABLE_NAME, $update_array, array( 'id' => $field_id ));
	
	$new_html = '<p class="button-controls" id="ninja_forms_insert_def_field_'.$def_id.'_p">
				<a class="button add-new-h2 ninja-forms-insert-def-field" id="ninja_forms_insert_def_field_'.$def_id.'" name=""  href="#">'.__($name, 'ninja-forms').'</a>
			</p>';
	header("Content-type: application/json");
	$array = array ('def_id' => $def_id, 'def_name' => $name, 'link_html' => $new_html);
	echo json_encode($array);
	
	die();
}

add_action('wp_ajax_ninja_forms_remove_fav', 'ninja_forms_remove_fav');
function ninja_forms_remove_fav(){
	global $wpdb, $ninja_forms_fields;
	
	$field_id = $_REQUEST['field_id'];
	$field_row = ninja_forms_get_field_by_id($field_id);
	$field_type = $field_row['type'];
	$fav_id = $field_row['fav_id'];
	$wpdb->query($wpdb->prepare("DELETE FROM ".NINJA_FORMS_FAV_FIELDS_TABLE_NAME." WHERE id = %d", $fav_id));
	$wpdb->update(NINJA_FORMS_FIELDS_TABLE_NAME, array('fav_id' => '' ), array('fav_id' => $fav_id));
	$type_name = $ninja_forms_fields[$field_type]['name'];
	header("Content-type: application/json");
	$array = array ('fav_id' => $fav_id, 'type_name' => $type_name);
	echo json_encode($array);
	
	die();
}

add_action('wp_ajax_ninja_forms_remove_def', 'ninja_forms_remove_def');
function ninja_forms_remove_def(){
	global $wpdb, $ninja_forms_fields;
	
	$field_id = $_REQUEST['field_id'];
	$field_row = ninja_forms_get_field_by_id($field_id);
	$field_type = $field_row['type'];
	$def_id = $field_row['def_id'];
	$wpdb->query($wpdb->prepare("DELETE FROM ".NINJA_FORMS_FAV_FIELDS_TABLE_NAME." WHERE id = %d", $def_id));
	$wpdb->update(NINJA_FORMS_FIELDS_TABLE_NAME, array('def_id' => '' ), array('def_id' => $def_id));
	$type_name = $ninja_forms_fields[$field_type]['name'];
	header("Content-type: application/json");
	$array = array ('def_id' => $def_id, 'type_name' => $type_name);
	echo json_encode($array);
	
	die();
}

add_action( 'wp_ajax_ninja_forms_side_sortable', 'ninja_forms_side_sortable' );
function ninja_forms_side_sortable(){
	$plugin_settings = get_option( 'ninja_forms_settings' );
	$page = $_REQUEST['page'];
	$tab = $_REQUEST['tab'];
	$order = $_REQUEST['order'];

	$plugin_settings['sidebars'][$page][$tab] = $order;
	update_option( 'ninja_forms_settings', $plugin_settings );

	die();
}

add_action('wp_ajax_ninja_forms_view_sub', 'ninja_forms_view_sub');
function ninja_forms_view_sub(){
	global $ninja_forms_fields;
	/*
	$plugin_settings = get_option("ninja_forms_settings");
	if(isset($plugin_settings['date_format'])){
		$date_format = $plugin_settings['date_format'];
	}else{
		$date_format = 'm/d/Y';
	}
	$sub_id = $_REQUEST['sub_id'];
	$sub_row = ninja_forms_get_sub_by_id($sub_id);
	$data = $sub_row['data'];
	
	$date_updated = strtotime($sub_row['date_updated']);
	$date_updated = date($date_format, $date_updated);
	*/
	$new_html = '<input type="hidden" id="ninja_forms_sub_id" value="'.$sub_id.'">';
	/*
	foreach($data as $field_id => $user_value){
		$new_html .= '<div id="" name="" class="description description-wide">';
		$field_row = ninja_forms_get_field_by_id($field_id);
		if(isset($field_row['data']['label'])){
			$field_label = $field_row['data']['label'];
		}else{
			$field_label = '';
		}
		$field_type = $field_row['type'];
		if($ninja_forms_fields[$field_type]['process_field']){
			$sub_edit = $ninja_forms_fields[$field_type]['sub_edit'];
			$user_value2 = '';
			if(is_array($user_value)){
				$x = 1;
				foreach($user_value as $val){
					$user_value2 .= esc_html(stripslashes($val));
					if($x != count($user_value)){
						$user_value2 .= ",";
					}
					$x++;
				}
			}else{
				$user_value2 = esc_html(stripslashes($user_value));
			}
			$new_html .= '<label for="ninja_forms_field_'.$field_id.'">'.$field_label;
			if($sub_edit == 'text'){
				$new_html .= '<input type="text" id="ninja_forms_field_'.$field_id.'" name="" value="'.$user_value2.'" class="code widefat">';
			}else if($sub_edit == 'textarea'){
			
			}
			$new_html .= '</label>';
			$new_html .= '</div>';
		}
	}
	*/
	header("Content-type: application/json");
	$array = array('new_html' => $new_html);
	echo json_encode($array);
	//echo "hello world";
	die();
}

add_action('wp_ajax_ninja_forms_edit_sub', 'ninja_forms_edit_sub');
function ninja_forms_edit_sub(){
	global $wpdb;
	$sub_id = $_REQUEST['sub_id'];
	$sub_data = $_REQUEST['sub_data'];

	$args = array(
		'sub_id' => $sub_id,
		'data' => $sub_data,
	);
	
	ninja_forms_update_sub($args);
	die();
}

add_action('wp_ajax_ninja_forms_delete_sub', 'ninja_forms_delete_sub');
add_action('wp_ajax_nopriv_ninja_forms_delete_sub', 'ninja_forms_delete_sub');
function ninja_forms_delete_sub($sub_id = ''){
	global $wpdb;
	if($sub_id == ''){
		$ajax = true;
		$sub_id = $_REQUEST['sub_id'];
	}else{
		$ajax = false;
	}
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".NINJA_FORMS_SUBS_TABLE_NAME." WHERE id = %d", $sub_id));
	if( $ajax ){
		die();
	}
}

add_action('wp_ajax_ninja_forms_ajax_submit', 'ninja_forms_ajax_submit');
add_action('wp_ajax_nopriv_ninja_forms_ajax_submit', 'ninja_forms_ajax_submit');
function ninja_forms_ajax_submit(){
	global $ninja_forms_processing;
	//add_action( 'init', 'test' );
	//add_action( 'init', 'ninja_forms_setup_processing_class', 5 );
	//add_action( 'init', 'ninja_forms_pre_process', 999 );
	//ninja_forms_setup_processing_class();
	//ninja_forms_pre_process();
	//die();
}

function ninja_forms_array_merge_recursive() {
	$arrays = func_get_args();
	$base = array_shift($arrays);

	foreach ($arrays as $array) {
		reset($base); //important
		while (list($key, $value) = @each($array)) {
			if (is_array($value) && @is_array($base[$key])) {
				$base[$key] = ninja_forms_array_merge_recursive($base[$key], $value);
			} else {
				$base[$key] = $value;
			}
		}
	}

	return $base;
}