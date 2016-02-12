<?php
/*
Plugin Name: MoxieTest
Plugin URI:  http://dev.tg000184.ferozo.com/moxietest
Description: This plugin allows to reproduce movies using a shortcode [list-movies] by creating a now post type (movie) and provide a list of the created posts using JSON format.
Version:     1.0
Author:      Fernando Gamba
Author URI:  http://tg000184.ferozo.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) or die( 'fuck off' );

/*
* Creating a function to create our CPT
*/

function movie_type() {

// Set UI labels for Movies Post Type
	$labels = array(
		'name'                => _x( 'Movies', 'Post Type', 'twentysixteen' ),
		'singular_name'       => _x( 'Movie', 'Post Type Singular Name', 'twentysixteen' ),
		'menu_name'           => __( 'Movies', 'twentysixteen' ),
		'name_admin_bar'      => __( 'Movie', 'twentysixteen' ),
		'parent_item_colon'   => __( 'Parent Movie', 'twentysixteen' ),
		'all_items'           => __( 'All Movies', 'twentysixteen' ),
		'view_item'           => __( 'View Movie', 'twentysixteen' ),
		'add_new_item'        => __( 'Add New Movie', 'twentysixteen' ),
		'add_new'             => __( 'Add New', 'twentysixteen' ),
		'edit_item'           => __( 'Edit Movie', 'twentysixteen' ),
		'update_item'         => __( 'Update Movie', 'twentysixteen' ),
		'search_items'        => __( 'Search Movie', 'twentysixteen' ),
		'not_found'           => __( 'Not Found', 'twentysixteen' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'twentysixteen' ),
	);
	
// Set other options for Custom Post Type
	
	$args = array(
		'label'               => __( 'Movies', 'twentysixteen' ),
		'description'         => __( 'Display Movies on your site', 'twentysixteen' ),
		'labels'              => $labels,
		// Features supported in Post Editor
		'supports'            => array( 'title' ),
		/* A hierarchical post is like Pages and can have
		* Parent and child items. A non-hierarchical CPT
		* is like Posts.
		*/	
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);
	
	register_post_type( 'movie', $args );
	
	/*$tax_args = array(
        'label'   => 'Movies Tags',
    );
    register_taxonomy( 'moxie_movie_tag', 'moxie_movie', $tax_args );*/

}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/

add_action( 'init', 'movie_type', 0 );


/* Adds a meta box to the post edit screen */
add_action( 'add_meta_boxes', 'moxie_add_custom_box' );
function moxie_add_custom_box() {
    $screens = array( 'post', 'movie' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'moxie_rating_id',     // Unique ID
            'Rating',      		// Box title
            'moxie_rating',  	// Content callback
             $screen            // post type
        );
		add_meta_box(
            'moxie_description_id',     // Unique ID
            'Description',      		// Box title
            'moxie_description',  	// Content callback
             $screen            // post type
        );
		add_meta_box(
            'moxie_posterurl_id',     // Unique ID
            'Poster URL',      		// Box title
            'moxie_poster',  	// Content callback
             $screen            // post type
        );
		add_meta_box(
            'moxie_year_id',     // Unique ID
            'Year',      		// Box title
            'moxie_year',  	// Content callback
             $screen            // post type
        );
    }
}

/* Print the input field for rating */
function moxie_rating() { 
	global $post; ?>
	 <p>
		<label for="moxie_rating_id"> Enter the rating of the movie</label>
		<input type="text" name="moxie_rating_id" id="moxie_rating_id"  value="<?php echo esc_attr( get_post_meta( $post->ID, 'moxie_rating_id', true ) ); ?>" />
	 </p>
<?php }

/* Print the input field for description */
function moxie_description() {
	global $post;	?>
	 <p>
		<label for="moxie_description_id"> Enter the Description of the movie</label>
		<textarea cols="50" rows="5" name="moxie_description_id" id="moxie_description_id"  ><?php echo esc_attr( get_post_meta( $post->ID, 'moxie_description_id', true ) ); ?></textarea>
	 </p>
<?php }

/* Print the input field for rating */
function moxie_poster() { 
	global $post ?>
	 <p>
		<label for="moxie_posterurl_id"> Enter the URL of the poster of the movie</label>
		<input type="text" name="moxie_posterurl_id" size="60" id="moxie_posterurl_id"  value="<?php echo esc_attr( get_post_meta( $post->ID, 'moxie_posterurl_id', true ) ); ?>" />
	 </p>
<?php }

/* Print the input field for rating */
function moxie_year() { 
	global $post ?>
	 <p>
		<label for="moxie_year_id"> Enter the year of the movie</label>
		<input type="text" name="moxie_year_id" id="moxie_year_id"  value="<?php echo get_post_meta( $post->ID, 'moxie_year_id', true) ; ?>" />
	 </p>
<?php }


// Save the Metabox Data
function moxie_save_meta($post_id, $post) {
	
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	
	$year_meta['moxie_year_id'] = $_POST['moxie_year_id'];
	$description_meta['moxie_description_id'] = $_POST['moxie_description_id'];
	$posterurl_meta['moxie_posterurl_id'] = $_POST['moxie_posterurl_id'];
	$rating_meta['moxie_rating_id'] = $_POST['moxie_rating_id'];
	
	// Add values of $year_meta as custom fields
	foreach ($year_meta as $key => $value) { // Cycle through the $year_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}
	// Add values of $description_meta as custom fields
	foreach ($description_meta as $key => $value) { // Cycle through the $year_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}
	// Add values of $posterurl_meta as custom fields
	foreach ($posterurl_meta as $key => $value) { // Cycle through the $year_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}
	// Add values of $rating_meta as custom fields
	foreach ($rating_meta as $key => $value) { // Cycle through the $year_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}

}

add_action('save_post', 'moxie_save_meta', 1, 2); // save the custom fields

?>