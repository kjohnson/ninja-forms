<?php
global $wpdb;
$ninja_forms_subs_table_name = $wpdb->prefix . "wpnj_forms_subs";
if(!empty($_POST) && check_admin_referer('ninja_save_plugin_settings','ninja_plugin_settings')){
	$current_settings = get_option("ninja_forms_settings");
	
	foreach($_POST as $key => $val){
		if($key != 'submitted' && $key != 'submit'){
			$current_settings[$key] = $val;
		}
	}
	update_option("ninja_forms_settings", $current_settings);
	?>
	<script language="javascript">
		window.location.reload();
	</script>
	<?php
}
$plugin_settings = get_option("ninja_forms_settings");

$color_array = array(
	array("label" => "White and Azure", "value" => "azure"),
	array("label" => "White and Black", "value" => "black"),
	array("label" => "White and Blue", "value" => "blue"),
	array("label" => "White and Green", "value" => "green"),
	array("label" => "White and Grey", "value" => "grey"),
	array("label" => "White and Orange", "value" => "orange"),
	array("label" => "White and Violet", "value" => "violet"),
	array("label" => "White and Yellow", "value" => "yellow"),
	array("label" => "Azure", "value" => "all-azure"),
	array("label" => "Black", "value" => "all-black"),
	array("label" => "Blue", "value" => "all-blue"),
	array("label" => "Green", "value" => "all-green"),
	array("label" => "Grey", "value" => "all-grey"),
	array("label" => "Orange", "value" => "all-orange"),
	array("label" => "Violet", "value" => "all-violet"),
	array("label" => "Yellow", "value" => "all-yellow")
	);
	
$upload_dir = dirname(__FILE__);
$upload_dir = str_replace("includes", "", $upload_dir);
$upload_dir .= "uploads/";

$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);
?>
<div id="" class="" style="display:none;">
<?php foreach($color_array as $color){ ?>
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/bottom-left.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/bottom-middle.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/bottom-right.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/middle-left.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/middle-right.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/tail-bottom.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/tail-left.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/top-left.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/top-middle.png">
<img src="<?php echo NINJA_FORMS_URL;?>/js/jquerybubblepopup-theme/<?php echo $color['value'];?>/top-right.png">
<?php } ?>
</div>

<div class="wrap">
<div id="icon-ninja-custom-forms" class="icon32"><img src="<?php echo NINJA_FORMS_URL;?>/images/wpnj-ninja-head.png"></div>
<h2><?php printf(__('Ninja Forms %s - Plugin Settings', 'ninja-forms'), NINJA_FORMS_TYPE);?></h2>

<h3><?php _e('Version', 'ninja-forms');?> <?php echo $plugin_settings['version'];?></h3>
<h3><?php _e('Database Version', 'ninja-forms');?> <?php echo $plugin_settings['db_version'];?></h3>
<form id="" name="" action="" method="post">
<?php wp_nonce_field('ninja_save_plugin_settings','ninja_plugin_settings'); ?>
<input type="hidden" name="submitted" value="yes">
<input type="hidden" name="default_style" value="unchecked"><input type="checkbox" name="default_style" id="default_style" value="checked" <?php echo $plugin_settings['default_style'];?>><label for="default_style"> <?php _e('Use Ninja Forms Default Stylesheet', 'ninja-forms');?></label><br />
<input type="hidden" name="admin_help" value="unchecked"><input type="checkbox" name="admin_help" id="admin_help" value="checked" <?php echo $plugin_settings['admin_help'];?>><label for="admin_help"> <?php _e('Show Admin Help/Tips', 'ninja-forms');?></label><br />
<h4><?php _e('Form Help/Tips hover color scheme', 'ninja-forms');?>:</h4>
	<select name="help_color" id="help_color" class="ninja_forms_settings">
		<?php foreach($color_array as $color){ ?>
		<option value="<?php echo $color['value'];?>" <?php if($plugin_settings['help_color'] == $color['value']){ echo 'selected';} ?>><?php echo $color['label'];?></option>
		<?php } ?>
	</select><br />
<label for="help_size"><h4><?php _e('Help/Tip Box Size', 'ninja-forms');?>:</h4> <input type="text" name="help_size" id="help_size" value="<?php echo $plugin_settings['help_size'];?>" class="ninja_forms_settings"></label>

<p><?php _e('Mouse-over to see your changes', 'ninja-forms');?>: <img id='show_help_color' src='<?php echo NINJA_FORMS_URL;?>/images/question-ico.gif'></p>
<?php /* Lite version does not include     */ 
	if(NINJA_FORMS_TYPE == 'Pro'){
		require_once(NINJA_FORMS_DIR."/includes/pro/plugin-settings-upload.php");
	}
/*               */ ?>
<input class="button-primary ninja_save_data" type="submit" value="<?php _e('Save Changes', 'ninja-forms');?>">
</form>	
</div>