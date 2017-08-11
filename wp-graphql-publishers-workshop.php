<?php
/**
 * Plugin Name: WPGraphQL - WordCamp for Publishers Example Plugin
 * Description: This plugin was created for the 2017 WordCamp for Publishers conference and is not intended for production use.
 * Author: WPGraphQL, Jason Bahl
 */

/**
 * Add a new root query entry point.
 *
 * This can be queried like so:
 *
 * {
 *   wordCampRocks
 * }
 *
 * and will return the following JSON:
 *
 * [
 *   data => [
 *     wordCampRocks: 'Yes, it does'
 *   ]
 * ]
 *
 */
add_action( 'graphql_root_queries', function( $fields ) {
	$fields['wordCampRocks'] = [
		'type' => \WPGraphQL\Types::string(),
		'description' => __( 'An example field showing how to add to the root schema', 'wp-graphql-publishers' ),
		'resolve' => function() {
			return 'Yes, it does';
		},
	];
	return $fields;
} );
