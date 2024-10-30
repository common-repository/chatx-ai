<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/public
 * @author     Chatx.ai <contact@chatx.ai>
 */
class Chatx_Ai_Public
{

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        $this->api_key   = Chatx_Ai_Options::read('chatxai_api_key');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/chatx-ai-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

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

        global $post;

        wp_enqueue_script($this->plugin_name . '-sdk', CHATX_BAESURL . '/assets/sdk.js', array('jquery'), $this->version, true);

        if(is_page()){

            $isResultsPage = !!get_post_meta($post->ID, 'chatx_results_page', true);

            if($isResultsPage){

                $scriptUrl = Chatx_Ai_Options::read('chatxai_results_page_script_url', null);

                if($scriptUrl){
                    wp_enqueue_script($this->plugin_name . '-sdk-results-page', CHATX_BAESURL . $scriptUrl, array('jquery'), $this->version, true);
                }
            }
        }

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/chatx-ai-public.js', array('jquery'), $this->version, true);

        $chatxJsSettings = array(
            'api_key' => Chatx_Ai_Options::read('chatxai_api_key')
        );

        wp_localize_script( $this->plugin_name, 'chatxSettings', $chatxJsSettings );
    }

    public function error_formatting($text = '')
    {
        return '<strong>ChatX Error: </strong>' . '<p style="color:red">' . $text . '</p>';
    }

    public function action_upgrade_completed($upgrader_object, $options)
    {
        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
            Chatx_Ai_Options::trigger_changes();
        }
    }
}
