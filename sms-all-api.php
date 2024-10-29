<?php 
/*
    Plugin Name: Any Api SMS For WooCommerce 
    Plugin URI: https://wordpress.org/plugins/howdy-replace-to-welcome-simple/
    Description: Very simple, Send SMS Notification when new order placed in woocommerce with any SMS API
    Author: B Damodar Reddy
    Version: 2.1
    Author URI: https://profiles.wordpress.org/damodar22
    */
function api_sms_any_main() {
    add_options_page(
        'Woo Any API SMS',
        'Woo Any API SMS',
        'manage_options',
        'any-api-sms',
        'api_sms_any'
    );
}
//
global $jal_db_version;
$jal_db_version = '1.0';
function api_pro_jal_install() {
	global $wpdb;
	global $jal_db_version;
	$table_name = $wpdb->prefix . 'api_sms_any';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE if not exists $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  api varchar(200) DEFAULT 'api here' NOT NULL,
  UNIQUE KEY id (id)
) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	add_option( 'jal_db_version', $jal_db_version );
}
function api_pro_jal_install_data() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'api_sms_any';
	$wpdb->insert( 
		$table_name, 
		array( 
			'id' => '', 
			'api' => 'api here'
		) 
	);
}
function api_pro_plugin_remove_database() {
     global $wpdb;
     $table_name = $wpdb->prefix . "api_sms_any";
     $sql = "DROP TABLE IF EXISTS $table_name;";
     $wpdb->query($sql);
     delete_option("my_plugin_db_version");
}
// Add settings link on plugin page
function api_pro_sms_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=any-api-sms">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'api_pro_sms_settings_link' );
//
 add_action( 'admin_menu', 'api_sms_any_main' );
register_deactivation_hook( __FILE__, 'api_pro_plugin_remove_database' );
register_activation_hook( __FILE__, 'api_pro_jal_install' );
register_activation_hook( __FILE__, 'api_pro_jal_install_data' );
// updation 
register_activation_hook(__FILE__, 'apisms_activation');
function apisms_activation() {
  $notices= get_option('my_plugin_deferred_admin_notices', array());
 // $notices[]= "Any Api SMS For WooCommerce : Thanks for using this plugin";
  update_option('my_plugin_deferred_admin_notices', $notices);
}

add_action('admin_init', 'apisms_admin_init');
function apisms_admin_init() {
  $current_version = 2.1;
  $version= get_option('my_plugin_version');
  if ($version != $current_version) {
    // Do whatever upgrades needed here.
    update_option('my_plugin_version', $current_version);
    $notices= get_option('my_plugin_deferred_admin_notices', array());
    $notices[]= "Any Api SMS For WooCommerce : Upgraded version $version to $current_version.";
    update_option('my_plugin_deferred_admin_notices', $notices);
  }
}

add_action('admin_notices', 'apisms_admin_notices');
function apisms_admin_notices() {
  if ($notices= get_option('my_plugin_deferred_admin_notices')) {
    foreach ($notices as $notice) {
      echo "<div class='updated'><p>$notice</p></div>";
    }
    delete_option('my_plugin_deferred_admin_notices');
  }
}

register_deactivation_hook(__FILE__, 'apisms_deactivation');
function apisms_deactivation() {
  delete_option('my_plugin_version'); 
  delete_option('my_plugin_deferred_admin_notices'); 
}
//

/**
 * WooCommerce notification submit customer order
 */
function api_pro_wps_wc_notify_customer($order_id){
	global $woocommerce, $wpdb;
		 $table_name = $wpdb->prefix . 'api_sms_any';
         $result = $wpdb->get_row( "SELECT * FROM $table_name" );
		//  foreach ( $result as $print )   { 
         $api=$result->api;
	$order = new WC_Order($order_id);
	$sms_mobile=$_REQUEST['billing_phone'];
	$customer_name=$_REQUEST['billing_first_name'];
	$sms_msg = urlencode("hi $customer_name, your order with the id $order_id  has been successfully placed,thank you");
	$api=str_replace("[mobile]",$sms_mobile,$api);
	$api=str_replace("[message]",$sms_msg,$api);
	$result = wp_remote_get("$api");
    }
