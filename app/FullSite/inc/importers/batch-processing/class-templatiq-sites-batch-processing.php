<?php
/**
 * Batch Processing
 *
 * @package Templatiq Sites
 * @since 1.0.14
 */

use Templatiq\FullSite\FullSite;

if ( ! class_exists( 'Templatiq_Sites_Batch_Processing' ) ) :

	/**
	 * Templatiq_Sites_Batch_Processing
	 *
	 * @since 1.0.14
	 */
	class Templatiq_Sites_Batch_Processing {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Process All
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access public
		 */
		public static $process_all;

		/**
		 * Last Export Checksums
		 *
		 * @since 2.0.0
		 * @var object Class object.
		 * @access public
		 */
		public $last_export_checksums;

		/**
		 * Sites Importer
		 *
		 * @since 2.0.0
		 * @var object Class object.
		 * @access public
		 */
		public static $process_site_importer;

		/**
		 * Process Single Page
		 *
		 * @since 2.0.0
		 * @var object Class object.
		 * @access public
		 */
		public static $process_single;

		/**
		 * Initiator
		 *
		 * @since 1.0.14
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.14
		 */
		public function __construct() {

			$this->includes();

			// Start image importing after site import complete.
			add_filter( 'templatiq_sites_image_importer_skip_image', array( $this, 'skip_image' ), 10, 2 );
			add_action( 'templatiq_sites_import_complete', array( $this, 'start_process' ) );
			add_action( 'templatiq_sites_process_single', array( $this, 'start_process_single' ) );
			if ( current_user_can( 'manage_options' ) ) {
				add_action( 'admin_init', array( $this, 'start_importer' ) );
			}
			add_action( 'wp_ajax_templatiq-sites-update-library', array( $this, 'update_library' ) );
			add_action( 'wp_ajax_templatiq-sites-update-library-complete', array( $this, 'update_library_complete' ) );
			add_action( 'wp_ajax_templatiq-sites-import-all-categories-and-tags', array( $this, 'import_all_categories_and_tags' ) );
			add_action( 'wp_ajax_templatiq-sites-import-all-categories', array( $this, 'import_all_categories' ) );
			add_action( 'wp_ajax_templatiq-sites-import-block-categories', array( $this, 'import_block_categories' ) );
			add_action( 'wp_ajax_templatiq-sites-import-page-builders', array( $this, 'import_page_builders' ) );
			add_action( 'wp_ajax_templatiq-sites-import-blocks', array( $this, 'import_blocks' ) );
			add_action( 'wp_ajax_templatiq-sites-get-sites-request-count', array( $this, 'sites_requests_count' ) );
			add_action( 'wp_ajax_templatiq-sites-get-blocks-request-count', array( $this, 'blocks_requests_count' ) );
			add_action( 'wp_ajax_templatiq-sites-import-sites', array( $this, 'import_sites' ) );
			add_action( 'wp_ajax_templatiq-sites-get-all-categories', array( $this, 'get_all_categories' ) );
			add_action( 'wp_ajax_templatiq-sites-get-all-categories-and-tags', array( $this, 'get_all_categories_and_tags' ) );

			add_action( 'templatiq_sites_site_import_batch_complete', array( $this, 'sync_batch_complete' ) );
		}

		/**
		 * Update the latest checksum after all the import batch processes are done.
		 */
		public function sync_batch_complete() {
			Templatiq_Sites_Importer::get_instance()->update_latest_checksums();
		}

		/**
		 * Include Files
		 *
		 * @since 2.5.0
		 */
		public function includes() {
			// Core Helpers - Image.
			// @todo 	This file is required for Elementor.
			// Once we implement our logic for updating elementor data then we'll delete this file.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Image Downloader.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/helpers/class-templatiq-sites-image-importer.php';

			// Core Helpers - Batch Processing.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-async-request.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process-templatiq.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process-templatiq-single.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process-templatiq-site-importer.php';

			// Prepare Widgets.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-widgets.php';

			// Prepare Page Builders.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-beaver-builder.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-elementor.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-gutenberg.php';
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-brizy.php';

			// Prepare Misc.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-misc.php';

			// Prepare Customizer.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-customizer.php';

			// Process Importer.
			require_once TEMPLATIQ_SITES_DIR . 'inc/importers/batch-processing/class-templatiq-sites-batch-processing-importer.php';

			self::$process_all           = new WP_Background_Process_Templatiq();
			self::$process_single        = new WP_Background_Process_Templatiq_Single();
			self::$process_site_importer = new WP_Background_Process_Templatiq_Site_Importer();
		}

		/**
		 * Import All Categories
		 *
		 * @since 2.6.22
		 * @return void
		 */
		public function import_all_categories() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to perform this action', 'templatiq-sites' );
			}
			Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_all_categories();
			wp_send_json_success();
		}

		/**
		 * Import All Categories and Tags
		 *
		 * @since 2.6.22
		 * @return void
		 */
		public function import_all_categories_and_tags() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to perform this action', 'templatiq-sites' );
			}

			Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_all_categories_and_tags();
			wp_send_json_success();
		}

		/**
		 * Import Block Categories
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function import_block_categories() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to perform this action', 'templatiq-sites' );
			}
			Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_block_categories();
			wp_send_json_success();
		}

		/**
		 * Import Page Builders
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function import_page_builders() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to perform this action', 'templatiq-sites' );
			}
			Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_page_builders();
			wp_send_json_success();
		}

		/**
		 * Import Blocks
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function import_blocks() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error();
			}
			$page_no = isset( $_POST['page_no'] ) ? absint( $_POST['page_no'] ) : '';
			if ( $page_no ) {
				$sites_and_pages = Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_blocks( $page_no );
				wp_send_json_success();
			}

			wp_send_json_error();
		}

		/**
		 * Import Sites
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function import_sites() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error();
			}
			$page_no = isset( $_POST['page_no'] ) ? absint( $_POST['page_no'] ) : '';
			if ( $page_no ) {
				$sites_and_pages = Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_sites( $page_no );

				$page_builder_keys    = wp_list_pluck( $sites_and_pages, 'astra-site-page-builder' );
				$default_page_builder = Templatiq_Sites_Page::get_instance()->get_setting( 'page_builder' );

				$current_page_builder_sites = array();
				foreach ( $page_builder_keys as $site_id => $page_builder ) {
					if ( $default_page_builder === $page_builder ) {
						$current_page_builder_sites[ $site_id ] = $sites_and_pages[ $site_id ];
					}
				}

				wp_send_json_success( $current_page_builder_sites );
			}

			wp_send_json_error();
		}

		/**
		 * Sites Requests Count
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function sites_requests_count() {

			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to perform this action', 'templatiq-sites' );
			}
			// Get count.
			$total_requests = $this->get_total_requests();
			if ( $total_requests ) {
				wp_send_json_success( $total_requests );
			}

			wp_send_json_error();
		}

		/**
		 * Blocks Requests Count
		 *
		 * @since 2.1.0
		 * @return void
		 */
		public function blocks_requests_count() {

			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to perform this action', 'templatiq-sites' );
			}
			// Get count.
			$total_requests = $this->get_total_blocks_requests();
			if ( $total_requests ) {
				wp_send_json_success( $total_requests );
			}

			wp_send_json_error();
		}

		/**
		 * Update Library Complete
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function update_library_complete() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}
			Templatiq_Sites_Importer::get_instance()->update_latest_checksums();
			do_action( 'starter_templates_save_sites_count_as_per_page_builder' );

			update_site_option( 'templatiq-sites-batch-is-complete', 'no' );
			update_site_option( 'templatiq-sites-manual-sync-complete', 'yes' );
			wp_send_json_success();
		}

		/**
		 * Get Last Exported Checksum Status
		 *
		 * @since 2.0.0
		 * @return string Checksums Status.
		 */
		public function get_last_export_checksums() {

			$old_last_export_checksums = get_site_option( 'templatiq-sites-last-export-checksums', '' );

			$new_last_export_checksums = $this->set_last_export_checksums();

			$checksums_status = 'no';

			if ( empty( $old_last_export_checksums ) ) {
				$checksums_status = 'yes';
			}

			if ( $new_last_export_checksums !== $old_last_export_checksums ) {
				$checksums_status = 'yes';
			}

			return apply_filters( 'templatiq_sites_checksums_status', $checksums_status );
		}

		/**
		 * Set Last Exported Checksum
		 *
		 * @since 2.0.0
		 * @return string Checksums Status.
		 */
		public function set_last_export_checksums() {

			if ( ! empty( $this->last_export_checksums ) ) {
				return $this->last_export_checksums;
			}

			$api_args = array(
				'timeout' => 60,
			);

			$response = wp_remote_get( trailingslashit( FullSite::init()->get_api_domain() ) . 'wp-json/templatiq-sites/v1/get-last-export-checksums', $api_args );
			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$result = json_decode( wp_remote_retrieve_body( $response ), true );

				// Set last export checksums.
				if ( ! empty( $result['last_export_checksums'] ) ) {
					update_site_option( 'templatiq-sites-last-export-checksums-latest', $result['last_export_checksums'] );

					$this->last_export_checksums = $result['last_export_checksums'];
				}
			}

			return $this->last_export_checksums;
		}

		/**
		 * Update Library
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function update_library() {
			check_ajax_referer( 'templatiq-sites', '_ajax_nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}

			if ( 'no' === $this->get_last_export_checksums() ) {
				wp_send_json_success( 'updated' );
			}
			wp_send_json_success();
		}

		/**
		 * Start Importer
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function start_importer() {

			$process_sync = apply_filters( 'templatiq_sites_initial_sync', true );

			if ( ! $process_sync ) {
				return;
			}

			$is_fresh_site = get_site_option( 'templatiq-sites-fresh-site', '' );

			if ( empty( $is_fresh_site ) && '' === $is_fresh_site ) {
				$dir = TEMPLATIQ_SITES_DIR . 'inc/json';

				// First time user save the data of sites, pages, categories etc from the JSON file.
				$list_files = $this->get_default_assets();
				foreach ( $list_files as $key => $file_name ) {
					if ( file_exists( $dir . '/' . $file_name . '.json' ) ) {
						$data = FullSite::init()->get_filesystem()->get_contents( $dir . '/' . $file_name . '.json' );
						if ( ! empty( $data ) ) {
							update_site_option( $file_name, json_decode( $data, true ) );
						}
					}
				}

				// Also, Trigger the batch to get latest data.
				// If batch failed then user have at least the data from the JSON file.
				update_site_option( 'templatiq-sites-fresh-site', 'no' );
			}

			$this->process_import();
		}

		/**
		 * Json Files Names.
		 *
		 * @since 2.6.1
		 * @return array
		 */
		public function get_default_assets() {

			return array(
				'astra-blocks-1',
				'templatiq-sites-site-category',
				'templatiq-sites-all-site-categories',
				'astra-blocks-4',
				'templatiq-sites-page-builders',
				'astra-blocks-3',
				'astra-blocks-2',
				'astra-blocks-categories',
				'templatiq-sites-requests',
				'templatiq-sites-and-pages-page-1',
				'templatiq-sites-and-pages-page-2',
				'templatiq-sites-and-pages-page-3',
				'templatiq-sites-and-pages-page-4',
				'templatiq-sites-and-pages-page-5',
				'templatiq-sites-and-pages-page-6',
				'templatiq-sites-and-pages-page-7',
				'templatiq-sites-and-pages-page-8',
				'templatiq-sites-and-pages-page-9',
				'templatiq-sites-and-pages-page-10',
				'templatiq-sites-and-pages-page-11',
				'templatiq-sites-and-pages-page-12',
				'templatiq-sites-and-pages-page-13',
				'templatiq-sites-and-pages-page-14',
				'templatiq-sites-and-pages-page-15',
				'templatiq-sites-and-pages-page-16',
				'templatiq-sites-and-pages-page-17',
				'templatiq-sites-and-pages-page-18',
				'templatiq-sites-and-pages-page-19',
				'templatiq-sites-and-pages-page-20',
				'templatiq-sites-and-pages-page-21',
				'templatiq-sites-and-pages-page-22',
				'templatiq-sites-and-pages-page-23',
				'templatiq-sites-and-pages-page-24',
			);
		}

		/**
		 * Process Batch
		 *
		 * @since 2.0.0
		 * @return mixed
		 */
		public function process_batch() {

			$process_sync = apply_filters( 'templatiq_sites_process_sync_batch', true );

			if ( ! $process_sync ) {
				return;
			}

			if ( 'no' === $this->get_last_export_checksums() ) {
				$this->log( 'Library is up to date!' );
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Library is up to date!' );
				}
				return;
			}

			$status = Templatiq_Sites_Page::get_instance()->test_cron();
			if ( is_wp_error( $status ) ) {
				templatiq_sites_error_log( 'Error! Batch Not Start due to disabled cron events!' );
				update_site_option( 'templatiq-sites-batch-status-string', 'Error! Batch Not Start due to disabled cron events!' );

				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Error! Batch Not Start due to disabled cron events!' );
				} else {
					// For non- WP CLI request return to prevent the request.
					return;
				}
			}

			$this->log( 'Sync Library Started!' );

			// Added the categories and tags.
			$this->log( 'Added All Categories and tags in queue.' );

			if ( defined( 'WP_CLI' ) ) {
				Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_all_categories_and_tags();
			} else {
				self::$process_site_importer->push_to_queue(
					array(
						'instance' => Templatiq_Sites_Batch_Processing_Importer::get_instance(),
						'method'   => 'import_all_categories_and_tags',
					)
				);
			}

			// Added the categories.
			$this->log( 'Added All Site Categories in queue.' );

			if ( defined( 'WP_CLI' ) ) {
				Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_all_categories();
			} else {
				self::$process_site_importer->push_to_queue(
					array(
						'instance' => Templatiq_Sites_Batch_Processing_Importer::get_instance(),
						'method'   => 'import_all_categories',
					)
				);
			}

			// Added the page_builders.
			$this->log( 'Added page builders in queue.' );

			if ( defined( 'WP_CLI' ) ) {
				Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_page_builders();
			} else {
				self::$process_site_importer->push_to_queue(
					array(
						'instance' => Templatiq_Sites_Batch_Processing_Importer::get_instance(),
						'method'   => 'import_page_builders',
					)
				);
			}

			// Get count.
			$total_requests = $this->get_total_blocks_requests();
			if ( $total_requests ) {
				$this->log( 'BLOCK: Total Blocks Requests ' . $total_requests );

				for ( $page = 1; $page <= $total_requests; $page++ ) {

					$this->log( 'BLOCK: Added page ' . $page . ' in queue.' );

					if ( defined( 'WP_CLI' ) ) {
						Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_blocks( $page );
					} else {
						self::$process_site_importer->push_to_queue(
							array(
								'page'     => $page,
								'instance' => Templatiq_Sites_Batch_Processing_Importer::get_instance(),
								'method'   => 'import_blocks',
							)
						);
					}
				}
			}

			// Added the categories.
			$this->log( 'Added Block Categories in queue.' );

			if ( defined( 'WP_CLI' ) ) {
				Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_block_categories();
			} else {
				self::$process_site_importer->push_to_queue(
					array(
						'instance' => Templatiq_Sites_Batch_Processing_Importer::get_instance(),
						'method'   => 'import_block_categories',
					)
				);
			}

			// Get count.
			$total_requests = $this->get_total_requests();
			if ( $total_requests ) {
				$this->log( 'Total Requests ' . $total_requests );

				for ( $page = 1; $page <= $total_requests; $page++ ) {

					$this->log( 'Added page ' . $page . ' in queue.' );

					if ( defined( 'WP_CLI' ) ) {
						Templatiq_Sites_Batch_Processing_Importer::get_instance()->import_sites( $page );
					} else {
						self::$process_site_importer->push_to_queue(
							array(
								'page'     => $page,
								'instance' => Templatiq_Sites_Batch_Processing_Importer::get_instance(),
								'method'   => 'import_sites',
							)
						);
					}
				}
			}

			if ( defined( 'WP_CLI' ) ) {
				$this->log( 'Sync Process Complete.' );
			} else {
				// Dispatch Queue.
				$this->log( 'Dispatch the Queue!' );

				self::$process_site_importer->save()->dispatch();
			}

		}

		/**
		 * Log
		 *
		 * @since 2.0.0
		 *
		 * @param  string $message Log message.
		 * @return void.
		 */
		public function log( $message = '' ) {
			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( $message );
			} else {
				templatiq_sites_error_log( $message );
				update_site_option( 'templatiq-sites-batch-status-string', $message );
			}
		}

		/**
		 * Process Import
		 *
		 * @since 2.0.0
		 *
		 * @return mixed Null if process is already started.
		 */
		public function process_import() {

			$process_sync = apply_filters( 'templatiq_sites_process_auto_sync_library', true );

			if ( ! $process_sync ) {
				return;
			}

			// Batch is already started? Then return.
			$status = get_site_option( 'templatiq-sites-batch-status' );
			if ( 'in-process' === $status ) {
				return;
			}

			// Check batch expiry.
			$expired = get_site_transient( 'templatiq-sites-import-check' );
			if ( false !== $expired ) {
				return;
			}

			// For 1 week.
			set_site_transient( 'templatiq-sites-import-check', 'true', apply_filters( 'templatiq_sites_sync_check_time', WEEK_IN_SECONDS ) );

			update_site_option( 'templatiq-sites-batch-status', 'in-process' );

			// Process batch.
			$this->process_batch();
		}

		/**
		 * Get Total Requests
		 *
		 * @return integer
		 */
		public function get_total_requests() {

			templatiq_sites_error_log( 'Getting Total Pages' );
			update_site_option( 'templatiq-sites-batch-status-string', 'Getting Total Pages' );

			$api_args = array(
				'timeout' => 60,
			);

			$response = wp_remote_get( trailingslashit( FullSite::init()->get_api_domain() ) . 'wp-json/templatiq-sites/v1/get-total-pages/?per_page=15', $api_args );
			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$total_requests = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( isset( $total_requests['pages'] ) ) {

					$this->log( 'Updated requests ' . $total_requests['pages'] );
					update_site_option( 'templatiq-sites-requests', $total_requests['pages'] );

					do_action( 'templatiq_sites_sync_get_total_pages', $total_requests['pages'] );

					return $total_requests['pages'];
				}
			}

			templatiq_sites_error_log( 'Request Failed! Still Calling..' );
			update_site_option( 'templatiq-sites-batch-status-string', 'Request Failed! Still Calling..' );

		}

		/**
		 * Get Blocks Total Requests
		 *
		 * @return integer
		 */
		public function get_total_blocks_requests() {

			templatiq_sites_error_log( 'BLOCK: Getting Total Blocks' );
			update_site_option( 'templatiq-sites-batch-status-string', 'Getting Total Blocks' );

			$api_args = array(
				'timeout' => 60,
			);

			$query_args = array(
				'page_builder' => 'elementor',
				'wireframe'    => 'yes',
			);

			$api_url = add_query_arg( $query_args, trailingslashit( FullSite::init()->get_api_domain() ) . 'wp-json/astra-blocks/v1/get-blocks-count/' );

			$response = wp_remote_get( $api_url, $api_args );
			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$total_requests = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( isset( $total_requests['pages'] ) ) {
					templatiq_sites_error_log( 'BLOCK: Updated requests ' . $total_requests['pages'] );
					update_site_option( 'astra-blocks-batch-status-string', 'Updated requests ' . $total_requests['pages'] );

					update_site_option( 'astra-blocks-requests', $total_requests['pages'] );

					do_action( 'templatiq_sites_sync_blocks_requests', $total_requests['pages'] );

					return $total_requests['pages'];
				}
			}

			templatiq_sites_error_log( 'BLOCK: Request Failed! Still Calling..' );
			update_site_option( 'astra-blocks-batch-status-string', 'Request Failed! Still Calling..' );

		}

		/**
		 * Start Single Page Import
		 *
		 * @param  int $page_id Page ID .
		 * @since 2.0.0
		 * @return void
		 */
		public function start_process_single( $page_id ) {

			templatiq_sites_error_log( '=================== ' . Templatiq_Sites_White_Label::get_instance()->get_white_label_name( TEMPLATIQ_SITES_NAME ) . ' - Single Page - Importing Images for Blog name \'' . get_the_title( $page_id ) . '\' (' . $page_id . ') ===================' );

			$default_page_builder = Templatiq_Sites_Page::get_instance()->get_setting( 'page_builder' );

			if ( 'gutenberg' === $default_page_builder ) {
				// Add "gutenberg" in import [queue].
				self::$process_single->push_to_queue(
					array(
						'page_id'  => $page_id,
						'instance' => Templatiq_Sites_Batch_Processing_Gutenberg::get_instance(),
					)
				);
			}

			// Add "brizy" in import [queue].
			if ( 'brizy' === $default_page_builder && is_plugin_active( 'brizy/brizy.php' ) ) {
				// Add "gutenberg" in import [queue].
				self::$process_single->push_to_queue(
					array(
						'page_id'  => $page_id,
						'instance' => Templatiq_Sites_Batch_Processing_Brizy::get_instance(),
					)
				);
			}

			// Add "bb-plugin" in import [queue].
			if (
				'beaver-builder' === $default_page_builder &&
				( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) || is_plugin_active( 'bb-plugin/fl-builder.php' ) )
			) {
				// Add "gutenberg" in import [queue].
				self::$process_single->push_to_queue(
					array(
						'page_id'  => $page_id,
						'instance' => Templatiq_Sites_Batch_Processing_Beaver_Builder::get_instance(),
					)
				);
			}

			// Add "elementor" in import [queue].
			if ( 'elementor' === $default_page_builder ) {
				// @todo Remove required `allow_url_fopen` support.
				if ( ini_get( 'allow_url_fopen' ) ) {
					if ( is_plugin_active( 'elementor/elementor.php' ) ) {

						// !important, Clear the cache after images import.
						\Elementor\Plugin::$instance->posts_css_manager->clear_cache();

						$import = new \Elementor\TemplateLibrary\Templatiq_Sites_Batch_Processing_Elementor();
						self::$process_single->push_to_queue(
							array(
								'page_id'  => $page_id,
								'instance' => $import,
							)
						);
					}
				} else {
					templatiq_sites_error_log( 'Couldn\'t not import image due to allow_url_fopen() is disabled!' );
				}
			}

			// Dispatch Queue.
			self::$process_single->save()->dispatch();
		}

		/**
		 * Skip Image from Batch Processing.
		 *
		 * @since 1.0.14
		 *
		 * @param  boolean $can_process Batch process image status.
		 * @param  array   $attachment  Batch process image input.
		 * @return boolean
		 */
		public function skip_image( $can_process, $attachment ) {

			if ( isset( $attachment['url'] ) && ! empty( $attachment['url'] ) ) {

				// If image URL contain current site URL? then return true to skip that image from import.
				if ( strpos( $attachment['url'], site_url() ) !== false ) {
					return true;
				}

				if (
					strpos( $attachment['url'], 'brainstormforce.com' ) !== false ||
					strpos( $attachment['url'], 'wpastra.com' ) !== false ||
					strpos( $attachment['url'], 'sharkz.in' ) !== false ||
					strpos( $attachment['url'], 'templatiq.com' ) !== false
				) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Start Image Import
		 *
		 * @since 1.0.14
		 *
		 * @return void
		 */
		public function start_process() {
			set_transient( 'templatiq_sites_batch_process_started', 'yes', HOUR_IN_SECONDS );

			/** WordPress Plugin Administration API */
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . 'wp-admin/includes/update.php';

			$this->includes();

			$wxr_id = get_site_option( 'templatiq_sites_imported_wxr_id', 0 );
			if ( $wxr_id ) {
				wp_delete_attachment( $wxr_id, true );
				Templatiq_Sites_Importer_Log::add( 'Deleted Temporary WXR file ' . $wxr_id );
				delete_option( 'templatiq_sites_imported_wxr_id' );
				Templatiq_Sites_Importer_Log::add( 'Option `templatiq_sites_imported_wxr_id` Deleted.' );
			}

			$classes = array();

			Templatiq_Sites_Importer_Log::add( 'Batch Process Started..' );
			Templatiq_Sites_Importer_Log::add( Templatiq_Sites_White_Label::get_instance()->get_white_label_name( TEMPLATIQ_SITES_NAME ) . ' - Importing Images for Blog name \'' . get_bloginfo( 'name' ) . '\' (' . get_current_blog_id() . ')' );

			// Add "widget" in import [queue].
			$classes[] = Templatiq_Sites_Batch_Processing_Widgets::get_instance();

			// Add "gutenberg" in import [queue].
			$classes[] = Templatiq_Sites_Batch_Processing_Gutenberg::get_instance();

			// Add "brizy" in import [queue].
			if ( is_plugin_active( 'brizy/brizy.php' ) ) {
				$classes[] = Templatiq_Sites_Batch_Processing_Brizy::get_instance();
			}

			// Add "bb-plugin" in import [queue].
			// Add "beaver-builder-lite-version" in import [queue].
			if ( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) || is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
				$classes[] = Templatiq_Sites_Batch_Processing_Beaver_Builder::get_instance();
			}

			// Add "elementor" in import [queue].
			// @todo Remove required `allow_url_fopen` support.
			if ( ini_get( 'allow_url_fopen' ) && is_plugin_active( 'elementor/elementor.php' ) ) {
				$import    = new \Elementor\TemplateLibrary\Templatiq_Sites_Batch_Processing_Elementor();
				$classes[] = $import;
			}

			// Add "astra-addon" in import [queue].
			if ( is_plugin_active( 'astra-addon/astra-addon.php' ) ) {
				$classes[] = Templatiq_Sites_Compatibility_Templatiq_Pro::get_instance();
			}

			// Add "misc" in import [queue].
			$classes[] = Templatiq_Sites_Batch_Processing_Misc::get_instance();

			// Add "customizer" in import [queue].
			$classes[] = Templatiq_Sites_Batch_Processing_Customizer::get_instance();

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'Batch Process Started..' );
				// Process all classes.
				foreach ( $classes as $key => $class ) {
					if ( method_exists( $class, 'import' ) ) {
						$class->import();
					}
				}
				WP_CLI::line( 'Batch Process Complete!' );
			} else {
				// Add all classes to batch queue.
				foreach ( $classes as $key => $class ) {
					self::$process_all->push_to_queue( $class );
				}

				// Dispatch Queue.
				self::$process_all->save()->dispatch();
			}

		}

		/**
		 * Get all post id's
		 *
		 * @since 1.0.14
		 *
		 * @param  array $post_types Post types.
		 * @return array
		 */
		public static function get_pages( $post_types = array() ) {

			if ( $post_types ) {
				$args = array(
					'post_type'      => $post_types,

					// Query performance optimization.
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				);

				$query = new WP_Query( $args );

				// Have posts?
				if ( $query->have_posts() ) :

					return $query->posts;

				endif;
			}

			return null;
		}

		/**
		 * Get Supporting Post Types..
		 *
		 * @since 1.3.7
		 * @param  integer $feature Feature.
		 * @return array
		 */
		public static function get_post_types_supporting( $feature ) {
			global $_wp_post_type_features;

			$post_types = array_keys(
				wp_filter_object_list( $_wp_post_type_features, array( $feature => true ) )
			);

			return $post_types;
		}

		/**
		 * Get all categories.
		 *
		 * @return void
		 */
		public function get_all_categories() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'templatiq-sites' ) );
				}

				$all_categories = get_site_option( 'templatiq-sites-all-site-categories', array() );
				wp_send_json_success( $all_categories );
			}

			wp_send_json_error( __( 'You are not allowed to perform this action.', 'templatiq-sites' ) );
		}

		/**
		 * Get all categories and tags.
		 *
		 * @return void
		 */
		public function get_all_categories_and_tags() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'templatiq-sites' ) );
				}

				$all_categories_and_tags = get_site_option( 'templatiq-sites-all-site-categories-and-tags', array() );
				wp_send_json_success( $all_categories_and_tags );
			}

			wp_send_json_error( __( 'You are not allowed to perform this action.', 'templatiq-sites' ) );
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Templatiq_Sites_Batch_Processing::get_instance();

endif;
