<?php
/**
 * Plugin Name: HRRN Petfinder
 * Plugin URI: https://github.com/wrparker/hrrn_petfinder
 * Description: Use Petfinder API to display rabbits.
 * Version: 0.1
 * Author: W. Ryan Parker
 * Author URI: https://wrparker.me
 */


$SETTINGS_FILE = plugin_dir_path(__FILE__).'settings.php';

require_once($SETTINGS_FILE);  # import administrative panel settings.

if( is_admin() )
    $hrrn_settings_page = new HrrnPetfinderSettingsPage();

/* Shortcode stuff */
function generate_auth_token($api_key, $secret_key){
    $response = wp_remote_post('https://api.petfinder.com/v2/oauth2/token',
        array(
            'body' => array('grant_type' => 'client_credentials',
                'client_id' => $api_key,
                'client_secret' => $secret_key))
    );

    $token = json_decode($response['body'], true);
    $token['auth_str'] = $token['token_type']. ' '.$token['access_token'];
    return $token;
}

function retrieve_animals($options, $shelter_id){
    $CACHE_DIR = plugin_dir_path(__FILE__).'tmp/';
    $CACHE_FILE = $CACHE_DIR.'cached_call.json';
    $CACHE_TIME = 3600;

    if (file_exists($CACHE_FILE)){
        print_r(filemtime($CACHE_FILE));
    }
    else{
        $token = generate_auth_token($options['api_key'], $options['secret_key']);
        # Get shelter animals.
        $response = wp_remote_get('https://api.petfinder.com/v2/animals?organization='.$shelter_id,
            array(
                'headers' => array(
                    'Authorization'=> $token['auth_str'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ),

            ));
        $fp = fopen($CACHE_FILE, 'w');
        $json = json_encode($response['body'], JSON_PRETTY_PRINT);
        fwrite($fp, $json);
        fclose($fp);
    }

}

function hrrn_petfinder(){
    # TODO: Probably refactor better.
    $options = get_option('hrrn_petfinder', option_defaults());

    if ($options['bootstrap_import']){
        echo "<!--Including Plugin Bootstrap on page -->";
        wp_enqueue_style('bootstrap',plugin_dir_url(__FILE__).'css/bootstrap.min.css' );
        wp_enqueue_script('bootstrap-js', plugin_dir_url(__FILE__).'js/jquery.min.js');
        wp_enqueue_script('bootstrap-popper', plugin_dir_url(__FILE__).'js/popper.min.js');
        echo "<!--End plugin bootstrap-->";
    }

    wp_enqueue_style('hrrn_petfinder', plugin_dir_url(__FILE__).'css/hrrn_petfinder.css' );
    wp_enqueue_script('hrrn_petfinder', plugin_dir_url(__FILE__).'js/hrrn_petfinder.js');


    if (!$options['api_key'] || !$options['secret_key']){
        ?>
            <div class="alert alert-warning" role="alert">
                You need to set your secret-key and api-key in the settings -> HRRN Petfinder Settings section in the
                administration panel.
            </div>
        <?php
        return '';
    }

    /* Petfinder API Calls */
    retrieve_animals($options,'TX194');
}

add_shortcode('hrrn_petfinder', 'hrrn_petfinder');


