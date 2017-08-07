<?php

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

	//Get list of WordPress Users
	$blogusers = get_users(array(
		'orderby' => 'registered',
		'order'   => 'DESC'
		) 
	);

	//Generate Nonce for Additonal Security as per WordPress Standards
	wp_nonce_field('1d9fe805f4b1ec59873a46bd299b9a60', 'rtcamp_contributors_nonce');

	//Add Scrolling Capability
	echo '<div style="overflow-y:scroll; height:150px">';

	foreach ($blogusers as $user) {
		echo '<input type="checkbox" name="rtcamp_contributors_list[]" value="' . $user->ID . '" ' . (in_array($user->ID, $user_ids) ? 'checked' : '') . '>' . esc_html($user->user_login) . '<br/>';
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

	//Save the Contributor Meta Box data
	if (isset($_POST['rtcamp_contributors_list'])) {
		update_post_meta($post_id, 'rtcamp_contributors_list', implode(',', array_map('esc_attr', $_POST['rtcamp_contributors_list'])));
	}

}