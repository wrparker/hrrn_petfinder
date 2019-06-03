<?php

function option_defaults(){
    return array(
        'api_key' => false,
        'secret_key' => false,
        'bootstrap_import' => false
    );
}
class HrrnPetfinderSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'HRRN Petfinder Settings',
            'manage_options',
            'hrrn-petfinder-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'hrrn_petfinder', option_defaults() );
        ?>
        <div class="wrap">
            <h1>My Settings</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'hrrn_petfinder_group' );
                do_settings_sections( 'hrrn-petfinder-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'hrrn_petfinder_group', // Option group
            'hrrn_petfinder', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'main_section', // ID
            'Petfinder API Token', // Title
            array( $this, 'print_section_info' ), // Callback
            'hrrn-petfinder-admin' // Page
        );

        add_settings_field(
            'api_key', // ID
            'Petfinder API Key', // Title
            array( $this, 'api_key_callback' ), // Callback
            'hrrn-petfinder-admin', // Page
            'main_section' // Section
        );

        add_settings_field(
            'secret_key', // ID
            'Petfinder Secret Key', // Title
            array( $this, 'secret_key_callback' ), // Callback
            'hrrn-petfinder-admin', // Page
            'main_section' // Section
        );

        add_settings_field(
            'bootstrap_import', // ID
            'Needs Bootstrap import?  (No if theme uses bootstrap)', // Title
            array( $this, 'bootstrap_import_callback' ), // Callback
            'hrrn-petfinder-admin', // Page
            'main_section' // Section
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['api_key'] ) )
            $new_input['api_key'] = sanitize_text_field( $input['api_key'] );

        if( isset( $input['secret_key'] ) )
            $new_input['secret_key'] = sanitize_text_field( $input['secret_key'] );

        if ( isset( $input['bootstrap_import'] ) )
            $new_input['bootstrap_import'] = $input['bootstrap_import'] == 'Yes' ? true : false;

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="hrrn_petfinder[api_key]" value="%s" />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );
    }

    public function secret_key_callback()
    {
        printf(
            '<input type="text" id="secret_key" name="hrrn_petfinder[secret_key]" value="%s" />',
            isset( $this->options['secret_key'] ) ? esc_attr( $this->options['secret_key']) : ''
        );
    }

    public function bootstrap_import_callback()
    {
        $items = array("Yes", "No");
        echo "<select id='bootstrap_import' name='hrrn_petfinder[bootstrap_import]'>";
        foreach($items as $item) {
            $selected = (($this->options['bootstrap_import']==true && $item == 'Yes') || ($this->options['bootstrap_import']==false && $item == 'No'))  ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";
    }
}