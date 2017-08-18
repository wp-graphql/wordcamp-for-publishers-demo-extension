<?php
/**
 * Registers a new Type "syndicatedBook" that will be used to represent objects that have been syndicated to
 * other systems.
 */
class SyndicatedBook extends \WPGraphQL\Type\WPObjectType {

	protected static $fields;

	public function __construct() {
		$config = [
			'name' => 'syndicatedBook',
			'fields' => self::fields(),
		];
		parent::__construct( $config );
	}

	public function fields() {
		if ( null === self::$fields ) {
			self::$fields = [
				'id' => [
					'type' => \WPGraphQL\Types::id(),
					'description' => __( 'The syndicated Book ID', 'wp-graphql-publishers' ),
				],
				'title' => [
					'type' => \WPGraphQL\Types::string(),
					'description' => __( 'The syndicated Book Title', 'wp-graphql-publishers' ),
				],
				'price' => [
					'type' => \WPGraphQL\Types::string(),
					'description' => __( 'The syndicated Book Price', 'wp-graphql-publishers' ),
				],
				'sourceName' => [
					'type' => \WPGraphQL\Types::string(),
					'description' => __( 'The name of the source where the Book exists', 'wp-graphql-publishers' ),
				],
			];
		}
		return self::$fields;
	}

}