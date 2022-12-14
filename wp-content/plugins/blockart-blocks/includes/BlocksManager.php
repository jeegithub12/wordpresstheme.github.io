<?php
/**
 * BlockArt Blocks Manager.
 *
 * @since 1.0.0
 * @package BlockArt
 */

namespace BlockArt;

defined( 'ABSPATH' ) || exit;

/**
 * BlockArt Blocks Manager
 *
 * Registers all the blocks & block categories and manages them.
 *
 * @since 1.0.0
 */
class BlocksManager {

	/**
	 * Init.
	 */
	public static function init() {
		self::init_hooks();
	}

	/**
	 * BlockArt/Blocks_Manager Constructor.
	 *
	 * @since 1.0.0
	 */
	private static function init_hooks() {

		// `block_categories` filter depreciated after WP5.8.
		if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', array( __CLASS__, 'block_categories' ), PHP_INT_MAX, 2 );
		} else {
			add_filter( 'block_categories', array( __CLASS__, 'block_categories' ), PHP_INT_MAX, 2 );
		}

		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
	}

	/**
	 * Add "BlockArt" category to the blocks listing in post edit screen.
	 *
	 * @since 1.0.0
	 * @param array $block_categories All registered block categories.
	 * @return array
	 */
	public static function block_categories( $block_categories ) {

		return array_merge(
			array(
				array(
					'slug'  => 'blockart',
					'title' => esc_html__( 'BlockArt', 'blockart' ),
				),
			),
			$block_categories
		);
	}

	/**
	 * Register all the blocks.
	 *
	 * @since 1.0.0
	 */
	public static function register_blocks() {
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/button' );
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/heading' );
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/image' );
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/section' );
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/section/column' );
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/spacing' );
		register_block_type_from_metadata( dirname( BLOCKART_PLUGIN_FILE ) . '/src/blocks/blocks/paragraph' );
	}
}
