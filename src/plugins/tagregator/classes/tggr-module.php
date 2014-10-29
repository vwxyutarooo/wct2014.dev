<?php

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( 'Access denied.' );

if ( ! class_exists( 'TGGRModule' ) ) {
	/**
	 * Abstract class to define/implement base methods for all module classes
	 * @package Tagregator
	 */
	abstract class TGGRModule {
		private static $instances = array();


		/**
		 * Public getter for protected variables
		 * @mvc Model
		 *
		 * @param string $variable
		 * @return mixed
		 */
		public function __get( $variable ) {
			$module = get_called_class();

			if ( in_array( $variable, $module::$readable_properties ) ) {
				return $this->$variable;
			} else {
				throw new Exception( __METHOD__ . " error: $" . $variable . " doesn't exist or isn't readable." );
			}
		}

		/**
		 * Public setter for protected variables
		 * @mvc Model
		 *
		 * @param string $variable
		 * @param mixed  $value
		 */
		public function __set( $variable, $value ) {
			$module = get_called_class();

			if ( in_array( $variable, $module::$writeable_properties ) ) {
				$this->$variable = $value;
			} else {
				throw new Exception( __METHOD__ . " error: $" . $variable . " doesn't exist or isn't writable." );
			}
		}

		/**
		 * Constructor
		 * @mvc Controller
		 */
		abstract protected function __construct();

		/**
		 * Provides access to a single instance of a module using the singleton pattern
		 * @mvc Controller
		 * @return object
		 */
		public static function get_instance() {
			$module = get_called_class();

			if ( ! isset( self::$instances[ $module ] ) ) {
				self::$instances[ $module ] = new $module();
			}

			return self::$instances[ $module ];
		}

		/**
		 * Prepares sites to use the plugin during single or network-wide activation
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		abstract public function activate( $network_wide );

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 * @mvc Controller
		 */
		abstract public function deactivate();

		/**
		 * Register callbacks for actions and filters
		 * @mvc Controller
		 */
		abstract public function register_hook_callbacks();

		/**
		 * Initializes variables
		 * @mvc Controller
		 */
		abstract public function init();

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary
		 * @mvc Controller
		 *
		 * @param string $db_version
		 */
		abstract public function upgrade( $db_version = 0 );
	} // end TGGRModule
}