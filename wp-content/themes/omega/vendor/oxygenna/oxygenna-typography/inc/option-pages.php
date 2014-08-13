<?php
/**
 * Sets up the typography option Pages
 *
 * @package OxygennaTypography
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license **LICENSE**
 * @version 1.2
 * @author Oxygenna.com
 */

global $oxy_theme;
if (isset($oxy_theme)) {
    $oxy_theme->register_option_page(array(
        'page_title' => THEME_NAME . ' - ' . __('Typography Settings', 'omega-admin-td'),
        'menu_title' => __('Typography', 'omega-admin-td'),
        'slug'       => THEME_SHORT . '-typography',
        'main_menu'  => false,
        'icon'       => 'tools',
        'stylesheets' => array(
            array(
                'handle' => 'typography-page',
                'src'    => OXY_TYPOGRAPHY_URI . 'assets/css/typography-page.css',
                'deps'   => array('oxy-typography-select2'),
            ),
        ),
        'javascripts' => array(
            array(
                'handle' => 'typography-page',
                'src'    => OXY_TYPOGRAPHY_URI . 'assets/javascripts/typography-page.js',
                'deps'   => array('jquery', 'underscore', 'thickbox', 'oxy-typography-select2', 'jquery-ui-dialog'),
                'localize' => array(
                    'object_handle' => 'typographyPage',
                    'data' => array(
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'listNonce'  => wp_create_nonce('list-fontstack'),
                        'fontModal'  => wp_create_nonce('font-modal'),
                        'updateNonce'  => wp_create_nonce('update-fontstack'),
                        'defaultFontsNonce' => wp_create_nonce('default-fonts'),
                    )
                )
            ),
        ),
        // include a font stack option to enqueue select 2 ok
        'sections'   => array(
            'font-section' => array(
                'title'   => __('Fonts settings section', 'omega-admin-td'),
                'header'  => __('Setup Fonts settings here.', 'omega-admin-td'),
                'fields' => array(
                    array(
                        'name' => __('Font Stack:', 'omega-admin-td'),
                        'id' => 'font_list',
                        'type' => 'fontlist',
                        'class-file' => OXY_TYPOGRAPHY_DIR . 'inc/options/font-list.php',
                    ),
                )
            )
        )
    ));
    $oxy_theme->register_option_page(array(
        'page_title' => THEME_NAME . ' - ' . __('Typography Settings', 'omega-admin-td'),
        'menu_title' => __('Fonts', 'omega-admin-td'),
        'slug'       => THEME_SHORT . '-fonts',
        'main_menu'  => false,
        'icon'       => 'tools',
        'sections'   => array(
            'google-fonts-section' => array(
                'title'   => __('Google Fonts', 'omega-admin-td'),
                // 'header'  => __('Setup Your Google Fonts Here.', 'omega-admin-td'),
                'fields' => array(
                    array(
                        'name'        => __('Fetch Google Fonts', 'omega-admin-td'),
                        'button-text' => __('Update Fonts', 'omega-admin-td'),
                        'id'          => 'google_update_fonts_button',
                        'type'        => 'button',
                        'desc'        => __('Click this button to fetch the latest fonts from Google and update your Google Fonts list.', 'omega-admin-td'),
                        'attr'        => array(
                            'id'    => 'google-update-fonts-button',
                            'class' => 'button button-primary'
                        ),
                        'javascripts' => array(
                            array(
                                'handle' => 'google-font-updater',
                                'src'    => OXY_TYPOGRAPHY_URI . 'assets/javascripts/options/google-font-updater.js',
                                'deps'   => array('jquery'),
                                'localize' => array(
                                    'object_handle' => 'googleUpdate',
                                    'data' => array(
                                        'ajaxurl'   => admin_url('admin-ajax.php'),
                                        // generate a nonce with a unique ID "myajax-post-comment-nonce"
                                        // so that you can check it later when an AJAX request is sent
                                        'nonce'     => wp_create_nonce('google-fetch-fonts-nonce'),
                                    )
                                )
                            ),
                        ),
                    )
                )
            ),
            'typekit-provider-options' => array(
                'title'   => __('TypeKit Fonts', 'omega-admin-td'),
                'header'  => __('If you have a TypeKit account and would like to use it in your site.  Enter your TypeKit API key below and then click the Update your kits button.', 'omega-admin-td'),
                'fields' => array(
                    array(
                        'name' => __('Typekit API Token', 'omega-admin-td'),
                        'desc' => __('Add your typekit api token here', 'omega-admin-td'),
                        'id'   => 'typekit_api_token',
                        'type' => 'text',
                        'attr'        => array(
                            'id'    => 'typekit-api-key',
                        )
                    ),
                    array(
                        'name'        => __('TypeKit Kits', 'omega-admin-td'),
                        'button-text' => __('Update your kits', 'omega-admin-td'),
                        'desc' => __('Click this button to update your typography list with the kits available from your TypeKit account.', 'omega-admin-td'),
                        'id'          => 'typekit_kits_button',
                        'type'        => 'button',
                        'attr'        => array(
                            'id'    => 'typekit-kits-button',
                            'class' => 'button button-primary'
                        ),
                        'javascripts' => array(
                            array(
                                'handle' => 'typekit-kit-updater',
                                'src'    => OXY_TYPOGRAPHY_URI . 'assets/javascripts/options/typekit-updater.js',
                                'deps'   => array('jquery' ),
                                'localize' => array(
                                    'object_handle' => 'typekitUpdate',
                                    'data' => array(
                                        'ajaxurl'   => admin_url('admin-ajax.php'),
                                        'nonce'     => wp_create_nonce('typekit-kits-nonce'),
                                    )
                                )
                            ),
                        ),
                    )
                )
            )
        )
    ));
}
