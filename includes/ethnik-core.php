<?php 
function exclude($query){

	global $ethnik_groups;

	$authors = $ethnik_groups->get_user_connections( wp_get_current_user()->ID );
	if ( count ($authors)==0 ) $authors = array ( wp_get_current_user()->ID );


	if ($query->is_main_query() && !$query->is_home()) {
		$query->set('author__in', $authors );

	}

}

function show_current_group_attachments( $query) {

	global $ethnik_groups;

	$authors = $ethnik_groups->get_user_connections( wp_get_current_user()->ID );
	if ( count ($authors)==0 ) $authors = array ( wp_get_current_user()->ID );

	$query['author__in'] = $authors;

	return $query;
}

function my_tweaked_admin_bar() {
	global $wp_admin_bar;
	global $ethnik_groups;

	if (current_user_can('manage_options')) return;

	$groupName = $ethnik_groups->get_current_user_groups();

	if ($groupName == null) return;

	$groups = '';

	foreach ( $groupName as $groupItem) {

		if (strlen($groups)>100){
			break;
		}

		$groups.= $groupItem->name;
		if( next( $groupName ) ) $groups.= ', ';
	}


	$wp_admin_bar->add_menu( array(
			'id'    => 'ethnik-group',
			'title' => '<span class="group-icon"></span> '.$groups,
			'meta'  => array( 'class' => 'wpse--item' )
	));

}

function getGroupsListByUserId($user_id){
	global $ethnik_groups;

	$groupsIds = $ethnik_groups->get_user_groups( $user_id );

	foreach ($groupsIds as $gId){
		$groupName.= $ethnik_groups->get_group_byid($gId)->name;
		if( next( $groupsIds ) ) $groups.= ', ';
	}

	return $groupName;

}

/* Display custom column */
function display_ethnik_group_owner( $column, $post_id ) {

	$authorId = get_post($post_id)->post_author;
	$groupName = getGroupsListByUserId($authorId);

	echo $groupName;
}


/* Add custom column to post list */
function add_ethnikGroup_column( $columns ) {
	return array_merge( $columns,array('Ethnik Group') );
}


function add_ethnikGroup_user_column( $columns ) {
	$columns['ethnik'] = 'Ethnik Group';
	return $columns;
}


function display_ethnik_user_group( $val, $column_name, $user_id ){

	switch ($column_name) {
		case 'ethnik' :
			$groupName = getGroupsListByUserId($user_id);
			return $groupName;
			break;
	}
	return $val;
}


	
if (class_exists('EthnikGroups')) {
	
	global $ethnik_groups;
	$ethnik_groups = new EthnikGroups();

	if (!is_admin()) {
		add_action( 'pre_get_posts', 'exclude' );
		add_action( 'wp_before_admin_bar_render', 'my_tweaked_admin_bar' );

		add_filter( 'ajax_query_attachments_args', 'show_current_group_attachments', 10, 1 );
	}
	else {
		add_filter( 'manage_posts_columns' , 'add_ethnikGroup_column' );
		add_filter( 'manage_pages_columns' , 'add_ethnikGroup_column' );
		add_filter('manage_users_columns' , 'add_ethnikGroup_user_column');

		add_filter( 'manage_users_custom_column', 'display_ethnik_user_group', 10, 3 );
		add_action( 'manage_posts_custom_column' , 'display_ethnik_group_owner', 10, 2 );
		add_action( 'manage_pages_custom_column' , 'display_ethnik_group_owner', 10, 2 );
	}
	
}
	


?>