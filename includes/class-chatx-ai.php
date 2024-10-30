<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 * @author     Chatx.ai <contact@chatx.ai>
 */

class Chatx_Ai {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Chatx_Ai_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The current plugin data
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $plugin_data    The current version of the plugin.
     */
    protected $plugin_data;

    /**
     * Does this user registered its plugin on ChatX.ai?
     *
     * @since    1.0.0
     * @access   private
     */
    private $is_registered = false;

    /**
     * The API Key from ChatX.ai
     *
     * @since    1.0.0
     * @access   private
     */
    private $api_key;

    /**
     * The API Token from ChatX.ai
     *
     * @since    1.0.0
     * @access   private
     */
    private $api_token;

    private $plugin_public;


    public function __construct($plugin_data = null) {

        $this->plugin_data = $plugin_data;

		$this->start_session();

		spl_autoload_register( [ $this, 'autoload' ] );

		$this->load_dependencies();
		$this->load_rest_api();

		$this->api_key = Chatx_Ai_Options::read('chatxai_api_key');
		$this->api_token = Chatx_Ai_Options::read('chatxai_api_token');

		$this->is_registered = !!$this->api_key && !!$this->api_token;

		$this->set_locale();
		$this->define_admin_hooks();

		$this->plugin_public = new Chatx_Ai_Public( $this->get_plugin_name(), $this->get_version() );

		$this->define_public_hooks();
	}


	private function start_session(){
		if( !session_id() )
		{
			session_start();
		}
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Chatx_Ai_Loader. Orchestrates the hooks of the plugin.
	 * - Chatx_Ai_i18n. Defines internationalization functionality.
	 * - Chatx_Ai_Admin. Defines all hooks for the admin area.
	 * - Chatx_Ai_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chatx-ai-loader.php';

		/**
		 * The class responsible for cleaning & sanitizing fields
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/clean.php';

		/**
		 * The class responsible for something. I don't know what yet.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/format.php';

		/**
		 * The class responsible for Options CRUD
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chatx-ai-options.php';

		/**
		 * The class responsible for REST API callbacks
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chatx-ai-rest-api.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chatx-ai-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-chatx-ai-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-chatx-ai-public.php';

		/**
		 * The class responsible for string clean operations
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/clean.php';

		/**
		 * The class responsible for notices operations
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/notices.php';

		$this->loader = new Chatx_Ai_Loader();
	}

	/**
	 * Define the REST API callbacks
	 *
	 * Uses the Chatx_Ai_Rest_API class in order to comunicate with another services
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_rest_api() {

		$rest_api = new Chatx_Ai_Rest_API($this->plugin_data);

		$this->loader->add_action( 'plugins_loaded', $rest_api, 'init_API' );

	}


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Chatx_Ai_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Chatx_Ai_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Chatx_Ai_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'replace_top_level_menu' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'check_admin_notices' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'setup_intercom' );
		$this->loader->add_filter( 'auto_update_plugin', $plugin_admin, 'auto_update_plugin', 10, 2 );

		if ( cx_request('action') === 'chatxai-settings' ) {
			$this->loader->add_action( 'wp_loaded', 'CX_Admin_Controller_Settings', 'save' );
		}
	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );

		// Chatx plugin actions
        $this->loader->add_action( 'upgrader_process_complete', $this->plugin_public, 'action_upgrade_completed', 10, 2 );
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}


	 /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return 'chatx-ai';
    }


    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Chatx_Ai_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }


    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return isset($this->plugin_data['Version']) ? $this->plugin_data['Version'] : '0.0.0';
    }


	/**
	 * @param string $end
	 * @return string
	 */
	function path( $end = '' ) {
		return untrailingslashit( dirname( CHATX_FILE ) ) . $end;
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function admin_path( $end = '' ) {
		return $this->path( '/admin' . $end );
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function lib_path( $end = '' ) {
		return $this->path( '/includes/lib' . $end );
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function url( $end = '' ) {
		return untrailingslashit( plugin_dir_url( $this->plugin_basename ) ) . $end;
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function admin_assets_url( $end = '' ) {
		return CX()->url( '/admin/assets' . $end );
	}


	/**
	 * @param $class
	 */
	function autoload( $class ) {
		$path = $this->get_autoload_path( $class );

		if ( $path && file_exists( $path ) )
			include $path;
	}

	/**
	 * @param $array
	 */
	public function custom_cron_schedules($schedules){
	    $schedules["chatxai_send_worker_interval"] = [
	        'interval' => 15 * 60,
	        'display' => __('Once every 15 minutes')
	    ];
	    return $schedules;
	}

	/**
	 * @param $class
	 * @return string
	 */
	function get_autoload_path( $class ) {

		if ( substr( $class, 0, 3 ) != 'CX_' ) {
			return false;
		}

		$file = str_replace( 'CX_', '/', $class );
		$file = str_replace( '_', '-', $file );
		$file = strtolower( $file );

		if ( strstr( $file, '/admin-' ) ) {
			$file = str_replace( '/admin-', '/admin/', $file );
			$file = str_replace( '/controller-', '/controllers/', $file );

			return $this->path() . $file . '.php';
		}

	}

	function is_registered() {
		return $this->is_registered;
	}

    function get_api_key() {
        return $this->api_key;
    }

    function get_api_token() {
        return $this->api_token;
    }
}
