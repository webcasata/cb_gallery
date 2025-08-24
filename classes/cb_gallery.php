<?php
/**
 * CB Gallery Plugin - Main Class
 *
 * Updated for PHP 8.2+ compatibility and WordPress coding standards
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class CB_Gallery {

    /** @var string */
    public string $version = '1.0.0';

    /** @var string */
    public $token = 'cb_gallery';

    /** @var string */
    public string $views_dir = '';

    /** @var object|null */
    public ?Taxonomy_Metadata $taxonomy_metadata = null;

    public function __construct() {
        $this->views_dir = plugin_dir_path( __FILE__ ) . 'views';

        // Initialize taxonomy metadata handler
        $this->taxonomy_metadata = new Taxonomy_Metadata();

        add_action( 'created_term', [ $this, 'hookCategoryTagsGallerySave' ], 10, 2 );
        add_action( 'edited_term', [ $this, 'hookCategoryTagsGallerySave' ], 10, 2 );
        add_action( $this->token . '_add_form_fields', [ $this, 'hookCategoryTagsGalleryAdd' ] );
    }

    /**
     * Save gallery metadata when a taxonomy term is created/edited
     */
    public function hookCategoryTagsGallerySave( $term_id, $tt_id ) : void {
        if ( empty( $term_id ) ) {
            return;
        }

        $options = $this->getOptions();

        $data = isset( $_POST[ $this->token ] ) && is_array( $_POST[ $this->token ] )
            ? array_map( 'sanitize_text_field', $_POST[ $this->token ] )
            : [];

        if ( empty( $data ) ) {
            return;
        }

        $gallery_meta       = get_term_meta( $term_id, $this->getGalleryMetaFieldKey(), true );
        $gallery_meta       = is_array( $gallery_meta ) ? $gallery_meta : [];
        $gallery_meta['a']  = isset( $data['a'] ) && is_array( $data['a'] ) ? array_map( 'sanitize_text_field', $data['a'] ) : [];

        update_term_meta( $term_id, $this->getGalleryMetaFieldKey(), $gallery_meta );
    }

    /**
     * Add gallery fields to taxonomy term add form
     */
    public function hookCategoryTagsGalleryAdd( string $taxonomy ) : void {
        wp_enqueue_script( $this->token . '-admin-gallery' );

        $options = $this->getOptions();

        foreach ( $options['applicable_taxonomies'] as $gallery_type_id => $applicable_taxonomies ) {
            if ( in_array( $taxonomy, $applicable_taxonomies, true ) ) {

                $gallery_type = get_term_by( 'id', absint( $gallery_type_id ), 'cb_gallery_types' );

                if ( $gallery_type && $gallery_type instanceof WP_Term ) {
                    $args = [
                        'id'           => 'new',
                        'token'        => $this->token,
                        'attachments'  => [],
                        'gallery_type' => $gallery_type,
                    ];

                    // Load template securely
                    $file = trailingslashit( $this->views_dir ) . 'taxonomy-options-add.php';
                    if ( file_exists( $file ) ) {
                        include $file;
                    }
                }
            }
        }
    }

    /**
     * Get plugin options
     */
    public function getOptions() : array {
        $defaults = [
            'applicable_taxonomies' => [],
        ];
        $options = get_option( $this->token . '_options', [] );
        return wp_parse_args( $options, $defaults );
    }

    /**
     * Get gallery meta field key
     */
    public function getGalleryMetaFieldKey() : string {
        return $this->token . '_gallery_meta';
    }
}
