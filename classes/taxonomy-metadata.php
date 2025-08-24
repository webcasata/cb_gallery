<?php
/**
 * Taxonomy Metadata Manager
 *
 * Handles saving and retrieving custom metadata for taxonomies.
 *
 * @package CB_Gallery
 */

if ( ! class_exists( 'Taxonomy_Metadata' ) ) {

	class Taxonomy_Metadata {

		/**
		 * Plugin token.
		 *
		 * @var string
		 */
		protected string $token;

		/**
		 * Constructor.
		 *
		 * @param string $token Plugin token/slug.
		 */
		public function __construct( string $token ) {
			$this->token = $token;

			// Register hooks for saving and editing term meta.
			add_action( 'created_term', [ $this, 'save_term_meta' ], 10, 3 );
			add_action( 'edited_term', [ $this, 'save_term_meta' ], 10, 3 );
		}

		/**
		 * Save custom metadata when a taxonomy term is created/edited.
		 *
		 * @param int    $term_id Term ID.
		 * @param int    $tt_id   Term taxonomy ID.
		 * @param string $taxonomy Taxonomy name.
		 *
		 * @return void
		 */
		public function save_term_meta( int $term_id, int $tt_id, string $taxonomy ): void {
			if ( empty( $_POST[ $this->token ] ) || ! is_array( $_POST[ $this->token ] ) ) {
				return;
			}

			$meta_data = wp_unslash( $_POST[ $this->token ] );

			foreach ( $meta_data as $key => $value ) {
				$meta_key = sanitize_key( $key );

				if ( is_array( $value ) ) {
					$value = array_map( 'sanitize_text_field', $value );
				} else {
					$value = sanitize_text_field( $value );
				}

				update_term_meta( $term_id, $meta_key, $value );
			}
		}

		/**
		 * Get a term’s metadata.
		 *
		 * @param int    $term_id Term ID.
		 * @param string $key Meta key.
		 * @param bool   $single Whether to return a single value.
		 *
		 * @return mixed Metadata value or array of values.
		 */
		public function get_meta( int $term_id, string $key, bool $single = true ) {
			$key = sanitize_key( $key );
			return get_term_meta( $term_id, $key, $single );
		}
	}
}
