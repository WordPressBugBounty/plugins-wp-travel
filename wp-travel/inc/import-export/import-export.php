<?php 

/*
 * @author Wp-Travel
 * @since 6.8.0
 * Settings Import/Export
*/

class WP_Travel_Import_Export{

	public function __construct(){
		add_action( 'rest_api_init', array( $this, 'wp_travel_import_export_api' ) );
	}

	public function wp_travel_import_export_api(){
		register_rest_route(
			'wp-travel/v1',
			'/import-settings-data',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'wp_travel_import_data' ),
			)
		);

		register_rest_route(
			'wp-travel/v1',
			'/export-settings-data',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'wp_travel_export_data' ),
			)
		);
	}

	public function wp_travel_import_data( WP_REST_Request $request ){

		$settings_data = $request->get_body_params();

		$settings_data =  (array)json_decode( $settings_data['settings_data'], true );
 
		if( update_option( 'wp_travel_settings', $settings_data ) ){
            return new WP_REST_Response('Settings import successfully', 200);
        } else {
            return new WP_REST_Response('Failed to import settings', 500);
        }

        return $settings_data;

	}

	public function wp_travel_export_data(){

		if (update_option('wp_travel_export_data', true)) {
            return new WP_REST_Response('Settings export successfully', 200);
        } else {
            return new WP_REST_Response('Failed to export settings', 500);
        }
	}

}

new WP_Travel_Import_Export();


if ( get_option( 'wp_travel_export_data' ) ) {

    if( update_option('wp_travel_export_data', false) ){
        $settings  = json_encode( wptravel_get_settings() );

        $file = "wp-travel-settings.json";
        $txt = fopen($file, "w") or die("Unable to open file!");
        fwrite($txt, $settings);
        fclose($txt);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header("Content-Type:  application/json");
        readfile($file);
        die;
    }
	
}
