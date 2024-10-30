<?php

/**
 * Register all REST API func for the plugin
 *
 * @package    Chatx_Ai
 * @subpackage Chatx_Ai/includes
 * @author     Chatx.ai <contact@chatx.ai>
 */

use CXLibrary\Notices;

class Chatx_Ai_Rest_API
{

    private $plugin_data = null;
    private $apiToken;
    private $data;
    private $success;
    private $message;
    private $version;
    private $notices = [];

    public function __construct($plugin_data)
    {
        $this->version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '0.0.0';
        $this->data    = [];
        $this->success = false;
        $this->message = 'Unknown message';
    }

    public function init_API()
    {
        $this->apiToken = Chatx_Ai_Options::read('chatxai_api_token');

        add_action('rest_api_init', function () {

            // Get shop data
            register_rest_route('chatxai-api/v1', '/shop', [
                'methods'  => 'GET',
                'callback' => [$this, 'getShopData'],
            ]);

            // Get products count
            register_rest_route('chatxai-api/v1', '/products/count', [
                'methods'  => 'GET',
                'callback' => [$this, 'getProductsCount'],
            ]);

            // Get products
            register_rest_route('chatxai-api/v1', '/products', [
                'methods'  => 'GET',
                'callback' => [$this, 'getProducts'],
            ]);

            // Get pages
            register_rest_route('chatxai-api/v1', '/pages', [
                'methods'  => 'GET',
                'callback' => [$this, 'getPages'],
            ]);

            // Create a page
            register_rest_route('chatxai-api/v1', '/pages', [
                'methods'  => 'POST',
                'callback' => [$this, 'createPage'],
            ]);

            // Set results page script
            register_rest_route('chatxai-api/v1', 'results/script', [
                'methods'  => 'POST',
                'callback' => [$this, 'setResultsPageScript'],
            ]);

            // Get articles
            register_rest_route('chatxai-api/v1', '/articles', [
                'methods'  => 'GET',
                'callback' => [$this, 'getArticles'],
            ]);

            // Get product by id
            register_rest_route('chatxai-api/v1', '/products/(?P<id>\d+)', [
                'methods'  => 'GET',
                'callback' => [$this, 'getProductByID'],
            ]);

            // Get categories
            register_rest_route('chatxai-api/v1', '/categories', [
                'methods'  => 'GET',
                'callback' => [$this, 'getCategories'],
            ]);

            // Create notification
            register_rest_route('chatxai-api/v1', '/notifications', [
                'methods'  => 'POST',
                'callback' => [$this, 'createNotification'],
            ]);

        });

        remove_action('rest_api_init', 'create_initial_rest_routes', 0);

        add_filter('rest_route_data', function ($routes) {
            return [];
        });
    }

    // Get shop data api
    public function getShopData($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $priceFormat = get_woocommerce_price_format();
        $priceFormat = str_replace('%1$s', get_woocommerce_currency_symbol(), $priceFormat);
        $priceFormat = str_replace('%2$s', '{{amount}}', $priceFormat);
        $priceFormat = str_replace('&nbsp;', ' ', $priceFormat);

        $productPageUrl = get_post_type_archive_link('product');

        $args = array(
            'posts_per_page' => isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 250,
            'paged' => isset($_REQUEST['page']) ? $_REQUEST['page'] : 1,
            'post_type' => 'product',
            'orderby' => 'ID',
            'order' => 'ASC'
        );

        $products = get_posts($args);
        if(count($products)){
            $testProduct = $products[0];

            $productPageUrl = str_replace($testProduct->post_name, '', get_permalink($testProduct));
            $productPageUrl = str_replace($testProduct->ID, '', $productPageUrl);

            $productPageUrl = preg_replace("~\/(?!.*\/)~", '', $productPageUrl);
            $productPageUrl = preg_replace("~\/(?!.*\/)~", '', $productPageUrl);
        }

        $primary_locale = explode('-', get_bloginfo('language'));
        $primary_locale = count($primary_locale) ? $primary_locale[0] : 'en';

        $shopData = [
            'email' => get_bloginfo('admin_email'),
            'currency' => get_woocommerce_currency(),
            'money_format' => $priceFormat,
            'product_page_url' => $productPageUrl,
            'primary_locale' => $primary_locale
        ];

        $this->success = true;
        return $this->sendResponseData([
            'shop' => $shopData
        ]);
    }

