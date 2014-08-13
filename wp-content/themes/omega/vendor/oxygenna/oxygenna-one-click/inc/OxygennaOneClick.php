<?php
/**
 * One Click Installer
 *
 * @package One Click Installer
 * @subpackage Admin
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license **LICENSE**
 * @version 1.2
 * @author Oxygenna.com
 */

define('OXY_ONECLICK_DIR', OXY_THEME_DIR . 'vendor/oxygenna/oxygenna-one-click/');
define('OXY_ONECLICK_URI', OXY_THEME_URI . 'vendor/oxygenna/oxygenna-one-click/');

class OxygennaOneClick
{
    public function __construct()
    {
        add_action('init', array(&$this, 'init'));
        // register hook for before option page
        add_action(THEME_SHORT . '-oneclick-before-page', array(&$this, 'render'));
        // add ajax calls
        add_action('wp_ajax_oxy_import_start', array(&$this, 'import_start'));

        add_action('wp_ajax_oxy_import_post', array(&$this, 'import_post'));

        add_action('wp_ajax_oxy_import_menu', array(&$this, 'import_menu'));

        add_action('wp_ajax_oxy_import_final_setup', array(&$this, 'final_setup'));

        add_action('wp_ajax_oxy_import_end', array(&$this, 'import_end'));

        add_action('wp_ajax_oxy_import_slideshow', array(&$this, 'import_slideshow'));
    }

    public function init()
    {
        include OXY_ONECLICK_DIR . 'inc/option-pages.php';
    }

    public function return_object()
    {
        $return = new stdClass();
        $return->status = false;
        $return->data = '';

        return $return;
    }

    public function import_start()
    {
        @error_reporting(0); // Don't break the JSON result
        header('Content-Type: application/json');

        $return = $this->return_object();
        $return->data = new WP_Error('Import Start', 'Could not validate nonce.');

        if (isset($_POST['nonce'])) {
            if (wp_verify_nonce($_POST['nonce'], 'oxy-importer')) {
                $import_job = new stdClass();
                $import_job->id = uniqid();
                $import_job->package = $_POST['installPackage'];
                update_option('import-'.$import_job->id, $import_job);
                $return->status = true;
                $return->data = $import_job;
            }
        }

        echo json_encode($return);
        die();
    }

    public function import_post()
    {
        header('Content-Type: application/json');
        @error_reporting(0); // Don't break the JSON result

        $return = $this->return_object();

        if (isset($_POST['nonce'])) {
            if (wp_verify_nonce($_POST['nonce'], 'oxy-importer')) {
                $new_post = json_decode(stripslashes($_POST['data']), true);

                $return->data = new WP_Error('CREATE_POST', 'Could not make this post.', $new_post);

                $new_post_return_id = null;
                if ($new_post) {
                    if (post_type_exists($new_post['post_type'])) {
                        switch ($new_post['post_type']) {
                            case 'attachment':
                                $new_post_return_id = $this->create_attachment($new_post);
                                break;
                            case 'post':
                            case 'page':
                            case 'oxy_slideshow_image':
                            case 'oxy_service':
                            case 'oxy_testimonial':
                            case 'oxy_staff':
                            case 'oxy_portfolio_image':
                            case 'product':
                                $new_post_return_id = $this->create_post($new_post);
                                break;
                        }
                    }
                }

                if ($new_post_return_id !== null  && !is_wp_error($new_post_return_id)) {
                    $return->status = true;
                    $return->data = get_post($new_post_return_id);
                }

            } else {
                $return->data = new WP_Error('Import Post Failed', 'Could not validate nonce.', $_POST['data']);
            }
        } else {
            $return->data = new WP_Error('Import Post Failed', 'Could not validate nonce.', $_POST['data']);
        }

        echo json_encode($return);
        die();
    }

