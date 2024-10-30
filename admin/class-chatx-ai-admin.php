<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://chatx.ai
 * @since      1.0.0
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/admin
 * @author     Chatx.ai <contact@chatx.ai>
 */
use CXLibrary\Notices;

class Chatx_Ai_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'wp_ajax_dismiss_cx_notice', [$this, 'dismiss_chatx_notice'] );

	}

	function dismiss_chatx_notice() {
		$id = isset($_POST['id']) ? $_POST['id'] : null;
		Notices::dismiss($id);
    	wp_die();
	}

	public function notify_chatx_about_settings(){
	    Chatx_Ai_Options::trigger_changes();
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chatx_Ai_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chatx_Ai_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/chatx-ai-admin.css', array(), $this->version, 'all' );

	}


	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chatx_Ai_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chatx_Ai_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/chatx-ai-admin.js', array( 'jquery' ), $this->version, false );
	}


	/**
	 * @param $view
	 * @param array $args
	 * @param mixed $path
	 */
	static function get_view( $view, $args = [], $path = false ) {

		if ( $args && is_array( $args ) )
			extract( $args );

		if ( ! $path )
			$path = CX()->admin_path( '/views/' );

		include( $path . $view . '.php' );
	}


	static function admin_menu() {

		$sub_menu = [];
		$position = '55.6324'; // fix for rare position clash bug

		add_menu_page( __( 'ChatX.ai', 'chatx-ai' ), __( 'ChatX.ai', 'chatx-ai' ), 'manage_woocommerce', 'chatxai', false, 'dashicons-format-chat', $position );

		$sub_menu[ 'dashboard' ] = [
			'title' => __( 'Dashboard', 'chatx-ai' ),
			'function' => [ 'CX_Admin_Controller_Dashboard', 'output' ]
		];

		foreach ( $sub_menu as $key => $item ) {

			if ( empty( $item['function'] ) ) $item['function'] = '';
			if ( empty( $item['capability'] ) ) $item['capability'] = 'manage_woocommerce';
			if ( empty( $item['slug'] ) ) $item['slug'] = 'chatxai-' . $key;
			if ( empty( $item['page_title'] ) ) $item['page_title'] = $item['title'];

			add_submenu_page( 'chatxai', $item['page_title'], $item['title'], $item['capability'], $item['slug'], $item['function'] );

		}
	}


	/**
	 * Dynamic replace top level menu
	 */
	static function replace_top_level_menu() {
		$top_menu_link = self::page_url('dashboard');

		?>
		<script type="text/javascript">
			jQuery('#adminmenu').find('a.toplevel_page_chatxai').attr( 'href', '<?php echo $top_menu_link ?>' );
		</script>
		<?php
	}


	/**
	 * @param $page
	 * @return string|false
	 */
	static function page_url( $page ) {

		switch ( $page ) {

			case 'dashboard':
				return admin_url( 'admin.php?page=chatxai-dashboard' );
				break;

		}

		return false;
	}


	/**
	 * @param $tip
	 * @param bool $pull_right
	 * @param bool $allow_html
	 * @return string
	 */
	static function help_tip( $tip, $pull_right = true, $allow_html = false ) {

		if ( $allow_html ) {
			$tip = wc_sanitize_tooltip($tip);
		}
		else {
			$tip = esc_attr($tip);
		}

		return '<span class="chatx-help-tip ' . ( $pull_right ? 'chatx-help-tip--right' : '' ) . ' woocommerce-help-tip" data-tip="' . $tip . '"></span>';
	}

	/**
	 * Display the admin notices for ChatX.ai
	 */
	public function check_admin_notices() {

		// Notice displayed after the plugin activation, when ChatX is not registered.
		if ( ! CX()->is_registered() ) {
			$this->add_warning_notice(__( 'You need to register ChatX before it starts working.', 'chatx-ai' ) . ' <a href="'.$this->page_url('dashboard').'">' . __( 'Click Here to Start', 'chatx-ai' ) . '</a>');
		}

        $chatx_notices = Notices::getAll();
    	foreach ($chatx_notices as $notice) {
    		$this->add_notice(
    			$notice['type'],
    			$notice['message'],
    			$notice['title'],
    			$notice['id'],
    			$notice['dismissable'],
    			$notice['icon'],
    			$notice['onlyonce']
    		);
    	}
    }

    public function add_success_notice($message) {
        $this->add_notice('success', $message);
    }


    public function add_error_notice($message) {
        $this->add_notice('error', $message);
    }


    public function add_warning_notice($message) {
    	$this->add_notice('warning', $message);
    }

    public function add_notice($type, $message, $title = null, $id = null, $dismissable = false, $icon = false, $onlyonce = false) {
    	$iconClass = $icon ? 'notice-chatx-icon' :  '';
    	$dismissableClass = $dismissable ? 'is-dismissible' : '';
        ?>
            <div class="notice notice-<?php echo $type . ' notice-chatx  '. $iconClass . ' '. $dismissableClass; ?>" data-notice-id="<?php echo $id; ?>">
            	<?php if($title){ ?>
            		<h3><?php echo $title; ?></h3>
            	<?php } ?>
                <p><?php echo stripslashes($message); ?></p>
            </div>
        <?php

        if($id && $onlyonce){
        	Notices::dismiss($id);
        }
    }

    public function setup_intercom() {

    }

    public function auto_update_plugin( $update, $item ) {
    	// Array of plugin slugs to always auto-update
	    $plugins = array (
	        'chatx-ai',
	    );
	    if ( in_array( $item->slug, $plugins ) ) {
	        return true; // Always update plugins in this array
	    } else {
	        return $update; // Else, use the normal API response to decide whether to update or not
	    }
    }
}
