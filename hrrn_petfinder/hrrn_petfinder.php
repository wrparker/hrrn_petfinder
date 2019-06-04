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

function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;

}

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
    $CACHE_TIME = 3600;  # 1 hour.
    $PAGE_LIMIT = 100;

    clearstatcache();  # mkae sure we dont cache time
    if (file_exists($CACHE_FILE) && abs(time() - filemtime($CACHE_FILE)) < $CACHE_TIME ){
      return true;
    }
    else{
      if (file_exists($CACHE_FILE)){
          unlink($CACHE_FILE);
        }
        $token = generate_auth_token($options['api_key'], $options['secret_key']);
        # Get shelter animals.
        $response = wp_remote_get('https://api.petfinder.com/v2/animals?organization='.$shelter_id,
            array(
                'headers' => array(
                    'Authorization'=> $token['auth_str'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ),
                'body' => array(
                    'limit' => $PAGE_LIMIT,
                )
            ));
        $animals = json_decode($response['body'], true)['animals'];
        $cur_page = json_decode($response['body'], true)['pagination']['current_page'];
        $total_pages = json_decode($response['body'], true)['pagination']['total_pages'];

        while($cur_page < $total_pages){
          $cur_page = $cur_page + 1;
          $response = wp_remote_get('https://api.petfinder.com/v2/animals?organization='.$shelter_id,
              array(
                  'headers' => array(
                      'Authorization'=> $token['auth_str'],
                      'Content-Type' => 'application/json',
                      'Accept' => 'application/json'
                  ),
                  'body' => array(
                      'limit' => $PAGE_LIMIT,
                      'page' => (int)$cur_page,
                  )
              ));
            $animals = array_merge($animals, json_decode($response['body'], true)['animals']);
        }

        $fp = fopen($CACHE_FILE, 'w');
        $json = json_encode($animals);
        fwrite($fp, $json);
        fclose($fp);
    }
}

function table_header($json_data){
  $html = '';
  $html = "
  <div class='container petfinder-legend-container'>
      <h2>Adoptable Rabbits</h2>
      <div class='row'>
      <div class='col-sm-12'>
        <p>You can search here for rabbits that are currently adoptable.  Click on the rabbit's name, photo or '[Read more]' for more information.</p>
      </div>
    </div>
    <div class='row'>
        <div class='form-group col-md-12'>
          <label for='name-filter'>Name:</label>
          <input type='text' name='name-filter' id='name-filter' />
        </div>

        <div class='form-group col-md-3'>
          <label for='gender-filter'>Gender:</label>
          <select name='gender-filter' class='form-control' id='gender-filter'>
            <option value='Any'>Any</option>
            <option value='Male'>Male</option>
            <option value='Female'>Female</option>
          </select>
        </div>

        <div class='form-group col-md-3'>
          <label for='species-filter'>Species:</label>
          <select name='species-filter' class='form-control' id='species-filter'>
            <option value='Any'>Any</option>
            <option value='Male'>Male</option>
            <option value='Female'>Female</option>
          </select>
        </div>

        <div class='form-group col-md-3'>
          <label for='species-filter'>Age:</label>
          <select name='age-filter' class='form-control' id='age-filter'>
            <option value='Any'>Any</option>
            <option value='Male'>Male</option>
            <option value='Female'>Female</option>
          </select>
        </div>


        <div class='form-group col-md-3'>
          <label for='species-filter'>Size:</label>
          <select name='age-filter' class='form-control' id='age-filter'>
            <option value='Any'>Any</option>
            <option value='Male'>Male</option>
            <option value='Female'>Female</option>
          </select>
        </div>


      <div class='col-sm-6'>
        <p class='legend'><strong>Legend</strong> <br />
        <i class='fas fa-neuter'></i> = Spayed/Neutered <br />
        <i class='fas fa-prescription'></i> = Requires Special Needs <br />
        <i class='fas fa-syringe'></i> = Current on Vaccinations (Pasturella) <br />
        <i class='fas fa-venus female'></i> = Female <i class='fas fa-mars male'></i> = Male
        </p>
      </div>
      <div class='col-sm-6'>
        <button>Apply Filters</button>
        <button>Reset Filters</button>
      </div>

      </div>
  </div>";

  return $html;
}

function display_animals(){
  #TODO: duplication, refactor this into something nicer.
  $CACHE_DIR = plugin_dir_path(__FILE__).'tmp/';
  $CACHE_FILE = $CACHE_DIR.'cached_call.json';


  $json = file_get_contents($CACHE_FILE);
  $json_data = json_decode($json, true);
  $json_data = array_msort($json_data, array('name'=>SORT_ASC));
  $counter = 0;
  $html = table_header($json_data);
  $html .= "<div class='container'>";
  $html .="<div class='row flex-row'>";
  $counter = 0;
  foreach ($json_data as $animal){
      $html .= "<div class='col-md-4 petfinder-container'>";
      if (!empty($animal['photos'])){
          $html .= "<a href='".$animal['url']."' target='_blank'><img class='rabbit_profile_picture' src='".$animal['photos'][0]['medium']."' /></a>";
        }
      else{
        # TODO: Get actaul photo for camer shy.
        $html .= "---CAMERA SHY HERE---";
      }
        $html .=  "<a href='".$animal['url']."' target='_blank'><p class='petfinder-rabbit-title'><span class='petfinder-rabbit-name'>".$animal['name']."</span>";
        if ($animal['gender'] == 'Female'){
          $html .= " (<i class='fas fa-venus female'></i>) ";
        }
        else{
          $html .= " (<i class='fas fa-mars male'></i>)";
        }
        $html .= "</p></a>";
        $html .= "<p class='petfinder-info-symbols'>";
        if ($animal['attributes']['spayed_neutered'] == '1'){
            $html .= " <i class='fas fa-neuter'></i>";
        }
        if ($animal['attributes']['special_needs'] == '1'){
            $html .= " <i class='fas fa-prescription'></i>";
        }
        if ($animal['attributes']['shots_current'] == '1'){
            $html .= " <i class='fas fa-syringe'></i>";
        }
        $html .= "</p>";


        $html .= "<p class='petfinder-breed-size-age'>".$animal['breeds']['primary']." (".$animal['size'].", ".$animal['age'].")</p>";
        $html .= "<p class='petfinder-description'>".htmlspecialchars_decode($animal['description'])." <a href='".$animal['url']."' target='_blank'> [Read more]</a></p>";
        $counter = $counter + 1;
        $html .= '</div>';

  }
$html .= '</div></div>';

return $html;
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
    $html = display_animals();
    return $html;
}

add_shortcode('hrrn_petfinder', 'hrrn_petfinder');
