<?php

/**
 * City field.
 *
 * @since 1.0.0
 */
class WPForms_Field_City extends WPForms_Fields
{
    public $name, $type, $icon, $group, $order, $value, $defaults;
    /**
     * International Telephone Input library CSS.
     *
     * @since 1.6.3
     */


    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function init()
    {

        // Define field type information.
        $this->name  = esc_html__('Lead City', 'wpforms');
        $this->type  = 'lead-city';
        $this->icon  = 'fa-map-marker';
        $this->order = 50;
        $this->group = 'fancy';
    }





    /**
     * Field options panel inside the builder.
     *
     * @since 1.0.0
     *
     * @param array $field Field data.
     */
    public function field_options($field)
    {
        /*
		 * Basic field options.
		 */

        // Options open markup.
        $args = array(
            'markup' => 'open',
        );
        $this->field_option('basic-options', $field, $args);

        // Label.
        $this->field_option('label', $field);



        // Description.
        $this->field_option('description', $field);

        // Required toggle.
        $this->field_option('required', $field);

        // Options close markup.
        $args = array(
            'markup' => 'close',
        );
        $this->field_option('basic-options', $field, $args);

        /*
		 * Advanced field options.
		 */

        // Options open markup.
        $args = array(
            'markup' => 'open',
        );
        $this->field_option('advanced-options', $field, $args);

        // Size.
        $this->field_option('size', $field);

        // Placeholder.
        $this->field_option('placeholder', $field);

        // Default value.
        $this->field_option('default_value', $field);

        // Custom CSS classes.
        $this->field_option('css', $field);

        // Hide Label.
        $this->field_option('label_hide', $field);

        // Options close markup.
        $args = [
            'markup' => 'close',
        ];

        $this->field_option('advanced-options', $field, $args);
    }

    /**
     * Field preview inside the builder.
     *
     * @since 1.0.0
     *
     * @param array $field Field data.
     */
    public function field_preview($field)
    {

        // Define data.
        $placeholder   = !empty($field['placeholder']) ? $field['placeholder'] : '';
        $default_value = !empty($field['default_value']) ? $field['default_value'] : '';

        // Label.
        $this->field_preview_option('label', $field);

        // Primary input.
        echo '<input type="text" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($default_value) . '" class="primary-input" readonly>';

        // Description.
        $this->field_preview_option('description', $field);
    }

    /**
     * Field display on the form front-end.
     *
     * @since 1.0.0
     *
     * @param array $field      Field data and settings.
     * @param array $deprecated Deprecated field attributes. Use field properties.
     * @param array $form_data  Form data and settings.
     */
    public function field_display($field, $deprecated, $form_data)
    {

        // Define data.
        $primary = $field['properties']['inputs']['primary'];

        // Allow input type to be changed for this particular field.
        $type = apply_filters('wpforms_phone_field_input_type', 'tel');

        // Primary field.
        printf(
            '<input type="%s" %s %s>',
            esc_attr($type),
            wpforms_html_attributes($primary['id'], $primary['class'], $primary['data'], $primary['attr']),
            $primary['required']
        );
    }

    /**
     * Validate field on form submit.
     *
     * @since 1.5.8
     *
     * @param int   $field_id     Field ID.
     * @param mixed $field_submit Submitted value.
     * @param array $form_data    Form data and settings.
     */
    public function validate($field_id, $field_submit, $form_data)
    {

        $form_id = $form_data['id'];
        $value   =  $field_submit;

        // If field is marked as required, check for entry data.
        if (
            !empty($form_data['fields'][$field_id]['required']) &&
            empty($value)
        ) {
            wpforms()->process->errors[$form_id][$field_id] = wpforms_get_required_label();
        }
    }

    /**
     * Format and sanitize field.
     *
     * @since 1.5.8
     *
     * @param int    $field_id     Field id.
     * @param string $field_submit Submitted value.
     * @param array  $form_data    Form data.
     */
    public function format($field_id, $field_submit, $form_data)
    {

        $name = !empty($form_data['fields'][$field_id]['label']) ? $form_data['fields'][$field_id]['label'] : '';

        // Set final field details.
        wpforms()->process->fields[$field_id] = array(
            'name'  => sanitize_text_field($name),
            'value' =>  $field_submit,
            'id'    => absint($field_id),
            'type'  => $this->type,
        );
    }
}

new WPForms_Field_City();
