<?php
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
foreach( $_COOKIE AS $c => $v ) {
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
		/* Check user */
		if ( $this->connect( $dbmngr_config['DB_HOST'], '', $dbmngr_config['DB_USER'], $dbmngr_config['DB_PASSWORD'] ) ) {
			$auth = 1;
			$this->CFG['my_db']   = $dbmngr_config['DB_NAME'];
			$this->CFG['exitURL'] = './';
		}
	}
} ?>