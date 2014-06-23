<?php
/*
Plugin Name: DB manager
Plugin URI: http://bestwebsoft.com/plugin/
Description: The DB manager plugin allows you to download the latest version of PhpMyadmin and Dumper and manage your site.
Author: BestWebSoft
Version: 1.0.2
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
	© Copyright 2014  BestWebSoft  ( http://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'dbmngr_add_admin_menu' ) ) {
	function dbmngr_add_admin_menu() {
		global $bstwbsftwppdtplgns_options, $wpmu, $bstwbsftwppdtplgns_added_menu;
		$bws_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) . "bws_menu/bws_menu.php" );
		$bws_menu_version = $bws_menu_info["Version"];
		$base = plugin_basename( __FILE__ );

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( 1 == $wpmu ) {
				if ( ! get_site_option( 'bstwbsftwppdtplgns_options' ) )
					add_site_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_site_option( 'bstwbsftwppdtplgns_options' );
			} else {
				if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
					add_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
			}
		}

		if ( isset( $bstwbsftwppdtplgns_options['bws_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			unset( $bstwbsftwppdtplgns_options['bws_menu_version'] );
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] < $bws_menu_version ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_added_menu ) ) {
			$plugin_with_newer_menu = $base;
			foreach ( $bstwbsftwppdtplgns_options['bws_menu']['version'] as $key => $value ) {
				if ( $bws_menu_version < $value && is_plugin_active( $base ) ) {
					$plugin_with_newer_menu = $key;
				}
			}
			$plugin_with_newer_menu = explode( '/', $plugin_with_newer_menu );
			$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
			if ( file_exists( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' ) )
				require_once( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' );
			else
				require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
			$bstwbsftwppdtplgns_added_menu = true;
		}

		add_menu_page( 'BWS Plugins', 'BWS Plugins', 'manage_options', 'bws_plugins', 'bws_add_menu_render', plugins_url( "images/px.png", __FILE__ ), 1001 );
		add_submenu_page( 'bws_plugins', __( 'DB manager Settings', 'dbmngr' ), __( 'DB manager', 'dbmngr' ), 'manage_options', "db-manager.php", 'dbmngr_settings_page' );

	}
}

/* Specify the path to the languages */
if ( ! function_exists ( 'dbmngr_plugin_init' ) ) {
	function dbmngr_plugin_init() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'dbmngr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'dbmngr_plugin_admin_init' ) ) {
	function dbmngr_plugin_admin_init() {
 		global $dbmngr_plugin_info, $bws_plugin_info;

 		$dbmngr_plugin_info = get_plugin_data( __FILE__, false );

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '131', 'version' => $dbmngr_plugin_info["Version"] );

		/* Check version on WordPress */
		dbmngr_version_check();

		/* Call register settings function */
		if ( isset( $_GET['page'] ) && "db-manager.php" == $_GET['page'] )
			register_dbmngr_settings();
	}
}

/* Function check if plugin is compatible with current WP version */
if ( ! function_exists ( 'dbmngr_version_check' ) ) {
	function dbmngr_version_check() {
		global $wp_version, $dbmngr_plugin_info;
		$require_wp		=	"3.0.1"; /* Wordpress at least requires version */
		$plugin			=	plugin_basename( __FILE__ );
	 	if ( version_compare( $wp_version, $require_wp, "<" ) ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "<strong>" . $dbmngr_plugin_info['Name'] . " </strong> " . __( 'requires', 'dbmngr' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'dbmngr' ) . "<br /><br />" . __( 'Back to the WordPress', 'dbmngr' ) . " <a href='" . get_admin_url( null, 'plugins.php' ) . "'>" . __( 'Plugins page', 'dbmngr' ) . "</a>." );
			}
		}
	}
}

/* Register settings function */
if ( ! function_exists( 'register_dbmngr_settings' ) ) {
	function register_dbmngr_settings() {
		global $wpmu, $dbmngr_options, $dbmngr_plugin_info;

		if ( ! $dbmngr_plugin_info )
			$dbmngr_plugin_info = get_plugin_data( __FILE__ );

		$dbmngr_option_defaults = array(
			'access_dumper'						=> '1',
			'access_phpmyadmin'					=> '1',
			'existence_dumper'					=> '1',
			'existence_phpmyadmin'				=> '1',
			'plugin_option_version' 			=> $dbmngr_plugin_info["Version"]
		);
		/* Install the option defaults */
		if ( 1 == $wpmu ) {
			if ( ! get_site_option( 'dbmngr_options' ) ) {
				add_site_option( 'dbmngr_options', $dbmngr_option_defaults, '', 'yes' );
			}
		} else {
			if ( ! get_option( 'dbmngr_options' ) )
				add_option( 'dbmngr_options', $dbmngr_option_defaults, '', 'yes' );
		}
		/* Get options from the database */
		if ( 1 == $wpmu )
			$dbmngr_options = get_site_option( 'dbmngr_options' );
		else
			$dbmngr_options = get_option( 'dbmngr_options' );
		/* Array merge incase this version has added new options */
		if ( ! isset( $dbmngr_options['plugin_option_version'] ) || $dbmngr_options['plugin_option_version'] != $dbmngr_plugin_info["Version"] ) {
			$dbmngr_options = array_merge( $dbmngr_option_defaults, $dbmngr_options );
			$dbmngr_options['plugin_option_version'] = $dbmngr_plugin_info["Version"];
			update_option( 'dbmngr_options', $dbmngr_options );
		}
	}
}

/* Access to page settings */
if ( ! function_exists( 'dbmngr_plugin_action_links' ) ) {
	function dbmngr_plugin_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=db-manager.php">' . __( 'Settings', 'dbmngr' ) . '</a>';
				array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

/* Additional links on the plugin page */
if ( ! function_exists( 'dbmngr_register_plugin_links' ) ) {
	function dbmngr_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=db-manager.php">' . __( 'Settings', 'dbmngr' ) . '</a>';
			$links[] = '<a href="http://bestwebsoft.com/plugin/db-manager/#faq" target="_blank">' . __( 'FAQ', 'dbmngr' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'dbmngr' ) . '</a>';
		}
		return $links;
	}
}

