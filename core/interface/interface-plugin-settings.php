<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:09 AM
 */

interface VSP_Plugin_Settings_Interface {
    /**
     * @param array $pages
     * @return mixed
     */
    public function add_pages($pages = array());

    /**
     * @param array $sections
     * @return mixed
     */
    public function add_sections($sections = array());

    /**
     * @param array $fields
     * @return mixed
     */
    public function add_fields($fields = array());
}