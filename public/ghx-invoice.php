<?php

require GHAX_LEADTRAIL_ABSPATH . 'vendor/autoload.php';

use Dompdf\Dompdf;

class GHAX_Invoice
{
    public function __construct()
    {
        if (isset($_GET['ltinvoice']) && isset($_GET['nc'])) {
            if (!wp_verify_nonce($_GET['nc'], 'leadtrailinvoice')) {
                exit('Verification failure!. Please try again.');
            }

            $this->ghx_download_invoice($_GET['ltinvoice']);
        }
    }

    private function ghx_download_invoice($paymentID)
    {

        $paymentID = intval($paymentID);

        global $wpdb;
        $qry1 = "SELECT * FROM {$wpdb->prefix}ghaxlt_leads_payments WHERE id = $paymentID";
        $paymentrow = $wpdb->get_row($qry1);

        if (!$paymentrow) {
            echo "No record found for provided payment ID.";
            exit();
        }

        $current_user = wp_get_current_user();

        // echo $custom_logo_id = get_theme_mod('custom_logo');
        // $image = wp_get_attachment_image_src($custom_logo_id, 'full');
        // print_r($image);
        // exit();

        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Invoice</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f8f8;
                }

                .invoice-box {
                    max-width: 800px;
                    margin: auto;
                    padding: 30px;
                    border: 1px solid #eee;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
                    background-color: #fff;
                }

                .invoice-box table {
                    width: 100%;
                    line-height: inherit;
                    text-align: left;
                }

                .invoice-box table td {
                    padding: 5px;
                    vertical-align: top;
                }

                .invoice-box table tr td:nth-child(2) {
                    text-align: right;
                }

                .invoice-box table tr.top table td {
                    padding-bottom: 20px;
                }

                .invoice-box table tr.top table td.title {
                    font-size: 45px;
                    line-height: 45px;
                    color: #333;
                }

                .invoice-box table tr.information table td {
                    padding-bottom: 40px;
                }

                .invoice-box table tr.heading td {
                    background: #eee;
                    border-bottom: 1px solid #ddd;
                    font-weight: bold;
                }

                .invoice-box table tr.details td {
                    padding-bottom: 20px;
                }

                .invoice-box table tr.item td {
                    border-bottom: 1px solid #eee;
                }

                .invoice-box table tr.item.last td {
                    border-bottom: none;
                }

                .invoice-box table tr.total td:nth-child(2) {
                    border-top: 2px solid #eee;
                    font-weight: bold;
                }

                .footer {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>

        <body>
            <div class="invoice-box">
                <table cellpadding="0" cellspacing="0">
                    <tr class="top">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td class="title">
                                        <h3><?php echo get_bloginfo('name'); ?></h3>
                                        <?php
                                        // $custom_logo_id = get_theme_mod('custom_logo');
                                        // $image = wp_get_attachment_image_src($custom_logo_id, 'full');
                                        ?>
                                        <!-- <img src="<?php //echo $image[0]; 
                                                        ?>" style="width:100%; max-width:300px;"> -->
                                    </td>
                                    <td>
                                        Invoice #: <?php echo intval($paymentrow->id); ?><br>
                                        Created: <?php $d = new DateTime($paymentrow->created_date);
                                                    echo $d->format('d-m-Y'); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="information">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td>
                                        <?php echo site_url(); ?>
                                    </td>
                                    <td>
                                        <?php echo $current_user->display_name; ?><br>
                                        <?php echo $current_user->user_email; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="heading">
                        <td>Item</td>
                        <td>Price</td>
                    </tr>
                    <tr class="item">
                        <td>Lead ID: <?php echo intval($paymentrow->lead_id); ?></td>
                        <td>$<?php echo number_format(intval($paymentrow->amount), 2); ?></td>
                    </tr>
                    <tr class="total">
                        <td></td>
                        <td>Total: $<?php echo number_format(intval($paymentrow->amount), 2); ?></td>
                    </tr>
                </table>
                <div class="footer">
                    Thank you for your business! If you have any questions, feel free to contact us at <?php echo get_option('admin_email'); ?>
                </div>
            </div>
        </body>

        </html>

<?php
        $html = ob_get_clean();

        // // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        $nm = 'invoice-' . intval($paymentrow->id) . '.pdf';

        // Output the generated PDF to Browser
        $dompdf->stream($nm, array("Attachment" => false));

        exit();
    }
}
