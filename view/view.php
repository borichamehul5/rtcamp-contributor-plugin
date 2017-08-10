<?php

//Prevent Direct Access to File (Only WordPress Can Access)
if (!defined('ABSPATH')) {
	die('No script kiddies please!');
}

//Add Filter to Display Contributor Box (Frontend)
add_filter('the_content', 'rtcamp_contributors_view');

//Register style sheet.
add_action('wp_enqueue_scripts', 'rtcamp_contributors_styles');

//Modify Author Archives
add_action('pre_get_posts', 'archive_meta_query', 1);

//Display Contributor Box at the end of the post
function rtcamp_contributors_view($content) {

	//If not a post then return
	if (!is_single()) {
		return $content;
	}

	//Get Custom Fields for the Post
	$post_custom = get_post_custom(get_the_ID());

	//Explode the list of users to array for search purpose
	$user_ids = explode(',', $post_custom['rtcamp_contributors_list'][0]);

	//If the users are not set then return content to hide contributors box
	if ($user_ids[0]=='') {
		return $content;
	}

	//Initialize flag to zero to check whether there will be any contributors or not
	$flag = 0;

	$box = '
	<div class="rtcamp_contributors_box">
		<h5>Contributors</h5>
		';

	foreach ($user_ids as $user) {

		//Get User Data from User ID
		$userdata = get_userdata($user);

		//If user with given ID exists
		if ($userdata!=FALSE) {
			$box = $box . '<div class="rtcamp_contributors">' . get_avatar($user, 40) . '&nbsp;&nbsp;<a href="' . get_author_posts_url($user) . '">' . $userdata->display_name . '</a></div><br/>';
			$flag++;
		}

	}

	//If users are set but deleted afterwards and no contributor is available then hide contributors box
	if ($flag==0) {
		return $content;
	}

	$box = $box . '</div>';

	//Using this function makes sure that stylesheet is included only on post
	wp_enqueue_style('rtcamp_contributors');

	return $content . $box;
}

function rtcamp_contributors_styles() {

	//Register Style Sheet
	wp_register_style('rtcamp_contributors', plugins_url('rtcamp-contributor-plugin/view/rtcamp-style.css'));

}

//Change Query to List Post Where user is Contributor or Author
function archive_meta_query($query) {

	//If it is author page
	if ($query->is_author) {

		//Initialize global wpdb variable
		global $wpdb;

		//Get author data
		$author_data = get_user_by('slug', get_query_var('author_name'));

		//Find IDs of posts where user is author or contributor
		$results = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key='rtcamp_contributors_list' AND FIND_IN_SET(" . ($author_data->ID) . ",meta_value)");

		//Initialize empty array to store IDs of posts
		$post_ids = array();

		//Initialize count to 0
		$cnt = 0;

		//Store post ids one by one
		foreach ($results as $row) {
			array_push($post_ids, $row->post_id);
			$cnt++;
		}

		//Change author name to blank to list all the posts from the database
		$query->query_vars["author_name"] = "";

		if ($cnt==0) {
		//If there are no post
			$query->query_vars["post__in"] = array("");
		} else {
		//List the specified post ids
			$query->query_vars["post__in"] = $post_ids;
		}

		//Add action to change author archives title
		add_filter('get_the_archive_title', 'archive_meta_title');
	}

}

//Change author archive title
function archive_meta_title($title) {

	//Add action to show role before each post
	add_action('loop_start', 'archive_contributor_role');

	//Initialize global wp_query variable
	global $wp_query;

	//Get Author Info
	$author_data = get_user_by('login', $wp_query->query['author_name']);

	//Return the new archive page title
	return $author_data->display_name . " is Author/Contributor of Following Posts";

}

function archive_contributor_role($query) {

	//If it is a main query (for skipping widgets recent posts and things that execute posts query)
	if ($query->is_main_query()) {

		add_action('the_post', 'archive_contributor_role_post');
		add_action('loop_end', 'archive_contributor_role_end');

	}
}

function archive_contributor_role_post() {

	//Initialize global wp_query variable
	global $wp_query;

	//If the user is author of the post
	if (strtolower(get_the_author_meta('user_login'))==$wp_query->query['author_name']) {
		echo '<div style="background-color: green; text-align: center; color: white;"> Author </div>';
	} else {
		echo '<div style="background-color: lightblue; text-align: center; color: white;"> Contributor </div>';
	}
}

function archive_contributor_role_end() {

	//If the main query is finished then remove action to skip widgets
	remove_action('the_post', 'archive_contributor_role_post');   
}  