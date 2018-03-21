<?php
/**
 * Name: WP Importer
 * Version: 1.0
 * Created by PhpStorm.
 * Project : vsp-framework
 * User: varun
 * Date: 20-03-2018
 * Time: 11:08 AM
 */

if( ! defined('ABSPATH') ) {
    die;
}


if( ! class_exists('WP_Importer') ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-importer.php' );
}

abstract class VSP_WP_Importer extends WP_Importer {
    public    $id          = NULL;
    public    $file_url    = NULL;
    public    $import_page = NULL;
    public    $options     = array();
    protected $delimiter   = ',';
    protected $header_size = 2;
    protected $errors      = array();

    public function __construct($options = array()) {
        $options = wp_parse_args($options, array(
            'page'   => FALSE,
            'fields' => array(),
            'title'  => '',
            'icon'   => '',
        ));

        $this->import_page = $options['page'];
        $this->options     = $options;

        parent::__construct();
    }

    public function dispatch() {
        $this->header();
        $step = empty($_GET['step']) ? 0 : (int) $_GET['step'];
        switch( $step ) {
            case 0:
                if( $this->is_writeable() === TRUE ) {
                    $this->greet();
                }
            break;

            case 1:
                check_admin_referer('import-upload');
                if( $this->handle_upload() ) {
                    if( $this->id ) {
                        $file = get_attached_file($this->id);
                    } else {
                        $file = ABSPATH . $this->file_url;
                    }

                    add_filter('http_request_timeout', array( $this, 'bump_request_timeout' ));
                    $this->init_import($file);
                }
            break;
        }

        $this->footer();
    }

    /**
     * Output header html.
     */
    public function header() {
        echo '<div class="wrap"><div class="icon32" id=""><br></div>';
        echo '<h1>' . $this->options['title'] . '</h1>';
    }

    public function is_writeable() {
        $upload_dir = wp_upload_dir();
        if( ! empty($upload_dir['error']) ) {
            echo '<div class="error"><p>' . __('Before you can upload your import file, you will need to fix the following error:') . '</p>
            <p><strong>' . $upload_dir['error'] . '</strong></p></div>';
            return FALSE;
        }
        return TRUE;
    }

    public function greet() {
        echo '<div class="vsp-wp-importer-wrap">
<div class="wpsf-content"> <form enctype="multipart/form-data" id="import-upload-form" method="post" action="' . esc_attr(wp_nonce_url($this->step_url(1), 'import-upload')) . '">
<input type="hidden" name="action" value="save" /> <input type="hidden" name="max_file_size" value="' . $this->upload_size(TRUE) . '" />';
        if( ! empty($this->fields()) ) {
            foreach( $this->fields() as $field ) {
                $default = ( isset($field['default']) ) ? $field['default'] : NULL;
                echo wpsf_add_element($field, $default);
            }
        }

        echo '<div class="wpsf-element"><p class="submit"> <input type="submit" class="button" value="' . __("Upload file and import") . '" /> </p></div> </form>';
        echo '</div>';
        echo '</div>';
        echo '<style>
                .vsp-wp-importer-wrap form > .wpsf-element { padding: 15px 0; } 
                .wpsf-element > p.submit{margin:0;padding:0;} 
                .vsp-wp-importer-wrap form > .wpsf-element .wpsf-fieldset{margin-left:0%;} 
            </style>';
    }

    public function step_url($step = 0) {
        return admin_url('admin.php?import=' . $this->import_page . '&step=' . $step);
    }

    public function upload_size($only_bytes = FALSE) {
        $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
        if( $only_bytes ) {
            return $bytes;
        }
        return size_format($bytes);
    }

    public function fields() {
        return array();
    }

    /**
     * Handles the CSV upload and initial parsing of the file to prepare for.
     * displaying author import options.
     *
     * @return bool False if error uploading or invalid file, true otherwise
     */
    public function handle_upload() {
        $upload_type       = ( isset($_POST['upload_type']) && ! empty($_POST['upload_type']) ) ? $_POST['upload_type'] : 'upload';
        $this->upload_type = $upload_type;

        if( $upload_type === 'upload' ) {
            $file = wp_import_handle_upload();
            if( isset($file['error']) ) {
                $this->import_error($file['error']);
            }
            $this->id = absint($file['id']);

        } else if( file_exists(ABSPATH . $_POST['file_url']) ) {
            $this->file_url = esc_attr($_POST['file_url']);
        } else {
            $this->import_error();
        }

        return TRUE;
    }

    /**
     * Show import error and quit.
     *
     * @param  string $message
     */
    private function import_error($message = '') {
        echo '<p><strong>' . __('Sorry, there has been an error.') . '</strong><br />';
        if( $message ) {
            echo esc_html($message);
        }
        echo '</p>';
        $this->footer();
        die();
    }

    /**
     * Output footer html.
     */
    public function footer() {
        echo '</div>';
    }

    /**
     * Import the file if it exists and is valid.
     *
     * @param mixed $file
     */
    public function init_import($file) {
        if( ! is_file($file) ) {
            $this->import_error(__('The file does not exist, please try again.'));
        }

        $this->__import_start();
        $loop = 0;

        if( ( $handle = fopen($file, "r") ) !== FALSE ) {
            $header = fgetcsv($handle, 0, $this->delimiter);
            if( $this->header_size === sizeof($header) ) {
                while( ( $row = fgetcsv($handle, 0, $this->delimiter) ) !== FALSE ) {
                    $this->import($row);
                    $loop++;
                }
            } else {
                $this->import_error(__('The CSV is invalid.'));
            }
            fclose($handle);
        }
        $this->__import_end();
    }

    /**
     * Import is starting.
     */
    private function __import_start() {
        if( function_exists('gc_enable') ) {
            gc_enable();
        }
        vsp_set_time_limit(0);
        @ob_flush();
        @flush();
        @ini_set('auto_detect_line_endings', '1');
        $this->before_import();
    }

    abstract protected function before_import();

    abstract protected function import($row = array());

    public function __import_end() {
        $this->after_import();
        $this->show_success();
        $this->show_errors();
        do_action('import_end');

    }

    abstract protected function after_import();

    protected function show_success() {
        echo '<div class="updated settings-error"><p>
			' . __('Import completed') . '
		</p></div>';
    }

    protected function show_errors() {
        if( ! empty(array_filter($this->errors)) ) {
            echo '<div class="error settings-error">';
            echo '<p>' . __("Import Errors -") . '</p>';
            echo '<p>' . implode("<br/>", $this->errors) . '</p>';
            echo '</div>';
        }
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import.
     *
     * @param  int $val
     *
     * @return int 60
     */
    public function bump_request_timeout($val) {
        return 60;
    }

    protected function error($error = '') {
        $this->errors[] = $error;
    }
}