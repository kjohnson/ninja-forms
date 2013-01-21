<?php
add_action( 'init', 'ninja_forms_admin_save', 999 );

function ninja_forms_admin_save(){
	global $ninja_forms_tabs, $ninja_forms_sidebars, $ninja_forms_tabs_metaboxes, $ninja_forms_admin_update_message;
	if(!empty($_POST) AND !empty($_POST['_ninja_forms_admin_submit']) AND $_POST['_ninja_forms_admin_submit'] != ''){
		if(wp_verify_nonce($_POST['_ninja_forms_admin_submit'],'_ninja_forms_save') AND check_admin_referer('_ninja_forms_save','_ninja_forms_admin_submit')){
			$current_page = $_REQUEST['page'];
			$current_tab = ninja_forms_get_current_tab();
			
			$data_array = array();
			if(isset($_REQUEST['form_id'])){
				$form_id = $_REQUEST['_form_id'];
			}
			foreach($_POST as $key => $val){
				if(substr($key, 0, 1) != '_'){
					$data_array[$key] = $val;
				}
			}

			$data_array = ninja_forms_stripslashes_deep( $data_array );
			//$data_array = ninja_forms_esc_html_deep( $data_array );
			// Get the save function of our current tab and call it, passing the data that has been posted.
			$save_function = $ninja_forms_tabs[$current_page][$current_tab]['save_function'];
			$arguments = func_get_args();
			array_shift($arguments); // We need to remove the first arg ($function_name)
			if(isset($form_id)){
				$arguments['form_id'] = $form_id;
			}
			$arguments['data'] = $data_array;
			if($save_function != ''){
				$ninja_forms_admin_update_message = call_user_func_array($save_function, $arguments);
			}

			//Call any save functions registered to metaboxes
			if( isset( $ninja_forms_tabs_metaboxes[$current_page][$current_tab] ) AND is_array( $ninja_forms_tabs_metaboxes[$current_page][$current_tab] ) AND !empty( $ninja_forms_tabs_metaboxes[$current_page][$current_tab] ) ){
				foreach( $ninja_forms_tabs_metaboxes[$current_page][$current_tab] as $slug => $opts ){
					if( isset( $opts['save_function'] ) ){
						$save_function = $opts['save_function'];
						$arguments = func_get_args();
						array_shift($arguments); // We need to remove the first arg ($function_name)
						if(isset($form_id)){
							$arguments['form_id'] = $form_id;
						}
						$arguments['data'] = $data_array;
						if($save_function != ''){
							if( $ninja_forms_admin_update_message != '' ){
								$ninja_forms_admin_update_message .= ' ';
							}
							$ninja_forms_admin_update_message .= call_user_func_array($save_function, $arguments);
						}
					}
					
					// Get the save function of our options, if set, and call them, passing the data that has been posted.
					if( isset( $opts['settings'] ) AND !empty( $opts['settings'] ) ){
						foreach( $opts['settings'] as $setting ){
							if( isset( $setting['save_function'] ) ){
								if(isset($form_id)){
									$arguments['form_id'] = $form_id;
								}
								$arguments['data'] = $data_array;
								if( $setting['save_function'] != '' ){
									if( $ninja_forms_admin_update_message != '' ){
										$ninja_forms_admin_update_message .= ' ';
									}
									$ninja_forms_admin_update_message .= call_user_func_array($setting['save_function'], $arguments);
								}
							}
						}
					}
				}
			}

			// Get the save function of our current sidebars, if present, and call them, passing the data that has been posted.
			if(isset($ninja_forms_sidebars[$current_page][$current_tab]) AND is_array($ninja_forms_sidebars[$current_page][$current_tab])){
				foreach($ninja_forms_sidebars[$current_page][$current_tab] as $slug => $sidebar){
					if($sidebar['save_function'] != ''){
						$save_function = $sidebar['save_function'];
						$arguments = func_get_args();
						array_shift($arguments); // We need to remove the first arg ($function_name)
						if( isset( $form_id ) ){
							$arguments['form_id'] = $form_id;
						}
						$arguments['data'] = $data_array;
						if($save_function != ''){
							if( $ninja_forms_admin_update_message != '' ){
								$ninja_forms_admin_update_message .= ' ';
							}
							$ninja_forms_admin_update_message .= call_user_func_array($save_function, $arguments);
						}
					}
				}
			}
		}
	}
}