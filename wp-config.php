<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'heroku_e7e1032d0f7e964');

/** MySQL database username */
define('DB_USER', 'bbe62db07e55a3');

/** MySQL database password */
define('DB_PASSWORD', '93ad1e26');

/** MySQL hostname */
define('DB_HOST', 'us-cdbr-iron-east-04.cleardb.net');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


define('WP_HOME','https://tolotranet.herokuapp.com/');
define('WP_SITEURL','https://tolotranet.herokuapp.com/');
define('FORCE_SSL_ADMIN', true);

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '`v>a0FL^|odWe]aZt*D&Zh4m+LjT]q>;5VC4^*?edHc^>2pZ^lp0%~rdcJ~xvsq)');
define('SECURE_AUTH_KEY',  'oZ)Nx?|2j@.Gdkz1@E_1dNLbSiUHpVvdrC5c5-cV(+9~;+5&}i0/fs*I{QVWJ0{9');
define('LOGGED_IN_KEY',    'v$m8Kv%c&K:T8dH>c,JH!|F(zR>Lj:QQCIaXY8~=j]9cv7w^yv,@Mf>3}x&_e+5V');
define('NONCE_KEY',        ')Yw{gEXd=Ok;qoJh Y _O092p;D8;Ex^9sGl.W&O9m3K au9Gl+HWJ]9J?f.B|-0');
define('AUTH_SALT',        '/]zn|iV5S}}Nj/BenTFSHgU^}pprGUTuIsEw[J_k)ut(Wt 1|v_k8-_/ohMge^71');
define('SECURE_AUTH_SALT', 'vh)<Sl;AWiM7E7-t[/ }3t:UFUR${D!o2J>ieL:&;Q@vjttCC>U%gu{nUw~ER;bb');
define('LOGGED_IN_SALT',   'WO_Jz,|!QD#$%fTvQ8e. 1`+iQ71(3.jRHquLEoVS$>q#vu;oP;IAm*XZak-lHG0');
define('NONCE_SALT',       'kVMLt`0-4WO_znk*2xK;bEN~<u%t<z`a?P]|u>)S/)(*>eiyAMNE34x#81o!.n7l');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
