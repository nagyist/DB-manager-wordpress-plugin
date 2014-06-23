<?php
/* Get user and password */
/*$dbmngr_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php';*/
$full_path = explode( '/', getcwd() );
for ( $i=0; $i < count( $full_path ); $i++ ) {
	if ( 0 == $i ) {
		$path = $full_path[$i] . '/';
	} else {
		$path = $path . $full_path[$i] . '/';
	}
	if ( file_exists( $path . 'wp-config.php' ) )
		$dbmngr_path = $path . 'wp-config.php';
}
$dbmngr_config = array();
foreach ( $_COOKIE AS $c => $v ) {
	if ( strpos( $c, 'wordpress_logged_in' ) !== false ) {
		$dbmngr_config['COOKIE'] = explode( '|', $v );
		break;
	}
}
/* Parsing config, to skip including wp-settings.php */
if ( isset( $dbmngr_config['COOKIE'] ) && count( $dbmngr_config['COOKIE'] ) == 3 ) {
	$dbmngr_file = file_get_contents( $dbmngr_path );
	if ( preg_match_all( "/define\('(\w+)',\s*'(.*?)'\);/m", $dbmngr_file, $m, PREG_SET_ORDER ) && preg_match( "/^\\\$table_prefix\s*=\s*'(.+?)';/m", $dbmngr_file, $t ) ) {
		$dbmngr_config['TAB_PREFIX'] = stripcslashes( $t[1] );
		foreach ( $m AS $c ) {
			$dbmngr_config[$c[1]] = stripcslashes( $c[2] );
		}
	}
} 
/* This is needed for cookie based authentication to encrypt password in cookie */
$cfg['blowfish_secret'] = 'xampp'; /* YOU SHOULD CHANGE THIS FOR A MORE SECURE COOKIE AUTH! */
/* Servers configuration */
$i = 0;
/* First server */
$i++;
/* Authentication type and info */
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['host'] = $dbmngr_config['DB_HOST'];
$cfg['Servers'][$i]['user'] = $dbmngr_config['DB_USER'];
$cfg['Servers'][$i]['password'] = $dbmngr_config['DB_PASSWORD'];
$cfg['Servers'][$i]['extension'] = 'mysql';
$cfg['Servers'][$i]['AllowNoPassword'] = true;

/* User for advanced features */
$cfg['Servers'][$i]['controluser'] = 'pma';
$cfg['Servers'][$i]['controlpass'] = '';

/* Advanced phpMyAdmin features */
$cfg['Servers'][$i]['pmadb'] = $dbmngr_config['DB_NAME'];
$cfg['Servers'][$i]['bookmarktable'] = 'pma_bookmark';
$cfg['Servers'][$i]['relation'] = 'pma_relation';
$cfg['Servers'][$i]['table_info'] = 'pma_table_info';
$cfg['Servers'][$i]['table_coords'] = 'pma_table_coords';
$cfg['Servers'][$i]['pdf_pages'] = 'pma_pdf_pages';
$cfg['Servers'][$i]['column_info'] = 'pma_column_info';
$cfg['Servers'][$i]['history'] = 'pma_history';
$cfg['Servers'][$i]['designer_coords'] = 'pma_designer_coords';
$cfg['Servers'][$i]['tracking'] = 'pma_tracking';
/* End of servers configuration */
?>