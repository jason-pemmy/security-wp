<?php

/** The name of the database for WordPress */
define( 'DB_NAME', 'qatbkc5_tbkbase' );
/** MySQL database username */
define( 'DB_USER', 'qatbkc5_dev' );
/** MySQL database password */
define( 'DB_PASSWORD', 'tbkqa4US' );
/** MySQL hostname */
define( 'DB_HOST', 'localhost' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define( 'TBK_ENVIRONMENT', 'stage' );

$hostname = isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' ? 'https' : 'http';
$hostname .= '://' . $_SERVER['HTTP_HOST'] . '/';
define( 'WP_HOME', $hostname . 'dev/tbk-base/src' );
define( 'WP_SITEURL', $hostname . 'dev/tbk-base/src' );

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);