    // Get products count api
    public function getProductsCount($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $productsIds = get_posts([
            'post_type' => [
                'product'
            ],
            'post_status' => [
                'publish'
            ],
            'fields' => 'ids',
            'posts_per_page' => '-1'
        ]);

        $this->success = true;
        return $this->sendResponseData([
            'count' => count($productsIds)
        ]);
    }

    static function getAttributeValuesArray($isTax, $values){
        if($isTax){
            return explode(',', str_replace(', ', ',', $values));
        }
        else{
            return explode('|', str_replace(' | ', '|', str_replace('| ', '|', str_replace(' |', '|', $values))));
        }
    }

    static function getProductAttributes($wooProduct){

        $attributes = [];
        $attributeId = 0;

        foreach ( $wooProduct->get_attributes() as $attributeKey => $attribute ) {

            $attributeKeyFormatted = str_replace('pa_', '', $attributeKey);

            if(is_array($attribute)){

                $attrTerms = get_terms($attribute['name']);

                $attributeOptions = [];

                $attributeTerms = is_array($attrTerms) ? $attrTerms : [];
                foreach ($attributeTerms as $attributeTerm) {
                    $attributeOptions[] = $attributeTerm->slug;
                }

                $attributes[] = [
                    'id' => $wooProduct->get_id() . '-' . $attributeId,
                    'name' => ucfirst(str_replace('pa_', '', $attribute['name'])),
                    'key' => $attributeKeyFormatted,
                    'options' => $attributeOptions,
                    'is_taxonomy' => $attribute['is_taxonomy'],
                    'position' => $attribute['position'],
                    'visible' => $attribute['is_visible'],
                    'variation' => $attribute['is_variation'],
                    'values' => Chatx_Ai_Rest_API::getAttributeValuesArray($attribute['is_taxonomy'], $wooProduct->get_attribute($attribute['name']))
                ];

            }
            else{

                $attributes[] = [
                    'id' => $wooProduct->get_id() . '-' . $attribute->get_id(),
                    'name' => ucfirst(str_replace('pa_', '', $attribute->get_name())),
                    'key' => $attributeKeyFormatted,
                    'options' => $attribute->get_slugs( ),
                    'is_taxonomy' => $attribute->is_taxonomy(),
                    'position' => $attribute->get_position(),
                    'visible' => $attribute->get_visible(),
                    'variation' => $attribute->get_variation(),
                    'values' => Chatx_Ai_Rest_API::getAttributeValuesArray($attribute['is_taxonomy'], $wooProduct->get_attribute($attribute->get_name()))
                ];

            }

            $attributeId++;
        }

        return $attributes;
    }

