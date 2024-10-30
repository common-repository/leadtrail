<?php

require_once GHAX_LEADTRAIL_ABSPATH . 'includes/function/functions.php';

class GHAX_Capture
{

    public function __construct()
    {

        //Elementor
        if (!do_action('elementor/loaded')) {
            add_action('elementor_pro/forms/new_record', [$this, 'capture_elementor'], 10, 2);
        }

        //CF7
        remove_all_filters('wpcf7_before_send_mail');
        add_action('wpcf7_before_send_mail', [$this, 'capture_contactform7']);

        //WPForms
        add_action("wpforms_process_complete", [$this, 'capture_wpforms']);

        //gravityforms
        add_action('gform_after_submission', [$this, 'capture_gravityforms'], 10, 2);

        //Forminator
        add_action('forminator_custom_form_submit_before_set_fields', [$this, 'capture_forminator'], 10, 3);

        //Ninja forms
        add_action('ninja_forms_after_submission', [$this, 'capture_ninjaforms']);

        add_filter('ghaxlt_lead_quantity', array($this, 'set_lead_quantity'), 10, 1);
    }

    public function capture_elementor($record, $ajax_handler)
    {
        global $wpdb;

        //make sure its our form
        $settings = $record->get('form_settings');
        $form_id = $settings['id'];
        $fpost_id = $settings['form_post_id'];
        $nform_id = $fpost_id . '-' . $form_id;

        $qry = "SELECT * FROM " . $wpdb->prefix . "ghaxlt_lead_groups WHERE forms LIKE '%" . $nform_id . "%'";
        $res = $wpdb->get_row($qry);
        $form_name = $record->get_form_settings('form_name');
        // Replace MY_FORM_NAME with the name you gave your form
        /*if ( 'Capture Leads Form' !== $form_name ) {
        return;
        }*/
        $lead_quantity = '';
        $raw_fields = $record->get('fields');

        $this->leadtrail_capture_custom_fields($raw_fields, 'ef', $form_id, $form_name);

        $fields = [];
        foreach ($raw_fields as $id => $field) {
            if ($field['type'] == 'lead-quantity') {
                $fields['lead-quantity'] = $field['value'];
                $lead_quantity = $field['value'];
            } else if ($field['type'] == 'lead-zipcode' || $field['type'] == 'lead-city' || $field['type'] == 'lead-state' || $field['type'] == 'lead-country') {
                $type = $field['type'];
                $fields[$type] = $field['value'];
            } else {
                $fields[$id] = $field['value'];
            }
        }

        $farr = $fields;
        if (get_option('lead_publish')) {
            if (get_option('lead_publish') == 'yes') {
                $publish = 1;
            } else {
                $publish = 0;
            }
        } else {
            $publish = 1;
        }
        $farr['form'] = 'ef';
        $farr['formid'] = $form_id;

        $lead_quantity = apply_filters('ghaxlt_lead_quantity', $lead_quantity);

        $data = array(
            'form_name' => $form_name,
            'data' => json_encode($farr),
            'lead_quantity' => $lead_quantity,
            'created_date' => date('Y-m-d H:i:s'),
            'group' => !is_null($res) ? $res->id : '',
            'submitted_by' => 'elementor',
            'status' => 'open',
            'publish' => intval($publish),
        );
        $tbllds = $wpdb->prefix . 'ghaxlt_leads';
        $output['success'] = $wpdb->insert($tbllds, $data);
        $lead_id =  $wpdb->insert_id;

        if ($lead_id) {
            send_email_notification_on_lead_creation($lead_id, $farr, $res);
        }

        $ajax_handler->add_response_data(true, $output);
    }

