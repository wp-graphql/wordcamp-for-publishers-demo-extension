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

/**
 * Add a new "color" field to the "post" schema.
 *
 * This can be queried like so (for the post with a database id 1):
 * NOTE: "cG9zdDox" = base64_encode( 'post:1' );
 *
 * {
 *   post(id:"cG9zdDox") {
 *     title
 *     color
 *   }
 * }
 *
 * Assuming the post being queried had a postmeta field "color" with a value of "blue"
 * This will return:
 *
 * [
 *   data => [
 *     post => [
 *       title => 'Hello World',
 *       color => 'blue'
 *     ]
 *   ]
 * ]
 *
 */
add_action( 'graphql_post_fields', function( $fields ) {
	$fields['color'] = [
		'type' => \WPGraphQL\Types::string(),
		'description' => __( 'An example showing how to add a field to the "post" schema', 'wp-graphql-publishers' ),
		'resolve' => function( \WP_Post $post ) {
			return get_post_meta( $post->ID, 'color', true );
		},
	];
	return $fields;
}, 10, 1);

/**
 * Register a new Post Type and add it to the GraphQL Schema
 *
 * These can be queried like so:
 *
 * {
 *   books {
 *     edges {
 *       node {
 *         id
 *         title
 *       }
 *     }
 *   }
 * }
 *
 */
add_action( 'init', function() {
	register_post_type( 'book', [
		'label' => __( 'Books', 'wp-graphql-publishers' ),
		'public' => true,
		'show_in_graphql' => true,
		'graphql_single_name' => 'book',
		'graphql_plural_name' => 'books',
	] );
} );

/**
 * Register custom taxonomy "genre" connected to the "book" post type
 *
 * This can be queried like so:
 *
 * {
 *   genres {
 *     edges {
 *       node {
 *         id
 *         name
 *       }
 *     }
 *   }
 * }
 *
 * or now we can query genres as a field of the book nodes:
 *
 * {
 *   books {
 *     edges {
 *       node {
 *         id
 *         title
 *         genres {
 *           edges {
 *             node {
 *               id
 *               name
 *             }
 *           }
 *         }
 *       }
 *     }
 *   }
 * }
 *
 */
add_action( 'init', function() {
	register_taxonomy( 'genre', 'book', [
		'label' => __( 'Genre' ),
		'public' => true,
		'show_in_graphql' => true,
		'graphql_single_name' => 'genre',
		'graphql_plural_name' => 'genres',
		'hierarchical' => true,
	]);
} );