/* Method returns content page */
if ( ! function_exists( 'dbmngr_file_get_contents_curl' ) ) {
	function dbmngr_file_get_contents_curl( $dbmngr_url ) {
		if ( function_exists( 'curl_init' ) ) {
			$dbmngr_defaults = array(
				CURLOPT_HEADER => 0,
				CURLOPT_URL => $dbmngr_url,
				CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.0" ,
				CURLOPT_RETURNTRANSFER => 1
			);
			$dbmngr_ch = curl_init();
			curl_setopt_array( $dbmngr_ch, $dbmngr_defaults );
			/* Check connected to the internet */
			if ( ! $dbmngr_result = curl_exec( $dbmngr_ch ) ) {				
				echo '<div class="error"><p><strong>' . curl_error( $dbmngr_ch ) . __( 'Check your internet connection!', 'dbmngr' ) . '</strong></p></div>';
				?><style>
					.form-table {
						display: none;
					}
				</style><?php
				return false;
			}
			curl_close( $dbmngr_ch );
			return $dbmngr_result;
		}
	}
}

/* Method returns a last version phpMyAdmin */
if ( ! function_exists( 'dbmngr_pma_last_version_parser' ) ) {
	function dbmngr_pma_last_version_parser( $dbmngr_url ) {
		$dbmngr_data = dbmngr_file_get_contents_curl( $dbmngr_url ); /* Gets the content of this page */
		if ( false != $dbmngr_data ) {
			preg_match_all( "/<a.*?>.+<\/a>/", $dbmngr_data, $dbmngr_result ); /* $result here set an array of all links */
			$dbmngr_temp = $dbmngr_result[0][2];
			$dbmngr_notag = strip_tags( $dbmngr_temp );
			$dbmngr_version = preg_replace( "/[^0-9.]/", '', $dbmngr_notag );
			return $dbmngr_version;
		}
	}
}

/* Method returns a last version dumper */
if ( ! function_exists( 'dbmngr_dumper_last_version_parser' ) ) {
	function dbmngr_dumper_last_version_parser( $dbmngr_url ) {
		$dbmngr_temp = '';
		$dbmngr_data = dbmngr_file_get_contents_curl( $dbmngr_url );
		if ( false != $dbmngr_data ) {
			preg_match_all( "/<A.*?>.+<\/A>/", $dbmngr_data, $dbmngr_result );
			if ( isset( $dbmngr_result[0][1] ) )
				$dbmngr_temp = $dbmngr_result[0][1];
			$dbmngr_notag = strip_tags( $dbmngr_temp );
			$dbmngr_version = preg_replace( "/[^0-9.]/", '', $dbmngr_notag );
			return $dbmngr_version;
		}
	}
}

/* Method returns a reference on dumper */
if ( ! function_exists( 'dbmngr_dumper_get_href_parser' ) ) {
	function dbmngr_dumper_get_href_parser( $dbmngr_url ) {
		$dbmngr_data = dbmngr_file_get_contents_curl( $dbmngr_url );
		if ( false != $dbmngr_data ) {
			preg_match_all( "/<A.*?>.+<\/A>/", $dbmngr_data, $dbmngr_result );
			$dbmngr_temp = $dbmngr_result[0][1];
			$dbmngr_link = substr( $dbmngr_temp, 1 );
			preg_match_all( "/\"(.*?)\"/i", $dbmngr_link, $matches );
			$dbmngr_link_result = str_replace( " ", "", $matches[1][0] );
			return $dbmngr_link_result;
		}
	}
}

/* Method returns a reference on phpMyAdmin */
if ( ! function_exists( 'dbmngr_pma_get_href_parser' ) ) {
	function dbmngr_pma_get_href_parser( $dbmngr_url ) {
		$dbmngr_data = dbmngr_file_get_contents_curl( $dbmngr_url );
		if ( false != $dbmngr_data ) {
			preg_match_all( "/<a.*?>.+<\/a>/", $dbmngr_data, $dbmngr_result );
			$dbmngr_temp = $dbmngr_result[0][2];
			$dbmngr_link = substr( $dbmngr_temp, 1);
			preg_match_all( "/\"(.*?)\"/i", $dbmngr_link, $matches );
			return $matches[1][0];
		}
	}
}