    public function create_attachment($post)
    {
        @set_time_limit(900); // 5 minutes per image should be PLENTY
        header('Content-Type: application/json');

        $return = $this->return_object();

        $image_url = apply_filters('oxy_one_click_import_download_url', $post['filename']);

        $response = wp_remote_get($image_url, array('sslverify' => false));

        if (is_wp_error($response)) {
            return $response;
        } else {
            $image_body = wp_remote_retrieve_body($response);

            // next upload to the WP uploads directory
            $upload = wp_upload_bits($post['filename'], null, $image_body);

            // did everything go ok?
            if ($upload['error']) {
                return new WP_Error('upload_dir_error', $upload['error']);
            }

            // everything is fine so lets
            $attachment = array(
                'guid'           => $upload['url'],
                'post_mime_type' => $post['post_mime_type'],
                'post_title'     => $post['post_title'],
                'post_content'   => $post['post_content'],
                'post_status'    => $post['post_status']
            );

            // create attachment post
            $attach_id = wp_insert_attachment($attachment, $upload['file']);
            // regenerate thumbnails
            $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // store old url and new url in map
            $map = $this->add_to_map('images', $post['guid'], $upload['url']);

            // store old id and new id in map
            $map = $this->add_to_map('attachments', $post['ID'], $attach_id);

        }
        return $attach_id;
    }

    public function replace_shortcode_attachment_id($content, $shortcode, $param, $lookup_map)
    {
        if (preg_match_all('/\[' . $shortcode . '[^\]]*' . $param . '="([^"]*)"[^\]]*\]/i', $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                // found a single image
                // replace old ids with new ones
                if (array_key_exists($i, $matches[0]) && array_key_exists($i, $matches[1])) {
                    $new_id = $this->lookup_map($lookup_map, $matches[1][$i]);
                    if ($new_id !== false) {
                        $old_string = $matches[0][$i];
                        $new_string = str_replace($matches[1][$i], $new_id, $matches[0][$i]);
                        $content = str_replace($old_string, $new_string, $content);
                    }
                }
            }
        }

        return $content;
    }

    public function replace_gallery_shortcode_ids($post_content)
    {
        $pattern = get_shortcode_regex();
        // look for an embeded shortcode in the post content
        if (preg_match_all('/'. $pattern .'/s', $post_content, $gallery_shortcode) && array_key_exists(2, $gallery_shortcode) && in_array('gallery', $gallery_shortcode[2])) {
            if (isset($gallery_shortcode[0])) {
                // show gallery
                $gallery_ids = null;
                if (array_key_exists(3, $gallery_shortcode)) {
                    if (array_key_exists(0, $gallery_shortcode[3])) {
                        $gallery_attrs = shortcode_parse_atts($gallery_shortcode[3][0]);
                        if (array_key_exists('ids', $gallery_attrs)) {
                            // we have a gallery with ids so lets replace the ids
                            $gallery_ids = explode(',', $gallery_attrs['ids']);
                            $new_gallery_ids = array();
                            foreach ($gallery_ids as $gallery_id) {
                                $new_gallery_ids[] = $this->lookup_map('attachments', $gallery_id);
                            }
                            // replace old ids with new ones
                            $old_string = 'ids="' . implode(',', $gallery_ids) . '"';
                            $new_string = 'ids="' . implode(',', $new_gallery_ids) . '"';
                            $post_content = str_replace($old_string, $new_string, $post_content);
                        }
                    }
                }
            }
        }
        return $post_content;
    }

