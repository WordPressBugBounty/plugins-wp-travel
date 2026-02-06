<?php 

// if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
// 	return;
// }

// if( function_exists( 'wordpress_importer_init' ) ){
// 	return;
// }

/** Display verbose errors */
if ( ! defined( 'IMPORT_DEBUG' ) ) {
	define( 'IMPORT_DEBUG', WP_DEBUG );
}

/** WordPress Import Administration API */
require_once ABSPATH . 'wp-admin/includes/import.php';

require_once ABSPATH . 'wp-admin/includes/post.php';

require_once ABSPATH . '/wp-admin/includes/taxonomy.php';

require_once ABSPATH . '/wp-admin/includes/comment.php';

require_once ABSPATH . '/wp-admin/includes/file.php';

require_once ABSPATH . '/wp-admin/includes/image.php';

require_once ABSPATH . '/wp-includes/pluggable.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) {
		require $class_wp_importer;
	}
}

/** Functions missing in older WordPress versions. */
require_once dirname( __FILE__ ) . '/compat.php';

/** Templazee_WXR_Parser class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser.php';

/** Templazee_WXR_Parser_SimpleXML class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser-simplexml.php';

/** Templazee_WXR_Parser_XML class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser-xml.php';

/** Templazee_WXR_Parser_Regex class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser-regex.php';

/** WP_Import class */
require_once dirname( __FILE__ ) . '/class-wp-import.php';

