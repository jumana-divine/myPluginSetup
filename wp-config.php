<?php
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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'plugin' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '0VDQ$,HCE]+?}8owtI0fj0^;!38I[_pz<0CGI<S)x-UHjOjFU63GocEc;%{?Nf|6' );
define( 'SECURE_AUTH_KEY',  'N|l$mELEeg`pTRFq|QR`!X!oEnzpjx.c@4`fsn^CN;2w4W%V6g9?$/q3Bn@@)@nQ' );
define( 'LOGGED_IN_KEY',    ':%`%6HX*+W^,`9))4s5*A3AAe}GC)-Bb0JFyZ0psHbt2<p.R?%NcVbPz*@KcteM?' );
define( 'NONCE_KEY',        'zJ6yuctEE>4M4$i*,b~-yktE2+G4f3z+{&Hh;KQ0k+ur+F5*}@$U^Yq;nl.MMt-)' );
define( 'AUTH_SALT',        '!SIzi(1&gc*-f8td;H5Bd0[-@n?xK=D+H_`K#>T%@i~Pk~vM4A6Kf&AXxk6Q%i;&' );
define( 'SECURE_AUTH_SALT', '?J01d>520,o$E*LROgfHX:}7y3Xf6kve2K%YX[7!%Qe6;MD+(H?0-rS;x%:6SywA' );
define( 'LOGGED_IN_SALT',   'Q!.n%zrdvhaz3-/fMT$g&`:&VtE9Y7$kp,PvzK%nIQX`jn$ls@UI,&qleZ)(!=:!' );
define( 'NONCE_SALT',       ' YX4`$|!IfGIz8[exir[5{]RKI#g/Tp*Rx.bfy_r8zIMKgSi+La^tq4p4z]8N:Ku' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