/* Method for download using curl */
if ( ! function_exists( 'dbmngr_curl_download' ) ) {
	function dbmngr_curl_download( $dbmngr_url, $dbmngr_file ) {
		if ( function_exists( 'curl_init' ) ) {
			global $dbmngr_error;
			/* open the file on the server write */
			if ( $dbmngr_fp = @fopen( $dbmngr_file, 'w' ) ) { 
				/* open cURL-session */
				$dbmngr_ch = curl_init( $dbmngr_url );
				/* set the place on the server where the file will be copied to the remote */
				curl_setopt( $dbmngr_ch, CURLOPT_FILE, $dbmngr_fp );
				curl_setopt( $dbmngr_ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.0" );
				/* perform the operation */
				$dbmngr_data = curl_exec( $dbmngr_ch );
				/* close cURL-session */
				curl_close( $dbmngr_ch );
				/* close file */
				fclose( $dbmngr_fp );
			} else {
				$dbmngr_error = __( 'Create file Error!' .' '. $dbmngr_file . '<br>', 'dbmngr' );
			}
		}
	}
}

/* Method for delete database managers */
if ( ! function_exists( 'dbmngr_remove_directory' ) ) {
	function dbmngr_remove_directory( $dbmngr_path ) {
		$dbmngr_dir_handle = opendir( $dbmngr_path );
		while ( false !== ( $dbmngr_file = readdir( $dbmngr_dir_handle ) ) ) {
			if ( $dbmngr_file != '.' && $dbmngr_file != '..' ) { /* exclude the folder with the name '.' and '..' */
				$dbmngr_tmp_path = $dbmngr_path . '/' . $dbmngr_file;
				chmod( $dbmngr_tmp_path, 0777 );
				if ( is_dir( $dbmngr_tmp_path ) ) {
					dbmngr_remove_directory( $dbmngr_tmp_path );
				} elseif ( file_exists( $dbmngr_tmp_path ) ) {
					/* Remove file */
					unlink( $dbmngr_tmp_path );
				}
			}
		}
		closedir( $dbmngr_dir_handle );
		/* Remove the current folder */
		if ( file_exists( $dbmngr_path ) ) {
			rmdir( $dbmngr_path );
		}
	}
}

/* Method download through redirect */
if ( ! function_exists( 'dbmngr_curl_redirect' ) ) {
	function dbmngr_curl_redirect( $dbmngr_ch ) {
		static $dbmngr_curl_loops = 0;
		static $dbmngr_curl_max_loops = 20;
		if ( $dbmngr_curl_loops >= $dbmngr_curl_max_loops ) {
			$dbmngr_curl_loops = 0;
			return false;
		}
		curl_setopt( $dbmngr_ch, CURLOPT_HEADER, true );
		curl_setopt( $dbmngr_ch, CURLOPT_RETURNTRANSFER, true );
		$dbmngr_data = curl_exec( $dbmngr_ch );
		@list( $dbmngr_header, $dbmngr_data ) = explode( "\n\n", $dbmngr_data, 2 );
		$dbmngr_http_code = curl_getinfo( $dbmngr_ch, CURLINFO_HTTP_CODE );

		if ( $dbmngr_http_code == 301 || $dbmngr_http_code == 302 ) {
			$matches = array();
			preg_match( '/Location:(.*?)\n/', $dbmngr_header, $dbmngr_matches );
			$dbmngr_url = @parse_url( trim( array_pop( $dbmngr_matches ) ) );
			if ( ! $dbmngr_url ) {
				$dbmngr_curl_loops = 0;
				return $dbmngr_data;
			}
			$dbmngr_last_url = parse_url( curl_getinfo( $dbmngr_ch, CURLINFO_EFFECTIVE_URL ) );
			if ( ! $dbmngr_url['scheme'] )
				$dbmngr_url['scheme'] = $dbmngr_last_url['scheme'];
			if ( ! $dbmngr_url['host'] )
				$dbmngr_url['host'] = $dbmngr_last_url['host'];
			if ( ! $dbmngr_url['path'] )
				$dbmngr_url['path'] = $dbmngr_last_url['path'];
			$dbmngr_new_url = $dbmngr_url['scheme'] . '://' . $dbmngr_url['host'] . $dbmngr_url['path'] . ( @$dbmngr_url['query']?'?' . $dbmngr_url['query']:'' );
			/* Method is called recursively until the redirect will not stop */
			dbmngr_curl_download( $dbmngr_new_url, plugin_dir_path( __FILE__ ) . 'phpmyadmin.zip' );
			curl_setopt( $dbmngr_ch, CURLOPT_URL, $dbmngr_new_url );
			return dbmngr_curl_redirect( $dbmngr_ch );
		} else {
			$dbmngr_curl_loops = 0;
			return $dbmngr_data;
		}
	}
}

/* Unpacking on the server */
if ( ! function_exists( 'dbmngr_unzip_new_catalog' ) ) {
	function dbmngr_unzip_new_catalog( $dbmngr_input_name, $dbmngr_output_name ) {
		global $dbmngr_error;
		$dbmngr_user_info = get_userdata( 1 );
		$dbmngr_new_name_catalog = md5( get_option( 'siteurl' ) . time() . $dbmngr_user_info->user_login );
		/* Unpacking the server */
		$dbmngr_zip = new ZipArchive;
		$dbmngr_res = $dbmngr_zip->open( plugin_dir_path( __FILE__ ) . $dbmngr_input_name );
		if ( $dbmngr_res === true ) {
			if ( $dbmngr_zip->extractTo( plugin_dir_path( __FILE__ ) . $dbmngr_output_name . '/' . $dbmngr_new_name_catalog ) )
				$dbmngr_zip->close();
			else
				$dbmngr_error = __( 'Error: Extracting failed!', 'dbmngr' );
		} else {
			$dbmngr_error = __( 'Error: Unpacking failed!', 'dbmngr' );
		}
		return $dbmngr_new_name_catalog;
	}
}

/* Download dumper */
if ( ! function_exists( 'dbmngr_download_dumper' ) ) {
	function dbmngr_download_dumper( $key ) {
		$dbmngr_url_dumper_download = dbmngr_dumper_get_href_parser( 'http://sypex.net/ru/products/dumper/downloads/' );
		$dbmngr_url_dumper_temp = 'http://sypex.net/';
		$dbmngr_url_dumper = $dbmngr_url_dumper_download;
		dbmngr_curl_download( $dbmngr_url_dumper_temp . $dbmngr_url_dumper, plugin_dir_path( __FILE__ ) . 'dumper.zip' );
		/* Unpacking zip archive */
		$dbmngr_new_name_catalog = dbmngr_unzip_new_catalog( 'dumper.zip', 'dumper' );
		/* Delete zip archive */
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper.zip' ) ) {
			unlink( plugin_dir_path( __FILE__ ) . 'dumper.zip' );
		}
		/* Check file exist */
		if ( is_dir( plugin_dir_path( __FILE__ ) . 'dumper/'.$dbmngr_new_name_catalog . '/sxd' ) ) {
			/* Add config dumper */
			$start_dir = plugin_dir_path( __FILE__ ) . 'temp/auth_wp3.php';
			$end_dir = plugin_dir_path( __FILE__ ) . 'dumper/'.$dbmngr_new_name_catalog . '/sxd/auth_wp3.php';
			$start_dir_cfg = plugin_dir_path( __FILE__ ) . 'temp/cfg.php';
			$end_dir_cfg = plugin_dir_path( __FILE__ ) . 'dumper/' . $dbmngr_new_name_catalog . '/sxd/cfg.php';
			copy( $start_dir, $end_dir );
			copy( $start_dir_cfg, $end_dir_cfg );
			/* Create a dynamic .htpasswd file */
			dbmngr_file_access( 'dumper/.htaccess', 'dumper/.htpasswd', 'dumper' );
			dbmngr_file_password( 'dumper/.htpasswd', $key );
			/* Add option */
			$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
			$dbmngr_phpmyadmin_option['catalog_dumper'] = $dbmngr_new_name_catalog;
			$dbmngr_phpmyadmin_option['version_dumper'] = dbmngr_dumper_last_version_parser( 'http://sypex.net/ru/products/dumper/downloads/' );
			update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
		} else {
			return 'Error';
		}

	}
}

