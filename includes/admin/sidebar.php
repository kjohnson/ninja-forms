<?php

function ninja_forms_sidebar_sorter($array, $sequence){
  $tmp = array();
  foreach($sequence as $s){
    foreach($array as $key => $a){
      if($key == $s){ 
        $tmp[$key] = $a;
        unset( $array[$key] );
        break;
      }
    }
  }
  if( is_array( $array ) AND !empty( $array ) ){
  	  foreach( $array as $key => $a ){
  	  	$tmp[$key] = $a;
  	}
  }

  return $tmp;
}

function ninja_forms_display_sidebars($data){
	global $ninja_forms_sidebars;
	$current_tab = ninja_forms_get_current_tab();
	$current_page = $_REQUEST['page'];
	$opt = get_option('ninja_forms_settings');
	if( isset( $opt['sidebars'][$current_page][$current_tab] ) ){
		$order = $opt['sidebars'][$current_page][$current_tab];
		$ninja_forms_sidebars[$current_page][$current_tab] = ninja_forms_sidebar_sorter( $ninja_forms_sidebars[$current_page][$current_tab], $order );
	}

?>
<div id="menu-settings-column" class="metabox-holder">
	<div id="side-sortables" class="meta-box-sortables ui-sortable">
		<?php
		if(isset($ninja_forms_sidebars[$current_page][$current_tab]) AND is_array($ninja_forms_sidebars[$current_page][$current_tab])){
			foreach($ninja_forms_sidebars[$current_page][$current_tab] as $slug => $sidebar){

				if((isset($opt['screen_options']['tab'][$current_tab]['sidebars'][$slug]['visible']) AND $opt['screen_options']['tab'][$current_tab]['sidebars'][$slug]['visible'] == 1) OR !isset($opt['screen_options']['tab'][$current_tab]['sidebars'][$slug]['visible'])){
		?>
		<div id="<?php echo $slug;?>" class="postbox">
			<h3 >
				<span><?php _e($sidebar['name'], 'ninja-forms');?></span>
			</h3>
				<span class="item-controls">
					<a class="item-edit" id="edit_id" title="<?php _e('Edit Menu Item', 'ninja-forms'); ?>" href="#"><?php _e( 'Edit Menu Item' , 'ninja-forms'); ?></a>
				</span>
			<div class="inside" id="ninja_forms_sidebar_<?php echo $slug;?>">
				<?php
				if(isset($sidebar['display_function']) AND !empty($sidebar['display_function'])){
					$sidebar_callback = $sidebar['display_function'];
					$arguments = func_get_args();
					array_shift($arguments); // We need to remove the first arg ($function_name)
					$arguments['slug'] = $slug;
					$arguments['data'] = $data;
					call_user_func_array($sidebar_callback, $arguments);
				}

				if(isset($sidebar['settings']) AND !empty($sidebar['settings'])){
					foreach($sidebar['settings'] as $option){
						?>
						<p class="field-controls">
						<?php
						if(isset($option['display_function']) AND !empty($option['display_function'])){
							call_user_func_array($option['display_function'], $arguments);
						}else{

							$name = $option['name'];

							if( isset( $data[$name] ) ){
								$value = $data[$name];
							}else{
								$value = '';
							}
							if( isset( $option['class'] ) ){
								$class = $option['class'];
							}else{
								$class = '';
							}

							switch($option['type']){
								case 'checkbox':
									?>
									<input type="hidden" name="<?php echo $name;?>" value="0">
									<input type="checkbox" name="<?php echo $name;?>" id="<?php echo $name;?>" value="1" <?php checked(1, $value);?>>
									<label for="<?php echo $name;?>"><?php _e($option['label'], 'ninja-forms');?></label>
									<?php
									break;
								case 'radio':
									?>
									<label for="<?php echo $name;?>"><?php _e($option['label'], 'ninja-forms');?></label>
									<br>
									<?php
									if(isset($option['options'])){
											$x = 0;
											foreach($option['options'] as $option){
												?>
												<input type="radio" id="<?php echo $name.'_'.$x;?>" value="<?php echo $option['value'];?>" <?php checked($option['value'], $value);?> name="<?php echo $name;?>">
												<label for="<?php echo $name.'_'.$x;?>"><?php echo $option['name'];?></label>
												<br>
												<?php
												$x++;
											}
										}
									break;
								case 'select':
									?>
									<label for="<?php echo $name;?>"><?php _e($option['label'], 'ninja-forms');?></label>
									<select name="<?php echo $name;?>" id="<?php echo $name;?>">
										<?php
										if(isset($option['options'])){
											foreach($option['options'] as $option){
												?>
												<option value="<?php echo $option['value'];?>" <?php selected($option['value'], $value);?>><?php echo $option['name'];?></option>
												<?php
											}
										}
										?>
									</select>
									<?php
									break;
								case 'text':
									?>
									<label for="<?php echo $name;?>"><?php _e($option['label'], 'ninja-forms');?></label>									
									<input type="text" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo $value;?>">
									<?php
									break;
								case 'textarea':
									?>
									<label for="<?php echo $name;?>"><?php _e($option['label'], 'ninja-forms');?></label>
									<br>
									<textarea name="<?php echo $name;?>" id="<?php echo $name;?>"><?php echo $value;?></textarea>	
									<?php
									break;
								case 'submit':
									?>
									<input type="submit" name="submit" id="" class="<?php echo $class;?>" value="<?php _e('View Submissions', 'ninja-forms');?>">
									<?php
									break;
							}
							if(isset($option['help']) AND !empty($option['help'])){
								?>
								<a href="#" class="tooltip">
								    <img id="" class='ninja-forms-help-text' src="<?php echo NINJA_FORMS_URL;?>/images/question-ico.gif" title="">
								    <span>
								        <img class="callout" src="<?php echo NINJA_FORMS_URL;?>/images/callout.gif" />
								        <?php _e($option['help'], 'ninja-forms');?>
								    </span>
								</a>
								
								<?php
							}
							if(isset($option['desc']) AND !empty($option['desc'])){
								?>
								<span class="howto"><?php echo $option['desc'];?></span>
								<?php
							}
						}
						?>
						</p>
						<?php
					}
				}
				?>
			</div>
		</div>		
		<?php
				}
			}
		}
		?>
	</div>
<?php
}
?>