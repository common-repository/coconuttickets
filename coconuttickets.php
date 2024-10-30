<?php
/*
Plugin Name: Coconut Tickets
Plugin URI: http://www.coconuttickets.com
Description: Sell the event tickets and pitches you defined on Coconut Tickets from your own WordPress site using this plugin, while knowing that Coconut Tickets will take care of the e-ticket fulfillment.
Version: 1.3
Author: coconuttickets.com
Author URI: http://www.coconuttickets.com
*/




if (!class_exists('CoconutTicketsEmbed')) {
    /**
     * Class CoconutTicketsEmbed
     *
     * Allows Coconut Tickets sales pages to be displayed within WordPress pages
     */
    class CoconutTicketsEmbed {
    	
        public function __construct() {
            add_action('wp_enqueue_scripts', array(
                $this,
                'enqueue_plugin_scripts'
            ));
            add_action('admin_enqueue_scripts', array(
            		$this,
            		'admin_enqueue_plugin_scripts'
            ));
            // 
            add_action('admin_notices', array(
                $this,
                'admin_notices'
            ));
            add_shortcode('coconuttickets', array(
                $this,
                'display_tickets'
            ));
            add_action( 'wp_ajax_coconuttickets_accepted_notice',
                array($this,'accepted_admin_notice'
            ));

            // Admin specific setup
            if (is_admin()) {
    			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			}
		}


        
		public function enqueue_plugin_scripts() {

            /** iFrame resizer to ensure embedded frame doesn't clip the coconuttickets.com page  **/
            wp_enqueue_script('iframeResizer.js', plugins_url('/js/iframeResizer.min.js', __FILE__), array(
                'jquery'
            ));
        }
        
        /**
         * Only load these if we are going to display the admin data management pages
         */
        public function admin_enqueue_plugin_scripts() {

        	wp_enqueue_script('coconuttickets_js', plugins_url('/js/coconuttickets_js.js', __FILE__), array(
        			'jquery'
        	));

        	wp_enqueue_style('coconuttickets_style.css', plugins_url('css/coconuttickets_style.css', __FILE__), array(
        	));

        }
        

        /**
         * Warn the administrator that they need to have an account with coconuttickets.com
         */
        public function admin_notices() {
            $notice_accepted = get_option('coconuttickets_notice');
            if( empty($notice_accepted)) {
                ?>
                <div class="notice notice-success coconuttickets_dismiss is-dismissible">
                    <p>To sell event tickets and vendor pitches with this plugin you will need to create an account with <a href="//coconuttickets.com" target="_blank">Coconut Tickets</a></p>
                </div>
                <?php
            }
        }

        /**
         * Flag the admin notice as having been accepted so that it is not displayed again
         */
        public function accepted_admin_notice() {
            header('Content-Type: application/json'); // State that the following message is JSON
            $result = update_option('coconuttickets_notice', 1 );
            echo json_encode($result);
            wp_die();
        }

        protected function parameterOverride($definedUrl) {
            $definedUrl = str_replace("&amp;", "&", $definedUrl);
            $index = strpos($definedUrl, '?');
            if( $index  === false) {
                // unable to find the beginning of the parameters in the URL
                return $definedUrl;
            }
            $url = substr($definedUrl, 0, $index);
            $params = substr($definedUrl, $index +1);
            $defineParamArr = array();
            parse_str($params, $defineParamArr);

            foreach($_GET as $key => $value) {
                if(strpos($key, 'ct_') === 0) {
                    // value starts with ct_
                    $coconutKey = substr($key, 3);
                    $defineParamArr[$coconutKey] = $value;
                }
            }

            $newUrl = $url . '?' . http_build_query($defineParamArr);

            return $newUrl;
        }

        /**
         * Display the ticket sales tool on the page where this shortcode is embedded
         */
        public function display_tickets($atts) {
        	$a = shortcode_atts( array(
        	 'url' => "https://coconuttickets.com"	// Default URL if not supplied
        	 ), $atts );

        	$targetUrl = $this->parameterOverride($a['url']);   // allow dynamic addition/overwrite of Coconut Tickets parameters

            ob_start();
        	?>
            <iframe name="coconuttickets-frame" id="coconuttickets-frame" width=100%" marginwidth="3" marginheight="1" border="0" frameborder="0"  src="<?php echo $targetUrl; ?>" scrolling="no">
            Warning: Your browser does not support inline frames or is currently configured not to display inline frames.
            </iframe>

            <script type="text/javascript"> jQuery("#coconuttickets-frame").iFrameResize( {log:false} );</script>

            <?php
            return ob_get_clean();
        }
        
        /**
         * Add plugin menus and submenus
         */
        public function add_plugin_page()
        {
        	// create a new top level menu
        	add_menu_page(
        	        "Coconut Tickets",
                    "Coconut Tickets",
                    "manage_options",
                    "coconuttickets",
                    array( $this, 'create_admin_page' ),
                    plugins_url( 'img/coconuttickets_icon.png', __FILE__ )
            );
        }
        
        /**
         * Display the coconut tickets admin page within the admin dashboard
         */
        public function create_admin_page()
        {
        	?>
                <div class="wrap coconuttickets-admin">
                    <h2>Coconut Tickets sales plugin for WordPress</h2>
                    <p>To sell event tickets and vendor pitches with this plugin you will need to create an account with <a href="//coconuttickets.com" target="_blank">Coconut Tickets</a></p>
                    <h3>Selling Tickets through WordPress</h3>
                    <p>Once you have defined your tickets and pitches on the Coconut Tickets website, you then have the choice to
                        display the sales page for your tickets from within your website (using this plugin) or to have your customers click
                        on a link on your website and be redirected to Coconut Tickets. Both of these approaches allow the customer to complete
                        their purchase on the secure Coconut Tickets website.
                    </p>
                    <h3>To embed the Coconut Tickets sales page within your WordPress site.</h3>
                    <ol>
                        <li>Visit your Coconut Tickets dashboard.</li>
                        <li>All your events will be listed on the event dashboard under "Your Events".</li>
                        <li>Find the event you wish to sell from WordPress and click on the "view" link for that event and the event admin will open.</li>
                        <li>On the left sidebar of event admin look for the "Publish links" option.</li>
                        <li>From the list of publishing options, click the WordPress icon</li>
                        <li>Then click the copy icon (at the bottom right of the list of icons) and the WordPress short code will be copied to the clipboard.</li>
                        <li>Go to your WordPress site where you wish to sell your tickets</li>
                        <li>Edit the page on which you wish to display the Coconut Tickets sales tool</li>
                        <li>Create a new WP Shortcode block in the block editor and paste the short code that was copied from Coconut Tickets</li>
                        <li>Save the page and display it</li>
                        <li>You should see tickets for your event available for selection and purchase</li>
                    </ol>
                    <p>For a more detailed explanation please see the link below</p>
                    <a href="https://coconuttickets.com/blog/linking-wordpress-coconut-tickets/">Linking WordPress to Coconut Tickets</a>
                </div>
            <?php
        }
    }
}


// make the plugin object available within GLOBALS super variable
if (class_exists('CoconutTicketsEmbed')) {
	$GLOBALS['coconutticketsembed'] = new CoconutTicketsEmbed();
}


?>
