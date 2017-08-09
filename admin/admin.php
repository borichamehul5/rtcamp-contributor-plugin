<?php

//Prevent Direct Access to File (Only WordPress Can Access)
if (!defined('ABSPATH')) {
	die('No script kiddies please!');
}

//Add Action to Display Contributor Meta Box in Admin
add_action('add_meta_boxes', 'rtcamp_contributors');

//Add Action to Save Contributor Meta Box
add_action('save_post', 'rtcamp_contributors_save');

//Contributor Meta Box Function
function rtcamp_contributors() {

	if (!current_user_can('publish_posts')) {
		return;
	}

	add_meta_box('contributors', 'Contributors', 'rtcamp_contributors_content', 'post', 'normal', 'high');
}

//Contributor Meta Box Render Function
function rtcamp_contributors_content($post) {
	
	//Get Custom Fields for the Post
	$post_custom = get_post_custom($post->ID);

	//Explode the list of users to array for search purpose
	$user_ids = explode(',', $post_custom['rtcamp_contributors_list'][0]);

	//Get list of WordPress Users who are not subscribers
	$blogusers = get_users(array(
		'orderby' => 'registered',
		'order'   => 'DESC',
   		'who'     => 'authors'
		) 
	);

	//Generate Nonce for Additonal Security as per WordPress Standards
	wp_nonce_field('1d9fe805f4b1ec59873a46bd299b9a60', 'rtcamp_contributors_nonce');

	//Add Scrolling Capability
	echo '<div style="overflow-y:scroll; height:150px">';

	foreach ($blogusers as $user) { 

		//Initialize string to user login
 		$userdetails=$user->user_login;

 		//If First Name of User is Available
 		if($user->first_name!="") {

 			$userdetails = $userdetails." (".$user->first_name;

 			//If Last Name of User is Available
 			if($user->last_name!="") {
 				$userdetails = $userdetails." ".$user->last_name;
 			}

 			$userdetails = $userdetails.")";
 		}

 		//For post author
		if( wp_get_current_user()->ID == $user->ID ) {
			echo '<input type="checkbox" checked disabled>' . esc_html($user->user_login) . '<br/>';
		}
		//If user checked as contributor
		elseif( in_array($user->ID, $user_ids) ) {
			echo '<input type="checkbox" name="rtcamp_contributors_list[]" value="' . $user->ID . '" checked>' . esc_html($userdetails) . '<br/>';
		}
		//Else
  		else{
  			echo '<input type="checkbox" name="rtcamp_contributors_list[]" value="' . $user->ID . '">' . esc_html($userdetails) . '<br/>';
  		}
	}

	echo '</div>';

}

//Save Function for Contributor Meta Box
function rtcamp_contributors_save($post_id) {

	//If autosave then return (do not alter the database)
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	//Nonce Verification
	if (!isset($_POST['rtcamp_contributors_nonce']) || !wp_verify_nonce($_POST['rtcamp_contributors_nonce'], '1d9fe805f4b1ec59873a46bd299b9a60')) {
		return;
	}

	//Check Permission for Current User
	if (!current_user_can('publish_posts')) {
		return;
	}

	//If Contributors are Selected
	if (isset($_POST['rtcamp_contributors_list'])) {
		$custom_meta = wp_get_current_user()->ID.','.implode(',', array_map('esc_attr', $_POST['rtcamp_contributors_list']));
	}

	//By default add the user creating the post
	else {
	  $custom_meta = wp_get_current_user()->ID;
	}

	//Save the Contributor Meta Box data
	update_post_meta($post_id, 'rtcamp_contributors_list', $custom_meta);

}