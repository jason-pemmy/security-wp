<?php
/**
 * @package   MasterSlider
 * @author    averta [averta.net]
 * @license   LICENSE.txt
 * @link      http://masterslider.com
 * @copyright Copyright © 2014 averta
*/

// no direct access allowed
if ( ! defined('ABSPATH') ) {
    die();
}



if( ! class_exists('Plugin_Upgrader') ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}



if( ! class_exists('Axiom_Plugin_Updater') ) {

    class Axiom_Plugin_Updater extends Plugin_Upgrader {

        /**
         * Plugin directory and main file name (e.g myplugin/myplugin.php)
         * 
         * @var string
         */
        public $plugin_slug;

        /**
         * Plugin slug
         * 
         * @var string
         */
        public $slug;

        /**
         * Installable update file name
         * 
         * @var string
         */
        public $installable_plugin_zip_file = '';


        /**
         * Set plugin info and add essential hooks
         * 
         * @param string $plugin_slug                 The name of directory and main file name of plugin
         * @param string $slug                        Then slug name of plugin (optional)
         * @param string $installable_plugin_zip_file Installable update file name. Default is {plugin_slug}-installable.zip
         */
        public function __construct( $plugin_slug, $slug = '', $installable_plugin_zip_file = '' ){
            
            parent::__construct();

            $this->plugin_slug = $plugin_slug;
            $parts = explode('/', $plugin_slug);

            $this->slug = empty( $slug ) ? str_replace('.php', '', $parts[1] ) : $slug;
            $this->installable_plugin_zip_file = empty( $installable_plugin_zip_file ) ? $this->slug . '-installable.zip' : $installable_plugin_zip_file;

            add_action( 'admin_init', array( $this, 'plugin_update_rows' ) , 12 );
            // a custom hook that fires on update.php page while upgrading package
            add_action( "update-custom_{$this->slug}-upgrade", array( $this, 'on_update_plugin' ) );
        }


        public function get_download_api() {
            return 'http://support.averta.net/envato/api/?branch=envato&group=items&cat=download-purchase';
        }


        function on_update_plugin() {
            
            if( apply_filters( $this->slug.'_disable_auto_update', 0 ) )
                return;
            
            $plugin = isset($_REQUEST['plugin']) ? trim($_REQUEST['plugin']) : '';

            if ( ! current_user_can('update_plugins') )
                wp_die(__('You do not have sufficient permissions to update plugins for this site.'));

            check_admin_referer('upgrade-plugin_' . $plugin);

            $title = __('Update Plugin');
            $parent_file = 'plugins.php';
            $submenu_file = 'plugins.php';
            require_once(ABSPATH . 'wp-admin/admin-header.php');

            $nonce = 'upgrade-plugin_' . $plugin;
            $url   = 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin );

            if ( $this->update_plugin() )
                do_action( $plugin . "_updated" );

            echo '<a href="' . self_admin_url('plugins.php') . '" title="' . esc_attr__('Go to plugins page') . '" target="_parent">' . __('Return to Plugins page') . '</a>';

            include( ABSPATH . 'wp-admin/admin-footer.php' );
        }




        public function get_download_url ( $username, $purchase_code, $token ) {

            if( $custom_download = apply_filters( 'axiom_plugin_updater_custom_package_download_url', 0 ) )
                return $custom_download;

            if( empty( $username ) || empty( $purchase_code ) ) {
                return new WP_Error( 'no_credentials', 
                                        apply_filters( 'axiom_plugin_updater_login_info_required', 
                                            __( 'Envato username, API key and your item purchase code are required for downloading updates from Envato marketplace.' ) , $this->slug
                                        ) 
                                    );
            }

            global $wp_version;

            $api_url = $this->get_download_api();
            
            $args = array(
                'user-agent' => 'WordPress/'.$wp_version.'; ' . get_site_url(),
                'timeout'    => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3), 
                'body' => array(
                    'action'    => 'token',
                    'key'       => $purchase_code,
                    'user'      => $username,
                    'token'     => $token,
                    'url'       => get_site_url()
                )
            );

            $request = wp_remote_post( $api_url, $args );

            if ( is_wp_error( $request ) || wp_remote_retrieve_response_code($request) !== 200 ) {
                
                $error_message = isset( $result['error'] ) ? $result['error'].'. ' : '';
                $error_code    = isset( $result['code'] ) ? $result['code']. '. ' : '';

                return new WP_Error( 'no_credentials', 
                                        apply_filters( 'axiom_plugin_updater_failed_connect_api', 
                                            __( 'Faild to connect to download API ..') . $error_message . $error_code , 
                                            $this->slug, $error_message , $error_code
                                        ) 
                                    );
            }
            $json = $request['body'];
            $result = json_decode( $request['body'], true );

            if( ! ( isset( $result['download_url'] ) && ! empty( $result['download_url'] ) ) ) {
                $result         = json_decode( $request['body'], true );
                $error_message  = isset( $result['msg'] )  ? $result['msg'].'. '   : '';
                $error_code     = isset( $result['code'] ) ? $result['code']. '. ' : '';

                // Envato API error ..
                return new WP_Error( 'no_credentials', 
                                        apply_filters( 'axiom_plugin_updater_api_error', 
                                            __( $json . 'Error on connecting to download API ..') . $error_message . ' [' . $error_code . ']' , 
                                            $this->slug, $error_message , $error_code
                                        ) 
                                    );
            }

            return $result['download_url'];
        }



        // download installable file and put it temporary folder for installation
        protected function get_downloaded_package_url() {

            global $wp_filesystem;

            $this->skin->feedback('download_item_package');

            $res = $this->fs_connect( array( WP_CONTENT_DIR ) );

            if ( ! $res ) {
                return new WP_Error('no_credentials', __( "Error! Failed to connect filesystem" ) );
            }

            $username       = msp_get_setting( 'username'      , 'msp_envato_license' );
            $purchase_code  = msp_get_setting( 'purchase_code' , 'msp_envato_license' );
            $token          = msp_get_setting( 'token'         , 'msp_envato_license' );


            $the_download_url = $this->get_download_url( $username, $purchase_code, $token );

            if( is_wp_error( $the_download_url ) )
                return $the_download_url;
            
            $download_file =  download_url( $the_download_url );
            
            if( is_wp_error( $download_file ) ) {
                return $download_file;
            }

            $upgrade_folder   = $wp_filesystem->wp_content_dir() . "upgrade_dir/{$this->slug}";
            $installable_file = trailingslashit( $upgrade_folder ) . $this->installable_plugin_zip_file;


            if ( $wp_filesystem->is_dir( $upgrade_folder ) ) {
                if ( ! $wp_filesystem->delete( $upgrade_folder ) ){
                    return new WP_Error( 'delete_failed_update_plugin', __( 'Could not clean installation directory.' ) . ' ['. $upgrade_folder .']', $upgrade_folder );
                }
            }

            if ( ! $wp_filesystem->mkdir( $upgrade_folder, FS_CHMOD_DIR ) ){
                return new WP_Error( 'mkdir_failed_update_plugin', __( 'Could not create directory.' ) . ' ['. $upgrade_folder .']', $upgrade_folder );
            }
            
            if ( ! $wp_filesystem->copy( $download_file, $installable_file, true, FS_CHMOD_FILE) ) {
                // If copy failed, chmod file to 0644 and try again.
                $wp_filesystem->chmod( $installable_file, FS_CHMOD_FILE );
                if ( ! $wp_filesystem->copy( $download_file, $installable_file, true, FS_CHMOD_FILE) ){
                    return new WP_Error( 'copy_failed_copy_file', __( 'Could not copy installable file.' ), $installable_file );
                }
            }

            // remove temp file
            unlink( $download_file );

            if( is_file( $installable_file ) ) {
                return $installable_file;
            }
            
            return new WP_Error( 'no_credentials', __( 'Error on installing archive file ..' ) );
        }

        // install installable zip package
        public function download_package( $package ) {
            $package = $this->get_downloaded_package_url();

            if( is_wp_error( $package ) ) 
                return $package;

            return parent::download_package( $package );
        }

        // this method calls 'download_package' function
        public function update_plugin() {
            global $wp_filesystem;

            $this->init();
            $this->upgrade_strings();

            $this->strings['download_item_package'] = apply_filters( 'axiom_plugin_updater_strings_downloading_package', __( 'Downloading package ...' ) );

            add_filter( 'upgrader_pre_install'      , array( $this, 'deactivate_plugin_before_upgrade' ), 10, 2);
            add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ), 10, 4);

            $this->run( array(
                'package'           => $this->slug,
                'destination'       => WP_PLUGIN_DIR,
                'clear_destination' => true,
                'clear_working'     => true,
                'hook_extra'        => array(
                    'plugin' => $this->slug
                )
            ));

            // Cleanup our hooks, in case something else does a upgrade on this connection.
            remove_filter( 'upgrader_pre_install'       , array( $this, 'deactivate_plugin_before_upgrade') );
            remove_filter( 'upgrader_clear_destination' , array( $this, 'delete_old_plugin') );

            if ( ! $this->result || is_wp_error( $this->result ) )
                return $this->result;

            if( is_dir( $wp_filesystem->wp_content_dir() . "upgrade_dir/{$this->slug}" ) ) {
                $wp_filesystem->delete( $wp_filesystem->wp_content_dir() . "upgrade_dir/{$this->slug}", true ) ;
            }

            // Flush plugin update information
            delete_site_transient( 'update_plugins' );
            wp_cache_delete( 'plugins', 'plugins' );

            return true;
        }


        public function plugin_update_rows() {

            if( apply_filters( $this->slug.'_disable_auto_update', 0 ) )
                return;

            remove_action( "after_plugin_row_{$this->plugin_slug}", 'wp_plugin_update_row', 10 );
            add_action( "after_plugin_row_{$this->plugin_slug}", array( $this, 'plugin_update_row' ), 10, 2 );
        }


        public function plugin_update_row( $file, $plugin_data ) {

            $current = get_site_transient( 'update_plugins' );

            if ( ! isset( $current->response[ $file ] ) )
                return false;

            $r = $current->response[ $file ];

            // if license is already actived (token is set), add temp download link
            $r->package = msp_get_setting('token', 'msp_envato_license') ? 'temp_package' : '';

            $plugins_allowedtags = array(
                'a'     => array(
                    'href'  => array(),'title' => array()
                ),
                'abbr'  => array(
                    'title' => array()
                ),
                'acronym' => array(
                    'title' => array()
                ),
                'code'  => array(),
                'em'    => array(),
                'strong'=> array()
            );
            
            $plugin_name    = wp_kses( $plugin_data['Name'], $plugins_allowedtags );
            $details_url    = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $r->slug . '&section=changelog&TB_iframe=true&width=600&height=800');
            $wp_list_table  = _get_list_table('WP_Plugins_List_Table');

            if ( is_network_admin() || !is_multisite() ) {
                echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

                if ( ! current_user_can('update_plugins') )
                    printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
                else if ( empty( $r->package ) )
                    printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. <em>Please visit %5$ssetting page%6$s to enable automatic update for this plugin.</em>' ), 
                           $plugin_name, esc_url( $details_url ), esc_attr( $plugin_name ), $r->new_version, '<a href="'.admin_url( 'admin.php?page='. $this->slug.'-setting' ).'">', '</a>' );
                else
                    printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update now</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version, wp_nonce_url( self_admin_url("update.php?action={$this->slug}-upgrade&plugin=") . $file, 'upgrade-plugin_' . $file) );

                
                do_action( "in_plugin_update_message-{$file}", $plugin_data, $r );

                echo '</div></td></tr>';
            }
        }
    }


    new Axiom_Plugin_Updater( MSWP_AVERTA_BASE_NAME );
}