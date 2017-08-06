<?php

//Add Filter to Display Contributor Box (Frontend)
add_filter('the_content', 'rtcamp_contributors_view');

// Register style sheet.
add_action('wp_enqueue_scripts', 'rtcamp_contributors_styles');

//Display Contributor Box at the end of the post
function rtcamp_contributors_view($content){

	//If not a post then return
	if (!is_single()) {
		return $content;
	}

	//Get Custom Fields for the Post
	$post_custom = get_post_custom (get_the_ID());

	//Explode the list of users to array for search purpose
	$user_ids = explode (',', $post_custom['rtcamp_contributors_list'][0]);

	//If the users are not set then return content to hide contributors box
	if($user_ids[0]=='') {
		return $content;
	}

	//Initialize flag to zero to check whether there will be any contributors or not
	$flag=0;

	$box = '
	<div class="rtcamp_contributors_box">
		<h5>Contributors</h5>
		';

	foreach ($user_ids as $user){

		//Get User Data from User ID
		$userdata = get_userdata ($user);

		//If user with given ID exists
		if ($userdata != FALSE){
			$box = $box . '<div class="rtcamp_contributors">' . get_avatar($user, 40) . '&nbsp;&nbsp;<a href="' . get_author_posts_url($user) . '">' . $userdata->display_name . '</a></div><br/>';
			$flag++;
		}

	}

	//If users are set but deleted afterwards and no contributor is available then hide contributors box
	if($flag==0) {
		return $content;
	}

	$box = $box . '</div>';

	//Using this function makes sure that stylesheet is included only on post
	wp_enqueue_style('rtcamp_contributors');

	return $content.$box;
}

function rtcamp_contributors_styles() {

	//Register Style Sheet
	wp_register_style('rtcamp_contributors', plugins_url('rtcamp-contributors/view/rtcamp-style.css'));

}