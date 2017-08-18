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
		'supports' => [ 'title', 'editor', 'custom-fields' ],
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

/**
 * Add a "price" field to our "book" post so that it's queryable and mutable.
 *
 * We can now mutate the "price" field like so:
 * NOTE: "Ym9vazoyOTIw" = base64_encode( 'book:2920' );
 *
 * mutation {
 *   updateBook(input:{
 *     clientMutationId: "someIdFromTheClient"
 *     id: "Ym9vazoyOTIw"
 *     price: "9.99"
 *   }){
 *      id
 *      price
 *   }
 * }
 *
 * This will update the book and set it's "price" postmeta to "9.99"
 */

// add the field to the "type" definition so that it's queryable
add_action( 'graphql_book_fields', function( $fields ) {
	$fields['price'] = [
		'type' => \WPGraphQL\Types::string(),
		'description' => __( 'The price of the book', 'wp-graphql-publishers' ),
		'resolve' => function( \WP_Post $book ) {
			$price = get_post_meta( $book->ID, 'price', true );
			return ! empty( $price ) ? $price : null;
		},
	];
	return $fields;
}, 10, 1);

// add the type to the "mutation" input fields so that it's an accepted input value on the mutation
add_action( 'graphql_post_object_mutation_input_fields', function( $fields, \WP_Post_Type $post_type_object ) {
	if ( 'book' === $post_type_object->name ) {
		$fields['price'] = [
			'type'        => \WPGraphQL\Types::string(),
			'description' => __( 'The price of the book', 'wp-graphql-publishers' ),
		];
	}
	return $fields;
}, 10, 2 );

// update the postmeta value during the mutation process
add_action( 'graphql_post_object_mutation_update_additional_data', function( $post_id, $input, \WP_Post_Type $post_type_object ) {
	if ( 'book' === $post_type_object->name && ! empty( $input['price'] ) ) {
		update_post_meta( $post_id, 'price', $input['price'] );
	}
}, 10, 3 );

/**
 * Include our "syndicatedBook" type.
 * Since it extends classes registered by WPGraphQL, we want to include it after WPGraphQL has been loaded, which is
 * why we hook into the "graphql_init" action to include the file
 */
add_action( 'graphql_init', function() {
	require_once dirname( __FILE__ ) . '/inc/Type/SyndicatedBook.php';
} );

/**
 * Filter the root query to add an entry to get a list of "syndicated books"
 *
 * @todo: Right now, we're just resolving to a static array for simplicity. We'll change this to a remote request.
 */
add_action( 'graphql_root_queries', function( $fields ) {
	$fields['syndicatedBooks'] = [
		'type' => \WPGraphQL\Types::list_of( new SyndicatedBook() ),
		'description' => __( 'Get a list of syndicated books', 'wp-graphql-publishers' ),
		'resolve' => function() {

			$sources = [
				'http://wpgraphqlpub1.wpengine.com/graphql',
				'http://wpgraphqlpub2.wpengine.com/graphql',
			];

			$books = [];

			foreach ( $sources as $source ) {
				$response = wp_remote_post( $source, [
					'body' => [
						'query' => '
							query {
								books {
									id
									title
									price
									sourceName
								}
							}
						',
					],
				]);

				/**
				 * If we received books back from the request, merge them into our response
				 */
				$books = ! empty( $response['data']['books'] ) && is_array( $response['data']['books'] ) ? array_merge( $books, $response['data']['books'] ) : $books;
			}

			return $books;
		},
	];
	return $fields;
} );
