<?php
class Academizer_Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public static $defaults = array(
        'name' => '',
        'style' => 'simple',
        'theme' => 'light'
    );

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
            'Academizer Settings', 
            'Academizer', 
            'manage_options', 
            'academizer-settings', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'academizer_options', self::$defaults );
        ?>
        <div class="wrap">
            <h1>Academizer <em>1.0</em></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'academizer_option_group' );
                do_settings_sections( 'academizer-settings' );
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
            'academizer_option_group', // Option group
            'academizer_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'academizer_section_main', // ID
            'Academizer Settings', // Title
            array( $this, 'print_academizer_section_main_info' ), // Callback
            'academizer-settings' // Page
        );  

        add_settings_field(
            'name', 
            'Full Name', 
            array( $this, 'academizer_name_callback' ),
            'academizer-settings',
            'academizer_section_main'
        );

        add_settings_section(
            'academizer_section_style', // ID
            'Academizer Style', // Title
            array( $this, 'print_academizer_section_style_info' ), // Callback
            'academizer-settings' // Page
        );

        add_settings_field(
            'style',
            'Style',
            array( $this, 'academizer_style_callback' ),
            'academizer-settings',
            'academizer_section_style'
        );

        add_settings_field(
            'theme',
            'Theme',
            array( $this, 'academizer_theme_callback' ),
            'academizer-settings',
            'academizer_section_style'
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

        if( isset( $input['name'] ) )
            $new_input['name'] = sanitize_text_field( $input['name'] );

        if( isset( $input['style'] ) )
            $new_input['style'] = sanitize_text_field( $input['style'] );

        if( isset( $input['theme'] ) )
            $new_input['theme'] = sanitize_text_field( $input['theme'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_academizer_section_main_info()
    {
        print 'Enter your name below as <em>"Surname, Name(s)"</em>, including the comma but without quotes. It will be used to identify yourself in the citation and apply a specific css class.';
    }

    public function print_academizer_section_style_info()
    {
        print 'Choose how Academizer renders references. You can still override the default style by <a href="https://wordpress.org/plugins/academizer/#installation" target="_blank">customising the shortcode.</a>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function academizer_name_callback()
    {
        printf(
            '<input type="text" id="name" name="academizer_options[name]" value="%s" />',
            isset( $this->options['name'] ) ? esc_attr( $this->options['name']) : ''
        );
    }

    public function academizer_style_callback()
    {
        print '
        <fieldset>
            <label for="academizer_style-simple"><input type="radio" name="academizer_options[style]" id="academizer_style-simple" value="simple"'. ('simple' == $this->options['style'] ? ' checked' : false ) . '>Simple</label>
            <br>
            <label for="academizer_style-thumbnail_left"><input type="radio" name="academizer_options[style]" id="academizer_style-thumbnail_left" value="thumbnail_left"'. ('thumbnail_left' == $this->options['style'] ? ' checked' : false ) . '>Thumbnail (left)</label>
            <br>
            <label for="academizer_style-detailed"><input type="radio" name="academizer_options[style]" id="academizer_style-detailed" value="detailed"'. ('detailed' == $this->options['style'] ? ' checked' : false ) . '>Detailed <em>(requires Bootstrap 4)</em></label>
        </fieldset>';
    }

    public function academizer_theme_callback() {
        print '
        <fieldset>
            <label for="academizer_theme-dark"><input type="radio" name="academizer_options[theme]" id="academizer_theme-dark" value="dark"'. ('dark' == $this->options['theme'] ? ' checked' : false ) . '>Dark</label>
            <br>
            <label for="academizer_theme-light"><input type="radio" name="academizer_options[theme]" id="academizer_theme-light" value="light"'. ('light' == $this->options['theme'] ? ' checked' : false ) . '>Light</label>
        </fieldset>
        <p class="description">You can customise the themes in the file <em>/academizer/css/academizer.css</em>.</p>';
    }
}

if( is_admin() )
    $academizer_settings = new Academizer_Settings();