/* Download phpmyadmin */
if ( ! function_exists( 'dbmngr_download_phpmyadmin' ) ) {
	function dbmngr_download_phpmyadmin( $key ) {
		if ( function_exists( 'curl_init' ) ) {
			$dbmngr_ch = curl_init();
			$dbmngr_target_url = 'http://www.phpmyadmin.net/home_page/index.php';
			curl_setopt( $dbmngr_ch, CURLOPT_URL, dbmngr_pma_get_href_parser( $dbmngr_target_url ) ); /* Link to phpmyadmin. ( redirect ) */
			curl_setopt( $dbmngr_ch, CURLOPT_TIMEOUT, 60 ); /* How time, wait for a response server */
			$dbmngr_page = dbmngr_curl_redirect( $dbmngr_ch );
			$dbmngr_page = curl_exec( $dbmngr_ch );
			curl_close( $dbmngr_ch );
		}
		/* Unpacking zip archive */
		$dbmngr_new_name_catalog = dbmngr_unzip_new_catalog( 'phpmyadmin.zip', 'phpmyadmin' );
		/* Delete zip archive */
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin.zip' ) ) {
			unlink( plugin_dir_path( __FILE__ ) . 'phpmyadmin.zip' );
		}
		/* Check file exist */
		$dbmngr_temp = dbmngr_pma_get_href_parser( $dbmngr_target_url );
		$path = explode( '/', $dbmngr_temp );
		foreach ( $path as $path ) {
			if ( strpos( $path, '.zip' ) ) {
				$dbmngr_str = ( basename( $path, ".zip" ) );
				break;
			}
		}
		if ( is_dir( plugin_dir_path( __FILE__ ) . 'phpmyadmin/' . $dbmngr_new_name_catalog . '/' . $dbmngr_str ) ) {
			/* Add config phpmyadmin */
			$start_dir = plugin_dir_path( __FILE__ ) . 'temp/config.inc.php';
			$end_dir = plugin_dir_path( __FILE__ ) . 'phpmyadmin/' . $dbmngr_new_name_catalog . '/' . $dbmngr_str . '/config.inc.php';
			copy( $start_dir, $end_dir );
			/* Create a dynamic .htaccess file */
			dbmngr_file_access( 'phpmyadmin/.htaccess', 'phpmyadmin/.htpasswd', 'phpmyadmin' );
			dbmngr_file_password( 'phpmyadmin/.htpasswd', $key );
			/* Add option */
			$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
			$dbmngr_phpmyadmin_option['catalog_phpmyadmin'] = $dbmngr_new_name_catalog;
			$dbmngr_phpmyadmin_option['version_phpmyadmin'] = dbmngr_pma_last_version_parser( 'http://www.phpmyadmin.net/home_page/index.php' );
			update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
		} else {
			return 'Error';
		}
	}
}

/* Create a dynamic .htaccess file for disable database managers */
if ( ! function_exists( 'dbmngr_off_access_pma' ) ) {
	function dbmngr_off_access_pma( $dbmngr_path ) {
		global $dbmngr_error;
		/* Reate the .htaccess file at the root directory */
		$dbmngr_create_name = plugin_dir_path( __FILE__ ) . $dbmngr_path;
		if ( is_writable( $dbmngr_create_name ) ) {
			/* Open the .htaccess file for editing */
			$dbmngr_file_handle = fopen( $dbmngr_create_name, 'w' );
			/* Enter the contents */
			$dbmngr_content_string = "deny from all";
			fwrite( $dbmngr_file_handle, $dbmngr_content_string );
			fclose( $dbmngr_file_handle );
		} else {
			$dbmngr_error = __( 'Disable function does not work! File is not writable!' .' '. $dbmngr_create_name . '<br>', 'dbmngr' );
		}
	}
}

/* Create a dynamic .htaccess file for enable database managers */
if ( ! function_exists( 'dbmngr_file_access' ) ) {
	function dbmngr_file_access( $dbmngr_path, $dbmngr_path_pwd, $dbmngr_auth_name ) {
		global $dbmngr_error;
		/* Create the .htaccess file at the root directory */
		$dbmngr_create_name = plugin_dir_path( __FILE__ ) . $dbmngr_path;
		/* Ccreate if not exist or open the .htaccess file for editing */
		if ( ! $dbmngr_file_handle = @fopen( $dbmngr_create_name, 'w' ) ) { 
			$dbmngr_error = __( 'Impossible to open the file!' .' '. $dbmngr_create_name . '<br>', 'dbmngr' );
		} 
		if ( is_writable( $dbmngr_create_name ) ) {
			/* -Wwritabel the contents */
			$dbmngr_content_string = "AuthType Basic\n";
			fwrite( $dbmngr_file_handle, $dbmngr_content_string );
			$dbmngr_content_string = "AuthName " . $dbmngr_auth_name . "\n";
			fwrite( $dbmngr_file_handle, $dbmngr_content_string );
			$dbmngr_content_string = "AuthUserFile " . plugin_dir_path( __FILE__ ) . $dbmngr_path_pwd . "\n";
			fwrite( $dbmngr_file_handle, $dbmngr_content_string );
			$dbmngr_content_string = "require valid-user";
			fwrite( $dbmngr_file_handle, $dbmngr_content_string );
			fclose( $dbmngr_file_handle );
		} else {
			$dbmngr_error = __( 'Enable not work! File is not writable!' .' '. $dbmngr_create_name . '<br>', 'dbmngr' );
		}
	}
}