add_action('woocommerce_new_order', 'api_pro_wps_wc_notify_customer',10,1);
//
function api_sms_any() {
    ?>
<div class="wrap">
  <h2>Any SMS Api For WooCommerce</h2>
  <hr />
  <?php
		 if(isset($_REQUEST['save']))
		   {   
			   $ur_api_id=$_REQUEST['ur_api_id'];
			   $ur_api=$_REQUEST['ur_api'];
				global $wpdb;
				$table_name = $wpdb->prefix . 'api_sms_any';
				 $output=$wpdb->query("UPDATE $table_name SET api='$ur_api' WHERE id='$ur_api_id'");
				 if($output)
				 { 
				     ?>
  <div class='success'>successfully updated<br>
    <br />
    <h4>Please Wait....Reloading the page </h4>
    <br />
  </div>
  <br />
  <meta http-equiv="refresh" content="0">
  <?php
				 }
				 else
				 {
					echo "<div class='error-msg'>Already updated (or) please Try again</div><br />"; 
				 }
	  	  	   }
		 global $wpdb;
		 $table_name = $wpdb->prefix . 'api_sms_any';
         $result = $wpdb->get_row( "SELECT * FROM $table_name" );
         $api_id=$result->id;
         $api=$result->api;
		 ?>
  <form action="#" method="post">
    <div class="notice" style="margin-top:30px;">Repplace your mobile number with just <strong>[mobile]</strong> (this plugin take it from user billing phone number directly) , message text with just <strong>[message]</strong> (this plugin shoot pre defined sms with order id directly)</div>
    <br />
    <input type="hidden" name="ur_api_id" value="<?php echo $api_id; ?>"  />
    Your API:<br />
    <input type="text" name="ur_api" value="<?php echo $api; ?>" size="150" />
    <br />
    <span style="color:red">EX:http://tra.xxxxx.com/websms/sendsms.aspx?userid=xxxxxx&password=xxxxx&sender=xxxxxx&phone=[mobile]&message=[message] </span><br />
    <br />
    <hr />
    <input  type="submit" name="save" value="Save" />
  </form>
  <h2><a href="http://www.konnectplugins.com/woo-sms-plugin-installation-steps/" target="_blank">Help Documentation</a></h2>
</div>
<hr />
<a href="http://www.konnectplugins.com/" target="_blank"><img src="http://www.konnectplugins.com/wp-content/uploads/2016/05/logo-300x89.png" /></a>
<h2 style="color:red">500 transactional SMS  RS 200</h2>
<br>
<h4>online payment available </h4>
<hr>
<h1><a href="http://sms.konnectplugins.com/Account/Register.aspx" target="_blank">Click Here</a>.</h1>
<h3>** Professional Package **</h3>
1.Send SMS to any number(s)<br />
2.Admin can get Order SMS notifications<br />
3.Customizable SMS text<br />
4.Different SMS send corresponding to different Order Status<br />
5.Custom status 'SHIPPED' added.
6.Order sms send after successful payment
Click
<h2><a href="http://www.konnectplugins.com/product/sms-gateway-integration-wordpress-plugin/" target="_blank">WOO SMS ANY API-Pro</a></h2>
or
<h3>
Email me : support@konnectplugins.com
</h2>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
  <input type="hidden" name="cmd" value="_s-xclick">
  <input type="hidden" name="hosted_button_id" value="BN3FXAQDJ4CNW">
  <input type="image" style="width: 167px;margin-top: 34px;" src="https://scuderiacp.files.wordpress.com/2014/09/pp-donate1.png" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
  <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
<?php
}
?>