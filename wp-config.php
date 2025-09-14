<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u228945434_EcUDW' );

/** Database username */
define( 'DB_USER', 'u228945434_MtTG1' );

/** Database password */
define( 'DB_PASSWORD', 'Bo2YzIyMJH' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '1Uc>=:v%kF{sVMp>z[]?6}Odw.Y]KxO tvWBL)=/()z;Rjtn?iRu!_g|umfln3;`' );
define( 'SECURE_AUTH_KEY',   '=.{OPhT^N6;h]H&m4Wn;M]a5w@M1KU=#*MbLx/N%AepLl`4CnBO!PO+a0za=<>|s' );
define( 'LOGGED_IN_KEY',     'u 8=R0`ANV0U5%uNNrD@!l6K]%6}+u[n6)6y:aGz!`#akblm~^xtNe88NlETA1vX' );
define( 'NONCE_KEY',         ' `eVX(GO~jOeB,YIz0cP_il^q#hrHXkt.@uAkI[wm@NsJ[t{R<Yf :IOc+Q?54oZ' );
define( 'AUTH_SALT',         'xbh[M}!F8.`T)4:o9!Ou|72fW#u{+a,W@gtJf+*F7xeFCZ44pw-yHty!2!lNMB1U' );
define( 'SECURE_AUTH_SALT',  'oz>(;2Vx#b6rCSNI}nfaS1V8;7LO1^S>2SrEbNZXyb?LJE`@Z=e}JTPxo&o7?cZ>' );
define( 'LOGGED_IN_SALT',    'YE+Q!N+i/Ju1GD&M+][#aLA45D,v/fG;%Tui*(`J8_?}<YOhhh6WwNg9Wgv [Sf,' );
define( 'NONCE_SALT',        'H5{MrG$!$ lI83//8hGF#8p|qq8f$5`#@9dT)]LE0IFzDxo&YW4|B_MTW3!HUsp!' );
define( 'WP_CACHE_KEY_SALT', '+PMF&]CbApYf)LIYdyNa8)f:&k_h&kzbr&:*XrXT8:~T=.<b<a/G@g@1M+rSGc7j' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '9783a04e0ef3ebbec2d266c30b23590e' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG_LOG', true );
// define( 'WP_DEBUG_DISPLAY', false );