/* Create password */
if ( ! function_exists( 'dbmngr_crypt_apr1_md5' ) ) {
	function dbmngr_crypt_apr1_md5( $dbmngr_plainpasswd ) {
		$dbmngr_tmp = null;
		$dbmngr_salt = substr( str_shuffle( "abcdefghijklmnopqrstuvwxyz0123456789" ), 0, 8 );
		$dbmngr_len = strlen( $dbmngr_plainpasswd );
		$dbmngr_text = $dbmngr_plainpasswd . '$apr1$' . $dbmngr_salt;
		$dbmngr_bin = pack( "H32", md5($dbmngr_plainpasswd . $dbmngr_salt . $dbmngr_plainpasswd ) );
		for ( $i = $dbmngr_len; $i > 0; $i -= 16 ) {
			$dbmngr_text .= substr( $dbmngr_bin, 0, min(16, $i ) );
		}
		for ( $i = $dbmngr_len; $i > 0; $i >>= 1 ) {
			$dbmngr_text .= ( $i & 1 ) ? chr( 0 ) : $dbmngr_plainpasswd{ 0 };
		}
		$dbmngr_bin = pack( "H32", md5( $dbmngr_text ) );
		for ( $i = 0; $i < 1000; $i++ ) {
			$dbmngr_new = ( $i & 1 ) ? $dbmngr_plainpasswd : $dbmngr_bin;
			if ( $i % 3 ) $dbmngr_new .= $dbmngr_salt;
			if ( $i % 7 ) $dbmngr_new .= $dbmngr_plainpasswd;
			$dbmngr_new .= ( $i & 1 ) ? $dbmngr_bin : $dbmngr_plainpasswd;
			$dbmngr_bin = pack( "H32", md5( $dbmngr_new ) );
		}
		for ( $i = 0; $i < 5; $i++ ) {
			$k = $i + 6;
			$j = $i + 12;
			if ( $j == 16 ) $j = 5;
			$dbmngr_tmp = $dbmngr_bin[$i] . $dbmngr_bin[$k] . $dbmngr_bin[$j] . $dbmngr_tmp;
		}
		$dbmngr_tmp = chr( 0 ) . chr( 0 ) . $dbmngr_bin[ 11 ] . $dbmngr_tmp;
		$dbmngr_tmp = strtr( strrev( substr( base64_encode( $dbmngr_tmp ), 2 ) ),
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz" );
		return "$" . "apr1" . "$" . $dbmngr_salt . "$" . $dbmngr_tmp;
	}
}

/* Create random string */
if ( ! function_exists( 'dbmngr_generate_random_string' ) ) {
	function dbmngr_generate_random_string( $dbmngr_length ) {
		$dbmngr_characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$dbmngr_random_string = '';
		for ( $i = 0; $i < $dbmngr_length; $i++ ) {
			$dbmngr_random_string .= $dbmngr_characters[ rand( 0, strlen( $dbmngr_characters ) - 1 ) ];
		}
		return $dbmngr_random_string;
	}
}

/* Create a dynamic .htpasswd file */
if ( ! function_exists( 'dbmngr_file_password' ) ) {
	function dbmngr_file_password( $dbmngr_path, $key ) {
		global $dbmngr_error;
		$dbmngr_user_info = get_userdata( 1 );
		$dbmngr_login = $dbmngr_user_info->user_login;
		$dbmngr_password = dbmngr_generate_random_string( 5 );
		$dbmngr_hash_password = dbmngr_crypt_apr1_md5( $dbmngr_password );
		/* Add option */
		$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
		$dbmngr_phpmyadmin_option['user_name'] = $dbmngr_login;
		update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
		if ( isset( $_GET['action'] ) ) {
			if ( 'pma' == $_GET["action"] || 'on-pma' == $_GET["action"] || 'update-phpmyadmin' == $_GET["action"] ) {
				$dbmngr_phpmyadmin_option['user_password_phpmyadmin'] = $dbmngr_password;
				update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
			}
			if ( 'dumper' == $_GET["action"] || 'on-dumper' == $_GET["action"] || 'update-dumper' == $_GET["action"] ) {
				$dbmngr_phpmyadmin_option['user_password_dumper'] = $dbmngr_password;
				update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
			}
		} elseif ( 'pma' == $key ) {
				$dbmngr_phpmyadmin_option['user_password_phpmyadmin'] = $dbmngr_password;
				update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
		} elseif ( 'dumper' == $key ) {
				$dbmngr_phpmyadmin_option['user_password_dumper'] = $dbmngr_password;
				update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
		}
		/* Create the .htaccess file at the root directory */
		$dbmngr_create_name = plugin_dir_path( __FILE__ ) . $dbmngr_path;
		/* Open the .htaccess file for editing */
		if ( $dbmngr_file_handle = @fopen( $dbmngr_create_name, 'w' ) ) {
			/* -Eenter the contents */
			$dbmngr_content_string = $dbmngr_login . ":" . $dbmngr_hash_password;
			fwrite( $dbmngr_file_handle, $dbmngr_content_string );
			fclose( $dbmngr_file_handle );
		} else {
			$dbmngr_error = __( 'Impossible to open the file!' .' '. $dbmngr_create_name . '<br>', 'dbmngr' );
		}
	}
}

/* Detect Internet Explorer */
if ( ! function_exists( 'dbmngr_detect_ie' ) ) {
	function dbmngr_detect_ie() {
		if ( isset($_SERVER['HTTP_USER_AGENT'] ) && ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== false ) ) {
			return 'true';
		} else {
			return 'false';
		}	
	}
}

