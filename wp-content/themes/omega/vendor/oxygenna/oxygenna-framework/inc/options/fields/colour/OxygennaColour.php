<?php
/**
 * Date Option
 *
 * @package ThemeFramework
 * @subpackage Options
 * @since 1.0
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license http://wiki.envato.com/support/legal-terms/licensing-terms/
 * @version 1.2
 */

/**
 * Creates a date picker option
 */
class OxygennaColour extends OxygennaOption
{
    /**
     * Creates option
     *
     * @return void
     *              @since 1.0
     **/
    public function __construct($field, $value, $attr)
    {
        parent::__construct($field, $value, $attr);
        $this->set_attr('type', 'text');
        $this->set_attr('class', 'colour-option');
        $this->set_attr('value', $value);
    }

    /**
     * Overrides super class render function
     *
     * @return string HTML for option
     *                @since 1.0
     **/
    public function render($echo = true)
    {
        echo '<input ' . $this->create_attributes() . ' />';
    }

    public function enqueue()
    {
        parent::enqueue();
        // load wordpress bundled colorpicker
        wp_enqueue_style('wp-color-picker');
        // load script
        wp_enqueue_script('colour-field', OXY_TF_URI . 'inc/options/fields/colour/colour.js', array('wp-color-picker'));
    }
}