    static function getProductInFormat($product){

        $productTagsTerms = get_the_terms( $product->ID, 'product_tag' );

        $wooProduct = wc_get_product($product->ID);

        $tags = [];

        if($productTagsTerms){
            $tags = array_map(function($tag){
                return $tag->name;
            }, $productTagsTerms);
        }

        $productCreatedAt = new DateTime($product->post_date);
        $productUpdatedAt = new DateTime($product->post_modified);

        $variations = [];

        if($wooProduct->is_type( 'variable' )){

            foreach ($wooProduct->get_available_variations() as $variation) {

                if(!$variation['variation_is_active'] || !$variation['variation_is_visible']){
                    continue;
                }

                $variationFormated = [
                    'id' => $variation['variation_id'],
                    'image' => isset($variation['image']) ? $variation['image']['url'] : null,
                    'compare_at_price' => $variation['display_regular_price'] !== $variation['display_price'] ? $variation['display_regular_price'] : null,
                    'price' => $variation['display_price'],
                    'sku' => $variation['sku'],
                    'inventory_quantity' => $variation['is_in_stock'] ? 1 : 0,
                    'vendor' => null,
                    'title' => null,
                    'options' => []
                ];

                foreach ($variation['attributes'] as $attributeKey => $attribute) {

                    $attributeKeyFormatted = str_replace('attribute_', '', str_replace('attribute_pa_', '', $attributeKey));
                    $variationFormated['options'][$attributeKeyFormatted] = $attribute;
                }

                $variations[] = $variationFormated;
            }
        }

        $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->ID), 'full');
        $imageMedium = wp_get_attachment_image_src(get_post_thumbnail_id($product->ID), 'medium');
        $imageThumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($product->ID), 'thumbnail');

        $categories = wp_get_post_terms( $product->ID, 'product_cat');

        $customIndexTerms = [];

        $newProduct = [
            'id' => $product->ID,
            'handle' => $product->post_name,
            'tags' => $tags,
            'image' => [
                'full' => is_array($image) ? $image[0] : null,
                'medium' => is_array($imageMedium) ? $imageMedium[0] : null,
                'thumbnail' => is_array($imageThumbnail) ? $imageThumbnail[0] : null
            ],
            'created_at' => $productCreatedAt->format('c'),
            'updated_at' => $productUpdatedAt->format('c'),
            'status' => $product->post_status,
            'title' => get_the_title($product),
            'sku' => $wooProduct->get_sku(),
            'variations' => $variations,
            'attributes' => Chatx_Ai_Rest_API::getProductAttributes($wooProduct),
            'price' => $wooProduct->get_price(),
            'compare_at_price' => $wooProduct->get_regular_price(),
            'categories' => $categories,
            'body_html' => do_shortcode(apply_filters( 'the_content', $product->post_content )),
            'custom_index_terms' => $customIndexTerms
        ];

        if(method_exists($wooProduct, 'get_stock_status')){
            $newProduct['inventory_quantity'] = $wooProduct->get_stock_status() == 'instock' ? 1 : 0;
        }
        else{
            $newProduct['inventory_quantity'] = $wooProduct->is_in_stock() ? 1 : 0;
        }

        return $newProduct;
    }

    // Get products api
    public function getProducts($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $args = array(
            'posts_per_page' => isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 250,
            'paged' => isset($_REQUEST['page']) ? $_REQUEST['page'] : 1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'post_type' => [
                'product'
            ],
            'post_status' => [
                'publish'
            ]
        );

        $products = get_posts($args);

        $this->success = true;
        return $this->sendResponseData([
            'products' => array_map('Chatx_Ai_Rest_API::getProductInFormat', $products)
        ]);

        return $this->sendResponseError('Product not found');
    }

    // Create posts api
    public function getProductByID($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $product_id = $request->get_param('id');

        $product = get_post($product_id);

        if ($product && get_post_type($product) == "product") {
            $this->success = true;
            return $this->sendResponseData([
                'product' => $this->getProductInFormat($product)
            ]);
        }

        return $this->sendResponseError('Product not found');
    }

    static function getPageInFormat($page){

        $pageCreatedAt = new DateTime($page->post_date);
        $pageUpdatedAt = new DateTime($page->post_modified);

        $newProduct = [
            'id' => $page->ID,
            'handle' => $page->post_name,
            'tags' => $tags,
            'created_at' => $pageCreatedAt->format('c'),
            'updated_at' => $pageUpdatedAt->format('c'),
            'status' => $page->post_status,
            'title' => get_the_title($page),
            'author' => get_the_author_meta('display_name', $page->post_author),
            'body_html' => do_shortcode(get_post_field('post_content', $page->ID)),
            'permalink' => get_permalink($page)
        ];

        return $newProduct;

    }

    // Get pages api
    public function getPages($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $args = array(
            'posts_per_page' => isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 250,
            'paged' => isset($_REQUEST['page']) ? $_REQUEST['page'] : 1,
            'post_type' => 'page',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC'
        );

        $this->success = true;

        return $this->sendResponseData([
            'pages' => array_map('Chatx_Ai_Rest_API::getPageInFormat', get_posts($args))
        ]);

        return $this->sendResponseError('Product not found');
    }

    // Create a page api
    public function createPage($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
        $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';

        $oldPage = get_page_by_title( $title );

        $pageAlreadyExists = $oldPage && !is_wp_error($oldPage);

        $newPageOptions = [
            'comment_status'    =>  'closed',
            'ping_status'       =>  'closed',
            'post_name'         =>  sanitize_title($title, 'ChatX Results Page'),
            'post_title'        =>  $title,
            'post_content'      =>  $content,
            'post_status'       =>  'publish',
            'post_type'         =>  'page'
        ];

        if( !$pageAlreadyExists ) {

            $newPageId = wp_insert_post($newPageOptions);

            $permalink = get_permalink($newPageId);

            update_post_meta($newPageId, 'created_by_chatx', true);
            update_post_meta($newPageId, 'chatx_results_page', true);
        }
        else {
            $newPageOptions['ID'] = $oldPage->ID;

            wp_update_post( $newPageOptions );
        }

        return $this->sendResponseData([
            'page' => [
                'id' => $oldPage->ID,
                'permalink' => get_permalink($oldPage),
                'exists' => true,
                'chatx_page' => !!get_post_meta($oldPage->ID, 'created_by_chatx', true),
                'chatx_results_page' => !!get_post_meta($oldPage->ID, 'chatx_results_page', true)
            ],
            'home_url' => get_home_url()
        ]);
    }

    // Set results page script
    public function setResultsPageScript($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';

        Chatx_Ai_Options::update('chatxai_results_page_script_url', $url);

        return $this->sendResponseData([
            'url' => $url,
        ]);
    }

    // Get articles api
    public function getArticles($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $args = array(
            'posts_per_page' => isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 250,
            'paged' => isset($_REQUEST['page']) ? $_REQUEST['page'] : 1,
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC'
        );

        $this->success = true;

        return $this->sendResponseData([
            'articles' => array_map('Chatx_Ai_Rest_API::getPageInFormat', get_posts($args))
        ]);

        return $this->sendResponseError('Product not found');
    }

    // Get categories api
    public function getCategories($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $terms = get_terms('product_cat', array(
            'hide_empty' => false,
        ));

        $formatedTerms = array_map(function($item){
            $item->permalink = get_term_link($item);
            return $item;
        }, $terms);

        $this->success = true;
        return $this->sendResponseData([
            'categories' => $formatedTerms
        ]);

        return $this->sendResponseError('Product not found');
    }

    public function createNotification($request)
    {
        if (!$this->checkRequest()) {
            return $this->sendResponseError('Invalid request. Please check the documentation!');
        }

        $title   = isset($_REQUEST['title']) ? $_REQUEST['title'] : null;
        $message = isset($_REQUEST['message']) ? $_REQUEST['message'] : 'By ChatX.ai';
        $type    = isset($_REQUEST['type']) ? $_REQUEST['type'] : '1';

        $notification_created = true;
        // $title -> String
        // $message -> String
        // $type -> String: info|success|warning|error

        Notices::add($type, $title, $message, true, true);

        if ($notification_created) {
            return $this->sendResponseData();
        } else {
            return $this->sendResponseError();
        }
    }

    // Check if API key exists and is valid
    public function checkRequest()
    {
        if (!isset($_REQUEST['api_token']) || $_REQUEST['api_token'] !== $this->apiToken) {
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] == "PUT") {
            parse_str(file_get_contents("php://input"), $_POST);
        }

        return true;
    }

    // Send response
    public function sendResponse($echo = false)
    {
        $responseData = [
            'code'    => "ok",
            'success' => $this->success,
            'data'    => $this->data,
            'message' => $this->message,
        ];

        if (!$echo) {
            return $responseData;
        }

        wp_send_json($responseData);
    }

    // Send response with error message
    public function sendResponseError($error = "With error", $data = [], $echo = false)
    {
        $this->message = $error;
        $this->success = false;
        $this->data    = $data;
        return $this->sendResponse($echo);
    }

    // Send response with data and success
    public function sendResponseData($data = [], $msg = "Success", $echo = false)
    {
        $this->message = $msg;
        $this->data    = $data;
        $this->success = true;
        return $this->sendResponse($echo);
    }
}
