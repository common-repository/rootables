<?php
/*
Plugin Name: root.abl.es
Plugin URI: http://root.abl.es/
Description: Share your hacks with the world!
Author: Martin Fitzpatrick
Version: 1.3
Author URI: http://root.abl.es/user/mfitzp/
*/
	
$_ROOTABLES_URL = 'http://root.abl.es/widgets/wordpress/methods/';

/**
 * Rootables Class
 */
class Rootables extends WP_Widget {
    /** constructor */
    function Rootables() {
        parent::WP_Widget(false, $name = 'root.abl.es');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$cache = get_option('widget_rootables_cache');

		if ( !is_array($cache) )
			$cache = array();
        
    	echo $before_widget; 
        
	    if ( $title )
                echo $before_title . $title . $after_title; 

        if( $cache['last-updated'] == '' || $cache['last-updated'] < time() - ($instance['updateminutes'] * 60) )
        {
            global $_ROOTABLES_URL;
            // If our data is out of date, request a new lot from the server
            $url = $_ROOTABLES_URL . '?'
                     . 'width=' . $instance['width']
                     . '&number=' . $instance['number']
                     . '&show_filter=' . $instance['show']
                     . '&user_email=' . $instance['email']
                     . '&tags=' . $instance['tags'];

            $resp = wp_remote_request($url);
            if ( !is_wp_error($resp) ) {
                $cache['content'] = wp_remote_retrieve_body( $resp );
                $cache['last-updated'] = time();
                update_option('widget_rootables_cache', $cache);
            }
        }

	    if (!empty($cache['content']))
        	{ 
			  echo '<div id="mm-widget" class="mm-rootables">' . $cache['content'] . '</div>';
		    }

    	echo $after_widget; 
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        update_option('widget_rootables_cache', '');				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        if (isset($instance['title'])) : $title = esc_attr($instance['title']); else : $title = ''; endif;

        if (isset($instance['show'])) : $show = esc_attr($instance['show']); else : $show = 'featured'; endif;
        if (isset($instance['email'])) : $email = esc_attr($instance['email']); else : $email = ''; endif;

	    if (isset($instance['tags'])) : $tags = esc_attr($instance['tags']); else : $tags = 'linux'; endif;

	    if (isset($instance['number'])) : $number = esc_attr($instance['number']); else : $number = 3; endif;
	    if (isset($instance['updateminutes'])) : $updateminutes = esc_attr($instance['updateminutes']);  else : $updateminutes = 60; endif;
	    if (isset($instance['width'])) : $width = esc_attr($instance['width']); else : $width = 190; endif;
	

	    if ($number < 1)
	        $number = 1;

	    if ($updateminutes < 15)
	        $updateminutes = 15;

	    if ($width < 190)
	        $width = 190;
	
      ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

        <p><label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Show:'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show'); ?>" name="<?php echo $this->get_field_name('show'); ?>">
                <option value="featured" <?php if ($show == 'featured'){ ?>selected="selected"<? } ?>>Featured</option>
                <option value="random" <?php if ($show == 'random'){ ?>selected="selected"<? } ?>>Random</option>
                <option value="tagged" <?php if ($show == 'tagged'){ ?>selected="selected"<? } ?>>Tagged</option>
                <option value="user" <?php if ($show == 'user'){ ?>selected="selected"<? } ?>>Your Hacks</option>
                <option value="favorites" <?php if ($show == 'favorites'){ ?>selected="selected"<? } ?>>Your Favorites</option>
            </select></p>

	    <p><label for="<?php echo $this->get_field_id('email'); ?>"><?php _e('Your root.abl.es Account Email:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" type="text" value="<?php echo $email; ?>" />
        <small><br />You need this to show Your Hacks or Your Favorites. <a href="http://root.abl.es/accounts/register/?utm_source=wordpress&utm_medium=admin" target="_blank">Click here to register now!</a></small></label></p>

	    <p><label for="<?php echo $this->get_field_id('tags'); ?>"><?php _e('Tags:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('tags'); ?>" name="<?php echo $this->get_field_name('tags'); ?>" type="text" value="<?php echo $tags; ?>" />
        <small><br />Select to show Tagged hacks, then enter some comma-separated <a href="http://root.abl.es/tags/?utm_source=wordpress&utm_medium=admin" target="_blank">tags</a> here</small></label></p>

	    <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('# of Hacks:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" /></label></p>
	    <p><label for="<?php echo $this->get_field_id('updateminutes'); ?>"><?php _e('Update (minutes):'); ?> <input class="widefat" id="<?php echo $this->get_field_id('updateminutes'); ?>" name="<?php echo $this->get_field_name('updateminutes'); ?>" type="text" value="<?php echo $updateminutes; ?>" /></label></p>
	    <p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width (px):'); ?> <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" /></label></p>
      <?php 
    }


} // class Rootables


add_action('widgets_init', create_function('', 'return register_widget("Rootables");')); // register Rootables widget
wp_enqueue_style( 'rootables', plugins_url( 'style.css', __FILE__ ) );

?>
