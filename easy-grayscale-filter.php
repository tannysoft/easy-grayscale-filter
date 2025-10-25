<?php
/**
* Plugin Name:		 Easy Grayscale Filter
* Plugin URI:		 https://www.tannysoft.com
* Description:		 ปลั้กอินสำหรับเปลี่ยนสีเว็บไซต์ที่ใช้ WordPress เป็นสีขาวดำ สอบถามเพิ่มเติมได้ที่ https://www.tannysoft.com
* Version:			 1.3.0
* Author:			 Tannysoft
* Author 			 URI: https://www.tannysoft.com
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       easy-grayscale-filter
* Domain Path:       /languages
*/


add_action( 'wp_enqueue_scripts', 'easy_grayscale_filter_styles' );



function add_remove_grayscale() {

    $server_name = isset($_SERVER["SERVER_NAME"]) ? sanitize_text_field(wp_unslash($_SERVER["SERVER_NAME"])) : '';
    $local_key = str_replace(".", "_", $server_name);

    echo "<div class=\"remove-filter\"><a href=\"#\" class=\"btn-remove-filter\">ปิดโหมดสีเทา</a></div>";
    echo '

    <script>
    
    document.addEventListener(\'DOMContentLoaded\', function() {

        var is_grayscale = localStorage.getItem("' . esc_js($local_key) . '_easy_grayscale_filter");

        if(is_grayscale==1) {
            clear_grayscale();
        }

        var removeFilterBtn = document.querySelector(".btn-remove-filter");
        if (removeFilterBtn) {
            removeFilterBtn.addEventListener(\'click\', function(e) {
                e.preventDefault();
                clear_grayscale();
                localStorage.setItem("' . esc_js($local_key) . '_easy_grayscale_filter", 1);
            });
        }

        function clear_grayscale() {
            document.documentElement.classList.add("no-grayscale");
            var removeFilter = document.querySelector(".remove-filter");
            if (removeFilter) {
                removeFilter.remove();
            }
        }

    });   

    </script>    
    ';
}

function easy_grayscale_filter_styles() {
	if(!is_admin()) {
		$option = get_option( 'easy_grayscale_filter_option' );

		if(($option) and ($option!==null) and !empty($option)):
			$percent = $option['percent_number'];
			$percent_divide = $percent / 100;
		else:
			$percent = 40;
			$percent_divide = 4;
        endif;
        

		wp_enqueue_style(
			'easy-grayscale-filter',
            plugin_dir_url( __FILE__ ) . 'css/easy-grayscale-filter.css', array(),
            '1.1.2.000001',
            'all'
        );

        $custom_css = "html {
				/* IE */
				filter: progid:DXImageTransform.Microsoft.BasicImage(grayscale=$percent_divide);
				/* Chrome, Safari */
				-webkit-filter: grayscale($percent_divide);
				/* Firefox */
				filter: grayscale($percent_divide);
				filter: grayscale($percent%);
				filter: gray; 
				-moz-filter: grayscale($percent%);
				-webkit-filter: grayscale($percent%);
			}";
        wp_add_inline_style( 'easy-grayscale-filter', $custom_css );
        

        add_action('wp_footer', 'add_remove_grayscale');
	}
}

class Easy_Grayscale_Filter_Page
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
            'Easy Grayscale Filter', 
            'manage_options', 
            'easy-grayscale-filter', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'easy_grayscale_filter_option' );
        ?>
        <div class="wrap">
            <h1>Easy Grayscale Filter</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( 'easy-grayscale-filter' );
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
            'my_option_group', // Option group
            'easy_grayscale_filter_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'easy-grayscale-filter' // Page
        );  

        add_settings_field(
            'percent_number', // ID
            'ค่าสีขาวดำ (1-100%)', // Title 
            array( $this, 'percent_number_callback' ), // Callback
            'easy-grayscale-filter', // Page
            'setting_section_id' // Section           
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
        if( isset( $input['percent_number'] ) )
            $new_input['percent_number'] = absint( $input['percent_number'] );

        return $new_input;
    }

    public function print_section_info()
    {
        print 'ปรับค่าสีขาวดำของเว็บไซต์:';
    }

    public function percent_number_callback()
    {
        printf(
            '<input type="text" id="percent_number" name="easy_grayscale_filter_option[percent_number]" value="%s" />',
            isset( $this->options['percent_number'] ) ? esc_attr( $this->options['percent_number']) : '40'
        );
    }

}

if( is_admin() )
    $my_settings_page = new Easy_Grayscale_Filter_Page();