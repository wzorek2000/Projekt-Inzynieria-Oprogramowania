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
define( 'DB_NAME', 'epiz_31861078_w945' );

/** Database username */
define( 'DB_USER', '31861078_1' );

/** Database password */
define( 'DB_PASSWORD', '(9g5pzLS.8' );

/** Database hostname */
define( 'DB_HOST', 'sql202.byetcluster.com' );

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
define( 'AUTH_KEY',         'axmplm7owmcckocynhdxe7clagcce4jyzpvu4izs9gmlzemhdik4oz5fu1qgav5a' );
define( 'SECURE_AUTH_KEY',  'pt3ne9roqtd6jbmxsofr04efd3ndmqznnuuce5w6arci7dukjvojqbv55msqaqxs' );
define( 'LOGGED_IN_KEY',    'atxzwqwsi2x4fautyfyy28ysrxswcawvyprcvwxyockaxljgcker6nklvbfgl2c7' );
define( 'NONCE_KEY',        'lvjs27be1py3xvaoueafogyvsupksh1qzzq2trshtascuixv7oyfrow9efisrzvz' );
define( 'AUTH_SALT',        'd1rnxhnfqf1wjraquroskxw2coau2fnw9x2yaguafdxelfjgig2h2pcyfwf1pmn1' );
define( 'SECURE_AUTH_SALT', 'eqtsndqbebtkpdzcf5x3ayyrrgmp3evuy1gvsl7zwu4ysxni1mllpnamwrgoqh7x' );
define( 'LOGGED_IN_SALT',   'z9ouwevejaz6qo4yylbzigqrrk1rzm8ho7pdgsbfjy32vbkfwtisk72uorb9gokn' );
define( 'NONCE_SALT',       'myyilscbmhfrpxw4sm0peawbncltza6pamomvcoahavwucajypghyvxk83ogjisd' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wppr_';

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