/* Controller */
if ( ! function_exists( 'dbmngr_controller' ) ) {
	function dbmngr_controller() {
		global $dbmngr_error;
		$dbmngr_arr_message = array();
		if ( isset( $_GET['action'] ) ) {
			if ( 'pma' == $_GET["action"] ) { /* If press button download */
				if ( 'Error' == dbmngr_download_phpmyadmin( null ) )
					$dbmngr_error = __( 'Loading file Error!', 'dbmngr' );
				else {
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['existence_phpmyadmin'] = '0';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
				}
				if ( ! isset( $dbmngr_error ) ) {
					$dbmngr_arr_message = array( 'submit_download_pma' => __( 'PhpMyadmin loaded successfully!', 'dbmngr' ) );
				}
			} elseif ( 'dumper' == $_GET["action"] ) {				
				if ( 'Error' == dbmngr_download_dumper( null ) )
					$dbmngr_error = __( 'Loading file Error!', 'dbmngr' );
				else {
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['existence_dumper'] = '0';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
				}
				if ( ! isset( $dbmngr_error ) ) {
					$dbmngr_arr_message = array( 'submit_download_dumper' => __( 'Dumper loaded successfully!', 'dbmngr' ) );
				}
			} elseif ( 'delete-pma' == $_GET["action"] ) {
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin' ) ) {
					dbmngr_remove_directory( plugin_dir_path( __FILE__ ) . 'phpmyadmin' );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['existence_phpmyadmin'] = '1';
					$dbmngr_phpmyadmin_option['access_phpmyadmin'] = '1';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
					if ( ! isset( $dbmngr_error ) ) {
						$dbmngr_arr_message = array( 'submit_delete_pma' => __( 'PhpMyadmin deleted successfully!', 'dbmngr' ) );
					}
				}
			} elseif ( 'delete-dumper' == $_GET["action"] ) { /* If press button delete */
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper' ) ) {
					dbmngr_remove_directory( plugin_dir_path( __FILE__ ) . 'dumper' );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['existence_dumper'] = '1';
					$dbmngr_phpmyadmin_option['access_dumper'] = '1';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
					if ( ! isset( $dbmngr_error ) ) {
						$dbmngr_arr_message = array( 'submit_delete_dumper' => __( 'Dumper deleted successfully!', 'dbmngr' ) );
					}
				}
			} elseif ( 'off-pma' == $_GET["action"] ) { /* Off phpmyadmin and dumper */
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin' ) ) {
					dbmngr_off_access_pma( 'phpmyadmin/.htaccess' );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['access_phpmyadmin'] = '0';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
					if ( ! isset( $dbmngr_error ) ) {
						$dbmngr_arr_message = array( 'submit_off_pma' => __( 'PhpMyadmin disable!', 'dbmngr' ) );
					}
				}
			} elseif ( 'off-dumper' == $_GET["action"] ) {
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper' ) ) {
					dbmngr_off_access_pma( 'dumper/.htaccess' );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['access_dumper'] = '0';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
					if ( ! isset( $dbmngr_error ) ) {
						$dbmngr_arr_message = array( 'submit_off_dumper' => __( 'Dumper disable!', 'dbmngr' ) );
					}
					
				}
			} elseif ( 'on-pma' == $_GET["action"] ) { /* On phpmyadmin and damper */
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin' ) ) {
					/* Create a dynamic .htaccess file */
					dbmngr_file_access( 'phpmyadmin/.htaccess', 'phpmyadmin/.htpasswd', 'phpmyadmin' );
					/* Create a dynamic .htpasswd file */
					dbmngr_file_password( 'phpmyadmin/.htpasswd', null );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['access_phpmyadmin'] = '1';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
					if ( ! isset( $dbmngr_error ) ) {
						$dbmngr_arr_message = array( 'submit_on_pma' => __( 'PhpMyadmin enable!', 'dbmngr' ) );
					}
				}
			} elseif ( 'on-dumper' == $_GET["action"] ) {
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper' ) ) {
					/* Create a dynamic .htaccess file */
					dbmngr_file_access( 'dumper/.htaccess', 'dumper/.htpasswd', 'dumper' );
					/* Create a dynamic .htpasswd file */
					dbmngr_file_password( 'dumper/.htpasswd', null );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['access_dumper'] = '1';
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
					if ( ! isset( $dbmngr_error ) ) {
						$dbmngr_arr_message = array( 'submit_on_dumper' => __( 'Dumper enable!', 'dbmngr' ) );
					}
				}
			} elseif ( 'update-dumper' == $_GET["action"] ) { /* Update dumper */

				if ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper.zip' ) ) {
					unlink( plugin_dir_path( __FILE__ ) . 'dumper.zip' );
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper' ) ) {
					dbmngr_remove_directory( plugin_dir_path( __FILE__ ) . 'dumper' );
				} /* Download dumper */
				if ( 'Error' == dbmngr_download_dumper( null ) )
					$dbmngr_error = __( 'Update file Error!', 'dbmngr' );
				else {
					$dbmngr_value_dumper = dbmngr_dumper_last_version_parser( 'http://sypex.net/ru/products/dumper/downloads/' );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['version_dumper'] = $dbmngr_value_dumper;
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
				}
				if ( ! isset( $dbmngr_error ) ) {
					$dbmngr_arr_message = array( 'submit_download_dumper' => __( 'Dumper update successfully!', 'dbmngr' ) );
				}
			} elseif ( 'update-phpmyadmin' == $_GET["action"] ) { /* Update phpmyadmin */

				if ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin.zip' ) ) {
					unlink( plugin_dir_path( __FILE__ ) . 'phpmyadmin.zip' );
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin' ) ) {
					dbmngr_remove_directory( plugin_dir_path( __FILE__ ) . 'phpmyadmin' );
				} /* Download phpmyadmin */
				if ( 'Error' == dbmngr_download_phpmyadmin( null ) )
					$dbmngr_error = __( 'Loading file Error!', 'dbmngr' );
				else {
					dbmngr_download_phpmyadmin( null );
					$dbmngr_value_phpmyadmin = dbmngr_pma_last_version_parser( 'http://www.phpmyadmin.net/home_page/index.php' );
					$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
					$dbmngr_phpmyadmin_option['version_phpmyadmin'] = $dbmngr_value_phpmyadmin;
					update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
				}
				if ( ! isset( $dbmngr_error ) ) {
					$dbmngr_arr_message = array( 'submit_download_dumper' => __( 'PhpMyadmin update successfully!', 'dbmngr' ) );
				}
			}
		}
		if ( ! $dbmngr_error == '' ) {
			$dbmngr_arr_message['dbmngr_error'] = $dbmngr_error;
		}
		return $dbmngr_arr_message;
	}
}

/* Js localization */
if ( ! function_exists( 'db_manager_js_var' ) ) {
	function db_manager_js_var() { ?>
		<script type="text/javascript">
			var pma = '<?php _e( 'PhpMyadmin loaded successfully!' , 'dbmngr' ); ?>';
			var dumper = '<?php _e( 'Dumper loaded successfully!' , 'dbmngr' ); ?>';
			var pma_error = '<?php _e( 'PhpMyadmin loaded incorrect!' , 'dbmngr' ); ?>';
			var dumper_error = '<?php _e( 'Dumper loaded incorrect!' , 'dbmngr' ); ?>';
			var pma_update = '<?php _e( 'PhpMyadmin update successfully!' , 'dbmngr' ); ?>';
			var dumper_udate = '<?php _e( 'Dumper update successfully!' , 'dbmngr' ); ?>';
		</script>
<?php }
}

/* Add style css and js */
if ( ! function_exists ( 'dbmngr_admin_head' ) ) {
	function dbmngr_admin_head() {
		if ( isset( $_REQUEST['page'] ) && 'db-manager.php' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'dbmngr_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'dbmngr_script', plugins_url( '/js/script.js', __FILE__ ) );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'db_manager', db_manager_js_var() );
		}
	}
}

/* Function for delete options */
if ( ! function_exists ( 'dbmngr_delete_options' ) ) {
	function dbmngr_delete_options() {
		delete_option( 'dbmngr_options' );
		delete_site_option( 'dbmngr_options' );
	}
}

/* Ajax download dumper */
if ( ! function_exists ( 'dbmngr_ajax_download_dumper' ) ) {
	function dbmngr_ajax_download_dumper() {
		if ( 'Error' == dbmngr_download_dumper( 'dumper' ) ) {
			echo 'error';
			die();
		} else {
			dbmngr_download_dumper( 'dumper' );
			$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
			$dbmngr_phpmyadmin_option['existence_dumper'] = '0';
			update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
			echo dbmngr_show_dumper();
			die();
		}
	}
}

/* Ajax download phpmyadmin */
if ( ! function_exists ( 'dbmngr_ajax_download_phpmyadmin' ) ) {
	function dbmngr_ajax_download_phpmyadmin() {	
		if ( 'Error' == dbmngr_download_phpmyadmin( 'pma' ) ) {
			echo 'error';
			die();
		} else {
			$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
			$dbmngr_phpmyadmin_option['existence_phpmyadmin'] = '0';
			update_option( 'dbmngr_options', $dbmngr_phpmyadmin_option );
			echo dbmngr_show_phpmyadmin();
			die();
		}
	}
}

