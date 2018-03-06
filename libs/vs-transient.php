<?php
/**
 * Name: VS Transient
 * Version: 1.0
 * Created by PhpStorm.
 * User: varun
 * Date: 28-02-2018
 * Time: 03:50 PM
 */

abstract class VS_Transient_WP_Api {
    protected $is_option             = FALSE;
    protected $transient_limit       = 170;
    protected $option_limit          = 190;
    protected $option_prefix         = '';
    protected $option_surfix         = '';
    protected $transient_prefix      = '';
    protected $transient_surfix      = '';
    protected $option_version        = 1.0;
    protected $transient_version     = 1.0;
    protected $option_auto_delete    = FALSE;
    protected $transient_auto_delete = FALSE;

    public function key($key = '', $is_option = FALSE) {
        return $this->get_key($this->validate_length($key), $is_option);
    }

    public function get_key($key = '', $is_option = FALSE) {
        if( $is_option === TRUE ) {
            return $this->option_prefix . $key . $this->option_surfix;
        }

        return $this->transient_prefix . $key . $this->transient_surfix;
    }

    protected function validate_length($key = '') {
        if( $this->check_length($key) === FALSE ) {
            return $this->validate_length(md5($key));
        }
        return $key;
    }

    protected function check_length($key = '') {
        $length = strlen($key);
        if( $this->is_option === TRUE ) {
            return ( strlen($this->get_key($key)) > $this->option_limit ) ? FALSE : TRUE;
        }

        return ( strlen($this->get_key($key)) > $this->transient_limit ) ? FALSE : TRUE;
    }

    protected function wp_add_option($key = '', $value = '', $autoload = 'no') {
        return add_option($key, $value, '', $autoload);
    }

    protected function wp_update_option($key = '', $value = '', $autoload = 'no') {
        return update_option($key, $value, $autoload);
    }

    protected function wp_delete_option($key = '') {
        return delete_option($key);
    }

    protected function wp_get_option($key = '', $default = FALSE) {
        return get_option($key, $default);
    }

    protected function wp_set_transient($transient, $value, $expiration = 0) {
        return set_transient($transient, $value, $expiration);
    }

    protected function wp_get_transient($transient) {
        return get_transient($transient);
    }

    protected function wp_delete_transient($transient) {
        return delete_transient($transient);
    }

    protected function get_version_key($key = '') {
        return $this->validate_length($key . '-version');
    }

    protected function validate_version($value, $type = '') {
        if( $value === FALSE || empty($value) || is_null($value) ) {
            return FALSE;
        }

        if( $type === 'option' ) {
            return version_compare($this->option_version, $value, '=');
        }

        return version_compare($this->transient_version, $value, '=');
    }

    protected function delete_version_issue($key, $type = '') {
        if( $this->option_auto_delete === TRUE && $type === 'option' ) {
            $this->delete_option($key);
        }

        if( $this->transient_auto_delete === TRUE ) {
            return $this->delete_transient($key);
        }
    }
}

abstract class VS_Transient_Api extends VS_Transient_WP_Api {

    public function set($key = '', $value = '', $expiry = '') {
        if( $this->is_option === TRUE ) {
            return $this->set_option($key, $value, $expiry);
        }
        return $this->set_transient($key, $value, $expiry);

    }

    public function set_option($_key, $value, $status = '') {
        $key         = $this->key($_key, TRUE);
        $version_key = $this->get_version_key($key);
        $_status     = $this->wp_add_option($key, $value, $status);
        $this->wp_add_option($version_key, $this->option_version, $status);
        return $_status;
    }

    public function set_transient($_key, $value, $expiry = 0) {
        $key         = $this->key($_key, FALSE);
        $version_key = $this->get_version_key($key);
        $_status     = $this->wp_set_transient($key, $value, $expiry);
        $this->wp_set_transient($version_key, $this->option_version, $expiry);
        return $_status;
    }


    public function get($key = '') {
        if( $this->is_option === TRUE ) {
            return $this->get_option($key);
        }

        return $this->get_transient($key);
    }

    public function get_option($_key) {
        $key         = $this->key($_key, TRUE);
        $version_key = $this->get_version_key($key);
        $version     = $this->wp_get_option($version_key, TRUE);
        if( $this->validate_version($version, 'option') === FALSE ) {
            $this->delete_version_issue($_key);
            return FALSE;
        }

        return $this->wp_get_option($key);
    }

    public function get_transient($_key) {
        $key         = $this->key($_key, FALSE);
        $version_key = $this->get_version_key($key);
        $version     = $this->wp_get_transient($version_key);
        if( $this->validate_version($version, 'transient') === FALSE ) {
            $this->delete_version_issue($_key, 'transient');
            return FALSE;
        }
        return $this->wp_get_transient($key);
    }


    public function delete($key) {
        if( $this->is_option === TRUE ) {
            return $this->delete_option($key);
        }
        return $this->delete_transient($key);
    }

    public function delete_option($_key) {
        $key         = $this->key($_key, TRUE);
        $version_key = $this->get_version_key($key);
        $this->wp_delete_option($key);
        $this->wp_delete_option($version_key);
    }

    public function delete_transient($_key) {
        $key         = $this->key($_key, FALSE);
        $version_key = $this->get_version_key($key);
        $this->wp_delete_transient($key);
        $this->wp_delete_transient($version_key);
    }


    public function update($key, $value = '', $expiry = '') {
        if( $this->is_option ) {
            return $this->update_option($key, $value, $expiry);
        }
    }

    public function update_option($key, $value, $status = '') {
        $key         = $this->key($key, TRUE);
        $version_key = $this->get_version_key($key);
        $_status     = $this->wp_update_option($key, $value, $status);
        $this->wp_update_option($version_key, $this->option_version, $status);
        return $_status;
    }

}