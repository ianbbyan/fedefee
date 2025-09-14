<?php
/**
 * Plugin Name: Lev2Euro
 * Plugin URI: https://yourwebsite.com/
 * Description: Automatically displays product prices in both Bulgarian lev (BGN) and euro (EUR) for WooCommerce stores.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lev2euro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LEV2EURO_VERSION', '1.0.0');
define('LEV2EURO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LEV2EURO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LEV2EURO_CONVERSION_RATE', 1.95583); // 1 EUR = 1.95583 BGN

class Lev2Euro {
    
    /**
     * Debug information for tracking price extraction
     */
    private $debug_info = '';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'check_woocommerce'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('lev2euro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize admin settings
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'settings_init'));
        }
        
        // Initialize frontend functionality
        if ($this->is_plugin_enabled()) {
            $this->init_price_hooks();
        }
    }
    
    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
    }
    
    /**
     * Display notice if WooCommerce is missing
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p>' . __('Lev2Euro requires WooCommerce to be installed and active.', 'lev2euro') . '</p>';
        echo '</div>';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        add_option('lev2euro_enabled', '1');
        add_option('lev2euro_position', 'under');
        add_option('lev2euro_format', 'plain');
        add_option('lev2euro_symbol', '€');
        add_option('lev2euro_prefix', '');
        add_option('lev2euro_debug', '0');
        add_option('lev2euro_email_enabled', '1');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {

    }

    public function delete() {
        // Remove options on plugin deletion
        delete_option('lev2euro_enabled');
        delete_option('lev2euro_position');
        delete_option('lev2euro_format');
        delete_option('lev2euro_symbol');
        delete_option('lev2euro_prefix');
        delete_option('lev2euro_debug');
        delete_option('lev2euro_email_enabled');
    }
    
    /**
     * Check if plugin is enabled
     */
    private function is_plugin_enabled() {
        return get_option('lev2euro_enabled', '1') === '1';
    }
    
    /**
     * Check if plugin should run (only for BGN currency)
     */
    private function should_run() {
        return $this->is_plugin_enabled() && get_woocommerce_currency() === 'BGN';
    }
    
    /**
     * Check if we're on the cart page
     */
    private function is_cart_page() {
        // Check if WooCommerce is available
        if (!function_exists('is_cart')) {
            return false;
        }
        
        // Use WooCommerce's built-in cart detection
        return is_cart();
    }
    
    /**
     * Check if plugin should run for emails (only for BGN currency and if email option is enabled)
     */
    private function should_run_for_emails() {
        return $this->should_run() && get_option('lev2euro_email_enabled', '1') === '1';
    }
    
    /**
     * Initialize price hooks
     */
    private function init_price_hooks() {
        // Only initialize if currency is BGN
        if (!$this->should_run()) {
            return;
        }
        
        // Product page price
        add_filter('woocommerce_get_price_html', array($this, 'add_euro_price'), 10, 2);
        
        // Shop page and archive prices
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'modify_loop_price'), 10, 2);
        
        // Cart and checkout prices
        add_filter('woocommerce_cart_item_price', array($this, 'add_euro_to_cart_price'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'add_euro_to_cart_price'), 10, 3);
        
        // Cart totals (subtotal, total, etc.)
        add_filter('woocommerce_cart_subtotal', array($this, 'add_euro_to_cart_totals'), 10, 3);
        add_filter('woocommerce_cart_totals_order_total_html', array($this, 'add_euro_to_order_total'), 10, 1);
        
        // Variation prices
        add_filter('woocommerce_variation_price_html', array($this, 'add_euro_price'), 10, 2);
        
        // Widget prices
        add_filter('woocommerce_widget_cart_item_quantity', array($this, 'modify_widget_price'), 10, 3);
        
        // Email prices
        add_filter('woocommerce_email_order_item_quantity', array($this, 'add_euro_to_email_prices'), 10, 3);
        add_filter('woocommerce_order_formatted_line_subtotal', array($this, 'add_euro_to_email_line_subtotal'), 10, 3);
        add_filter('woocommerce_get_formatted_order_total', array($this, 'add_euro_to_email_order_total'), 10, 2);
    }
    
    /**
     * Add Euro price to main price display
     */
    public function add_euro_price($price_html, $product) {
        // Check if we should run (BGN currency only)
        if (!$this->should_run() || empty($price_html) || !$product) {
            return $price_html;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        // Extract numeric price from HTML
        $price_numeric = $this->extract_price_from_html($price_html);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $price_html = $this->insert_euro_price($price_html, $euro_display);
        }
        
        return $price_html;
    }
    
    /**
     * Modify loop price (shop page)
     */
    public function modify_loop_price($link, $product) {
        // This ensures the price filter is applied to loop prices as well
        return $link;
    }
    
    /**
     * Add Euro price to cart prices
     */
    public function add_euro_to_cart_price($price_html, $cart_item, $cart_item_key) {
        // Check if we should run (BGN currency only)
        if (!$this->should_run() || empty($price_html)) {
            return $price_html;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        $price_numeric = $this->extract_price_from_html($price_html);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $price_html = $this->insert_euro_price($price_html, $euro_display);
        }
        
        return $price_html;
    }
    
    /**
     * Add Euro price to cart totals (subtotal, shipping, etc.)
     */
    public function add_euro_to_cart_totals($subtotal, $compound, $cart) {
        // Check if we should run (BGN currency only)
        if (!$this->should_run() || empty($subtotal)) {
            return $subtotal;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        $price_numeric = $this->extract_price_from_html($subtotal);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $subtotal = $this->insert_euro_price($subtotal, $euro_display);
        }
        
        return $subtotal;
    }
    
    /**
     * Add Euro price to order total
     */
    public function add_euro_to_order_total($total_html) {
        // Check if we should run (BGN currency only)
        if (!$this->should_run() || empty($total_html)) {
            return $total_html;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        $price_numeric = $this->extract_price_from_html($total_html);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $total_html = $this->insert_euro_price($total_html, $euro_display);
        }
        
        return $total_html;
    }
    
    /**
     * Modify widget cart price
     */
    public function modify_widget_price($quantity_html, $cart_item, $cart_item_key) {
        // Extract and modify price in widget if needed
        return $quantity_html;
    }
    
    /**
     * Add Euro price to email item quantities and prices
     */
    public function add_euro_to_email_prices($quantity_html, $item, $order) {
        // Check if we should run for emails (BGN currency only and email option enabled)
        if (!$this->should_run_for_emails() || empty($quantity_html)) {
            return $quantity_html;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        $price_numeric = $this->extract_price_from_html($quantity_html);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $quantity_html = $this->insert_euro_price_for_email($quantity_html, $euro_display);
        }
        
        return $quantity_html;
    }
    
    /**
     * Add Euro price to email line subtotals
     */
    public function add_euro_to_email_line_subtotal($subtotal, $item, $order) {
        // Check if we should run for emails (BGN currency only and email option enabled)
        if (!$this->should_run_for_emails() || empty($subtotal)) {
            return $subtotal;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        $price_numeric = $this->extract_price_from_html($subtotal);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $subtotal = $this->insert_euro_price_for_email($subtotal, $euro_display);
        }
        
        return $subtotal;
    }
    
    /**
     * Add Euro price to email order total
     */
    public function add_euro_to_email_order_total($formatted_total, $order) {
        // Check if we should run for emails (BGN currency only and email option enabled)
        if (!$this->should_run_for_emails() || empty($formatted_total)) {
            return $formatted_total;
        }
        
        // Reset debug info for this price conversion
        $this->debug_info = '';
        
        $price_numeric = $this->extract_price_from_html($formatted_total);
        
        if ($price_numeric > 0) {
            $euro_price = $this->convert_to_euro($price_numeric);
            $euro_display = $this->format_euro_price($euro_price);
            $formatted_total = $this->insert_euro_price_for_email($formatted_total, $euro_display);
        }
        
        return $formatted_total;
    }
    
    /**
     * Extract numeric price from HTML string
     * For sale prices: extracts the SALE price (discounted price from <ins> tag)
     * For regular prices: extracts the REGULAR price
     */
    private function extract_price_from_html($price_html) {
        $is_sale_price = false;
        $extracted_price = 0;
        $price_type = 'regular';
        
        // Check if it's a sale price format with <del> and <ins> tags
        if (strpos($price_html, '<del') !== false && strpos($price_html, '<ins') !== false) {
            $is_sale_price = true;
            $price_type = 'sale';
            
            // Extract SALE price (from <ins> tag) - this is the current/active discounted price
            // More flexible pattern to handle various HTML structures with <bdi> tags
            if (preg_match('/<ins[^>]*>.*?<bdi>(\d+(?:[\.,]\d{2})?)\s*&nbsp;.*?<\/bdi>.*?<\/ins>/u', $price_html, $matches)) {
                $extracted_price = (float) str_replace(',', '.', $matches[1]);
            }
            // Fallback pattern for simpler <ins> structures without <bdi>
            elseif (preg_match('/<ins[^>]*>.*?(\d+(?:[\.,]\d{2})?)\s*(?:&nbsp;)?(?:лв|BGN).*?<\/ins>/u', $price_html, $matches)) {
                $extracted_price = (float) str_replace(',', '.', $matches[1]);
            }
            
            // If we successfully extracted a sale price, return it
            if ($extracted_price > 0) {
                // Add debug info if enabled
                if (get_option('lev2euro_debug', '0') === '1') {
                    $this->debug_info = "Extracted {$price_type} price: {$extracted_price} BGN";
                }
                return $extracted_price;
            }
        }
        
        // Handle regular prices (no discount) or fallback if sale price extraction failed
        $price_text = strip_tags($price_html);
        
        // Match Bulgarian lev amounts (handles various formats)
        if (preg_match('/(\d+(?:[\.,]\d{2})?)\s*(?:лв|BGN)/u', $price_text, $matches)) {
            $extracted_price = (float) str_replace(',', '.', $matches[1]);
            // Add debug info if enabled
            if (get_option('lev2euro_debug', '0') === '1') {
                $this->debug_info = "Extracted {$price_type} price: {$extracted_price} BGN";
            }
            return $extracted_price;
        }
        
        // Fallback: extract any decimal number
        if (preg_match('/(\d+(?:[\.,]\d{2})?)/', $price_text, $matches)) {
            $extracted_price = (float) str_replace(',', '.', $matches[1]);
            // Add debug info if enabled
            if (get_option('lev2euro_debug', '0') === '1') {
                $this->debug_info = "Extracted {$price_type} price (fallback): {$extracted_price} BGN";
            }
            return $extracted_price;
        }
        
        return 0;
    }
    
    /**
     * Convert BGN to EUR
     */
    private function convert_to_euro($bgn_amount) {
        return round($bgn_amount / LEV2EURO_CONVERSION_RATE, 2);
    }
    
    /**
     * Format Euro price for display
     */
    private function format_euro_price($euro_amount) {
        $prefix = get_option('lev2euro_prefix', '');
        $symbol = get_option('lev2euro_symbol', '€');
        
        return $prefix . number_format($euro_amount, 2, '.', ',') . ' ' . $symbol;
    }
    
    /**
     * Insert Euro price into existing price HTML
     */
    private function insert_euro_price($price_html, $euro_display) {
        $position = get_option('lev2euro_position', 'under');
        $format = get_option('lev2euro_format', 'parentheses');
        
        // Add debug information if enabled
        $debug_comment = '';
        if (get_option('lev2euro_debug', '0') === '1' && !empty($this->debug_info)) {
            $debug_comment = '<!-- Lev2Euro Debug: ' . esc_html($this->debug_info) . ' -->';
        }
        
        // Format the euro display based on position
        if ($position === 'under') {
            // For sale prices, reorganize to show: original price, sale price, euro price (each on new line)
            if (strpos($price_html, '<del') !== false && strpos($price_html, '<ins') !== false) {
                // Extract original price (from <del> tag)
                $original_price = '';
                if (preg_match('/<del[^>]*>(.*?)<\/del>/', $price_html, $del_matches)) {
                    $original_price = $del_matches[1];
                }
                
                // Extract sale price (from <ins> tag)
                $sale_price = '';
                if (preg_match('/<ins[^>]*>(.*?)<\/ins>/', $price_html, $ins_matches)) {
                    $sale_price = $ins_matches[1];
                }
                
                // Remove screen reader text for cleaner output
                $original_price = preg_replace('/<span class="screen-reader-text">.*?<\/span>/', '', $original_price);
                $sale_price = preg_replace('/<span class="screen-reader-text">.*?<\/span>/', '', $sale_price);
                
                // Create new structure with inline styles to force vertical layout (NO LABELS)
                $euro_formatted = '<span class="lev2euro-price" style="display:block!important;width:100%!important;margin:2px 0!important;clear:both!important;">' . $euro_display . '</span>' . $debug_comment;
                
                // Build the new price structure with CSS Grid to force vertical layout
                $new_structure = '<div style="display:grid!important;grid-template-columns:1fr!important;gap:2px!important;width:100%!important;">';
                $new_structure .= '<del class="lev2euro-original" style="display:block!important;width:100%!important;text-decoration:line-through!important;opacity:0.7!important;grid-row:1!important;">' . $original_price . '</del>';
                $new_structure .= '<ins class="lev2euro-sale" style="display:block!important;width:100%!important;font-weight:bold!important;text-decoration:none!important;grid-row:2!important;">' . $sale_price . '</ins>';
                $new_structure .= '<span style="display:block!important;width:100%!important;grid-row:3!important;">' . $euro_formatted . '</span>';
                $new_structure .= '</div>';
                
                return $new_structure;
            }
            
            // For regular prices, add Euro price on new line with inline styles (NO LABEL)
            $euro_formatted = '<div style="display:block!important;width:100%!important;margin:2px 0!important;clear:both!important;"><span class="lev2euro-price">' . $euro_display . '</span></div>' . $debug_comment;
            return $price_html . $euro_formatted;
        } elseif ($position === 'before') {
            if ($format === 'parentheses') {
                $euro_formatted = '<span class="lev2euro-price">(' . $euro_display . ')</span> ' . $debug_comment;
            } else {
                $euro_formatted = '<span class="lev2euro-price">' . $euro_display . '</span> ' . $debug_comment;
            }
            return $euro_formatted . $price_html;
        } else { // after
            if ($format === 'parentheses') {
                $euro_formatted = ' <span class="lev2euro-price">(' . $euro_display . ')</span>' . $debug_comment;
            } else {
                $euro_formatted = ' <span class="lev2euro-price">' . $euro_display . '</span>' . $debug_comment;
            }
            return $price_html . $euro_formatted;
        }
    }
    
    /**
     * Insert Euro price into existing price HTML for emails (simplified formatting)
     */
    private function insert_euro_price_for_email($price_html, $euro_display) {
        $position = get_option('lev2euro_position', 'under');
        $format = get_option('lev2euro_format', 'parentheses');
        
        // Add debug information if enabled
        $debug_comment = '';
        if (get_option('lev2euro_debug', '0') === '1' && !empty($this->debug_info)) {
            $debug_comment = '<!-- Lev2Euro Debug: ' . esc_html($this->debug_info) . ' -->';
        }
        
        // Format the euro display based on position - simplified for emails
        if ($position === 'under') {
            // For emails, use simple line breaks instead of complex CSS
            $euro_formatted = '<br><span style="color:#666;font-size:0.9em;">' . $euro_display . '</span>' . $debug_comment;
            return $price_html . $euro_formatted;
        } elseif ($position === 'before') {
            if ($format === 'parentheses') {
                $euro_formatted = '<span style="color:#666;font-size:0.9em;">(' . $euro_display . ')</span> ' . $debug_comment;
            } else {
                $euro_formatted = '<span style="color:#666;font-size:0.9em;">' . $euro_display . '</span> ' . $debug_comment;
            }
            return $euro_formatted . $price_html;
        } else { // after
            if ($format === 'parentheses') {
                $euro_formatted = ' <span style="color:#666;font-size:0.9em;">(' . $euro_display . ')</span>' . $debug_comment;
            } else {
                $euro_formatted = ' <span style="color:#666;font-size:0.9em;">' . $euro_display . '</span>' . $debug_comment;
            }
            return $price_html . $euro_formatted;
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Lev2Euro Settings', 'lev2euro'),
            __('Lev2Euro', 'lev2euro'),
            'manage_options',
            'lev2euro',
            array($this, 'options_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function settings_init() {
        register_setting('lev2euro_settings', 'lev2euro_enabled');
        register_setting('lev2euro_settings', 'lev2euro_position');
        register_setting('lev2euro_settings', 'lev2euro_format');
        register_setting('lev2euro_settings', 'lev2euro_symbol');
        register_setting('lev2euro_settings', 'lev2euro_prefix');
        register_setting('lev2euro_settings', 'lev2euro_debug');
        register_setting('lev2euro_settings', 'lev2euro_email_enabled');
        
        add_settings_section(
            'lev2euro_section',
            __('Display Settings', 'lev2euro'),
            array($this, 'settings_section_callback'),
            'lev2euro_settings'
        );
        
        add_settings_field(
            'lev2euro_enabled',
            __('Enable Euro Display', 'lev2euro'),
            array($this, 'enabled_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
        
        add_settings_field(
            'lev2euro_position',
            __('Euro Price Position', 'lev2euro'),
            array($this, 'position_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
        
        add_settings_field(
            'lev2euro_format',
            __('Display Format', 'lev2euro'),
            array($this, 'format_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
        
        add_settings_field(
            'lev2euro_prefix',
            __('Euro Price Prefix', 'lev2euro'),
            array($this, 'prefix_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
        
        add_settings_field(
            'lev2euro_symbol',
            __('Euro Symbol', 'lev2euro'),
            array($this, 'symbol_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
        
        add_settings_field(
            'lev2euro_debug',
            __('Debug Mode', 'lev2euro'),
            array($this, 'debug_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
        
        add_settings_field(
            'lev2euro_email_enabled',
            __('Show Euro in Emails', 'lev2euro'),
            array($this, 'email_enabled_render'),
            'lev2euro_settings',
            'lev2euro_section'
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo __('Configure how Euro prices are displayed alongside Bulgarian lev prices.', 'lev2euro');
        echo '<p><strong>' . __('Note:', 'lev2euro') . '</strong> ' . __('This plugin only works when the store currency is set to BGN (Bulgarian Lev).', 'lev2euro') . '</p>';
        echo '<p><strong>' . __('Current Currency:', 'lev2euro') . '</strong> ' . get_woocommerce_currency() . '</p>';
        echo '<p><strong>' . __('Conversion Rate:', 'lev2euro') . '</strong> 1 EUR = ' . LEV2EURO_CONVERSION_RATE . ' BGN</p>';
    }
    
    /**
     * Render enabled field
     */
    public function enabled_render() {
        $value = get_option('lev2euro_enabled', '1');
        echo '<input type="checkbox" name="lev2euro_enabled" value="1" ' . checked($value, '1', false) . ' />';
        echo '<label for="lev2euro_enabled">' . __('Display Euro prices next to BGN prices', 'lev2euro') . '</label>';
    }
    
    /**
     * Render position field
     */
    public function position_render() {
        $value = get_option('lev2euro_position', 'under');
        echo '<select name="lev2euro_position">';
        echo '<option value="under" ' . selected($value, 'under', false) . '>' . __('Under BGN price (new line)', 'lev2euro') . '</option>';
        echo '<option value="after" ' . selected($value, 'after', false) . '>' . __('After BGN price (same line)', 'lev2euro') . '</option>';
        echo '<option value="before" ' . selected($value, 'before', false) . '>' . __('Before BGN price (same line)', 'lev2euro') . '</option>';
        echo '</select>';
    }
    
    /**
     * Render format field
     */
    public function format_render() {
        $value = get_option('lev2euro_format', 'parentheses');
        echo '<select name="lev2euro_format">';
        echo '<option value="parentheses" ' . selected($value, 'parentheses', false) . '>' . __('In parentheses ( 19.94 €)', 'lev2euro') . '</option>';
        echo '<option value="plain" ' . selected($value, 'plain', false) . '>' . __('Plain text  19.94 €', 'lev2euro') . '</option>';
        echo '</select>';
    }
    
    /**
     * Render prefix field
     */
    public function prefix_render() {
        $value = get_option('lev2euro_prefix', '');
        echo '<input type="text" name="lev2euro_prefix" value="' . esc_attr($value) . '" placeholder=" " />';
        echo '<p class="description">' . __('Text to display before the Euro amount (e.g., " " or "~ ")', 'lev2euro') . '</p>';
    }
    
    /**
     * Render symbol field
     */
    public function symbol_render() {
        $value = get_option('lev2euro_symbol', '€');
        echo '<input type="text" name="lev2euro_symbol" value="' . esc_attr($value) . '" placeholder="€" size="3" />';
        echo '<p class="description">' . __('Symbol to display after the Euro amount', 'lev2euro') . '</p>';
    }
    
    /**
     * Render debug field
     */
    public function debug_render() {
        $value = get_option('lev2euro_debug', '0');
        echo '<input type="checkbox" name="lev2euro_debug" value="1" ' . checked($value, '1', false) . ' />';
        echo '<label for="lev2euro_debug">' . __('Enable debug mode (shows which price is being converted in HTML comments)', 'lev2euro') . '</label>';
        echo '<p class="description">' . __('When enabled, debug information will be added as HTML comments to help verify correct price extraction.', 'lev2euro') . '</p>';
    }
    
    /**
     * Render email enabled field
     */
    public function email_enabled_render() {
        $value = get_option('lev2euro_email_enabled', '1');
        echo '<input type="checkbox" name="lev2euro_email_enabled" value="1" ' . checked($value, '1', false) . ' />';
        echo '<label for="lev2euro_email_enabled">' . __('Display Euro prices in customer emails (order confirmations, receipts, etc.)', 'lev2euro') . '</label>';
        echo '<p class="description">' . __('When disabled, Euro prices will only show on the website but not in email notifications.', 'lev2euro') . '</p>';
    }
    
    /**
     * Options page
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo __('Lev2Euro Settings', 'lev2euro'); ?></h1>
            
            <div class="notice notice-info">
                <p><?php echo __('This plugin automatically converts Bulgarian lev (BGN) prices to Euro (EUR) using a fixed conversion rate.', 'lev2euro'); ?></p>
                <p><strong><?php echo __('Example:', 'lev2euro'); ?></strong></p>
                <ul>
                    <li><strong><?php echo __('Under:', 'lev2euro'); ?></strong> <?php echo __('59.90 лв<br/>30.62 €', 'lev2euro'); ?></li>
                    <li><strong><?php echo __('After:', 'lev2euro'); ?></strong> <?php echo __('59.90 лв (30.62 €)', 'lev2euro'); ?></li>
                    <li><strong><?php echo __('Before:', 'lev2euro'); ?></strong> <?php echo __('(30.62 €) 59.90 лв', 'lev2euro'); ?></li>
                </ul>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('lev2euro_settings');
                do_settings_sections('lev2euro_settings');
                submit_button();
                ?>
            </form>
            
            <div class="card">
                <h2><?php echo __('Plugin Information', 'lev2euro'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php echo __('Version:', 'lev2euro'); ?></th>
                        <td><?php echo LEV2EURO_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('Conversion Rate:', 'lev2euro'); ?></th>
                        <td>1 EUR = <?php echo LEV2EURO_CONVERSION_RATE; ?> BGN</td>
                    </tr>
                    <tr>
                        <th><?php echo __('Compatible with:', 'lev2euro'); ?></th>
                        <td>WordPress 5.0+, WooCommerce 5.0+</td>
                    </tr>
                </table>
            </div>
            
            <style>
                .lev2euro-price {
                    font-weight: normal;
                    font-size: 0.9em;
                }

                .product-listing-price .price h5 {
                    flex-direction: column;
                }
            </style>
        </div>
        <?php
    }
}

// Initialize the plugin
new Lev2Euro();

// Add frontend CSS
add_action('wp_head', function() {
    if (get_option('lev2euro_enabled', '1') === '1' && get_woocommerce_currency() === 'BGN') {
        $position = get_option('lev2euro_position', 'under');
        
        // Initialize the plugin instance to access cart detection method
        static $lev2euro_instance = null;
        if ($lev2euro_instance === null) {
            $lev2euro_instance = new Lev2Euro();
        }
        
        echo '<style>
            /* Position-specific styling */';
        
        if ($position === 'under') {
            echo '
            /* Very specific selectors to override theme CSS */
            body .product-listing-price h5,
            body .product-listing-price h6,
            body .product-listing-price .price h5,
            body .product-listing-price .price h6,
            body .product-listing-price.price h5,
            body .product-listing-price.price h6 {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                white-space: normal !important;
                line-height: 1.4 !important;
            }
            
            /* Force display block for price elements within h5/h6 */
            body .product-listing-price h5 .lev2euro-original,
            body .product-listing-price h5 .lev2euro-sale,
            body .product-listing-price h5 .lev2euro-price,
            body .product-listing-price h6 .lev2euro-original,
            body .product-listing-price h6 .lev2euro-sale,
            body .product-listing-price h6 .lev2euro-price {
                display: block !important;
                width: 100% !important;
                margin: 0 0 3px 0 !important;
                padding: 0 !important;
                float: none !important;
                clear: both !important;
            }
            
            /* Hide the <br> tags since we are using CSS for layout */
            body .product-listing-price h5 br,
            body .product-listing-price h6 br {
                display: none !important;
            }
            
            /* Styling for sale price structure */
            
            .lev2euro-sale {
                font-weight: bold !important;
                text-decoration: none !important;
            }
        ';
        }
        
        // Add cart page specific CSS
        if (function_exists('is_cart') && is_cart()) {
            echo '
            /* Cart page specific styling */
            .woocommerce-cart-form #mobile-cart .product .product-price {
                flex-direction: column !important;
            }
            
            /* Cart totals styling */
            .cart-totals .cart-subtotal .subtotal-amount,
            .cart-totals .order-total .amount,
            .woocommerce-cart-form .cart-totals .shop_table .amount {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            /* Euro price styling in cart totals */
            .product-price{
                display: flex !important;
                flex-direction: column !important;
            }
            ';
        }
        
        echo '
        </style>';
    }
});

// Add schema.org microdata support for SEO
add_filter('woocommerce_structured_data_product_offer', function($markup, $product) {
    if (get_option('lev2euro_enabled', '1') === '1' && isset($markup['price'])) {
        // Keep original BGN price in structured data for SEO
        // Euro price is display-only and doesn't affect SEO
    }
    return $markup;
}, 10, 2);

add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables', // това е HPOS
            __FILE__,
            true
        );
    }
});