    public function capture_contactform7($form_to_DB)
    {
        //set your db details
        global $wpdb;

        $form_to_DB = WPCF7_Submission::get_instance();
        $contact_form = $form_to_DB->get_contact_form();

        $tags = $contact_form->scan_form_tags();
        $wpcf7 = WPCF7_ContactForm::get_current();
        $lead_quantity = '';
        $form_id = $wpcf7->id;
        $qry = "SELECT * FROM " . $wpdb->prefix . "ghaxlt_lead_groups WHERE forms LIKE '%" . $form_id . "%'";
        $res = $wpdb->get_row($qry);

        if ($form_to_DB) $formData = $form_to_DB->get_posted_data();
        // echo '<pre>';
        // print_r($formData);
        $form_name = $contact_form->title;
        $this->leadtrail_capture_custom_fields($formData, 'cf7', $form_id, $form_name);


        foreach ($tags as $tag) {
            if ($tag->type == 'leadquantity') {
                $lead_quantity = $formData[$tag->name];
                $formData['lead-quantity'] = $lead_quantity;
                unset($formData[$tag->name]);
            }
            if ($tag->type == 'leadcountry') {
                $formData['lead-country'] = $formData[$tag->name];
                unset($formData[$tag->name]);
            }
            if ($tag->type == 'leadcity') {
                $formData['lead-city'] = $formData[$tag->name];
                unset($formData[$tag->name]);
            }
            if ($tag->type == 'leadstate') {
                $formData['lead-state'] = $formData[$tag->name];
                unset($formData[$tag->name]);
            }
            if ($tag->type == 'leadzipcode') {
                $formData['lead-zipcode'] = $formData[$tag->name];
                unset($formData[$tag->name]);
            }
        }

        $formData['form'] = 'cf7';
        $formData['formid'] = intval($form_id);

        $fdata = json_encode($formData);
        if (get_option('lead_publish')) {
            if (get_option('lead_publish') == 'yes') {
                $publish = 1;
            } else {
                $publish = 0;
            }
        } else {
            $publish = 1;
        }

        $lead_quantity = apply_filters('ghaxlt_lead_quantity', $lead_quantity);

        $tbllds = $wpdb->prefix . 'ghaxlt_leads';
        $wpdb->insert($tbllds, array(
            'form_name' => $form_name, 'data' => $fdata, 'lead_quantity' => $lead_quantity,
            'created_date' => date('Y-m-d H:i:s'), 'group' => !is_null($res) ? $res->id : '', 'submitted_by' => 'contact-form7', 'status' => 'open', 'publish' => intval($publish)
        ));
        $lead_id =  $wpdb->insert_id;

        if ($lead_id) {
            send_email_notification_on_lead_creation($lead_id, $fdata, $res);
        }
    }

    public function capture_wpforms($params)
    {
        global $wpdb;

        $keys = array();
        $values = array();
        $frms = wpforms();
        $lead_quantity = '';

        $dta = $frms->process->form_data;
        $form_name = $dta['settings']['form_title'];
        $qry1 = "SELECT * FROM " . $wpdb->prefix . "posts WHERE post_title='$form_name' AND post_type='wpforms'";
        $res1 = $wpdb->get_row($qry1);
        $form_id = $res1->ID;
        $qry = "SELECT * FROM " . $wpdb->prefix . "ghaxlt_lead_groups WHERE forms LIKE '%" . $form_id . "%'";
        $res = $wpdb->get_row($qry);

        foreach ($params as $idx => $item) {

            if ($item['type'] == "address") {
                if (isset($item['address1']) && $item['address1']) {
                    $keys[] = 'address1';
                    $values[] = $item['address1'];
                }
                if (isset($item['address2']) && $item['address2']) {
                    $keys[] = 'address2';
                    $values[] = $item['address1'];
                }
                if (isset($item['city']) && $item['city']) {
                    $keys[] = 'city';
                }
                if (isset($item['state']) && $item['state']) {
                    $keys[] = 'state';
                }
                if (isset($item['postal']) && $item['postal']) {
                    $keys[] = 'zipcode';
                }
                $keys[] = $item['name'];
                $values[] = $item['value'];
            } else if ($item['type'] == 'lead-quantity') {
                $keys[] = 'lead-quantity';
                $lead_quantity =  $item['value'];
                $values[] = $item['value'];
            } else if ($item['type'] == 'lead-zipcode' || $item['type'] == 'lead-city' || $item['type'] == 'lead-state' || $item['type'] == 'lead-country') {
                $keys[] = $item['type'];
                $values[] = $item['value'];
            } else {
                $keys[] = $item['name'];
                $values[] = $item['value'];
            }

            // Do whatever you need
        }

        $farr = array_combine($keys, $values);

        $this->leadtrail_capture_custom_fields($farr, 'wpf', $form_id, $form_name);
        $farr['form'] = 'wpf';
        $farr['formid'] = $form_id;

        $fdata = json_encode($farr);

        if (get_option('lead_publish')) {
            if (get_option('lead_publish') == 'yes') {
                $publish = 1;
            } else {
                $publish = 0;
            }
        } else {
            $publish = 1;
        }

        $lead_quantity = apply_filters('ghaxlt_lead_quantity', $lead_quantity);

        $tbllds = $wpdb->prefix . 'ghaxlt_leads';
        $wpdb->insert($tbllds, array(
            'form_name' => $form_name, 'data' => $fdata,
            'created_date' => date('Y-m-d H:i:s'), 'group' => !is_null($res) ? $res->id : '', 'lead_quantity' => $lead_quantity, 'submitted_by' => 'wpforms', 'status' => 'open', 'publish' => intval($publish)
        ));
        $lead_id =  $wpdb->insert_id;

        if ($lead_id) {
            send_email_notification_on_lead_creation($lead_id, $fdata, $res);
        }

        return true;
    }