    public function create_post($post)
    {
        $post = apply_filters('oxy_one_click_post', $post, $this);

        $old_id = $post['ID'];

        unset($post['ID']);
        unset($post['guid']);
        unset($post['post_parent']);

        $new_id = wp_insert_post($post);

        $this->add_to_map($post['post_type'], $old_id, $new_id);

        // handle custom fields
        if (isset($post['custom_fields'])) {
            foreach ($post['custom_fields'] as $key => $value) {
                $add_field = false;
                switch ($key) {
                    case '_thumbnail_id':
                        foreach ($value as $old_media_id) {
                            $new_media_id = $this->lookup_map('attachments', $old_media_id);
                            if ($new_media_id !== false) {
                                add_post_meta($new_id, '_thumbnail_id', $new_media_id);
                            }
                        }
                        break;
                    case '_product_image_gallery':
                        $old_media_ids = explode(',', $value[0]);
                        $new_media_ids = array();
                        foreach ($old_media_ids as $old_media_id) {
                            $new_media_id = $this->lookup_map('attachments', $old_media_id);
                            if ($new_media_id !== false) {
                                $new_media_ids[] = $new_media_id;
                            }
                        }
                        if (count($new_media_id) > 0) {
                            add_post_meta($new_id, '_product_image_gallery', implode(',', $new_media_ids));
                        }
                        break;
                    case THEME_SHORT . '_post_gallery':
                        foreach ($value as $post_gallery) {
                            $post_gallery = $this->replace_gallery_shortcode_ids($post_gallery);
                            add_post_meta($new_id, $key, $post_gallery);
                        }
                        break;
                    case '_edit_last':
                        // ignore
                        break;
                    case THEME_SHORT . '_masonry_image':
                    case THEME_SHORT . '_background_image':
                        // get the new url of the image
                        $new_url = $this->lookup_map('images', $value[0]);
                        add_post_meta($new_id, $key, $new_url);
                        break;
                    default:
                        $add_field = true;
                        break;
                }

                if ($add_field) {
                    foreach ($value as $old_value) {
                        add_post_meta($new_id, $key, $old_value);
                    }
                }
            }
        }

        if (isset($post['taxonomies'])) {
            $taxonomies = get_taxonomies();
            foreach ($taxonomies as $taxonomy) {
                if (isset($post['taxonomies'][$taxonomy])) {
                    foreach ($post['taxonomies'][$taxonomy] as $old_tax) {
                        $term_id = term_exists($old_tax['slug'], $taxonomy);

                        // if tag doesnt exist we must create it
                        if (!$term_id) {
                            $new_tag_args = array('slug' => $old_tax['slug'], 'description' => $old_tax['description']);
                            if ($old_tax['parent'] !== 0) {
                                $new_tag_args['parent'] = $this->lookup_map($taxonomy, $old_tax['term_id']);
                            }
                            $term_id = wp_insert_term($old_tax['name'], $taxonomy, $new_tag_args);
                        }

                        if (! is_wp_error($term_id)) {

                            if (is_array($term_id)) {
                                $term_id = $term_id['term_id'];
                            }

                            // store old / new term id in map
                            $this->add_to_map($taxonomy, $old_tax['term_id'], $term_id);

                            // now save the taxonomy
                            if ($taxonomy === 'post_tag' || $taxonomy === 'product_tag') {
                                 wp_set_post_terms($new_id, $old_tax['name'], $taxonomy, true);
                            } else {
                                wp_set_post_terms($new_id, array($term_id), $taxonomy, true);
                            }
                        }
                    }
                }
            }
        }

        // handle post_format
        if (isset($post['format']) && $post['format'] !== false) {
            set_post_format($new_id, $post['format']);
        }

        $this->attach_images($post, $new_id);

        return $new_id;
    }

    public function attach_images($post, $new_post_id)
    {
        if (isset($post['attachments'])) {
            foreach ($post['attachments'] as $old_attachment_id) {
                $this->update_post_parent($this->lookup_map('attachments', $old_attachment_id), $new_post_id);
            }
        }
    }

