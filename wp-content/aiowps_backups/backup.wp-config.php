<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_test');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'admin');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/* Added for increasign the memory limit*/
define( 'WP_MEMORY_LIMIT', '256M' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'H#A5ZDz%{`=lj-ri`q&[wHUQSp/<WcEV(f&-t+#|+Zcr.R3F[SL=-O83RXtXsE*X');
define('SECURE_AUTH_KEY',  'W2ls+9qJ<^;`?T<`|z+r.Q&J{; MdSjrSf[ej=<G--2Aj%Zw-vn$-LdBO[0cZ|t0');
define('LOGGED_IN_KEY',    't~)ANST|cZWW~C=10!-M>%+y5Zgdr_{S}Fi+!XQ(Q/yG}zrFaz.XY97K/Mt5LR(/');
define('NONCE_KEY',        '(e&BRiNS@cME-`-y$F&F3K<(iF4{-t@Lv:k`:s![-;}Skrz-H^ALum#.z[Jf6*-4');
define('AUTH_SALT',        '%OjW$4-D>+-z,%UZZ]^-*btG-k#0ex/ijQl>q$0kHd7.A[^=-GoYk>2AD7_0NhuF');
define('SECURE_AUTH_SALT', '4-us:P8[N=5Nz*VAdFhu7?4oHw2o%!;r}0O.H+ChpC{j|qHUChns%~W-~bavZaaa');
define('LOGGED_IN_SALT',   'w,Va?66=jf!Lu]($-R|-!N)+>pK?yh,paq?;SrFY4Y)@+x=%!IJ+anH+wC_#d!kC');
define('NONCE_SALT',       'NFY{/}R*O1r}bc-=.Lrk@.uax@j*%~KKmUYyjcYY8lHu</Wn3GKYwaucSs$te!ew');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/* added for allowing user to upload plugin through browsers*/
define('FS_METHOD', 'direct');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