/* Show dumper link */
if ( ! function_exists ( 'dbmngr_show_dumper' ) ) {
	function dbmngr_show_dumper() {
		$dbmngr_link_dumper = $dbmngr_link_download = $dbmngr_link_update = $dbmngr_link_delete = $dbmngr_on_off = '';

		/* Set link to dumper */
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'dumper' ) ) {
			$dbmngr_site_url = plugins_url();
			$dbmngr_site_name = substr( $dbmngr_site_url , 7 );
			$dbmngr_option_array = get_option( 'dbmngr_options' );
			if ( isset( $dbmngr_option_array['user_name'] ) && isset( $dbmngr_option_array['user_password_dumper'] ) && isset( $dbmngr_option_array['catalog_dumper'] ) ) {
				/* Get links access to dumper */
				if ( '1' == $dbmngr_option_array['access_dumper'] ) {
					if ( 'true' == dbmngr_detect_ie() ) {
						printf( __( 'Please use username: <b>%s</b> and password: <b>%s</b> for login to system!', 'dbmngr' ) , $dbmngr_option_array['user_name'], $dbmngr_option_array['user_password_dumper'] );
						$dbmngr_link_dumper = ' <a href="' . plugins_url() . '/db-manager/dumper/' . $dbmngr_option_array['catalog_dumper'] . '/sxd/index.php" target=_blank>' . __( 'Access', 'dbmngr' ) . '</a> ';
					} else {
						$dbmngr_link_dumper = ' <a href="http://' . $dbmngr_option_array['user_name'] . ':' . $dbmngr_option_array['user_password_dumper'] . '@' . $dbmngr_site_name . '/db-manager/dumper/' . $dbmngr_option_array['catalog_dumper'] . '/sxd/index.php" target=_blank>' . __( 'Access', 'dbmngr' ) . '</a> ';
					}
				}
			}
			/* Check new version Dumper */
			$dbmngr_value_dumper = dbmngr_dumper_last_version_parser( 'http://sypex.net/ru/products/dumper/downloads/' );
			$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
			if ( isset( $dbmngr_option_array['version_dumper'] ) && $dbmngr_phpmyadmin_option['version_dumper'] != $dbmngr_value_dumper ) {
				$dbmngr_link_update = "<p>" . __( 'There is a new version of Dumper available:', 'dbmngr' ) .' '. $dbmngr_value_dumper . ' <a href="admin.php?page=db-manager.php&action=update-dumper" id="dbmngr_update_ajax_dumper" >' . __( 'Update now', 'dbmngr' ) . '</a><img src="' . admin_url( '/images/wpspin_light.gif' ) . '"id="dbmngr-submit-loader-dumper" style="display: none;" /> </p>';
			}
		}

		$dbmngr_option_array = get_option( 'dbmngr_options' );
		if ( '1' == $dbmngr_option_array['existence_dumper'] ) { /* Show the controls elements - Download */
			$dbmngr_link_download = ' <a href="admin.php?page=db-manager.php&action=dumper" id="dbmngr_download_ajax_dumper" >' . __( 'Download', 'dbmngr' ) . '</a><img src="' . admin_url( '/images/wpspin_light.gif' ) . '"id="dbmngr-submit-loader-dumper" style="display: none;" /> ';
			
		} elseif ( '0' == $dbmngr_option_array['existence_dumper'] && file_exists( plugin_dir_path( __FILE__ ) . 'dumper' ) ) { /* Show the controls elements - Delete, Disable/Enable */
			$dbmngr_link_delete = ' <a href="admin.php?page=db-manager.php&action=delete-dumper" >' . __( 'Delete', 'dbmngr' ) . '</a> ';
			if( '1' == $dbmngr_option_array['access_dumper'] ) {
				$dbmngr_on_off = ' <a href="admin.php?page=db-manager.php&action=off-dumper">' . __( 'Disable access', 'dbmngr' ) . '</a> ';
			} else {
				$dbmngr_on_off = ' <a href="admin.php?page=db-manager.php&action=on-dumper">' . __( 'Enable access', 'dbmngr' ) . '</a> ';
			}
		} else { /* Show the controls elements - Download */
			$dbmngr_link_download = ' <a href="admin.php?page=db-manager.php&action=dumper" id="dbmngr_download_ajax_dumper" >' . __( 'Download', 'dbmngr' ) . '</a><img src="'.admin_url( '/images/wpspin_light.gif' ) . '"id="dbmngr-submit-loader-dumper" style="display: none;" /> ';
		}
		return $dbmngr_link_dumper . $dbmngr_link_download . $dbmngr_on_off . $dbmngr_link_delete . $dbmngr_link_update;
	}
	
}
/* Show phpmyadmin link */
if ( ! function_exists ( 'dbmngr_show_phpmyadmin' ) ) {
	function dbmngr_show_phpmyadmin() {
		$dbmngr_link_phpmyadmin = $dbmngr_link_download = $dbmngr_link_update = $dbmngr_link_delete = $dbmngr_on_off = '';

		/* Set links to phpmyadmin */
		$dbmngr_target_url = 'http://www.phpmyadmin.net/home_page/index.php';
		$dbmngr_temp = dbmngr_pma_get_href_parser( $dbmngr_target_url );
		$path = explode( '/', $dbmngr_temp );
		foreach ( $path as $path ) {
			if ( strpos( $path, '.zip' ) ) {
				$dbmngr_str = ( basename( $path, ".zip" ) );
				break;
			}
		}
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin' ) ) {
			$dbmngr_site_url = plugins_url();;
			$dbmngr_site_name = substr( $dbmngr_site_url , 7 );
			$dbmngr_option_array = get_option( 'dbmngr_options' );
			if ( isset( $dbmngr_option_array['user_name'] ) && isset( $dbmngr_option_array['user_password_phpmyadmin'] ) && isset( $dbmngr_option_array['catalog_phpmyadmin'] ) ) {
				/* Get links access to phpmyadmin */
				if ( '1' == $dbmngr_option_array['access_phpmyadmin'] ) {
					if ( 'true' == dbmngr_detect_ie() ) {
						printf( __( 'Please use username: <b>%s</b> and password: <b>%s</b> for login to system!', 'dbmngr' ) , $dbmngr_option_array['user_name'], $dbmngr_option_array['user_password_phpmyadmin'] );
						$dbmngr_link_phpmyadmin = ' <a href="' . plugins_url() . '/db-manager/phpmyadmin/' . $dbmngr_option_array['catalog_phpmyadmin'] . '/' . $dbmngr_str . '/index.php" target=_blank>' . __( 'Access', 'dbmngr' ) . '</a> ';
					} else {
						$dbmngr_link_phpmyadmin = ' <a href="http://' . $dbmngr_option_array['user_name'] . ':' . $dbmngr_option_array['user_password_phpmyadmin'] . '@' . $dbmngr_site_name . '/db-manager/phpmyadmin/' . $dbmngr_option_array['catalog_phpmyadmin'] . '/' . $dbmngr_str . '/index.php" target=_blank>' . __( 'Access', 'dbmngr' ) . '</a> ';
					}
				}
			}
			/* Check new version phpmyadmin */
			$dbmngr_value = dbmngr_pma_last_version_parser( $dbmngr_target_url );
			$dbmngr_phpmyadmin_option = get_option( 'dbmngr_options' );
			if ( isset( $dbmngr_option_array['version_phpmyadmin'] ) && $dbmngr_phpmyadmin_option['version_phpmyadmin'] != $dbmngr_value ) {
				$dbmngr_link_update = "<p>" . __( 'There is a new version of PhpMyAdmin available:', 'dbmngr' ) . ' ' . $dbmngr_value . ' <a href="admin.php?page=db-manager.php&action=update-phpmyadmin" id="dbmngr_update_ajax_pma">' . __( 'Update now', 'dbmngr' ) . '</a><img src="' . admin_url( '/images/wpspin_light.gif' ) . '"id="dbmngr-submit-loader-pma" style="display: none;" /> </p>';
			}
		}
		$dbmngr_option_array = get_option( 'dbmngr_options' );
		if( '1' == $dbmngr_option_array['existence_phpmyadmin'] ) {
			$dbmngr_link_download = '<a href="admin.php?page=db-manager.php&action=pma" id="dbmngr_download_ajax_pma" >' . __( 'Download', 'dbmngr' ) . '</a><img src="' . admin_url( '/images/wpspin_light.gif' ) . '"id="dbmngr-submit-loader-pma" style="display: none;" /> ';
		} elseif ( '0' == $dbmngr_option_array['existence_phpmyadmin'] && file_exists( plugin_dir_path( __FILE__ ) . 'phpmyadmin' ) ) {
			$dbmngr_link_delete = '<a href="admin.php?page=db-manager.php&action=delete-pma">' . __( 'Delete', 'dbmngr' ) . '</a> ';
			if( '1' == $dbmngr_option_array['access_phpmyadmin'] ) {
				$dbmngr_on_off = '<a href="admin.php?page=db-manager.php&action=off-pma">' . __( 'Disable access', 'dbmngr' ) . '</a> ';
			} else {
				$dbmngr_on_off = '<a href="admin.php?page=db-manager.php&action=on-pma">' . __( 'Enable access', 'dbmngr' ) . '</a> ';
			}
		} else {
			$dbmngr_link_download = '<a href="admin.php?page=db-manager.php&action=pma" id="dbmngr_download_ajax_pma">' . __( 'Download', 'dbmngr' ) . '</a><img src="' . admin_url( '/images/wpspin_light.gif' ) . '"id="dbmngr-submit-loader-pma" style="display: none;" /> ';
		}
		return $dbmngr_link_phpmyadmin . $dbmngr_link_download . $dbmngr_on_off . $dbmngr_link_delete . $dbmngr_link_update;
	}
}

