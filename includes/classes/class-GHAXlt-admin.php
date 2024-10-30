<?php

/** @wordpress-plugin
 * Author:            GHAX
 * Author URI:        https://leadtrail.io/
 */
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class GHAXlt_Admin
{

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  public function __construct($plugin_name, $version)
  {

    $this->plugin_name = $plugin_name;
    $this->version = $version;
    if (get_option('leadtrail_license_key') && (get_option('leadtrail_license_status') == 'active')) {
      $expp = get_option('leadtrail_license_expiry_date');
      $curDateTime = date("Y-m-d H:i:s");
      $myDate = date("Y-m-d H:i:s", strtotime($expp));
      if ($myDate < $curDateTime) {
        add_action('admin_menu', array(&$this, 'register_leadtrail_menu_page2'));
      } else {
        add_action('admin_menu', array(&$this, 'register_leadtrail_menu_page'));
      }
    } else {
      add_action('admin_menu', array(&$this, 'register_leadtrail_menu_page'));
    }
    add_action('wp_ajax_add_admin_note_action', array(&$this, 'add_admin_note_action'));
    add_action('wp_ajax_add_buyer_note_action', array(&$this, 'add_buyer_note_action'));
    add_action('wp_ajax_all_delete_action', array(&$this, 'all_delete_action'));
    add_action('wp_ajax_nopriv_all_delete_action', array(&$this, 'all_delete_action'));
  }


  /** Menu Function **/
  function register_leadtrail_menu_page()
  {
    global $submenu, $user_ID;
    global $PluginTextDomain;
    global $lmPluginName;

    $roles = $user_info = array();
    $user_info = get_userdata($user_ID);
    $roles = $user_info->roles;
    $pluginIcon = GHAX_LEADTRAIL_RELPATH . "icon-256x256.png";
    // echo $pluginIcon;
    add_menu_page(__($lmPluginName, $PluginTextDomain), __('LeadTrail', $PluginTextDomain),  'manage_options', 'leadtrail', array(&$this, 'leadtrail'), $pluginIcon);


    add_submenu_page('', __('Form Submission Data', $PluginTextDomain), __('Form Submission Data', $PluginTextDomain), 'manage_options', 'display_form_submission', array(&$this, 'display_form_submissions_page'));
    add_submenu_page('', __('Edit Lead Data', $PluginTextDomain), __('Edit Lead Data', $PluginTextDomain), 'manage_options', 'edit_lead_data', array(&$this, 'edit_lead_data'));


    $submenu['leadtrail'][0][0] = "Dashboard";
  }



  /*** display lead payments ****/


  /********* function to display leads analytics dashboard ************/
  function leadtrail()
  {
    include GHAX_LEADTRAIL_ABSPATH . 'admin/dashboard.php';
  }

  /****** function to display lead settings page *********/


  /***** function to edit lead data *****/
  function edit_lead_data()
  {
    global $wpdb;
    $tbllds = $wpdb->prefix . 'ghaxlt_leads';
    $id = (int) $_GET['id'];

    if (isset($_POST['update_lead_data'])) {
      $lead_status = sanitize_text_field($_POST['lead_status']);
      $lead_category = sanitize_text_field($_POST['lead_category']);
      $lead_group = sanitize_text_field($_POST['lead_group']);
      $lead_quality = sanitize_text_field($_POST['lead_quality']);


      $lead_quantity = sanitize_text_field($_POST['lead_quantity']);
      $discount_quantity = sanitize_text_field($_POST['discount_quantity']);
      $lead_discount = sanitize_text_field($_POST['lead_discount']);


      $lead_publish = sanitize_text_field($_POST['lead_publish']);
      if ($lead_publish == 'yes') {
        $publish = 1;
      } else {
        $publish = 0;
      }

      $wpdb->update($tbllds, array('status' => $lead_status, 'category' => $lead_category, 'group' => $lead_group, 'quality' => $lead_quality, 'lead_quantity' => $lead_quantity, 'discount_quantity' => $discount_quantity, 'lead_discount' => $lead_discount, 'publish' => intval($publish)), array('id' => (int) $_GET['id']));

      echo '<p class="success">Lead data updated successfully.</p>';
    }
    $res = $wpdb->get_row("SELECT * FROM " . $tbllds . " WHERE id=" . $id);
?>
    <div class="wrap">
      <div class="leaddatacontainer">
        <div class="top-head">
          <h1>Edit Lead Data</h1>
          <div class="button-holder">
            <a href="?page=leads" class="button-back">View Leads</a>
          </div>
        </div>
        <form method="post" action="" class="row">
          <div class="form-group">
            <label>Lead Category</label>
            <div class="form-class">
              <?php
              $tblcats = $wpdb->prefix . 'ghaxlt_lead_cats';
              $cqry = "SELECT * FROM " . $tblcats;
              $cresults = $wpdb->get_results($cqry);
              ?>
              <select id="" name="lead_category" class="custom-input">
                <option value="">-SELECT-</option>
                <?php foreach ($cresults as $cresult) {
                ?>
                  <option value="<?php echo esc_attr($cresult->id); ?>" <?php if ($res->category == $cresult->id) {
                                                                          echo "selected";
                                                                        } ?>><?php echo esc_html($cresult->name); ?></option>
                <?php
                }
                ?>

              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Lead Quality</label>
            <div class="form-class">
              <?php
              $tblqlty = $wpdb->prefix . 'ghaxlt_lead_qualities';
              $qqry = "SELECT * FROM " . $tblqlty;
              $qresults = $wpdb->get_results($qqry);
              ?>
              <select id="" name="lead_quality" class="custom-input">
                <option value="">-SELECT-</option>
                <?php foreach ($qresults as $qresult) {
                ?>
                  <option value="<?php echo esc_attr($qresult->id); ?>" <?php if ($res->quality == $qresult->id) {
                                                                          echo "selected";
                                                                        } ?>><?php echo esc_html($qresult->name); ?></option>
                <?php
                }
                ?>

              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Lead Group</label>
            <div class="form-class">
              <?php
              $tblgrps = $wpdb->prefix . 'ghaxlt_lead_groups';
              $gqry = "SELECT * FROM " . $tblgrps;
              $gresults = $wpdb->get_results($gqry);
              ?>
              <select id="" name="lead_group" class="custom-input">
                <option value="">-SELECT-</option>
                <?php foreach ($gresults as $gresult) {
                ?>
                  <option value="<?php echo esc_attr($gresult->id); ?>" <?php if ($res->group == $gresult->id) {
                                                                          echo "selected";
                                                                        } ?>><?php echo esc_html($gresult->name); ?></option>
                <?php
                }
                ?>
              </select>
            </div>

          </div>
          <div class="form-group">
            <label>Lead Status</label>
            <div class="form-class">
              <select id="" name="lead_status" class="custom-input">
                <option value="">-SELECT-</option>
                <option value="open" <?php if ($res->status == 'open') {
                                        echo "selected";
                                      } ?>>Open</option>
                <option value="sold" <?php if ($res->status == 'sold') {
                                        echo "selected";
                                      } ?>>Sold</option>
                <option value="dead" <?php if ($res->status == 'dead') {
                                        echo "selected";
                                      } ?>>Dead</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Lead Publish</label>
            <div class="form-class">
              <select name="lead_publish" class="custom-input">
                <option value="">-SELECT-</option>
                <option value="yes" <?php if ($res->publish == 1) {
                                      echo "selected";
                                    } ?>>Yes</option>
                <option value="no" <?php if ($res->publish == 0) {
                                      echo "selected";
                                    } ?>>No</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Max Quantity</label>
            <div class="form-class">
              <input class="custom-input" type="number" min="1" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="lead_quantity" value="<?php echo ($res->lead_quantity > 0) ? esc_attr($res->lead_quantity) : 1; ?>" style="width: 60%;">
            </div>
          </div>
          <div class="form-group">
            <label>Discount Quantity</label>
            <div class="form-class">
              <input class="custom-input" type="number" min="1" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="discount_quantity" value="<?php echo esc_attr($res->discount_quantity); ?>" style="width: 60%;">
            </div>
          </div>

          <div class="form-group">
            <label>Discount(%)</label>
            <div class="form-class">
              <input class="custom-input" type="number" id="lead_discount" name="lead_discount" value="<?php echo esc_attr($res->lead_discount); ?>" style="width: 60%;">
            </div>
          </div>
          <div class="form-group-wrap text-left">
            <div class="form-class">
              <input type="submit" name="update_lead_data" class="btn btn-primary" value="Update Data">
            </div>
          </div>
        </form>
      </div>
    </div>
    </div>
    <script type="text/javascript">
      jQuery('#lead_discount').on('keyup', function(e) {
        let get_price = document.getElementById('lead_discount').value;
        //
        if (get_price > 100) {
          jQuery('input[name=lead_discount').val(100);
        } else {
          let arr = get_price.split(".");
          if (arr[1]) {
            if (arr[1].length > 2) {
              var set_price = parseFloat(get_price).toFixed(2);
              jQuery('input[name=lead_discount').val(set_price);
            }

          }
          //
        }

      });
    </script>
  <?php
  }

  /****** fucntion to edit group ****/



  /****** fucntion to create new group ****/


  /****** fucntion to create new category ****/


  /*** function for csv import section ***/
  function GHAXlt_lead_import()
  {
    global $wpdb;
    $csv = array();
    $message = "";
    $tbllds = $wpdb->prefix . 'ghaxlt_leads';
    if (isset($_POST['csv_upload'])) {
      // check there are no errors
      if ($_FILES['csv']['error'] == 0) {
        $name = $_FILES['csv']['name'];
        $ext =  pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
        $type = $_FILES['csv']['type'];
        $tmpName = $_FILES['csv']['tmp_name'];
        $header = NULL;
        $datan = array();
        // check the file is a csv
        if ($ext === 'csv') {
          $datan = ghax_csv_to_array($tmpName, ',');
        } else {
          return;
        }

        if (get_option('lead_publish')) {
          if (get_option('lead_publish') == 'yes') {
            $publish = 1;
          } else {
            $publish = 0;
          }
        } else {
          $publish = 1;
        }
        foreach ($datan as $dat) {

          if (isset($dat['Group']) && $dat['Group']) {
            $group = sanitize_text_field($dat['Group']);
            $tblgrps = $wpdb->prefix . 'ghaxlt_lead_groups';
            $gqry = "SELECT id FROM " . $tblgrps . " WHERE name='" . $group . "'";
            $gresults = $wpdb->get_results($gqry);
            if ($gresults && $gresults[0]->id) {
              $group_id = $gresults[0]->id;
            } else {
              $wpdb->insert($tblgrps, array(
                'name' => $group,
                'price' => 0
              ));
              $group_id = $wpdb->insert_id;
            }
            unset($dat['Group']);
          } else if (isset($dat['group']) && $dat['group']) {
            $group = sanitize_text_field($dat['group']);
            $tblgrps = $wpdb->prefix . 'ghaxlt_lead_groups';
            $gqry = "SELECT id FROM " . $tblgrps . " WHERE name='" . $group . "'";
            $gresults = $wpdb->get_results($gqry);
            if ($gresults && $gresults[0]->id) {
              $group_id = $gresults[0]->id;
            } else {
              $wpdb->insert($tblgrps, array(
                'name' => $group,
                'price' => 0
              ));
              $group_id = $wpdb->insert_id;
            }
            unset($dat['group']);
          } else {
            $group_id = "";
          }
          if (isset($dat['Category']) && $dat['Category']) {
            $category = sanitize_text_field($dat['Category']);
            $tblcats = $wpdb->prefix . 'ghaxlt_lead_cats';
            $cqry = "SELECT id FROM " . $tblcats . " WHERE name='" . $category . "'";
            $cresults = $wpdb->get_results($cqry);
            if ($cresults && $cresults[0]->id) {
              $category_id = $cresults[0]->id;
            } else {
              $wpdb->insert($tblcats, array(
                'name' => $category,
                'type' => 'category'
              ));
              $category_id = $wpdb->insert_id;
            }
            unset($dat['Category']);
          } else if (isset($dat['category']) && $dat['category']) {
            $category = sanitize_text_field($dat['category']);
            $tblcats = $wpdb->prefix . 'ghaxlt_lead_cats';
            $cqry = "SELECT id FROM " . $tblcats . " WHERE name='" . $category . "'";
            $cresults = $wpdb->get_results($cqry);
            if ($cresults && $cresults[0]->id) {
              $category_id = $cresults[0]->id;
            } else {
              $wpdb->insert($tblcats, array(
                'name' => $category,
                'type' => 'category'
              ));
              $category_id = $wpdb->insert_id;
            }
            unset($dat['category']);
          } else {
            $category_id = "";
          }
          if (isset($dat['Quality']) && $dat['Quality']) {

            $quality = sanitize_text_field($dat['Quality']);
            $tblqlty = $wpdb->prefix . 'ghaxlt_lead_qualities';
            $qqry = "SELECT id FROM " . $tblqlty . " WHERE name='" . $quality . "'";
            $qresults = $wpdb->get_results($qqry);
            if ($qresults && $qresults[0]->id) {
              $quality_id = $qresults[0]->id;
            } else {
              $wpdb->insert($tblqlty, array(
                'name' => $quality,
                'price' => 0
              ));
              $quality_id = $wpdb->insert_id;
            }
            unset($dat['Quality']);
          } else if (isset($dat['quality']) && $dat['quality']) {
            $quality = sanitize_text_field($dat['quality']);
            $tblqlty = $wpdb->prefix . 'ghaxlt_lead_qualities';
            $qqry = "SELECT id FROM " . $tblqlty . " WHERE name='" . $quality . "'";
            $qresults = $wpdb->get_results($qqry);
            if ($qresults && $qresults[0]->id) {
              $quality_id = $qresults[0]->id;
            } else {
              $wpdb->insert($tblqlty, array(
                'name' => $quality,
                'price' => 0
              ));
              $quality_id = $wpdb->insert_id;
            }
            unset($dat['quality']);
          } else {
            $quality = '';
          }



          if (isset($dat['Country']) && $dat['Country']) {
            $dat['lead-country'] = $dat['Country'];
            unset($dat['Country']);
          } else if (isset($dat['country']) && $dat['country']) {
            $dat['lead-country'] = $dat['country'];
            unset($dat['country']);
          }

          if (isset($dat['State']) && $dat['State']) {
            $dat['lead-state'] = $dat['State'];
            unset($dat['State']);
          } else if (isset($dat['state']) && $dat['state']) {
            $dat['lead-state'] = $dat['state'];
            unset($dat['state']);
          }

          if (isset($dat['City']) && $dat['City']) {
            $dat['lead-city'] = $dat['City'];
            unset($dat['City']);
          } else if (isset($dat['city']) && $dat['city']) {
            $dat['lead-city'] = $dat['city'];
            unset($dat['city']);
          }

          if (isset($dat['Zipcode']) && $dat['Zipcode']) {
            $dat['lead-zipcode'] = $dat['Zipcode'];
            unset($dat['Zipcode']);
          } else if (isset($dat['zipcode']) && $dat['zipcode']) {
            $dat['lead-zipcode'] = $dat['zipcode'];
            unset($dat['zipcode']);
          }

          if (isset($dat['Quantity']) && $dat['Quantity']) {
            $lead_quantity = $dat['Quantity'];
            $dat['lead-quantity'] = $dat['Quantity'];
            unset($dat['Quantity']);
          } else if (isset($dat['quantity']) && $dat['quantity']) {
            $dat['lead-quantity'] = $dat['quantity'];
            $lead_quantity = $dat['Quantity'];
            unset($dat['quantity']);
          }
          $lead_quantity = ($lead_quantity != '' || $lead_quantity > 0) ? $lead_quantity : 1;

          $fdata = json_encode($dat);

          $wpdb->insert($tbllds, array(
            'data' => $fdata,
            'created_date' => date('Y-m-d H:i:s'), 'status' => 'open', 'publish' => intval($publish), 'lead_quantity' => $lead_quantity, 'category' => $category_id, 'group' => $group_id, 'quality' => $quality_id
          ));
        }
        $message = "<p>leads uploaded successfully, view them <a href='?page=leads'>here</a></p>";
      }
    }
  ?>

    <div class="container section-top">
      <div class="row logo">
        <div class="col-md-6 logo1"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/leadtrail-logo.jpeg" /></div>
        <div class="col-md-6 logo2"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/help.png"><a href="https://leadtrail.io/support" target="_blank">Help</a></div>
      </div>
    </div>
    <div class="wrap">
      <?php echo $message; ?>
      <h1>CSV Import</h1>
      <div class="csv_import_container">
        <h2>Import Options</h2>
        <form id="csv_import_form" enctype='multipart/form-data' action="" method="post">
          <div class="c_wrap">
            <div class="csv_wrap">



              <img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/bx_bxs-cloud-download.png" class="import-img">
              <div class="form-group">

                <label>Upload CSV file</label>
                <div class="or-row">
                  <h3>or</h3>
                </div>
                <div class="form-class">
                  <button class="browse-btn" onclick="document.getElementById('getFile').click();return false;">Browse File</button>
                  <input type='file' id="getFile" onchange="document.getElementById('file_name').innerHTML='<b>'+this.files[0].name+'</b>'" style="display:none" name="csv">
                  <span id="file_name"></span>
                  <!-- <input type="file" name="csv"> -->
                </div>
              </div>



            </div>
          </div>
          <div class="bottom-page">
            <div class="form-group">
              <div class="form-class">
                <input type="submit" class="btn btn-primary" name="csv_upload" value="Upload">
              </div>
            </div>
            <div class="ca-sample-file">
              <a class="btn btn-primary" href="data:text/csv;charset=utf-8,First Name,Last Name,Email,Country,City,State,Zipcode,Quantity,Group,Category,Quality
John,Smith,jsmith@gmail.com,US,Houston,Texas,77027,4,Group1,Category 1,Unqualified
Cary,Robinson,caryr@yahoo.com,US,Houston,Texas,77027,3,Group 2,Category 2,Marketing Qualified
Andy,Smith,andy@yahoo.com,US,Houston,Texas,77027,1,Group 2,Category 2,Sales Qualified" download="sample.csv">
                Download Sample File
              </a>
            </div>
          </div>
        </form>
      </div>

    </div>
  <?php
  }

  function display_form_submissions_page()
  {
    global $wpdb;
    $id = (int) $_GET['id'];
    $tbllds = $wpdb->prefix . 'ghaxlt_leads';
  ?>
    <div class="wrap">

      <h1>Form Submission Data</h1>
      <br>
      <a href="?page=leads" class="button-back">Back</a>
      <?php
      $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ghaxlt_leads WHERE id=$id");

      if ($result) {
      ?>
        <table class="leaddat">
          <tr>
            <th>Id</th>
            <td><?php echo $result->id; ?></td>
          </tr>
          <?php
          $myarr = json_decode($result->data, true);
          foreach ($myarr as $key => $value) {
            if (is_array($value)) {
              $vdata = '';
              foreach ($value as $v) {
                $vdata .= $v . ',';
              }
          ?>
              <tr>
                <th><?php echo esc_html(ucfirst($key)); ?></th>
                <td><?php echo esc_html($vdata); ?></td>
              </tr>
            <?php
            } else {
            ?>
              <tr>
                <th><?php echo esc_html(ucfirst($key)); ?></th>
                <td><?php echo esc_html($value); ?></td>
              </tr>
          <?php
            }
          }
          ?>

        </table>
        <div class="note-info">
          <h3 style="color:red;">IMPORTANT: Any comments left here will be transferred to all buyers of this lead.</h3>
        </div>
        <?php
        wp_enqueue_script('jquery');

        $settings = array(
          'teeny' => true,
          'textarea_rows' => 10,
          'tabindex' => 1,
          'media_buttons' => false
        );
        wp_editor(__(get_option('admin_note', $result->admin_note)), 'admin_note', $settings);
        ?>
        <input type="submit" name="submit" value="Save" class="button-back add_admin_note" data-lead-id="<?= $id ?>">

        <h3 class="note-response" style="color: green;"></h3>
      <?php
      }
      ?>
    </div>
    <script>
      jQuery(document).ready(function($) {
        jQuery(document).on('click', '.add_admin_note', function() {
          var id = jQuery(this).attr('data-lead-id');
          var note = tinyMCE.get('admin_note');
          // let countWord = note.getCharacterCount();
          const wordCount = tinyMCE.activeEditor.plugins.wordcount;
          console.log("countWord:::", note.length)
          jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
              "action": "add_admin_note_action",
              "id": id,
              "table": "<?php echo $tbllds; ?>",
              "note": note.getContent()
            },
            success: function(data) {
              console.log(data)
              jQuery('.note-response').append(data);
              setTimeout(() => {
                jQuery('.note-response').html('');
              }, 4000);
            }
          });

        });
      });
    </script>
  <?php
  }



  function GHAXlt_lead_qualities_display()
  {
    global $wpdb;
    $tblqlty = $wpdb->prefix . 'ghaxlt_lead_qualities';
  ?>

    <div class="container section-top">
      <div class="row logo">
        <div class="col-md-6 logo1"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/leadtrail-logo.jpeg" /></div>
        <div class="col-md-6 logo2"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/help.png"><a href="https://leadtrail.io/support" target="_blank">Help</a></div>
      </div>
    </div>
    <div class="wrap">
      <div class="wrap-inn">
        <h1>Lead Quality</h1>
        <p class="text-right"><a href="?page=create_quality" class="button-back">Create Quality</a></p>
      </div>
      <table id="leadsgrptbl" class="display mdl-data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <!--<th>Image</th>-->
            <!--<th>Price</th>-->
            <th>Shortcode</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $results = $wpdb->get_results("SELECT * FROM {$tblqlty}");
          if (count($results) > 0) {
            foreach ($results as $result) {
          ?>
              <tr id="delete_<?php echo esc_attr($result->id); ?>">
                <td><?php echo esc_html($result->id); ?></td>
                <td><?php echo esc_html($result->name); ?></td>
                <!--<td><?php //echo $result->image; 
                        ?></td>-->
                <!--<td><?php //echo $result->price; 
                        ?></td>-->
                <td>[display-quality-leads quality='<?php echo esc_attr($result->id); ?>']</td>
                <td><?php echo date('m-d-Y h:i:s A', strtotime($result->created_date)); ?></td>
                <td><a href="?page=edit_quality&id=<?php echo esc_attr($result->id); ?>" class="leadbtn"><img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/Frame-1023.png"></a> <a href="javascript:void(0)" data-quality-id="<?php echo esc_attr($result->id); ?>" class="cust_b_delete leadbtn"><img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/Frame-1022.png"></a></td>
              </tr>
            <?php
            }
          } else {
            ?>
            <tr>
              <td colspan="6">No Qualities Found</td>
            </tr>
          <?php
          }
          ?>

        </tbody>
        <tfoot>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <!--<th>Image</th>-->
            <!--<th>Price</th>-->
            <th>Shortcode</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </tfoot>
      </table>
    </div>
    <script>
      jQuery(document).ready(function($) {
        var table1 = $('#leadsgrptbl').DataTable({
          autoWidth: false,
          stateSave: true,
          columnDefs: [{
              targets: ['_all'],
              className: 'mdc-data-table__cell'
            },
            {
              orderable: false,
              targets: [4]
            },
          ]
        });
        jQuery(document).on('click', '.cust_b_delete', function() {
          Swal.fire({
            title: "Are you sure?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Delete it!",
            cancelButtonText: "Cancel",
            closeOnConfirm: false,
            closeOnCancel: false
          }).then((result) => {
            if (result.isConfirmed) {
              var id = jQuery(this).attr('data-quality-id');
              jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                  "action": "all_delete_action",
                  "id": id,
                  "table": "<?php echo $tblqlty; ?>"
                },
                success: function(data) {
                  var rowID = "#delete_" + id;
                  table1.row(jQuery(rowID)).remove().draw(false);
                  //jQuery(rowID).remove();
                }
              });
            }
          });
        });
      });
    </script>
  <?php
  }
  function GHAXlt_lead_groups_display()
  {
    global $wpdb;
    $tbllds = $wpdb->prefix . 'ghaxlt_leads';
    $tblcats = $wpdb->prefix . 'ghaxlt_lead_cats';
    $tblgrps = $wpdb->prefix . 'ghaxlt_lead_groups';
  ?>

    <div class="container section-top">
      <div class="row logo">
        <div class="col-md-6 logo1"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/leadtrail-logo.jpeg" /></div>
        <div class="col-md-6 logo2"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/help.png"><a href="https://leadtrail.io/support" target="_blank">Help</a></div>
      </div>
    </div>
    <div class="wrap">
      <div class="wrap-inn">
        <h1>Lead Groups</h1>
        <p class="text-right"><a href="?page=create_group" class="button-back">Create Group</a></p>
      </div>


      <table id="leadsgrptbl" class="display mdl-data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <!--<th>Image</th>-->
            <!--<th>Price</th>-->
            <th>Shortcode</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ghaxlt_lead_groups");
          if (count($results) > 0) {
            foreach ($results as $result) {
          ?>
              <tr id="delete_<?php echo esc_attr($result->id); ?>">
                <td><?php echo esc_html($result->id); ?></td>
                <td><?php echo esc_html($result->name); ?></td>
                <!--<td><?php //echo $result->image; 
                        ?></td>-->
                <!--<td><?php //echo $result->price; 
                        ?></td>-->
                <td>[display-group-leads group='<?php echo esc_attr($result->id); ?>']</td>
                <td><?php echo date('m-d-Y h:i:s A', strtotime($result->created_date)); ?></td>
                <td><a href="?page=edit_group&id=<?php echo esc_attr($result->id); ?>" class="leadbtn"><img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/Frame-1023.png"></a>
                  <a href="javascript:void(0)" data-group-id="<?php echo esc_attr($result->id); ?>" class="cust_b_delete leadbtn"><img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/Frame-1022.png"></a>


                </td>
              </tr>
            <?php
            }
          } else {
            ?>
            <tr>
              <td colspan="6">No Groups Found</td>
            </tr>
          <?php
          }
          ?>

        </tbody>
        <tfoot>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <!--<th>Image</th>-->
            <!--<th>Price</th>-->
            <th>Shortcode</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </tfoot>
      </table>
    </div>
    <script>
      jQuery(document).ready(function($) {
        var table1 = $('#leadsgrptbl').DataTable({
          autoWidth: false,
          stateSave: true,
          columnDefs: [{
              targets: ['_all'],
              className: 'mdc-data-table__cell'
            },
            {
              orderable: false,
              targets: [4]
            },
          ]
        });
        jQuery(document).on('click', '.cust_b_delete', function() {
          Swal.fire({
            title: "Are you sure?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Delete it!",
            cancelButtonText: "Cancel",
            closeOnConfirm: false,
            closeOnCancel: false
          }).then((result) => {
            if (result.isConfirmed) {
              var id = jQuery(this).attr('data-group-id');
              jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                  "action": "all_delete_action",
                  "id": id,
                  "table": "<?php echo $tblgrps; ?>"
                },
                success: function(data) {
                  var rowID = "#delete_" + id;
                  table1.row(jQuery(rowID)).remove().draw(false);
                  //jQuery(rowID).remove();
                }
              });
            }
          });
        });
      });
    </script>
  <?php
  }



  function GHAXlt_lead_categories_display()
  {
    global $wpdb;
    $tbllds = $wpdb->prefix . 'ghaxlt_leads';
    $tblcats = $wpdb->prefix . 'ghaxlt_lead_cats';
    $tblgrps = $wpdb->prefix . 'ghaxlt_lead_groups';
  ?>

    <div class="container section-top">
      <div class="row logo">
        <div class="col-md-6 logo1"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/leadtrail-logo.jpeg" /></div>
        <div class="col-md-6 logo2"><img src="<?php echo GHAX_LEADTRAIL_RELPATH; ?>includes/img/help.png"><a href="https://leadtrail.io/support" target="_blank">Help</a></div>
      </div>
    </div>
    <div class="wrap">
      <div class="wrap-inn">
        <h1>Lead Categories</h1>
        <p class="text-right"><a href="?page=create_category" class="button-back">Create Category</a></p>
      </div>
      <table id="leadscattbl" class="display mdl-data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <!--<th>Image</th>-->
            <th>Type</th>
            <th>Shortcode</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ghaxlt_lead_cats");
          if (count($results) > 0) {
            foreach ($results as $result) {
          ?>
              <tr id="delete_<?php echo esc_attr($result->id); ?>">
                <td><?php echo esc_html($result->id); ?></td>
                <td><?php echo esc_html($result->name); ?></td>
                <!--<td><?php //echo $result->image; 
                        ?></td>-->
                <td><?php echo esc_html($result->type); ?></td>
                <td>[display-category-leads category='<?php echo esc_attr($result->id); ?>']</td>
                <td><?php echo date('m-d-Y h:i:s A', strtotime($result->created_date)); ?></td>
                <td>
                  <a href="?page=edit_category&id=<?php echo esc_attr($result->id); ?>" class="leadbtn"><img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/Frame-1023.png"></a>
                  <a href="javascript:void(0)" data-cat-id="<?php echo esc_attr($result->id); ?>" class="cust_b_delete leadbtn"><img src="<?= GHAX_LEADTRAIL_RELPATH ?>admin/assets/ghax/Frame-1022.png"></a>
                </td>
              </tr>
            <?php
            }
          } else {
            ?>
            <tr>
              <td colspan="6">No Categories Found</td>
            </tr>
          <?php
          }
          ?>

        </tbody>
        <tfoot>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <!--<th>Image</th>-->
            <th>Type</th>
            <th>Shortcode</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </tfoot>
      </table>
    </div>
    <script>
      jQuery(document).ready(function($) {
        var table1 = $('#leadscattbl').DataTable({
          autoWidth: false,
          stateSave: true,
          columnDefs: [{
              targets: ['_all'],
              className: 'mdc-data-table__cell'
            },
            {
              orderable: false,
              targets: [5]
            },
          ]
        });

        jQuery(document).on('click', '.cust_b_delete', function() {
          Swal.fire({
            title: "Are you sure?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Delete it!",
            cancelButtonText: "Cancel",
            closeOnConfirm: false,
            closeOnCancel: false
          }).then((result) => {
            if (result.isConfirmed) {
              var id = jQuery(this).attr('data-cat-id');
              jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                  "action": "all_delete_action",
                  "id": id,
                  "table": "<?php echo $tblcats; ?>"
                },
                success: function(data) {
                  var rowID = "#delete_" + id;
                  table1.row(jQuery(rowID)).remove().draw(false);
                  //jQuery(rowID).remove();
                }
              });
            }
          });
        });
      });
    </script>
<?php
  }
}
