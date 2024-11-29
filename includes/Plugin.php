<?php

namespace S3S\WP\LatestCommentsExtended;

class Plugin {

	/**
	 * The post type to query comments for.
	 *
	 * If empty, the block will query comments for all post types.
	 *
	 * @var string
	 */
	public $post_type = '';

	/**
	 * Plugin singleton instance.
	 *
	 * @var Plugin $instance Plugin Singleton instance
	 */
	public static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks.
	 */
	public function setup() {
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_assets' ] );
		add_filter( 'block_type_metadata', [ $this, 'block_metadata' ] );
		add_filter( 'register_block_type_args', [ $this, 'block_type_args' ], 10, 2 );
	}

	/**
	 * Register assets for the block.
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		$scripts = [
			'editor-script' => 'index',
		];

		foreach ( $scripts as $asset_handle => $filename ) {
			$asset_file = sprintf(
				'%s/build/%s.asset.php',
				untrailingslashit( S3S_LATEST_COMMENTS_EXTENDED_PATH ),
				$filename
			);

			$asset        = file_exists( $asset_file ) ? require $asset_file : null;
			$dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : [];
			$version      = isset( $asset['version'] ) ? $asset['version'] : filemtime( $asset_file );

			wp_register_script(
				"latest-comments-$asset_handle",
				sprintf(
					'%s/build/%s.js',
					untrailingslashit( S3S_LATEST_COMMENTS_EXTENDED_URL ),
					$filename
				),
				$dependencies,
				$version,
				true
			);
		}
	}

	/**
	 * Update the metadata for the Latest Comments block.
	 *
	 * @param  array $metadata Metadata for registering a block type.
	 * @return array
	 */
	public function block_metadata( $metadata ) {

		if ( empty( $metadata['name'] ) ) {
			return $metadata;
		}

		if ( 'core/latest-comments' !== $metadata['name'] ) {
			return $metadata;
		}

		// Extend the block with new attributes.
		if ( ! isset( $metadata['attributes']['postType'] ) ) {
			$metadata['attributes']['postType'] = [
				'type'    => 'string',
				'default' => '',
			];
		}

		// Extend the block with new scripts.
		$field_mappings = [
			'editorScript' => 'editor-script',
		];

		foreach ( $field_mappings as $field_name => $asset_handle ) {
			if ( ! isset( $metadata[ $field_name ] ) ) {
				$metadata[ $field_name ] = [];
			}

			if ( ! is_array( $metadata[ $field_name ] ) ) {
				$metadata[ $field_name ] = [ $metadata[ $field_name ] ];
			}

			$metadata[ $field_name ][] = "latest-comments-$asset_handle";
		}

		return $metadata;
	}

	/**
	 * Override the block registration args to add the custom render callback.
	 *
	 * @param  array  $args Block arguments.
	 * @param  string $block_type Block type name.
	 * @return array
	 */
	public function block_type_args( $args, $block_type ) {

		if ( 'core/latest-comments' !== $block_type ) {
			return $args;
		}

		$args['render_callback'] = [ $this, 'render_callback' ];

		return $args;
	}

	/**
	 * Custom render callback for the Latest Comments block.
	 *
	 * @param  array $attributes Block attributes.
	 * @return string
	 */
	public function render_callback( $attributes = [] ) {

		$this->post_type = $attributes['postType'] ?? '';

		add_filter( 'widget_comments_args', [ $this, 'filter_comments_args' ] );

		$markup = render_block_core_latest_comments( $attributes );

		remove_filter( 'widget_comments_args', [ $this, 'filter_comments_args' ] );

		return $markup;
	}

	/**
	 * Modify the arguments for the comments query based on the block's custom attributes.
	 *
	 * @param  array $attributes The original arguments for the comments query.
	 * @return array
	 */
	public function filter_comments_args( $attributes ) {

		if ( ! empty( $this->post_type ) ) {
			$attributes['post_type'] = $this->post_type;
		}

		return $attributes;
	}
}