    /**
     * Capture Gravity form submission
     * rgar() is gravity forms function
     *
     * @param [type] $entry
     * @param [type] $form
     * @return void
     */
    function capture_gravityforms($entry, $form)
    {
        global $wpdb;
        $keys = array();
        $values = array();
        $group_id = "";

        $form_name = $form['title'];
        $form_id = $form['id'];
        $qry = "SELECT * FROM " . $wpdb->prefix . "ghaxlt_lead_groups WHERE forms LIKE '%" . $form_id . "%'";
        $res = $wpdb->get_row($qry);
        if ($res) {
            $group_id = $res->id;
        }

        $lead_quantity  = '';
        foreach ($form['fields'] as $field) {
            //echo $field->type;
            $inputs = $field->get_entry_inputs();
            if ($field->type == 'lead-quantity') {
                $keys[] = 'lead-quantity';
                $lead_quantity =  rgar($entry, (string) $field->id);
            } else if ($field->type == 'lead-zipcode' || $field->type == 'lead-city' || $field->type == 'lead-state' || $field->type == 'lead-country') {
                $keys[] = $field->type;
            } else {
                $keys[] = $field['label'];
            }

            if (is_array($inputs)) {
                $value1 = "";
                foreach ($inputs as $input) {

                    $value1 .= rgar($entry, (string) $input['id']) . ' ';
                    // do something with the value
                }
                $value = $value1;
            } else {
                $value = rgar($entry, (string) $field->id);
                // do something with the value
            }
            $values[] = $value;
        }
        $farr = array_combine($keys, $values);

        $this->leadtrail_capture_custom_fields($farr, 'gf', $form_id, $form_name);

        $farr['form'] = 'gf';
        $farr['formid'] = $form_id;

        $fdata = json_encode($farr);

        if (get_option('lead_publish')) {
            if (get_option('lead_publish') == 'yes') {
                $publish = 1;
            } else {
                $publish = 0;
            }
        } else {
            $publish = 1;
        }

        $lead_quantity = apply_filters('ghaxlt_lead_quantity', $lead_quantity);

        $tbllds = $wpdb->prefix . 'ghaxlt_leads';
        $wpdb->insert($tbllds, array(
            'form_name' => $form_name, 'data' => $fdata, 'lead_quantity' => $lead_quantity,
            'created_date' => date('Y-m-d H:i:s'), 'group' => $group_id, 'submitted_by' => 'gravity-forms', 'status' => 'open', 'publish' => intval($publish)
        ));
        $lead_id =  $wpdb->insert_id;
        if ($lead_id) {
            send_email_notification_on_lead_creation($lead_id, $fdata, $res);
        }
    }

    function capture_forminator($entry, $form_id, $form_data)
    {
        global $wpdb;
        // do something here with $entry, $form_id and $form_data to get them 
        // formatted the way you want/need to work with your code


        $keys = array();
        $values = array();
        $form_name = get_the_title($form_id);
        //$form_name = '';
        foreach ($form_data as $fdata) {
            $keys[] = $fdata['name'];
            $values[] = $fdata['value'];
        }
        $farr = array_combine($keys, $values);
        $fdata = json_encode($farr);
        if (get_option('lead_publish')) {
            if (get_option('lead_publish') == 'yes') {
                $publish = 1;
            } else {
                $publish = 0;
            }
        } else {
            $publish = 1;
        }

        $qry = "SELECT * FROM " . $wpdb->prefix . "ghaxlt_lead_groups WHERE forms LIKE '%" . $form_id . "%'";
        $res = $wpdb->get_row($qry);
        $tbllds = $wpdb->prefix . 'ghaxlt_leads';
        $wpdb->insert($tbllds, array(
            'form_name' => $form_name, 'data' => $fdata,
            'created_date' => date('Y-m-d H:i:s'), 'group' => !is_null($res) ? $res->id : '', 'submitted_by' => 'forminator', 'status' => 'open', 'publish' => intval($publish)
        ));

        $lead_id =  $wpdb->insert_id;
        if ($lead_id) {
            send_email_notification_on_lead_creation($lead_id, $fdata, $res);
        }
    }

