<?php
/**
 * @author  wpWax
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Templatiq\Admin;

use Templatiq\Abstracts\EnqueuerBase;
use Templatiq\Repositories\DependencyRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Enqueuer extends EnqueuerBase {

	public function __construct() {
		$this->action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );
	}

	public function admin_enqueue_scripts( $hook ) {
		if ( ! isset( $_GET['page'] ) || 'templatiq' !== $_GET['page'] ) {
			return;
		}

		wp_enqueue_style( 'wp-components' );

		$script_asset_path = TEMPLATIQ_ASSETS_PATH . '/js/admin.asset.php';

		$script_info = file_exists( $script_asset_path ) ? include $script_asset_path : [
			'dependencies' => [],
			'version'      => TEMPLATIQ_VERSION,
		];

		$script_dep = array_merge( $script_info['dependencies'], ['updates', 'wp-hooks'] );

		$this->enqueue_script( 'templatiq-app', '/js/admin.js', $script_dep );

		$this->enqueue_style( 'templatiq-app', '/css/global.css' );

		$obj = $this->localize_object();

		wp_localize_script( 'templatiq-app', 'templatiq_obj', $obj );
	}

	public function localize_object() {
		return apply_filters(
			'templatiq_admin_localize_data',
			[
				'rest_args'    => [
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'endpoint' => get_rest_url( null, 'templatiq' ),
				],
				'assets_url'   => TEMPLATIQ_ASSETS,
				'theme_status' => ( new DependencyRepository )->get_theme_status(),
				'theme_slug'   => 'best-listing',
			]
		);
	}
}