/* Function for display DB manager settings page in the admin area */
if ( ! function_exists( 'dbmngr_settings_page' ) ) {
	function dbmngr_settings_page() {
		global $dbmngr_message;
		$dbmngr_error = $dbmngr_message = $dbmngr_error_message = '';
		$dbmngr_target_url = 'http://www.phpmyadmin.net/home_page/index.php';

		/* Check permission on plugin */
		if ( ! is_writable( plugin_dir_path( __FILE__ ) ) ) {
			$dbmngr_error = __( 'Folder with plugin is not writable please give permission!', 'dbmngr' );
		}
		/* Check cURL */
		if ( ! function_exists( 'curl_init' ) ) {
			$dbmngr_error = __( 'Curl support is disabled, contact the administrator of your server!', 'dbmngr' );
		}
		$dbmngr_arr_str = dbmngr_controller(); /* Get array message for user */
		if ( isset( $dbmngr_arr_str['submit_delete_pma'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_delete_pma'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_delete_dumper'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_delete_dumper'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_off_dumper'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_off_dumper'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_on_dumper'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_on_dumper'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_off_pma'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_off_pma'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_on_pma'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_on_pma'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_download_pma'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_download_pma'] ;
		} elseif ( isset( $dbmngr_arr_str['submit_download_dumper'] ) ) {
			$dbmngr_message = $dbmngr_arr_str['submit_download_dumper'] ;
		}
		if ( isset( $dbmngr_arr_str['dbmngr_error'] ) ) {
			$dbmngr_error = $dbmngr_arr_str['dbmngr_error'];
		} ?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'DB manager settings', 'dbmngr' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=db-manager.php"><?php _e( 'Settings', 'dbmngr' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/plugin/db-manager/#faq" target="_blank" ><?php _e( 'FAQ', 'dbmngr' ); ?></a>
			</h2>
			<noscript>			
				<div class="error"><p><strong><?php _e( 'Please enable JavaScript!', 'dbmngr' ); ?></strong></p></div>
			</noscript> 
			<div class="updated fade" <?php if( "" == $dbmngr_message ) echo "style=\"display:none\""; ?>><p><strong><?php echo $dbmngr_message; ?></strong></p></div>
			<div class="error" <?php if( "" == $dbmngr_error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $dbmngr_error; ?></strong></p></div>
			<form method="post" action="admin.php?page=db-manager.php">
				<table class="form-table">
					<tr valign="top">
						<td colspan="2">
							<?php _e( 'You can download PhpMyAdmin and Dumper from the official site!', 'dbmngr' ); ?>
						</td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e( 'PhpMyAdmin', 'dbmngr' ); ?> </th>
						<td class="dbmngr-phpmyadmin">
							<?php /* Show the controls elements phpmyadmin */
							if ( is_writable( plugin_dir_path( __FILE__ ) ) ) {
								echo dbmngr_show_phpmyadmin();
							} ?>
						</td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e( 'Dumper', 'dbmngr' ); ?> </th>
						<td class="dbmngr-dumper">
							<?php /* Show the controls elements dumper */
							if ( is_writable( plugin_dir_path( __FILE__ ) ) ) {
								echo dbmngr_show_dumper();
							} ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
	<?php }
}

add_action( 'admin_menu', 'dbmngr_add_admin_menu' );
add_action( 'admin_init', 'dbmngr_plugin_admin_init' );
add_action( 'init', 'dbmngr_plugin_init' );

add_action( 'admin_enqueue_scripts', 'dbmngr_admin_head' );
add_action( 'wp_enqueue_scripts', 'dbmngr_admin_head' );

add_filter( 'plugin_action_links', 'dbmngr_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'dbmngr_register_plugin_links', 10, 2 );

add_action( 'wp_ajax_dbmngr_phpmyadmin', 'dbmngr_ajax_download_phpmyadmin' );
add_action( 'wp_ajax_dbmngr_dumper', 'dbmngr_ajax_download_dumper' );

register_uninstall_hook( __FILE__, 'dbmngr_delete_options' );
?>