    function capture_ninjaforms($form_data)
    {
        global $wpdb;

        // Do stuff.
        $lead_quantity = '';
        $keys = array();
        $values = array();
        $form_name = $form_data['settings']['title'];
        $form_id = $form_data['form_id'];
        if (!empty($form_data['fields'])) {
            $fields = $form_data['fields'];
        } else {
            $fields = $form_data['fields_by_key'];
        }

        foreach ($fields as $fdata) {
            if ($fdata['type'] == "lead-quantity") {
                $lead_quantity = $fdata['value'];
                $keys[] = $fdata['type'];
            } else if ($fdata['type'] == 'lead-zipcode' || $fdata['type'] == 'lead-city' || $fdata['type'] == 'lead-state' || $fdata['type'] == 'lead-country') {
                $keys[] = $fdata['type'];
            } else {
                $keys[] = $fdata['label'];
            }

            $values[] = $fdata['value'];
        }
        $farr = array_combine($keys, $values);

        $this->leadtrail_capture_custom_fields($farr, 'nf', $form_id, $form_name);

        $farr['form'] = 'nf';
        $farr['formid'] = $form_id;

        $fdata = json_encode($farr);
        if (get_option('lead_publish')) {
            if (get_option('lead_publish') == 'yes') {
                $publish = 1;
            } else {
                $publish = 0;
            }
        } else {
            $publish = 1;
        }

        $qry = "SELECT * FROM " . $wpdb->prefix . "ghaxlt_lead_groups WHERE forms LIKE '%" . $form_id . "%'";
        $res = $wpdb->get_row($qry);

        $lead_quantity = apply_filters('ghaxlt_lead_quantity', $lead_quantity);

        $tbllds = $wpdb->prefix . 'ghaxlt_leads';
        $wpdb->insert($tbllds, array(
            'form_name' => $form_name, 'data' => $fdata, 'lead_quantity' => $lead_quantity,
            'created_date' => date('Y-m-d H:i:s'), 'group' => !is_null($res) ? $res->id : '', 'submitted_by' => 'ninja-forms', 'status' => 'open', 'publish' => intval($publish)
        ));

        $lead_id =  $wpdb->insert_id;
        if ($lead_id) {
            send_email_notification_on_lead_creation($lead_id, $fdata, $res);
        }
    }

    /**
     * Capture field names and make it available in LeadTrail Settings
     * 
     * @param array $formdata
     * @param string $plugin
     * @param integer $formID
     * @return void
     * @since 1.3.0
     */
    private function leadtrail_capture_custom_fields($formdata = array(), $plugin = '', $formID = '', $form_name = '')
    {
        $customfields = $formnames = array();
        $getsavedfields = get_option('leadtrail_custom_fields');
        $getformnames = get_option('leadtrail_form_names');

        if (FALSE != $getsavedfields) {
            $customfields = $getsavedfields;
        }

        if (FALSE != $getformnames) {
            $formnames = $getformnames;
        }

        $plugin = sanitize_text_field($plugin);
        $formID = sanitize_text_field($formID);
        $form_name = sanitize_text_field($form_name);

        $customfields[$plugin][$formID] = array_map('sanitize_text_field', array_keys($formdata));

        if ($plugin == 'ef') {
            $ltelementor = [];
            foreach ($formdata as $key => $fields) {
                if (array_key_exists('title', $fields)) $ltelementor[$key] = sanitize_text_field($fields['title']);
            }
            update_option('leadtrail_elementor', $ltelementor);
        }

        $formnames[$plugin][$formID] = $form_name;


        // echo '<pre>';
        // print_r($customfields);
        // exit();
        //         Array
        // (
        //     [cf7] => Array
        //         (
        //             [0] => your-name
        //             [1] => your-email
        //             [2] => your-subject
        //             [3] => your-message
        //             [4] => siteurl
        //             [5] => incomerange
        //             [6] => gender
        //             [7] => myportrait
        //         )

        // )
        update_option('leadtrail_custom_fields', $customfields);
        update_option('leadtrail_form_names', $formnames);
    }

    function set_lead_quantity($quantity)
    {

        if ($quantity == 0 || $quantity == '') { //its taking default value
            if ($pc = get_option('lead_purchase_count')) {
                return $pc;
            }
        }

        return $quantity;
    }
}
