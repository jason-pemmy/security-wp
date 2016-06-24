<?php

/** The name of the database for WordPress */
define( 'DB_NAME', 'tbksecurity' );
/** MySQL database username */
define( 'DB_USER', 'root' );
/** MySQL database password */
define( 'DB_PASSWORD', '' );
/** MySQL hostname */
define( 'DB_HOST', 'localhost' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define( 'TBK_ENVIRONMENT', 'dev' );

/* * #@- */
$hostname = isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' ? 'https' : 'http';
$hostname .= '://' . $_SERVER['HTTP_HOST'] . '/';
define( 'WP_HOME', $hostname . 'dev/security.tbkdev.com/src' );
define( 'WP_SITEURL', $hostname . 'dev/security.tbkdev.com/src' );
define( 'FS_METHOD', 'direct' );

ini_set('log_errors','Off');
ini_set('display_errors','on');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);