    public function update_post_parent($post_id, $parent_id)
    {
        global $wpdb;

        $parent_id = (string) $parent_id;
        $post_id = (string) $post_id;
        $result = $wpdb->update($wpdb->posts, array('post_parent' => $parent_id), array('ID' => $post_id));

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    public function import_menu()
    {
        @error_reporting(0); // Don't break the JSON result
        header('Content-Type: application/json');
        @set_time_limit(900); // 5 minutes per menu should be PLENTY
        $return = $this->return_object();

        if (isset($_POST['nonce'])) {
            if (wp_verify_nonce($_POST['nonce'], 'oxy-importer')) {
                $menu = json_decode(stripslashes($_POST['data']), true);
                // var_dump($menu);
                $new_menu_id = wp_create_nav_menu($menu['name']);
                $locations = get_theme_mod('nav_menu_locations');

                if (is_wp_error($new_menu_id)) {
                    // menu must already exist so get that
                    if (isset($menu['location']) && isset($locations[$menu['location']])) {
                        $new_menu_id = $locations[$menu['location']];
                    } else {
                        $menu_lookup = get_term_by('slug', $menu['slug'], 'nav_menu');
                        $new_menu_id = $menu_lookup->term_id;
                    }

                } else {
                    // set the new menu to location if we have one
                    if (isset($menu['location'])) {
                        $locations[$menu['location']] = $new_menu_id;
                        set_theme_mod('nav_menu_locations', $locations);
                    }
                }

                foreach ($menu['menu_items'] as $menu_item) {
                    $this->import_menu_item($menu_item, $new_menu_id);
                }

                // return new menu id
                $return->status = true;
                $return->data = $new_menu_id;
            } else {
                $return->data = new WP_Error('Import Menu', 'Could not validate nonce.');
            }
        } else {
            $return->data = new WP_Error('Import Menu', 'Could not validate nonce.');
        }

        echo json_encode($return);
        die();
    }

    private function import_menu_item($menu_item, $menu_id)
    {
        $new_menu_item = array(
            'menu-item-type'        => $menu_item['type'],
            'menu-item-object'      => $menu_item['object'],
            'menu-item-object-id'   => 0,
            'menu-item-status'      => $menu_item['post_status'],
            'menu-item-title'       => $menu_item['title'],
            'menu-item-description' => $menu_item['description'],
            'menu-item-attr-title'  => $menu_item['attr_title'],
            'menu-item-target'      => $menu_item['target'],
            'menu-item-classes'     => implode(' ', $menu_item['classes']),
            'menu-item-xfn'         => $menu_item['xfn'],
            'menu-item-url'         => $menu_item['url'],
        );

        if ($menu_item['menu_item_parent'] != 0) {
            $new_menu_item['menu-item-parent-id'] = $this->lookup_map('menu_items', $menu_item['menu_item_parent']);
        }

        $new_menu_item = apply_filters('oxy_one_click_import_menu_item', $new_menu_item, $menu_item, $this);

        $new_menu_item_id = @wp_update_nav_menu_item($menu_id, 0, $new_menu_item);

        if (!is_wp_error($new_menu_item_id)) {
            // add to map
            $this->add_to_map('menu_items', $menu_item['ID'], $new_menu_item_id);
        }

        return $new_menu_item_id;
    }

    public function final_setup()
    {
        @error_reporting(0); // Don't break the JSON result
        header('Content-Type: application/json');

        $return = $this->return_object();

        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'oxy-importer')) {
            if (isset($_POST['data'])) {
                $data = json_decode(stripslashes($_POST['data']), true);

                if (isset($data['page_options'])) {
                    foreach ($data['page_options'] as $option => $option_value) {
                        update_option($option, $this->lookup_map('page', $option_value));
                    }
                }

                if (isset($data['options'])) {
                    foreach ($data['options'] as $option => $option_value) {
                        update_option($option, $option_value);
                    }
                }
            }
            $return->status = true;
        } else {
            $return->data = new WP_Error('Import Menu', 'Could not validate nonce.');
        }
        echo json_encode($return);
        die();
    }

    public function import_end()
    {
        @error_reporting(0); // Don't break the JSON result
        header('Content-Type: application/json');

        $return = $this->return_object();

        if (isset($_POST['nonce'])) {
            if (wp_verify_nonce($_POST['nonce'], 'oxy-importer')) {
                $import_job_id = $_POST['data'];

                $import_job = get_option('import-' . $import_job_id);

                do_action('oxy_one_click_import_end', $this, $import_job);

                $success = delete_option('import-' . $import_job_id);

                $return->status = true;
                $return->data = $import_job_id;
            } else {
                $return->data = new WP_Error('Import Menu', 'Could not validate nonce.');
            }
        } else {
            $return->data = new WP_Error('Import Menu', 'Could not validate nonce.');
        }
        echo json_encode($return);
        die();
    }

    public function import_slideshow()
    {
        @error_reporting(0); // Don't break the JSON result
        header('Content-Type: application/json');

        $return = $this->return_object();

        if (isset($_POST['nonce'])) {
            if (wp_verify_nonce($_POST['nonce'], 'oxy-importer')) {
                $slideshow = $_POST['slideshow'];
                // upload slideshow zip
                $url = $slideshow['url'];
                $filename = $slideshow['filename'];

                $response = wp_remote_get($url, array('sslverify' => false));

                if (is_wp_error($response)) {
                    $return->data = new WP_Error('Import Slider', 'Could not download ' . $url);
                } else {
                    $body = wp_remote_retrieve_body($response);

                    // next upload to the WP uploads directory
                    $upload = wp_upload_bits($filename, null, $body);

                    // did everything go ok?
                    if ($upload['error']) {
                        $return->data = new WP_Error('Import Slider', 'Could not save ' . $filename);
                    } else {
                        // we have the slider now install
                        switch($slideshow['type']) {
                            case 'layerslider':
                                if (is_plugin_active('LayerSlider/layerslider.php')) {
                                    // Get importUtil
                                    include LS_ROOT_PATH . '/classes/class.ls.importutil.php';

                                    // import the layer slider
                                    $import_object = @new LS_ImportUtil($upload['file']);

                                    // layerslider for some reason doesn't save the slug properly so we have to do it ourselves
                                    global $wpdb;
                                    // get the last inserted id
                                    $ls_import_id = $wpdb->insert_id;
                                    // get the layerslider
                                    $ls_slider = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . LS_DB_TABLE . ' WHERE id = ' . $ls_import_id);

                                    // decode the properties data to get the slug
                                    $ls_data = json_decode($ls_slider->data);
                                    $ls_slug = $ls_data->properties->slug;

                                    // update the layerslider with the new slug
                                    $updated = $wpdb->update(
                                        $wpdb->prefix . LS_DB_TABLE,
                                        array(
                                            'slug' => $ls_slug,
                                        ),
                                        array(
                                            'ID' => $ls_import_id
                                        ),
                                        array(
                                            '%s'
                                        ),
                                        array( '%d' )
                                    );
                                    $return->status = true;
                                }
                                break;
                            case 'revslider':
                                if (is_plugin_active('revslider/revslider.php')) {
                                    $_FILES['import_file']['tmp_name'] = $upload['file'];
                                    $slider = @new RevSlider();
                                    ob_start();
                                    $return->data = @$slider->importSliderFromPost(false, false);
                                    ob_end_clean();
                                    $return->status = true;
                                }
                                break;
                        }
                    }
                }
            }
        }

        echo json_encode($return);
        die();
    }


    public function add_to_map($map_name, $old_value, $new_value)
    {
        $option_name = 'import-' . $_POST['jobID'];
        $import_job = get_option($option_name, array());

        if (!isset($import_job->maps[$map_name])) {
            $import_job->maps[$map_name] = array();
        }

        if (is_int($new_value)) {
            $new_value = (int) $new_value;
        }

        $import_job->maps[$map_name][$old_value] = $new_value;

        update_option($option_name, $import_job);

        return $import_job->maps;
    }

    public function lookup_map($map_name, $old_value)
    {
        $import_job = get_option('import-' . $_POST['jobID'], array());

        if (isset($import_job->maps[$map_name]) && isset($import_job->maps[$map_name][$old_value])) {
            return $import_job->maps[$map_name][$old_value];
        } else {
            return false;
        }
    }

    // OPTION PAGES CODE
    public function render()
    {
        $status = isset($_POST['one_click_status']) ? $_POST['one_click_status'] : 'start-page';
        switch($status) {
            case 'start-page':
                $this->render_start_page();
                break;
            case 'install-page':
                $this->render_install_page();
                break;
            case 'finished-page':
                $this->render_finished_page();
                break;
        }
    }

    public function render_start_page()
    {
        // get packages available from theme
        $packages = apply_filters('oxy_one_click_import_packages', array());
        // check if requirements are there
        foreach ($packages as &$package) {
            $package['missing_requirements'] = array();
            $package['disabled'] = '';
            $package['checked'] = 'checked';

            foreach ($package['requirements'] as $plugin_file => $plugin_name) {
                if (!is_plugin_active($plugin_file)) {
                    $package['disabled'] = 'disabled';
                    $package['checked'] = '';
                    $package['missing_requirements'][] = $plugin_file;
                }
            }
        }

        ob_start();
        include(OXY_ONECLICK_DIR . 'partials/start-page.php');
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
        die();
    }

    public function render_install_page()
    {
        // get packages available from theme
        $packages = apply_filters('oxy_one_click_import_packages', array());

        // get selected packages
        $selected_packages = $_POST['installpackages'];

        ob_start();
        include(OXY_ONECLICK_DIR . 'partials/demo-installer.php');
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
        die();
    }

    public function render_finished_page()
    {
        $docs_url = apply_filters('oxy_one_clicl_theme_docs_url', '');
        ob_start();
        include(OXY_ONECLICK_DIR . 'partials/install-complete.php');
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
        die();
    }

    /**
     * let_to_num function.
     *
     * This function transforms the php.ini notation for numbers (like '2M') to an integer.
     *
     * @access public
     * @param $size
     * @return int
     */
    public function ini_to_num($size)
    {
        $l      = substr($size, -1);
        $ret    = substr($size, 0, -1);
        switch (strtoupper($l)) {
            case 'P':
                $ret *= 1024;
                // fall through
            case 'T':
                $ret *= 1024;
                // fall through
            case 'G':
                $ret *= 1024;
                // fall through
            case 'M':
                $ret *= 1024;
                // fall through
            case 'K':
                $ret *= 1024;
                // fall through
        }

        return $ret;
    }
}

$one_click_install = new OxygennaOneClick();
