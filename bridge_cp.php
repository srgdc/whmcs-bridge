<?php
function cc_whmcs_bridge_options() {
	global $cc_whmcs_bridge_shortname,$cc_login_type,$current_user;
	$cc_whmcs_bridge_shortname = "cc_whmcs_bridge";

	$is='This section customizes the way '.WHMCS_BRIDGE.' interacts with Wordpress.';
	$cc_whmcs_bridge_options[100] = array(  "name" => "Integration Settings",
            "type" => "heading",
			"desc" => $is);
	$cc_whmcs_bridge_options[110] = array(	"name" => WHMCS_BRIDGE_PAGE." URL",
			"desc" => "The site URL of your ".WHMCS_BRIDGE_PAGE." installation. Make sure this is exactly the same as the settings field 'WHMCS System URL'. If you want to use SSL (https), make sure this URL and the 'WHMCS System URL' are using the https URL. In all cases make sure the setting 'WHMCS SSL System URL' is left blank.",
			"id" => $cc_whmcs_bridge_shortname."_url",
			"type" => "text");
	
	$cc_whmcs_bridge_options[200] = array(  "name" => "Styling Settings",
            "type" => "heading",
			"desc" => "This section customizes the look and feel.");
	
	$cc_whmcs_bridge_options[210] = array(	"name" => "jQuery library",
			"desc" => "Select the jQuery library you want to load. If you have a theme using jQuery, you may be able to solve conflicts by choosing a different library or no library. Note that ".WHMCS_BRIDGE." uses the jQuery $ function, hence it needs to be defined if you manage the loading of jQuery in your Wordpress theme.",
			"id" => $cc_whmcs_bridge_shortname."_jquery",
			"options" => array('' => WHMCS_BRIDGE_PAGE, 'wp' => 'Wordpress', 'checked' => 'None'),
			"default" => 'wp',
			"type" => "selectwithkey");
	$cc_whmcs_bridge_options[220] = array(	"name" => "Custom styles",
			"desc" => 'Enter your custom CSS styles here',
			"id" => $cc_whmcs_bridge_shortname."_css",
			"type" => "textarea");
	$cc_whmcs_bridge_options[230] = array(	"name" => "Load ".WHMCS_BRIDGE_PAGE." styles",
			"desc" => 'Select if you want to load the '.WHMCS_BRIDGE_PAGE.' style.css style sheet. It is recommended to keep this turned off as loading those styles may have an impact on your the styling of your Wordpress site.',
			"id" => $cc_whmcs_bridge_shortname."_style",
			"type" => "checkbox");
	
	$cc_whmcs_bridge_options[300] = array(  "name" => "Other Settings",
            "type" => "heading",
			"desc" => "This section customizes miscellaneous settings.");
	$cc_whmcs_bridge_options[310] = array(	"name" => "Debug",
			"desc" => "If you have problems with the plugin, activate the debug mode to generate a debug log for our support team",
			"id" => $cc_whmcs_bridge_shortname."_debug",
			"type" => "checkbox");
	
	if (!get_option('cc_whmcs_bridge_sso_active')) {
		$cc_whmcs_bridge_options[320] = array(	"name" => "Footer",
				"desc" => "Show your support by displaying the ".WHMCS_BRIDGE_COMPANY." footer on your site.",
				"id" => $cc_whmcs_bridge_shortname."_footer",
				"std" => 'None',
				"type" => "select",
				"options" => array('Page','Site','None'));
	}
	
	if (get_option('cc_whmcs_bridge_sso_active') && defined('WHMCS_BRIDGE_PRO')) {
		require(get_option('cc_whmcs_bridge_sso_active').'/includes/controlpanel.inc.php');
	}
	
	ksort($cc_whmcs_bridge_options);
	
	return $cc_whmcs_bridge_options;
}

function cc_whmcs_bridge_add_admin() {

	global $cc_whmcs_bridge_shortname;

	$cc_whmcs_bridge_options=cc_whmcs_bridge_options();

	if (isset($_GET['page']) && ($_GET['page'] == "cc-ce-bridge-cp")) {
		
		if ( isset($_REQUEST['action']) && 'install' == $_REQUEST['action'] ) {
			delete_option('cc_whmcs_bridge_log');
			foreach ($cc_whmcs_bridge_options as $value) {
				update_option( $value['id'], $_REQUEST[ $value['id'] ] );
			}

			foreach ($cc_whmcs_bridge_options as $value) {
				if( isset( $_REQUEST[ $value['id'] ] ) ) {
					update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
				} else { delete_option( $value['id'] );
				}
			}
			cc_whmcs_bridge_install();
			if (function_exists('cc_whmcs_bridge_sso_update')) cc_whmcs_bridge_sso_update();
			header("Location: options-general.php?page=cc-ce-bridge-cp&installed=true");
			die;
		}
	}

	add_options_page(WHMCS_BRIDGE, WHMCS_BRIDGE, 'administrator', 'cc-ce-bridge-cp','cc_whmcs_bridge_admin');
}

function cc_whmcs_bridge_admin() {

	global $cc_whmcs_bridge_shortname;

	$controlpanelOptions=cc_whmcs_bridge_options();

	if ( isset($_REQUEST['installed']) ) echo '<div id="message" class="updated fade"><p><strong>'.WHMCS_BRIDGE.' installed.</strong></p></div>';
	if ( isset($_REQUEST['error']) ) echo '<div id="message" class="updated fade"><p>The following error occured: <strong>'.$_REQUEST['error'].'</strong></p></div>';
	
	?>
<div class="wrap">
<div id="cc-left" style="position:relative;float:left;width:80%">
<h2><b><?php echo WHMCS_BRIDGE; ?></b></h2>

	<?php
	$cc_whmcs_bridge_version=get_option("cc_whmcs_bridge_version");
	$submit='Update';
	?>
<form method="post">

<?php require(dirname(__FILE__).'/includes/cpedit.inc.php')?>

<p class="submit"><input name="install" type="submit" value="<?php echo $submit;?>" /> <input
	type="hidden" name="action" value="install"
/></p>
</form>
<hr />
<?php  
	if ($cc_whmcs_bridge_version && get_option('cc_whmcs_bridge_debug')) {
		echo '<h2 style="color: green;">Debug log</h2>';
		echo '<textarea rows=10 cols=80>';
		$r=get_option('cc_whmcs_bridge_log');
		if ($r) {
			$v=$r;
			foreach ($v as $m) {
				echo date('H:i:s',$m[0]).' '.$m[1].chr(13).chr(10);
				echo $m[2].chr(13).chr(10);
			}
		}
		echo '</textarea><hr />';
	}
?>

</div> <!-- end cc-left -->
<?php
	require(dirname(__FILE__).'/support-us.inc.php');
	zing_support_us('whmcs-bridge','whmcs-bridge','cc-ce-bridge-cp',CC_WHMCS_BRIDGE_VERSION);
}
add_action('admin_menu', 'cc_whmcs_bridge_add_admin'); ?>