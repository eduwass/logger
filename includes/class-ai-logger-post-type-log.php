<?php

/**
 * Custom post type for Logs
 */
class AI_Logger_Post_Type_Log extends AI_Logger_Post_Type {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 */
	public $name = 'ai_log';

	/**
	 * Creates the post type.
	 */
	public function create_post_type() {
		register_post_type( $this->name, array(
			'labels' => array(
				'name'               => __( 'Logs', 'ai-logger' ),
				'singular_name'      => __( 'Log', 'ai-logger' ),
				'add_new'            => __( 'Add New Log', 'ai-logger' ),
				'add_new_item'       => __( 'Add New Log', 'ai-logger' ),
				'edit_item'          => __( 'Edit Log', 'ai-logger' ),
				'new_item'           => __( 'New Log', 'ai-logger' ),
				'view_item'          => __( 'View Log', 'ai-logger' ),
				'search_items'       => __( 'Search Logs', 'ai-logger' ),
				'not_found'          => __( 'No Logs found', 'ai-logger' ),
				'not_found_in_trash' => __( 'No Logs found in Trash', 'ai-logger' ),
				'parent_item_colon'  => __( 'Parent Log:', 'ai-logger' ),
				'menu_name'          => __( 'Logs', 'ai-logger' ),
			),
			'description' => __( 'A collection of logs that are generated by the system to assist in debugging', 'ai-logger' ),
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'menu_icon' => 'dashicons-warning',
			'menu_position' => 80,
			'capabilities' => array(
				'create_posts'       => 'ai_log_create_posts',
				'edit_post'          => 'create_users',
				'read_post'          => 'create_users',
				'delete_post'        => 'create_users',
				'edit_posts'         => 'create_users',
				'edit_others_posts'  => 'create_users',
				'delete_posts'       => 'create_users',
				'publish_posts'      => 'create_users',
				'read_private_posts' => 'create_users',
			),
		) );
	}
}

$post_type_ai_log = new AI_Logger_Post_Type_Log();