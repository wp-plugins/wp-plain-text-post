<?php
/*
Plugin Name: WP Plain Text Post
Plugin URI: http://www.thamizhchelvan.com/wordpress/wp-plain-text-post-plugin/
Description: A simple plug-in to configure plain text post for user roles and post types.
Author: Thamizhchelvan
Version: 1.0
Author URI: http://thamizhchelvan.com/
*/

add_action('admin_menu', 'wpplainpost_create_menu');

function wpplainpost_create_menu(){	
	add_submenu_page( 'options-general.php', 'WP Plain Post', 'WP Plain Post', 'manage_options', 'wpplainpost-page', 'wpplain_post_settings_page' );
	add_action( 'admin_init', 'register_wpplainpost_settings' );
}

function register_wpplainpost_settings() {	
	register_setting( 'wpplain-post-settings', 'wpplainpost_user_roles' );	
	register_setting( 'wpplain-post-settings', 'wpplainpost_post_types' );		
	register_setting( 'wpplain-post-settings', 'wpplainpost_post_allowed_tags' );		
}





function wpplain_post_settings_page(){  
?>
<div class="wrap">
<h2>WP Plain Post</h2>
<p><i>WP Plain Post</i> Wordpress plugin allows you to configure the list of allowed HTML tags in the post content by user roles and post types.</p>

<form method="post" action="options.php">
    <?php settings_fields( 'wpplain-post-settings' ); ?>
    <?php do_settings_sections( 'wpplain-post-settings' ); ?>
    
	<table class="form-table">
        
		<tr valign="top">
        <th scope="row">Enabled User Roles</th>
			<td>
			<?php
			global $wp_roles;
			if(!isset($wp_roles)){
				$wp_roles = new WP_Roles();
			}
			$roles = $wp_roles->get_names();
			$selected_roles = get_option('wpplainpost_user_roles');
			if(!is_array($selected_roles)){
				$selected_roles = array();
			}
		
			foreach ($roles as $role_value => $role_name) {		
				$checked = in_array($role_value, $selected_roles) ? "checked" : "";
				echo '<p><input name="wpplainpost_user_roles[]" '. $checked. ' type="checkbox" value="' . $role_value . '">'.$role_name.'</p>';
			}
			?>
			
			
			
			
			</td>
        </tr>
         
       <tr valign="top">
        <th scope="row">Enabled Post Types</th>
			<td>
			<?php
			$args = array(
				'public' => true,				
			);
			
			$post_types = get_post_types($args, 'names' ); 
			$skip_types = array('attachment');
			

			$selected_types = get_option('wpplainpost_post_types');
			if(!is_array($selected_types)){
				$selected_types = array();
			}
		
			foreach ($post_types as $ptype) {	
				if(!in_array($ptype,$skip_types)){
					$checked = in_array($ptype, $selected_types) ? "checked" : "";
					echo '<p><input name="wpplainpost_post_types[]" '. $checked. ' type="checkbox" value="' . $ptype . '">'.$ptype.'</p>';
				}
			}
			
		
			?>
			
			
			
			
			</td>
        </tr>
		
		<tr valign="top">
        <th scope="row">Allowed HTML tags in post content</th>
			<td>
			<p>Enter the allowed HTML tags without space and comma. Example: &lt;a&gt;&lt;p&gt;&lt;img&gt;&lt;strong&gt;<p>
			<textarea name="wpplainpost_post_allowed_tags" class="large-text" rows="3"><?php echo get_option('wpplainpost_post_allowed_tags'); ?></textarea>		
			</td>
        </tr>
		
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php
}

function _wpplainpost_role_disabled(){
	$selected_roles = get_option('wpplainpost_user_roles');
			if(!is_array($selected_roles)){
				$selected_roles = array();
			}
			
			$current_user = wp_get_current_user();
			$current_user_roles = $current_user->roles;
			
			$intersection = array_intersect($current_user_roles,$selected_roles);
			if(count($intersection) > 0){
				return true;
			}
			else{
				return false;
			}
			
}





function wpplainpost_clean_post_content($content){
	global $post;	
	$selected_types = get_option('wpplainpost_post_types');
			if(!is_array($selected_types)){
				$selected_types = array();
			}
			
	if(_wpplainpost_role_disabled() && in_array($post->post_type,$selected_types)){
		$allowed_tags = get_option('wpplainpost_post_allowed_tags');
		return strip_tags($content, $allowed_tags);
	}
	
	return $content;
}

add_filter( 'content_save_pre' , 'wpplainpost_clean_post_content' , 10, 1);
?>
