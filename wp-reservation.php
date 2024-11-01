<?php
/*
Plugin Name: WP-Reservation booking system
Plugin URI: http://integra.work/wpresbuy
Description: This WordPress plugin allows for the creation of own sites with a booking system. It can apply hostels and hotels on their websites. Activate plugin and create a page  which includes the text {RESERVATION}, or {RESERVATION2} new interface with a list only groups, or {RESERVATION3} new interface with a list of all resources or {RESERVATION4} interface with a two calendars and two selects for choose resources (recommended for hotel portals). For PRO version working tags {RESERVATION+id1,id2,...} and {RESERVATION-id1,id2,...} for reservation only one resource on page or all resources exclude one. Examples {RESERVATION+103},{RESERVATION+103,104},{RESERVATION-103}   
Version: 1.5.4
Author: Alexey Isaev
Author URI: http://integra.work/wpresbuy
*/
/*  Copyright 2009-2015 Alexey  Isaev

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

//print_r($_REQUEST);


	foreach ( $_REQUEST as $key => $value ) {
		$_REQUEST[$key] = filter_var($value, FILTER_SANITIZE_STRING);}
		
define ("RES_PLUGIN_DIR", basename(dirname(__FILE__)));
define ("RES_PLUGIN_URL", get_settings("siteurl")."/wp-content/plugins/".RES_PLUGIN_DIR);
define ("RES_PLUGIN_PATH",ABSPATH."wp-content/plugins/".RES_PLUGIN_DIR);
define ("RES_CUR_PAGE","http://".$_SERVER["HTTP_HOST"].$_SERVER["REDIRECT_URL"]);
add_action("init", "res_init");
add_action("admin_init", "res_adm_init");
add_action("admin_menu", "res_menu");
add_action("admin_head", "res_add_head" );
add_action("wp_head", "res_add_head" );
add_filter("the_content","res_insert");
add_filter("plugin_action_links", "res_links", 10, 2 );
register_activation_hook(__FILE__,'activate');

$path_to_php_file_plugin = "wp-reservation/wp-reservation.php";
add_action("deactivate_" . $path_to_php_file_plugin, "deactivate"); 
add_action("activate_" . $path_to_php_file_plugin,  "activate"); 

$GLOBALS["version"] = "free";
if (file_exists(RES_PLUGIN_PATH. '/wp-res-pro.php')) { require_once(RES_PLUGIN_PATH. "/wp-res-pro.php" );  $GLOBALS["version"] = "pro";}

if ($GLOBALS["version"]	== "pro") add_action("show_user_profile", "show_user_fields");
if ($GLOBALS["version"]	== "pro") add_action("edit_user_profile", "show_user_fields");
if ($GLOBALS["version"]	== "pro") add_action("personal_options_update", "update_user_fields");
if ($GLOBALS["version"]	== "pro") add_action("edit_user_profile_update", "update_user_fields");





function res_adm_init() {
	if ($_REQUEST['page']=="resources") {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jqtreetable', RES_PLUGIN_URL.'/js/jQTreeTable/jqtreetable.js');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-ui-resizable'); }
	
	 	
	//<script src="'.RES_PLUGIN_URL.'/js/jQTreeTable/jqtreetable.js" type="text/javascript"></script>';
	
}


function resLocale($locale = "") { 
	global $locale;
	
		$mofile = RES_PLUGIN_PATH  .'/lang/'.RES_PLUGIN_DIR.'-'.$locale.'.mo';
		return load_textdomain(RES_PLUGIN_DIR, $mofile);
	
	if ( empty( $locale ) ) $locale = get_locale();
	if ( !empty( $locale ) ) {
		
		$mofile = RES_PLUGIN_PATH  .'/lang/'.RES_PLUGIN_DIR.'-'.$locale.'.mo';
		
		if (file_exists($mofile))    return load_textdomain(RES_PLUGIN_DIR, $mofile);
		else                        return false;
	} return
	false;
}


if ( !function_exists('wp_sanitize_redirect') ) :
function wp_sanitize_redirect($location) {
	$location = preg_replace('|^a-z0-9-~+_.?#=&;,/:%!|i', '', $location);
	$location = wp_kses_no_null($location);

	
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$location = _deep_replace($strip, $location);
	return $location;
}
endif;

function res_links($links, $file){ 
	
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(dirname(__FILE__).'/wp-reservation.php');
	
	if ($file == $this_plugin){
		$settings_link1 = '<a href="admin.php?page=settings">' . __('Settings', 'wp-reservation') . '</a>';
		$settings_link2 = '<a href="http://integra.work/wpresbuy">' . __('Buy PRO for 5 USD!', 'wp-reservation') . '</a>';
		if ($GLOBALS["version"]	!= "pro") array_unshift( $links, $settings_link2 ); 
		array_unshift( $links, $settings_link1 ); 
	}
	return $links;
}

function res_init()
{


	wp_register_script('jscal2', RES_PLUGIN_URL	   .'/js/jscal2/jscal2.js');
	wp_enqueue_script('jscal2');
	$lang='en';

	if (file_exists(RES_PLUGIN_PATH.'/js/jscal2/lang/'.substr(get_locale(),0,2).'.js')) { $lang=substr(get_locale(),0,2);}
	wp_register_script('jscal2lang', RES_PLUGIN_URL.'/js/jscal2/lang/'.$lang.'.js');
	wp_enqueue_script('jscal2lang');
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-dialog');
	
}



function res_add_head()
{
resLocale() ;

if ($_REQUEST['pager']=="selectajax") {select_ajax();  exit;}
if ($_REQUEST['pager']=="sberprint") {echo sberprint();  exit;}


	echo '
<link rel="stylesheet" type="text/css" href="'.RES_PLUGIN_URL.'/css/calendar/jscal2.css" />
<link rel="stylesheet" type="text/css" href="'.RES_PLUGIN_URL.'/css/calendar/border-radius.css" />
<link rel="stylesheet" type="text/css" href="'.RES_PLUGIN_URL.'/css/calendar/'.get_option("res_calendar_color").'/'.get_option("res_calendar_color").'.css" />
<link rel="stylesheet" type="text/css" href="'.RES_PLUGIN_URL.'/css/res/'.get_option("res_color").'/style.css" />
<link href="'.RES_PLUGIN_URL.'/css/res/res.css" rel="stylesheet" type="text/css" /> 
';
	
if (is_admin()) { echo '<link href="'.RES_PLUGIN_URL.'/css/res/admin.css" rel="stylesheet" type="text/css" /> ';}
}


function res_insert($content)
{ 


	if (preg_match('{RESERVATION([0-9]*)}',$content,$interf))
	{

		$interf=$interf[1];
		$res_output = res_page(0,$interf);
		$content = preg_replace('/{RESERVATION([0-9]*)}/',$res_output,$content);
		}

	if ($GLOBALS["version"]	== "pro")	$content=pro_res_insert($content); 

	return $content;
}


function res_page($charr,$interf)
{
//echo $_REQUEST['datebegin']."<br>";
	$content = "";	
	$content .= '<div class="reservation">';
	//print_r($charr);
	
	
	
	switch ($_REQUEST['pager']) {
	
	case '1':  
	if (strtotime($_REQUEST['dateend'])<=strtotime($_REQUEST['datebegin']) && $interf==4)
	{
			$err .="<br>". __(" - Arrival date more then Departure date","wp-reservation");
			if ($err!="") {
					$err = "<div class=alert>".__("When completing the form, the following error(s) occurred:","wp-reservation") . $err."</div>";
					$content .=($err);
					$content .= pageI4($interf);
					
					}
	
	}
	else
	{
	
	if (empty($_REQUEST['datebegin']) || empty($_REQUEST['dateend'])) {
	$err="";
	if ($_REQUEST['datebegin']=="") 
				{
					$err .= "<br>".__(" - Not specified Arrival date","wp-reservation");
				}
	if ($_REQUEST['dateend']=="") 
				{
					$err .="<br>". __(" - Not specified Departure date","wp-reservation");
				}				
	if ($err!="") {
					$err = "<div class=alert>".__("When completing the form, the following error(s) occurred:","wp-reservation") . $err."</div>";
					$content .=($err);
					$content .= pageI4($interf);
					
					}
	
	}
	else  {
		
	if ($interf!=2 && $interf!=3 && $interf!=4)  $content .= page1($charr);  else { if ($_REQUEST['resource']!=0)  $charr=charr("+",explode(",",$_REQUEST['resource']));  $content .= page1($charr);}
	}
	}
	break ; 
	case '2': if (array_sum($_REQUEST['reskol'])==0) {$content .= "<div class=alert>".__("You must select at least one of the resources","wp-reservation")."</div>"; $content .= page1($charr);} else $content .= page2a();        break ; 
	case '3':  {
			if ($_REQUEST['alreadyreg']!=1) {
				$err="";
				if ($_REQUEST['last_name']=="") {
					$err .= __(" - Not specified (Name)","wp-reservation");
				}
				
				
				if ($GLOBALS["version"]	== "pro") {
					$err .= check_fields();
				} 
				
				
				if (empty($_REQUEST['rules'])) {
					$err .= __(" - You must agree to the terms","wp-reservation");
				}
				

				
				if ($err!="") {
					$err = "<div class=alert>".__("When completing the form, the following error(s) occurred:","wp-reservation") . $err."</div>";
					$content .=($err); $content .= page2b();
				} else {		
					$content .= page3();
				} 
			} else $content .= page3(); }
		
		
		break ; 
		
		
	case '2b': { 	$err="";
			
			if ($_REQUEST['log']=="") {
				$err .= __(" - Not specified (email address)","wp-reservation");
			}
			
			if ($err!="") {
				$err = "<div class=alert>".__("When completing the form, the following error(s) occurred:","wp-reservation") . $err."</div>";
				$content .=($err); $content .= page2a();
			} else {
				if (!eregi("^[._a-zA-Z0-9-]+@[.a-zA-Z0-9-]+.[a-z]{2,6}$", $_REQUEST['log'])) {$err.= __("That isn`t a valid e-mail address.  E-mail addresses look like: username@example.com","wp-reservation");
					$err = "<div class=alert>".$err."</div>"; $content .= $err; $content .= page2a();
				} else {
					$content .= page2b();}
			} }         break ; 
		
	case 'mobcash': $content .= mobcash();        break ; 
	//case 'selectajax':  select_ajax();  exit;       break ; 
	case 'sber': $content .= sber();        break ; 
	case 'sberprint': $content .= sberprint();        break ; 
	case 'webmoney': $content .= webmoney ();        break ; 
	case 'robokassa': $content .= robokassa ();        break ; 
	case 'paypal': $content .= paypal ();        break ; 
	case 'confirm_paypal': $content .=confirm_paypal(); break;  
	case 'success_paypal': $content .=success_paypal(); break; 
	case 'cancel_paypal': $content .=cancel_paypal(); break; 
	case 'nopay': $content .= "<div class=alert>".__("You have not chosen a payment system","wp-reservation")."</div>"; $content .= page3 ();break ; 
		
	default: if ($interf!=2 && $interf!=3 && $interf!=4 )  	$content .= page0(); else if ($interf==4) $content .= pageI4($interf); else $content .= pageI23($interf);               break;
	}

	$content .= '</div>';

	return $content;

}


function update_table_ajax() {
	global $wpdb;
	if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
		$time = date("H:i:s", time());
		header('Content-Type: text/html; charset=utf-8');
		//	print_r ($_REQUEST); exit;

		if (isset($_REQUEST['resourceid'])) {

			$qry="update ".$wpdb->prefix ."res_resources  set ";

			foreach ($_REQUEST as $key=>$value) {
				if ($key!="page" && !strpos($key,"id")) {
				if (($value)=='')  $value="NULL";
				$qry.=	" $key='$value' ,";

				}
				if (strpos($key,"id")) $key_id=$key;	
			}
			$qry=substr($qry,0,-1).	" where $key_id='".$_REQUEST[$key_id]."' ";
									
			$wpdb->query($wpdb->prepare($qry,"") );

			echo '<img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/load.gif">';
			exit 	;


		}
	}	
}



function select_ajax() {
	global $wpdb;
	//if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
	{
		$time = date("H:i:s", time());
//		header('Content-Type: text/html; charset=utf-8');
		//	print_r ($_REQUEST); exit;

	list($results,$map,$treet,$level)=treesort();
	
	
	$treeselect=treeselect($results,$level,42);

			echo '<br><b>'.__("Room","wp-reservation").'</b><br>'.$treeselect;
			
			//.'<img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/load.gif">';
	//		exit 	;


		
	}	
}



function res_menu() {

global $submenu, $menu	, $wpdb;


if (isset($_REQUEST["expand_x"]) || $_REQUEST['action']=="add") setcookie ("treet", "", time() - 3600);             
if (isset($_REQUEST["collapse_x"])) {  

list($results,$map,$treet)=treesort(); 

setrawcookie ("treet", $treet);
}

resLocale() ;

if ( strpos($_SERVER['HTTP_HOST'],'integra.work') !== FALSE ) {$user_role_plugin = $user_role_settings  = 0 ; } else { $user_role_plugin = get_option("res_security_plugin") ; $user_role_settings = get_option("res_security_settings") ;}

update_table_ajax();



	add_menu_page(__("Reservation","wp-reservation"), __("Reservation","wp-reservation"), $user_role_plugin,"ordertable" , 'res_options'
,RES_PLUGIN_URL."/img/ico16x16.png");
	add_submenu_page("ordertable", __("Reservation","wp-reservation"), __("Orders","wp-reservation"), $user_role_plugin,"orders", 'res_options');
	add_submenu_page("ordertable", __("Reservation","wp-reservation"), __("Reserve","wp-reservation"), $user_role_plugin,"makeorder", 'res_options');
	add_submenu_page("ordertable", __("Reservation","wp-reservation"), __("Resources","wp-reservation"), $user_role_plugin, "resources", 'res_options');
	add_submenu_page("ordertable", __("Reservation","wp-reservation"), __("Special offers","wp-reservation"), $user_role_plugin, "offers", 'res_options');
	add_submenu_page("ordertable", __("Reservation","wp-reservation"), __("Settings","wp-reservation"), $user_role_settings, "settings", 'res_options');
	if ($GLOBALS["version"]	!= "pro") add_submenu_page("ordertable", __("Reservation","wp-reservation"), __("Pro","wp-reservation"), $user_role_settings, "pro", 'res_options');
	$submenu[plugin_basename( "ordertable" )][0][0] = __("Orders table","wp-reservation");	
    

	
	}




function res_options() {

echo '<div class="wrap">';

echo menu_admin();		
		
	
	if(isset($_REQUEST['page']))  switch ($_REQUEST['page']) {
		
		case "resources" : 	adm_resources(); break;
		case "orders" : 	
			if ($_REQUEST['subpage']=="mail")  {adm_mailbron();	break;}
			elseif ($_REQUEST['subpage']=="suborder")  {adm_suborder();	break;}
			else {adm_orders(); break;}
		case "mail" : 		adm_mailbron(); break;
		case "suborder" : 	adm_suborder(); break;
		case "offers" : 	adm_offers(); break;
		case "settings"  :  adm_settings(); break;
		case "pro"  :  		adm_pro(); break;
		case "makeorder" : 		adm_makeorder0(); break;
		case "makeorder1" : 	adm_makeorder1();	 break;
		case "makeorder2" : 	adm_makeorder2();	 break;
		case "ordertable" : echo adm_ordertable(); break;
	
	}
	else  
		adm_orders();
						
echo '</div>';			
			
	
	
}

function activate() 
{ 
	
	global $wpdb;

$table_name = $wpdb->prefix . "res_resources";	
$table_name_old = $wpdb->prefix . "res_typeroom";
$sqlresources = "INSERT INTO $table_name (resourceid, parent, name, price, capacity) VALUES
(100,0,'Demo resources',NULL ,NULL ),
(101,100,'Hotel New York',NULL ,NULL ),
(102,101,'Garden view rooms',NULL ,NULL ),
(103,102,'Room 201',30,1),
(104,102,'Room 203',35,1),
(105,102,'Room 204',40,1),
(106,101,'Sea view rooms',NULL ,NULL),
(107,106,'Room 302',50,1),
(108,106,'Room 304',60,1),
(109,100,'Hotel Sydney',NULL ,NULL),
(110,109,'Mountain views rooms',NULL ,NULL),
(111,110,'Room 111',20,4),
(112,110,'Room 112',15,6),
(113,100,'Athletic Gym',NULL ,NULL),
(114,113,'Pilates',NULL ,NULL),
(115,114,'Trainer - Kate',15,10),
(116,114,'Trainer - Alice',15,10),
(117,113,'Bodybuilding',NULL ,NULL),
(118,117,'Trainer - Max',20,4),
(119,100,'MaxHealth Clinic',NULL ,NULL),
(120,119,'Dentist',NULL ,NULL),
(121,120,'Dr. Dick',50,1),
(122,120,'Dr. Quick',50,1),
(123,100,'Car rental',NULL ,NULL),
(124,123,'Toyota',NULL ,NULL),
(125,124,'Camry',50,1),
(126,124,'Corolla',50,1),
(127,123,'Ford',NULL ,NULL),
(128,127,'Mondeo',50,1),
(129,127,'Focus',50,1),
(130,123,'Mitsubishi',NULL ,NULL),
(131,130,'Lancer',60,1),
(132,130,'L200',80,1),
(133,100,'Workers',NULL ,NULL),
(134,133,'Worker 1',50,1),
(135,133,'Worker 2',70,1)
; ";	

	
	
	if($wpdb->get_var("show tables like '$table_name'") != $table_name && $wpdb->get_var("show tables like '$table_name_old'")  != $table_name_old ) {
		$sql = "CREATE TABLE $table_name (
`resourceid` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
`parent` MEDIUMINT(9) NOT NULL  DEFAULT '0' ,
`name` TINYTEXT,
`price` DECIMAL(11,2) DEFAULT NULL,
`capacity` INTEGER(11) DEFAULT NULL,
PRIMARY KEY (`resourceid`)
)";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sqlresources);		
}
	$table_name = $wpdb->prefix . "res_orders";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
`orderid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`userid` BIGINT(20) UNSIGNED NOT NULL,
`price` DECIMAL(11,2) DEFAULT NULL,
`datebegin` DATE DEFAULT NULL,
`dateend` DATE DEFAULT NULL,
`comments` TEXT ,
`payed` TINYINT(4) DEFAULT NULL,
`paysys` MEDIUMINT(9) DEFAULT NULL,
PRIMARY KEY (`orderid`),
KEY `userid` (`userid`)
)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);		}
	
	$table_name = $wpdb->prefix . "res_orders_content";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
`id` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
`orderid` BIGINT(20) UNSIGNED NOT NULL,
`resourceid` MEDIUMINT(9) DEFAULT NULL,
`kol` INTEGER(11) DEFAULT NULL,
`price` DECIMAL(11,2) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `userid` (`orderid`)
)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);				}
	
	$table_name = $wpdb->prefix . "res_paysys";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
`paysysid` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
`name` TINYTEXT,
PRIMARY KEY (`paysysid`)
)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);	
		
		$sql = "	INSERT INTO $table_name (`paysysid`, `name`) VALUES
(1,'Qiwi'),
(2,'PayOnlineSystem'),
(3,'Sberbank'),
(4,'Robokassa'),
(5,'Webmoney'),
(6,'PayPal'); ";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);	
	}
	$table_name = $wpdb->prefix . "res_offers";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
`resourceid` MEDIUMINT(9) UNSIGNED NOT NULL,
`datebegin` DATE NOT NULL,
`dateend` DATE NOT NULL,
`price` DECIMAL(11,2) NOT NULL,
`description` TEXT ,
UNIQUE KEY `id` (`id`)
)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);				
		
	$sql = "INSERT INTO $table_name (id,resourceid, datebegin, dateend, price, description ) VALUES
(1,103,'2010-03-01','2010-03-31',5,'March discount'),
(2,104,'2010-04-01','2010-04-30',5,'April discount'),
(3,105,'2010-05-01','2010-05-31',5,'May discount');";		
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);				
				
		}

	add_option("res_db_version", "0.7");
	add_option("res_days","14");
	add_option("res_color","white");
	add_option("res_calendar_color","gold");
	add_option("res_security_plugin","7");
	add_option("res_security_settings","10");
	add_option("res_full_uninstall","");
	add_option("res_terms","Your terms of booking");
	add_option("res_calc","1");

	if ($GLOBALS["version"]	== "pro") pro_add_settings();
	
	if (get_option("res_db_version")== "0.5" || get_option("res_db_version")== FALSE )
	{
	$table_name = $wpdb->prefix . "res_typeroom";
	$table_resources = $wpdb->prefix . "res_resources";
	$sql =("rename table $table_name to $table_resources");
	$wpdb->query($sql);				
	$sql =("alter table $table_resources CHANGE roomid resourceid MEDIUMINT(9), change places capacity INTEGER(11) DEFAULT NULL,  add  parent MEDIUMINT(9) NOT NULL  DEFAULT '0' after resourceid ,  drop minorder,  CHANGE price price INTEGER(11) DEFAULT NULL");
	$wpdb->query($sql);					
	$table_name = $wpdb->prefix . "res_orders_content";
	$sql =("alter table $table_name CHANGE roomid resourceid MEDIUMINT(9)");
	$wpdb->query($sql);					
	$table_name = $wpdb->prefix . "res_offers";
	$sql =("alter table $table_name CHANGE roomid resourceid MEDIUMINT(9)");
	$wpdb->query($sql);					
	$wpdb->query($sqlresources);				
	


	//$sql =("alter table $table_resources add column parent ");
	//$wpdb->query($sql);				

	update_option("res_db_version", "0.7");		
	}	;

}


function deactivate() 
{ 
	global $wpdb;
	if (get_option("res_full_uninstall"))
	{

		$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix . "res_offers, ".$wpdb->prefix . "res_orders, ".$wpdb->prefix . "res_orders_content, ".$wpdb->prefix . "res_paysys, ".$wpdb->prefix . "res_resources ";	
		$wpdb->query($sql);					

		delete_option("res_db_version");
		delete_option("res_days");
		delete_option("res_color");
		delete_option("res_calendar_color");
		delete_option("res_security_plugin");
		delete_option("res_security_settings");
		delete_option("res_full_uninstall");
		delete_option("res_terms");
		delete_option("res_calc");
			
		if ($GLOBALS["version"]	== "pro") pro_delete_settings();
		
		
	}


}

function res_pass ()
{

	$fp = file ("pass.txt",1);
	return (trim($fp[rand(0,count($fp)-1)]).rand(100,999));

}


function page0() {

	$today = date("Ymd");  $today2 = date("Y-m-d"); 
	$content = "";
	$content .= <<<EOF
	<p>
EOF
	.__('To see prices and availability, enter your arrival date and how many days you will use our resources.', 'wp-reservation').
	<<<EOF
	</p>
	<form name="res_order"  action="" method=post ><input type="hidden" name=pager value="1" >
	<input size="10" id="datebegin2" name="datebegin" value="$today2" />
	<input type="hidden" id="dateend" name="dateend" value="$today2" />
	<button id="f_btn2" onclick="return false;">...</button> 
	<select name=kolday class="koldays" >
EOF;
	$maxdays=get_option("res_days");
	for($i=1;$i<=$maxdays;$i++)
	{$content .= "<option value=\"$i\" >$i</option>";}	
	$content .= <<<EOF
	</select>
	<input   type="submit" value="
EOF
.__("Continue","wp-reservation").
<<<EOF
"></form>
<script type="text/javascript">//<![CDATA[
var cal = Calendar.setup({
onSelect: function(cal) { cal.hide() }
,
min:$today 
});
cal.manageFields("f_btn2", "datebegin2", "%Y-%m-%d");
//]]></script>	
	
EOF;

	return $content ;
};


function page1($charr) {
	global $wpdb;
	$content = "";

 	
	
	if ($_REQUEST['pager']	== 1) {
		list($year, $month, $day) = sscanf($_REQUEST['datebegin'], "%04s-%02s-%02s"); 
		
		$datebegin=$year."-". $month."-". $day;
		
		$dateend=date("Y-m-d",strtotime($datebegin)+($_REQUEST['kolday']-1)*24*60*60);
		
	} else {
		
		$datebegin=$_REQUEST['datebegin'];
		
		$dateend=$_REQUEST['kolday'];
	}
	if ( isset ($_REQUEST['flagkd']) )	{
	//if (get_option("res_calc")==2) {$_REQUEST['kolday']=$_REQUEST['kolday']-1;}
	//$dateend=$_REQUEST['dateend']; 
	$dateend=date("Y-m-d",strtotime($_REQUEST['dateend'])-1*24*60*60);
	$kolday=$_REQUEST['kolday']=(strtotime($_REQUEST['dateend'])-strtotime($_REQUEST['datebegin']))/(24*60*60);}


	

	$content .= "<p>".__("Please select resources you want to book, and desired amount.","wp-reservation")."</p>";

	
	
	
	

	$qry="select a.resourceid, name ,  
	(if ('$datebegin' >= d.datebegin and '$dateend'<= d.dateend, d.price, a.price)) price,  capacity  
	";

	$dt=strtotime ($datebegin);


	
	
	$bt = '<form name="res_order" id="res_order"  action="" method="post"> <table width=100% class="res" > <thead>	<tr ><td style=\"width:200px\" > </td>';
	while (strtotime($dateend)>=$dt)  {
		$qdt=date("Y-m-d",$dt);
		$qdtout=date("d/m",$dt);

		$qry.=",    capacity - sum(if ( '$qdt' between c.datebegin and c.dateend and payed=1, kol, 0)) `$qdt`  ";
		$bt .= "<td  >$qdtout</td>";
		$dt=$dt+24*60*60;
	}
	$bt .= '<td  >'.__("price","wp-reservation").'</td><td  >'.__("select resources","wp-reservation").'</td></tr></thead>';
	if ($GLOBALS["version"]	== "pro" && $charr!=0) $qry.=pro_page1_qry($charr); else
	$qry.="   from  
	".$wpdb->prefix . "res_orders  c
INNER JOIN  ".$wpdb->prefix . "res_orders_content   b 
ON (c.orderid =
	b.orderid)
	RIGHT JOIN  ".$wpdb->prefix . "res_resources   a ON (b.resourceid =
	a.resourceid)
	LEFT JOIN ".$wpdb->prefix . "res_offers d ON (d.resourceid = a.resourceid)
	where capacity>0 
	group by name, a.resourceid, price ";

//echo $qry;
	$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );

	$rowflag=1;  $j=1;
	if (count($results)>0) {
		
		$content .= $bt;
		foreach ($results as $fld) {
			$content .= '<tr class="row'.$rowflag.'">';
			$k=1;
			$min=$fld[3];
			foreach ($fld as $fld2) {

				switch ($k) {
				case 1:   $resource=$fld2; break;
				case 2: $content .= "<td >".$fld2."</td>"; break;
				case 3: $price = $fld2;  break;
				case 4: $capacity = $fld2;  break;
//				case 4: $minorder = $fld2;  break;       
				default:  if ($capacity==1) {if ($fld2==1) $content .= "<td ><img src=\"".RES_PLUGIN_URL."/js/jQTreeTable/images/tick.png\"></td>"; else $content .= "<td ><img src=\"".RES_PLUGIN_URL."/js/jQTreeTable/images/del.png\"></td>";} else {$content .= "<td >$fld2</td>";} if ($fld2<$min) $min=$fld2; break;
				}
				$k++;
			}

			$content .= '<td>'.$price.'</td><td><input type=hidden name="price['.$resource.']" value="'.$price.'"><input type=hidden name=resource_name['.$resource.'] value="'.$fld[1].'">';
			
			
			if ($min==1) {$content .= '<input type="checkbox" name="reskol['.$resource.']" value="1">'; 
			if ($j>1) $javaif .= " && (obj.elements['reskol[$resource]'].checked==false) "  ; else $javaif = " (obj.elements['reskol[$resource]'].checked==false) ";
			}
			else 
			//if ($minorder<=$min ) 
			if  ($min>0)
			{ 
				$content .= '<select col=30  name=reskol['.$resource.']> <option value="0">'.__("Select").'</option>   ';
				if ($j>1) $javaif .= " && (obj.elements['reskol[$resource]'].value==0) "  ; else $javaif = " (obj.elements['reskol[$resource]'].value==0) ";
				for ($i=1;$i<=$min; $i++) {
					$content .= '<option value="'.$i.'">'.$i.'</option>';			
				}
				$content .= '</select>' ; 
			} 
			
			$content .= '</td></tr>';			
			$j++;
			$rowflag = $rowflag*(-1);

		}

		$content .= '</table><div align=right><input type="hidden" name=pager value="2" ><input type="hidden" name="datebegin" value="'.$datebegin.'" ><input type="hidden" name="dateend" value="'.$dateend.'" ><input type="hidden" name="kolday" value="'.$_REQUEST['kolday'].'" > <input class="res-next" id="button"  type="submit" value="'.__("next","wp-reservation").'"   ></div></form>';			

		$content .= <<<EOF
		<div id="error" title="
EOF
.__("Error","wp-reservation").
<<<EOF
">
</div>
	<script language="javascript">
	
	jQuery(function() {
		jQuery( "#error" ).dialog({
						bgiframe: true,
			height: 120,
			modal: true,
			autoOpen: false

		});

		jQuery( "#button" ).click(function() {
			var err = "";
			var obj = document.forms['res_order'];
			
			
			if (  $javaif ) {
				err += "  ";
			}
			
			

			if (err!="") {
				err ="
EOF
				.__("You must select at least one of the resources","wp-reservation").
				<<<EOF
				"	+ err;
				document.getElementById("error").innerHTML=err;
			jQuery( "#error" ).dialog( "open" );
			return false; } 
		
		
			
		});
	});
		
		-->
		</script> 
EOF;
		
		
	}
	else $content .= "<p>".__("No information about free resources","wp-reservation")."</p>";
	return $content ;
}

function page2a() {
	global $wpdb;

	
	$content = "";
	$content .= <<<EOF
	<div id="error" title="
EOF
.__("Error","wp-reservation").
<<<EOF
">
</div>
	<script language="javascript">
	jQuery(function() {
		jQuery( "#error" ).dialog({
						bgiframe: true,
			height: 220,
			modal: true,
			autoOpen: false

		});

		jQuery( "#button" ).click(function() {
	
	 
		var err = "";
		var obj = document.forms['res_order'];
		
		
		if (obj.log.value=="") {
			err += " - Not specified (email address)";
		}
		

		if (err!="") {
			err = "
EOF
			.__("When completing the form, the following error(s) occurred:","wp-reservation").
<<<EOF
" + err;
document.getElementById("error").innerHTML=err;
jQuery( "#error" ).dialog( "open" );
			return false;
		} else {
			if (isValidEmail(obj.log.value, true)) {
				return true; } else { err = "
EOF
	.__("That isn`t a valid e-mail address.  E-mail addresses look like: username@example.com","wp-reservation").
<<<EOF
				" ;
				document.getElementById("error").innerHTML=err;
				jQuery( "#error" ).dialog( "open" );
				return false; } 
		
		
		};	
		});
	});
	function isValidEmail (email, strict)
	{
		if ( !strict ) email = email.replace(/^\s+|\s+$/g, '');
		return (/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i).test(email);
	}	
	</script> 
EOF;

	$dateend=date("Y-m-d",strtotime($datebegin)+($_REQUEST['dateend']-1)*24*60*60);
	$datebegin=date("Y-m-d",strtotime($_REQUEST['datebegin']));
	$dateend=date("Y-m-d",strtotime($_REQUEST['dateend']));

	
	$content .= '<br>'.sprintf(__("Your order from %s to %s ","wp-reservation"),$datebegin,$dateend).
	'<table class="res" width="400" >
	<thead>
	<tr >
	<th width=100>'.__("resource","wp-reservation").'</th>
	<th width=100>'.__("quantity","wp-reservation").'</th>
	<th width=100>'.__("price","wp-reservation").'</th>
	</tr>
	</thead>
';




	
	$reskol=$_REQUEST['reskol'];
	$price=$_REQUEST['price'];
	$kolday=$_REQUEST['kolday'];
	$resource_name=$_REQUEST['resource_name'];
	$rowflag=1; $sumprice=0;
	foreach ($_REQUEST['reskol'] as $key=>$value )
	{
		if ($value>0) {
			$content .= "<tr class=\"row$rowflag\" ><td>".$resource_name[$key]."</td><td>".$value."</td><td>".$price[$key]."</td></tr>";
			$rowflag = $rowflag*(-1); $sumprice+=$reskol[$key]*$price[$key]*$kolday;
		}
	}
	$content .= "<tr class=\"row$rowflag\" ><td colspan=2 >".__("Total","wp-reservation")." </td><td>$sumprice</td></tr>";
	$content .= "</table>";
	
	$hidden="";		
	$hiddenget="?nvar=0";		
	$sumprice=0;
	foreach ($_REQUEST['reskol'] as $key=>$value )
	{
		if ($value>0) {
			$sumprice+=$reskol[$key]*$price[$key]*$kolday;
			$hidden .= "<input type=\"hidden\" name=\"reskol[$key]\" value=\"$value\" >";
			$hidden .= "<input type=\"hidden\" name=\"price[$key]\" value=\"".$price[$key]."\" >";
			$hidden .= "<input type=\"hidden\" name=\"resource_name[$key]\" value=\"".$resource_name[$key]."\" >";
			
			$hiddenget .= "&reskol[$key]=".urlencode($value);
			$hiddenget .= "&price[$key]=".urlencode($price[$key]);
			$hiddenget .= "&resource_name[$key]=".urlencode($resource_name[$key]);
		}
		
	}
	
	$hidden .= "<input type=\"hidden\" name=pager value=\"2b\" >";
	$hidden .= "<input type=\"hidden\" name=\"datebegin\" value=\"".$_REQUEST['datebegin']."\" >";
	$hidden .= "<input type=\"hidden\" name=\"dateend\" value=\"".$_REQUEST['dateend']."\" >";
	$hidden .= "<input type=\"hidden\" name=\"kolday\" value=\"".$_REQUEST['kolday']."\" >";
	$hiddenget .= "&datebegin=".urlencode($_REQUEST['datebegin']);
	$hiddenget .= "&dateend=".urlencode($_REQUEST['dateend']);
	$hiddenget .= "&kolday=".urlencode($_REQUEST['kolday']);
	$hiddenget .= "&pager=2b";

	{ 
		$content .= '<p>'.__("Enter your email","wp-reservation").'</p> 
<form action="" method="post" name="res_order">

	<table width=400  ><tr><td width=200><label for="log">Email</label></td><td><input type="text" name="log" id="log" value="'. wp_specialchars(stripslashes($user_login), 1) .'" size="20" /></td></tr>
	

	<tr><td></td><td class="res-2a-td"><input class="res-next" id="button" type="submit" name="submit" value="'.__("next","wp-reservation").'" /></td></tr></table>

	<p>
	<input name="rememberme" id="rememberme" type="hidden"  value="forever" /> 
	<input type="hidden" name="redirect_to" value="'. $_SERVER['REQUEST_URI'] .''.($hiddenget).'" />
	</p> 
'.$hidden.'	 
</form>
<!--<a href="'. get_option('home'). '/wp-login.php?action=lostpassword">Recover password</a>-->';
	} 

	
	
	return $content;
}


function page2b() {
	global $wpdb;
	$content = "";

	$hidden="";		
	$hiddenget="?nvar=0";		
	$reskol=$_REQUEST['reskol'];
	$price=$_REQUEST['price'];
	$resource_name=$_REQUEST['resource_name'];
	
	foreach ($_REQUEST['reskol'] as $key=>$value )
	{
		
		$sumprice+=$reskol[$key]*$price[$key]*$kolday;
		$hidden .= "<input type=\"hidden\" name=\"reskol[$key]\" value=\"$value\" >";
		$hidden .= "<input type=\"hidden\" name=\"price[$key]\" value=\"".$price[$key]."\" >";
		$hidden .= "<input type=\"hidden\" name=\"resource_name[$key]\" value=\"".$resource_name[$key]."\" >";
		
		$hiddenget .= "&reskol[$key]=".urlencode($value);
		$hiddenget .= "&price[$key]=".urlencode($price[$key]);
		$hiddenget .= "&resource_name[$key]=".urlencode($resource_name[$key]);
		
		
	}
	$hidden .= "<input type=\"hidden\" name=pager value=\"3\" >";
	$hidden .= "<input type=\"hidden\" name=\"datebegin\" value=\"".$_REQUEST['datebegin']."\" >";
	$hidden .= "<input type=\"hidden\" name=\"dateend\" value=\"".$_REQUEST['dateend']."\" >";
	$hidden .= "<input type=\"hidden\" name=\"kolday\" value=\"".$_REQUEST['kolday']."\" >";
	$hiddenget .= "&datebegin=".urlencode($_REQUEST['datebegin']);
	$hiddenget .= "&dateend=".urlencode($_REQUEST['dateend']);
	$hiddenget .= "&kolday=".urlencode($_REQUEST['kolday']);
	$hiddenget .= "&pager=3";
	$hiddenget .= "&alreadyreg=1";

	
	
	$dateend=date("Y-m-d",strtotime($datebegin)+($_REQUEST['dateend']-1)*24*60*60);
	$datebegin=date("d.m.Y",strtotime($_REQUEST['datebegin']));
	$dateend=date("d.m.Y",strtotime($_REQUEST['dateend']));

	$content .= '<br>'.sprintf(__("Your order from %s to %s ","wp-reservation"),$datebegin,$dateend).'	
	<table class="res" width="400" >
	<thead>
	<tr >
	<th width=100>'.__("resource","wp-reservation").'</th>
	<th width=100>'.__("quantity","wp-reservation").'</th>
	<th width=100>'.__("price","wp-reservation").'</th>
	</tr>
	</thead>
';



	$rowflag=1; $sumprice=0;
	$kolday=$_REQUEST['kolday'];
	
	foreach ($_REQUEST['reskol'] as $key=>$value )
	{
		$content .= "<tr class=\"row$rowflag\" ><td>".$resource_name[$key]."</td><td>".$value."</td><td>".$price[$key]."</td></tr>";
		$rowflag = $rowflag*(-1); $sumprice+=$reskol[$key]*$price[$key]*$kolday;
	}
	$content .= "<tr class=\"row$rowflag\" ><td colspan=2 >".__("Total","wp-reservation")."</td><td>$sumprice</td></tr>";
	$content .= "</table>";
	
	
	require_once ( ABSPATH . WPINC . '/registration.php' );	
	
	$user_info = email_exists($_REQUEST['log']);
	//print_r($user_info->user_login);
	if ($user_info)
	
	{
		$user_info=get_userdata($user_info);
		
		$hiddenget .= "&alreadyreg=1";
		$content .= sprintf(__('<p>Mail us <b>% s </b> is already registered in the system, enter your password and click "Login"</p>',"wp-reservation"),$_REQUEST['log']); 
		$content .= '<form action="'. get_option("home").'/wp-login.php" method="post">

	<table width=400  >

	<tr><td><label for="pwd">'.__("Password","wp-reservation").'</label></td><td><input type="hidden" name="log" id="log" value="'. wp_specialchars(stripslashes($user_info->user_login), 1) .'" size="20" /><input type="password" name="pwd" id="pwd" size="20" /></td></tr>

	<tr><td></td><td><input type="submit" id="button" name="submit" value="'.__("Login","wp-reservation").'" class="res-next" /></td></tr></table>

	<p>
	<input name="rememberme" id="rememberme" type="hidden"  value="forever" /> 
	
	<input type="hidden" name="redirect_to" value="'. $_SERVER['REQUEST_URI'] .''.($hiddenget).'" />
	</p> 
'.$hidden.'	 
</form>
<a href="'. get_option('home'). '/wp-login.php?action=lostpassword">'.__("Lost Password","wp-reservation").'</a>';


		
	}
	
	
	else 
	{
		
if ($GLOBALS["version"]	== "pro") $check_fields_js=check_fields_js();

		$content .= <<<EOF
		<div id="error" title="
EOF
.__("Error","wp-reservation").
<<<EOF
">
</div>
<div id="term" title="
EOF
.__("Terms of booking","wp-reservation").
<<<EOF
">
EOF
.get_option("res_terms").
<<<EOF
</div>
		<script language="javascript">
jQuery(function() {
		jQuery( "#error" ).dialog({
						bgiframe: true,
			height: 220,
			modal: true,
			autoOpen: false

		});
		jQuery( "#term" ).dialog({
						bgiframe: true,
			height: 320,
			width: 450,
			modal: true,
			autoOpen: false

		});
        jQuery( "#aterm" ).click(function() {
		jQuery( "#term" ).dialog( "open" );})
		
		jQuery( "#button" ).click(function() {
	
			var err = "";
			var obj = document.forms['res_order'];
			if (obj.last_name.value=="") {
				err += "
EOF
.__(" - Not specified (Name)","wp-reservation").
<<<EOF
			<br>";
			}
EOF
.
$check_fields_js
.			
<<<EOF
			
			if (obj.rules.checked==false) {
				err += "
EOF
.__(" - You must agree to the terms","wp-reservation").
<<<EOF
		<br>	";
			}

			if (err!="") {
				err = "" + err;
			document.getElementById("error").innerHTML=err;
			jQuery( "#error" ).dialog( "open" );
			return false; } 
		
		
			
		});
	});

		</script> 
EOF;

		$content .= '<form name="res_order" id="res_order" action="" method="post"> ';
		$content .= $hidden." <br><p>".__("Enter your personal data","wp-reservation")."</p>";
		if ($GLOBALS["version"]	== "pro") $phone_row=phone_row();	
		$content .= "<table class=restabname width=400  >
<tr ><td  width=200>".__("First name","wp-reservation")."</td><td><input type=text name=first_name value=\"".$_REQUEST['first_name']."\"></td></tr>
<tr ><td>".__("Last name","wp-reservation")."</td><td><input type=text name=last_name value=\"".$_REQUEST['last_name']."\"></td></tr>
<tr ><td>".__("Middle name","wp-reservation")."</td><td><input type=text name=middle_name value=\"".$_REQUEST['middle_name']."\"></td></tr>"
.$phone_row."
<tr ><td>".__("Comments","wp-reservation")."</td><td><textarea  name=comments rows=5 cols=10 >".$_REQUEST['comments']."</textarea></td></tr>
<tr ><td><a id=\"aterm\">".__("I agree with the terms of booking","wp-reservation")."</a></td><td><input type=checkbox name=rules></td></tr>
<tr ><td> </td><td><input type=\"submit\" class=\"res-next\" id=\"button\" value=\"".__("next","wp-reservation")."\"  ></td></tr>
</table>
<input type=hidden name=email value=\"".$_REQUEST['log']."\">
</form>

";
	}




	
	return $content;
}


function page3() {
	global $wpdb, $current_user, $_SESSION;
	
	$content = "";
	$content .= "<h3>".__("Order created","wp-reservation")."</h3>";
	if ($_REQUEST['pager']!="nopay") {
	



		if ($_REQUEST['alreadyreg']!=1 ) {
			$passgen=res_pass();
			require_once ( ABSPATH . WPINC . '/registration.php' );	
			

			$userid=wp_insert_user (array(
			'user_login'	=> $_REQUEST['email'],
			'user_nicename'	=> $_REQUEST['email'],
			'user_email'	=> $_REQUEST['email'],
			'user_url'		=> 'http://',
			'display_name'	=> $_REQUEST['email'],
			'user_pass' => $passgen,
			'first_name' =>  $_REQUEST['first_name']." ".$_REQUEST['middle_name'],
			'last_name' =>  $_REQUEST['last_name']
			));
		if (is_wp_error($userid)) {	$userid = $_SESSION['userid']; $refflag=1; }	else {$_SESSION['userid'] = $userid ; }

		if ($GLOBALS["version"]	== "pro") update_user_fields($userid);
			
			
			$content .= sprintf(__("<p> For your order to create an account. </p> <p> Your login <b>%s</b> <br> your password <b>%s</b> </p>","wp-reservation"),$_REQUEST['email'],$passgen);
			
			$content.="<p><b>".__("Thank you for your order","wp-reservation")."</b></p>";
									
		} 
		else 
		{
			$userid=$current_user->ID;
		}
		

		//$userid = $_REQUEST['userid'];
		wp_set_current_user( $userid );

		$reskol=$_REQUEST['reskol'];
		$price=$_REQUEST['price'];
		$kolday=$_REQUEST['kolday'];
		$resource_name=$_REQUEST['resource_name'];
	
$order_details = '<table  width="400" >
	<thead>
	<tr >
	<th width=100 align="left">'.__("resource","wp-reservation").'</th>
	<th width=100 align="left">'.__("quantity","wp-reservation").'</th>
	<th width=100 align="left">'.__("price","wp-reservation").'</th>
	</tr>
	</thead>
';		
$rowflag=1; 
		foreach ($_REQUEST['reskol'] as $key=>$value )
		{
			$sumprice+=$reskol[$key]*$price[$key]*$kolday;
	
	
	
	
	//$sumprice=0;
	
		if ($value>0) {
			$order_details .= "<tr  ><td>".$resource_name[$key]."</td><td>".$value."</td><td>".$price[$key]."</td></tr>";
			//$rowflag = $rowflag*(-1); 
			//$sumprice+=$reskol[$key]*$price[$key]*$kolday;
		}
	
	//$order_details .= "<tr class=\"row$rowflag\" ><td colspan=2 >".__("Total","wp-reservation")." </td><td>$sumprice</td></tr>";
	
			

		}
	$order_details .= "</table>";
//echo $order_details;
		
if (isset($_REQUEST['comments'])) $comments=$_REQUEST['comments'];
			
      if (!$refflag) {	
		$qry ="insert into ".$wpdb->prefix . "res_orders"." (userid, price, datebegin, dateend, comments) values ('".$userid."','$sumprice','".$_REQUEST['datebegin']."','".$_REQUEST['dateend']."','".$comments."')";
		

		$wpdb->query($wpdb->prepare($qry,"") );	
		$orderid= mysql_insert_id();

		foreach ($_REQUEST['reskol'] as $key=>$value )
		{
			if ($reskol[$key]>0 ) {
				$qry ="insert into ".$wpdb->prefix . "res_orders_content"." (orderid, resourceid, kol, price) values ('$orderid','$key','$reskol[$key]','$price[$key]')";
				$wpdb->query($wpdb->prepare($qry,"") );	
				
				}
		}
		
      }
		
	}


$_REQUEST['orderid']=$orderid;
$_REQUEST['summa']=$sumprice;
if (isset($orderid)) $_REQUEST['orderid']=$orderid;
if (isset($summa)) $_REQUEST['summa']=$sumprice;

if ($GLOBALS["version"]	== "pro" ) { $content.="<p>".sprintf(__('You can finish ordering and return to the <a href="%s">home page</a> or pay for your order',"wp-reservation"),get_option("home"))."</p>";
$content.=payment($_REQUEST['orderid'],$_REQUEST['summa']);
}

	if ($GLOBALS["version"]	== "pro") {
				add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
				
				$adm_subject = get_option("res_mail_admin_subject");
				$adm_message = get_option("res_mail_admin_message");
				$client_subject = get_option("res_mail_client_subject");
				$client_message = get_option("res_mail_client_message");
				
				$adm_subject=str_replace('[num]',$orderid,$adm_subject);
				$adm_message=str_replace('[num]',$orderid,$adm_message);
				$client_subject=str_replace('[num]',$orderid,$client_subject);
				$client_message=str_replace('[num]',$orderid,$client_message);
				
				$adm_subject=str_replace('[sum]',$sumprice,$adm_subject);
				$adm_message=str_replace('[sum]',$sumprice,$adm_message);
				$client_subject=str_replace('[sum]',$sumprice,$client_subject);
				$client_message=str_replace('[sum]',$sumprice,$client_message);
				
				$adm_subject=str_replace('[datebegin]',$_REQUEST['datebegin'],$adm_subject);
				$adm_message=str_replace('[datebegin]',$_REQUEST['datebegin'],$adm_message);
				$client_subject=str_replace('[datebegin]',$_REQUEST['datebegin'],$client_subject);
				$client_message=str_replace('[datebegin]',$_REQUEST['datebegin'],$client_message);
				
				$adm_subject=str_replace('[dateend]',$_REQUEST['dateend'],$adm_subject);
				$adm_message=str_replace('[dateend]',$_REQUEST['dateend'],$adm_message);
				$client_subject=str_replace('[dateend]',$_REQUEST['dateend'],$client_subject);
				$client_message=str_replace('[dateend]',$_REQUEST['dateend'],$client_message);
				
				$adm_subject=str_replace('[login]',$_REQUEST['email'],$adm_subject);
				$adm_message=str_replace('[login]',$_REQUEST['email'],$adm_message);
				$client_subject=str_replace('[login]',$_REQUEST['email'],$client_subject);
				$client_message=str_replace('[login]',$_REQUEST['email'],$client_message);
				
				$adm_subject=str_replace('[password]',$passgen,$adm_subject);
				$adm_message=str_replace('[password]',$passgen,$adm_message);
				$client_subject=str_replace('[password]',$passgen,$client_subject);
				$client_message=str_replace('[password]',$passgen,$client_message);
				
				$adm_subject=str_replace('[first_name]',$_REQUEST['first_name'],$adm_subject);
				$adm_message=str_replace('[first_name]',$_REQUEST['first_name'],$adm_message);
				$client_subject=str_replace('[first_name]',$_REQUEST['first_name'],$client_subject);
				$client_message=str_replace('[first_name]',$_REQUEST['first_name'],$client_message);
				
				$adm_subject=str_replace('[last_name]',$_REQUEST['last_name'],$adm_subject);
				$adm_message=str_replace('[last_name]',$_REQUEST['last_name'],$adm_message);
				$client_subject=str_replace('[last_name]',$_REQUEST['last_name'],$client_subject);
				$client_message=str_replace('[last_name]',$_REQUEST['last_name'],$client_message);
				
				$advance=$sumprice/100*get_option("res_advance_payment_in_percents");
				$adm_subject=str_replace('[advance]',$advance,$adm_subject);
				$adm_message=str_replace('[advance]',$advance,$adm_message);
				$client_subject=str_replace('[advance]',$advance,$client_subject);
				$client_message=str_replace('[advance]',$advance,$client_message);
				
				$adm_subject=str_replace('[order_details]',$order_details,$adm_subject);
				$adm_message=str_replace('[order_details]',$order_details,$adm_message);
				$client_subject=str_replace('[order_details]',$order_details,$client_subject);
				$client_message=str_replace('[order_details]',$order_details,$client_message);
				
				
				
				
					
												
				if (!$refflag) {pro_confirm_mail($userid, $adm_subject , $adm_message,  $client_subject , $client_message );
				

				}
	}	else 
	{
	if (!$refflag) {
					wp_mail(  get_userdata( $userid )->user_email , __("Registration on ","wp-reservation").  get_bloginfo('name') , sprintf(__("Thank you for your order! For your order to create an account. Your Login %s Your password %s","wp-reservation"),$_REQUEST['email'],$passgen));
					}
	}

	return $content;	
}

function payment($orderid,$sumprice)
{
$total_amount=$sumprice;
$sumprice=$sumprice/100*get_option("res_advance_payment_in_percents");
	
if (get_option("res_advance_payment_in_percents")!="100") $content .= sprintf(__("The reservation will be confirmed after you makes the advance payment of %s percent(%%) of the total order by. Total amount %s . Advance payment %s .","wp-reservation"),get_option("res_advance_payment_in_percents"),$total_amount,$sumprice);	
	
	if ( get_option("res_pay_paypal_enabled") && !get_option("res_pay_pos_enabled") && !get_option("res_pay_robokassa_enabled") && !get_option("res_pay_sber_enabled") && !get_option("res_pay_webmoney_enabled") && !get_option("res_pay_mobcash_enabled") ) $content .= paypal (); 
	else
	if ( !get_option("res_pay_paypal_enabled") && get_option("res_pay_pos_enabled") && !get_option("res_pay_robokassa_enabled") && !get_option("res_pay_sber_enabled") && !get_option("res_pay_webmoney_enabled") && !get_option("res_pay_mobcash_enabled") ) $content .= wp_redirect(payonlinesystem()); 
	else
	if ( !get_option("res_pay_paypal_enabled") && !get_option("res_pay_pos_enabled") && get_option("res_pay_robokassa_enabled") && !get_option("res_pay_sber_enabled") && !get_option("res_pay_webmoney_enabled") && !get_option("res_pay_mobcash_enabled") ) $content .= robokassa (); 
	else
	if ( !get_option("res_pay_paypal_enabled") && !get_option("res_pay_pos_enabled") && !get_option("res_pay_robokassa_enabled") && get_option("res_pay_sber_enabled") && !get_option("res_pay_webmoney_enabled") && !get_option("res_pay_mobcash_enabled") ) $content .= sber (); 
	else
	if ( !get_option("res_pay_paypal_enabled") && !get_option("res_pay_pos_enabled") && !get_option("res_pay_robokassa_enabled") && !get_option("res_pay_sber_enabled") && get_option("res_pay_webmoney_enabled") && !get_option("res_pay_mobcash_enabled") ) $content .= webmoney (); 
	else
	if ( !get_option("res_pay_paypal_enabled") && !get_option("res_pay_pos_enabled") && !get_option("res_pay_robokassa_enabled") && !get_option("res_pay_sber_enabled") && !get_option("res_pay_webmoney_enabled") && get_option("res_pay_mobcash_enabled") ) $content .= mobcash (); 
	
	else 
	
	{$content .= <<<EOF
			
	<form name="res_order" id="res_order" method="post" action="">
	<input type="hidden" name=summa value="$sumprice" >
	<input type="hidden" name=orderid value="$orderid" >
	<input type="hidden" name="pager" value="nopay" />
EOF;

	if ($GLOBALS["version"]	== "pro" ) $content .= pro_paysys_list();
	
	$content .= '</form>
	
	';
		$content .= <<<EOF
<div id="error" title="
EOF
.__("Error","wp-reservation").
<<<EOF
">
</div>
	<script language="javascript">
// increase the default animation speed to exaggerate the effect
	
	jQuery(function() {
		jQuery( "#error" ).dialog({
						bgiframe: true,
			height: 120,
			modal: true,
			autoOpen: false

		});

		jQuery( "#button" ).click(function() {
		
		var err = "";
		var obj = document.forms['res_order'];

		
		//alert(obj.pager.length);
		for (i=0;i<obj.pager.length;i++){
			//alert(obj.pager[i].value);
			if (obj.pager[i].checked==true) {
				err += "1";
			}
		}

		if (err!="") {
			return true;
		} else {
			err = "
EOF
.__("You have not chosen a payment system","wp-reservation").
<<<EOF
			" ;
			document.getElementById("error").innerHTML=err;
			jQuery( "#error" ).dialog( "open" );
			return false; } 
		
		
			
		});
	});
	
	
	</script> 
EOF;
	
	}
	return $content;
	}

function pageI23($interf) {

	$today = date("Ymd");  $today2 = date("Y-m-d"); 
	list($results,$map,$treet,$level)=treesort();
	$content = "";
	$treeselect=treeselect($results,$level,$interf);
	$content .= <<<EOF
	<p>
EOF
	.__('To see prices and availability, enter your arrival date and how many days you will use our resources.', 'wp-reservation').
	<<<EOF
	</p>
<form name="res_order"  action="" method=post ><input type="hidden" name="pager" value="1" >
<input type="hidden" id="datebegin2" name="datebegin" value="" /> 
<input type="hidden" id="dateend" name="dateend" class="koldays" >	
<input type="hidden" id="flagkd" name="flagkd"   value="1" >	
<table>
	<tr>
		<td class="res-I2-td1">
			<div id="cont1"></div>
			<div id="info" style="text-align: center; font-size: 11px">
EOF
.__("Click to select arrival date","wp-reservation").
<<<EOF
</div>
		</td>
		<td class="res-I2-td2"><b>	
EOF
	.__("Location","wp-reservation"). 
<<<EOF
</b>
<br>
EOF
.
$treeselect
.
<<<EOF
	<br><br><input class="res-next" id="button" type="submit" value="
EOF
.__("next","wp-reservation").
<<<EOF
">
		</td>
	</tr>
</table>
<div id="error" title="
EOF
.__("Error","wp-reservation").
<<<EOF
">
</div>

	<script type="text/javascript">//<![CDATA[

	var SELECTED_RANGE = null;
	function getSelectionHandler() {
		var startDate = null;
		var ignoreEvent = false;
		return function(cal) {
			var selectionObject = cal.selection;

			// avoid recursion, since selectRange triggers onSelect
			if (ignoreEvent)
			return;

			var selectedDate = selectionObject.get();
			if (startDate == null) {
				startDate = selectedDate;
				SELECTED_RANGE = null;
				document.getElementById("info").innerHTML = "
EOF
.__("Click to select departure date","wp-reservation").
<<<EOF
				";
				date1 = Calendar.intToDate(startDate);
				document.getElementById("datebegin2").value = Calendar.printDate(date1,"%Y-%m-%d");
				document.getElementById("dateend").value = "";
				// comment out the following two lines and the ones marked (*) in the else branch
				// if you wish to allow selection of an older date (will still select range)
				cal.args.min = Calendar.intToDate(selectedDate);
				cal.refresh();
			} else {
				ignoreEvent = true;
				selectionObject.selectRange(startDate, selectedDate);
				ignoreEvent = false;
				SELECTED_RANGE = selectionObject.sel[0];
				date2 = Calendar.intToDate(selectedDate);
				document.getElementById("info").innerHTML = "
EOF
.__("Click again to select new arrival date","wp-reservation").
<<<EOF
";
				document.getElementById("dateend").value = Calendar.printDate(date2,"%Y-%m-%d");
				startDate = null;

				// (*)
				cal.args.min = null;
				cal.refresh();
				
			}
		};
	};

	Calendar.setup({
		cont          : "cont1",
		fdow          : 1,
		selectionType : Calendar.SEL_SINGLE,
		onSelect      : getSelectionHandler(),
		min			: $today
	});

	
	jQuery(function() {
		jQuery( "#error" ).dialog({
bgiframe: true,
height: 150,
modal: true,
autoOpen: false

		});

		jQuery( "#button" ).click(function() {	  
			var err = "";
			var obj = document.forms['res_order'];
			if (obj.datebegin2.value=="") {
				err += "
EOF
.__(" - Not specified Arrival date","wp-reservation").
<<<EOF
		<br>";
			}

if (obj.dateend.value=="") {
				err += "
EOF
.__(" - Not specified Departure date","wp-reservation").
<<<EOF
			";
			}			
			
			if (err!="") {
			document.getElementById("error").innerHTML=err;
			jQuery( "#error" ).dialog( "open" );
				return false;} 
			
		});
	});
	//]]></script>	
	
	
</form>	
	
	
EOF;
	

	return $content ;
};	
function pageI4($interf) {

	$today = date("Ymd");  $today2 = date("Y-m-d"); 
	list($results,$map,$treet,$level)=treesort();
	$content = "";
	//print_r($level);
	$treeselect=treeselect($results,$level,$interf);
	
	$content .= <<<EOF
	<p>
EOF
	.__('To see prices and availability, enter your arrival date, departure date and select resort and room.', 'wp-reservation').
	<<<EOF
	</p>
<form name="res_order"  action="" method=post ><input type="hidden" name="pager" value="1" >
<input type="hidden" id="datebegin2" name="datebegin" value="$today2" /> 
<input type="hidden" id="dateend" name="dateend"   value="$today2" >	
<input type="hidden" id="flagkd" name="flagkd"   value="1" >	

<table>
	<tr>
		<td class="res-I4-td1">
			<div id="cont1"></div>
			<div id="info" style="text-align: center; font-size: 11px">
EOF
	.__('Click to select arrival date', 'wp-reservation').
	<<<EOF
	</div>
			<td "res-I4-td2">
			<div id="cont2"></div>
			<div id="info" style="text-align: center; font-size: 11px">
EOF
	.__('Click to select departure date', 'wp-reservation').
	<<<EOF
	</div>
			</td>
			
		</td>
		<td class="res-I4-td3"><b>	
EOF
	.__("Resort","wp-reservation"). 
<<<EOF
</b>
<br>
EOF
.
$treeselect
.
<<<EOF
	<div id="select2"></div>
	<br><br><input class="res-next" id="button" type="submit" value="
EOF
.__("next","wp-reservation").
<<<EOF
">
		</td>
	</tr>
</table>
<div id="error" title="
EOF
.__("Error","wp-reservation").
<<<EOF
">
</div>

<script type="text/javascript">
jQuery('#sel1').change(function() {
jQuery("#select2").load("
EOF
.RES_CUR_PAGE."/?pager=selectajax".
<<<EOF
");

});

</script>

	<script type="text/javascript">//<![CDATA[

	var SELECTED_RANGE = null;
	function getSelectionHandler( cont) {
		var startDate = null;
		var ignoreEvent = false;
		return function(cal) {
			var selectionObject = cal.selection;

			// avoid recursion, since selectRange triggers onSelect
			if (ignoreEvent)
			return;
			
			var selectedDate = selectionObject.get();
			startDate = selectedDate;
			date1 = Calendar.intToDate(startDate);
			
			if (cont==1) {
			
			
			document.getElementById("datebegin2").value = Calendar.printDate(date1,"%Y-%m-%d");
			//document.getElementById("datebegin3").value = Calendar.printDate(date1,"%Y%m%d");
			date2 = Calendar.dateToInt(document.getElementById("datebegin4").value);
			//if (document.getElementById("datebegin4").value=="") document.getElementById("kolday").value = 1; else //document.getElementById("kolday").value = date2-startDate+1;
			
			} else
			{
			
			date2 = Calendar.dateToInt(document.getElementById("dateend").value);
			//document.getElementById("kolday").value = startDate-date2+1;
			document.getElementById("dateend").value = Calendar.printDate(date1,"%Y-%m-%d");
			//document.getElementById("kolday").value = date2;
			
			}
			

		};
	};

	Calendar.setup({
		cont          : "cont1",
		fdow          : 1,
		selectionType : Calendar.SEL_SINGLE,
		onSelect      : getSelectionHandler(1),
		min			: $today
	});
	Calendar.setup({
		cont          : "cont2",
		fdow          : 1,
		selectionType : Calendar.SEL_SINGLE,
		onSelect      : getSelectionHandler(2),
		min			: $today
	});

	
	jQuery(function() {
		jQuery( "#error" ).dialog({
bgiframe: true,
height: 150,
modal: true,
autoOpen: false

		});

		jQuery( "#button" ).click(function() {	  
		
			var err = "";
			var obj = document.forms['res_order'];
			//alert("111"+jQuery( "#datebegin2" ).val()+"111");
			if (jQuery( "#datebegin2" ).val()=="") {
				err += "
EOF
.__(" - Not specified Arrival date","wp-reservation").
<<<EOF
		<br>";
		
			}

if (obj.dateend.value=="") {
				err += "
EOF
.__(" - Not specified Departure date","wp-reservation").
<<<EOF
			";
			}			
			
			if (err!="") {
			jQuery("#error").html(err);
			jQuery( "#error" ).dialog( "open" );
				return false;} 
			
		});
	});
	//]]></script>	
	
	
</form>	
	
	
EOF;
	

	return $content ;
};	
	
  /////////////////////////////////////////////////////////////////////////////
 //					Admin page section										//
/////////////////////////////////////////////////////////////////////////////


function menu_admin () {
	global $wpdb;
	$qry="select count(resourceid)   from  ".$wpdb->prefix . "res_resources";
	$resources=$wpdb->get_var($wpdb->prepare($qry,"") );
	
	$qry="select count(orderid)   from  ".$wpdb->prefix . "res_orders";
	$ordersall=$wpdb->get_var($wpdb->prepare($qry,"") );

	$qry="select count(orderid)   from  ".$wpdb->prefix . "res_orders where payed>0";
	$orderspayed=$wpdb->get_var($wpdb->prepare($qry,"") );
	
	$qry="select count(id)   from  ".$wpdb->prefix . "res_offers";
	$sp_offers=$wpdb->get_var($wpdb->prepare($qry,"") );


	$pageadr=$_REQUEST['page'];
	if ($_REQUEST["page"]) $divid=$_REQUEST["page"]; else $divid="orders";
	if ($_REQUEST["page"]=="suborder" || $_REQUEST["page"]=="mail") $divid="orders";
	if ($_REQUEST["page"]=="makeorder1" || $_REQUEST["page"]=="makeorder2")  $divid="makeorder";
	$content = '
	<div id="'.$divid.'">
	<br>
	<ul id="tabnav">
	<li class="tab1"><a href="?page=ordertable">'.__("Orders table","wp-reservation").'</a></li>
	<li class="tab2"><a href="?page=orders">'.sprintf(__("Orders (total: %s | paid: %s)","wp-reservation"),$ordersall,$orderspayed).'</a></li>
	<li class="tab3"><a href="?page=makeorder">'.__("Reserve","wp-reservation").'</a></li>
	<li class="tab4"><a href="?page=resources">'.__("Resources","wp-reservation").' ('.$resources.')</a></li>
	<li class="tab5"><a href="?page=offers">'.__("Special offers","wp-reservation").' ('.$sp_offers.')</a></li>
	<li class="tab6"><a href="?page=settings">'.__("Settings","wp-reservation").'</a></li> ';
	if ($GLOBALS["version"]	!= "pro") $content .='<li class="tab6"><a href="?page=pro">Buy the <img src="'.RES_PLUGIN_URL.'/img/pro.gif"> '.__("version","wp-reservation").' for 5 USD</a></li>';
	$content .='</ul>
	</div>
';


	
	return 	$content;
}



function adm_settings()
{
	global $wpdb;

	$content="";

	
	if ($_REQUEST['change_settings']) {

		update_option("res_days",$_REQUEST["res_days"]) ;
		update_option("res_color",$_REQUEST["res_color"]) ;
		update_option("res_calendar_color",$_REQUEST["res_calendar_color"]) ;
		update_option("res_security_plugin",$_REQUEST["res_security_plugin"]) ;
		update_option("res_security_settings",$_REQUEST["res_security_settings"]) ;
		update_option("res_full_uninstall",$_REQUEST["res_full_uninstall"]) ;
		update_option("res_terms",$_REQUEST["res_terms"]) ;
		update_option("res_calc",$_REQUEST["res_calc"]) ;
		
		if ($GLOBALS["version"] == "pro")	{ pro_update_settings(); }
		
		echo "<div class=alert>".__("Settings saved","wp-reservation")."</div>"		 ;
		
	}

	$role_list= array ("Administrator"=>10,"Editor"=>7,"Author"=>2,"Contributor"=>1,"Subscriber"=>0);

	$content .= '
	<h2>'.__("Settings","wp-reservation").'</h2>
	<form id="form1" name="form1" method="post" action="">	';
	
	$content .= '
	<br>
	<h3>'.__("Design settings","wp-reservation").'</h3>
	<form id="form1" name="form1" method="post" action="">
	<table class="widefat page fixed" style="width:650px">
	<thead>
	<tr >
	<th >'.__("Parameter","wp-reservation").'</th>
	<th>'.__("Value","wp-reservation").'</th>
	</tr>
	</thead>
	<tr>
	<td>'.__("How many days to show for choosing","wp-reservation").'</td>
	<td><select name="res_days" class="koldays" id="settings">';

	$maxdays=31;
	for($i=1;$i<=$maxdays;$i++)
	{
	if ($i==get_option("res_days")) $adder="selected" ; else $adder="";
	$content .= "<option $adder value=\"$i\" >$i</option>";}	
	$content .= '
	</select></td>	
	</tr>
	<tr>
	<td>'.__("Plugin colors (CSS)","wp-reservation").'</td>
	<td><select name="res_color" id="settings">
	
	';
	$adder="";
	$pathcss=RES_PLUGIN_PATH.'/css/res/';
	if ($handle = opendir($pathcss)) {
		while (false !== ($file = readdir($handle))) { 
			if (is_dir($pathcss.$file) && $file !== '.' && $file !== '..') {  
			if ($file==get_option("res_color")) $adder="selected" ; else $adder="";
	$content .= '<option '.$adder.'>'.$file;
			}
		}
	closedir($handle);
	}

	//<td><input type="text" name="res_color"  size=49  value="'.get_option("res_color") .'" /></td>
	$content .= '</select></td>	
	</tr>
	<tr>
	<td>'.__("Calendar colors (CSS)","wp-reservation").'</td>
	<td><select name="res_calendar_color" id="settings">
	';
	$pathcss=RES_PLUGIN_PATH.'/css/calendar/';
	if ($handle = opendir($pathcss)) {
		while (false !== ($file = readdir($handle))) { 
			if (is_dir($pathcss.$file) && $file !== '.' && $file !== '..' && $file !== 'img') {  
			if ($file==get_option("res_calendar_color")) $adder="selected" ; else $adder="";
	$content .= '<option '.$adder.'>'.$file;
			}
		}
	closedir($handle);
	}
	//<td><input type="text" name="res_calendar_color"  size=49  value="'.get_option("res_calendar_color") .'" /></td>
$content .= '	
	</select></td>	
	</tr></table>
	<br>
	<h3>'.__("Security settings","wp-reservation").'</h3>
	<table class="widefat page fixed" style="width:650px">
	<thead>
	<tr >
	<th >'.__("Parameter","wp-reservation").'</th>
	<th>'.__("Value","wp-reservation").'</th>
	</tr>
	</thead>
	<tr><td>'.__("User access level to plugin","wp-reservation").'</td><td><select  name="res_security_plugin" id="settings">';
	reset($role_list);
while (list ($key, $val)=each($role_list)){
if ($val==get_option("res_security_plugin")) $adder="selected" ; else $adder="";
	$content .= '<option '.$adder.' value="'.$val.'">'.translate_user_role($key);
}
	
	$content .= '</select></td></tr>
	<tr><td>'.__("User access level to settings","wp-reservation").'</td><td><select  name="res_security_settings" id="settings">';
	reset($role_list);
while (list ($key, $val)=each($role_list)){
if ($val==get_option("res_security_settings")) $adder="selected" ; else $adder="";
	$content .= '<option '.$adder.' value="'.$val.'">'.translate_user_role($key);
}
	$content .= '</select></td></tr></table>';
	
	$content .= '<h3>'.__("Deactivation settings","wp-reservation").'</h3>
	<table class="widefat page fixed" style="width:650px">
	<thead>
	<tr >
	<th >'.__("Parameter","wp-reservation").'</th>
	<th>'.__("Value","wp-reservation").'</th>
	</tr>
	</thead>
	<tr><td>'.__("Full uninstall (all data will be removed)","wp-reservation").'</td><td><input type="checkbox"  name="res_full_uninstall"'; 
	if(get_option("res_full_uninstall"))  $content.=' checked ';
	$content.='" onclick="if (this.checked == true) {return confirm(\''.__("Are you sure ? (all data will be removed)","wp-reservation").'\')}" /></td></tr></table>';
	
	
	
	$content .= '<h3>'.__("Other settings","wp-reservation").'</h3>
	<table class="widefat page fixed" style="width:650px">
	<thead>
	<tr >
	<th >'.__("Parameter","wp-reservation").'</th>
	<th>'.__("Value","wp-reservation").'</th>
	</tr>
	</thead>';
	/*
	//per_day & per_night calculate
	$content .='<tr><td>
	'.__("How to calculate employment resources","wp-reservation").'</td><td><select  name="res_calc" id="settings">';
	$calc_list= array (__("per day","wp-reservation")=>1,__("per night","wp-reservation")=>2);
	reset($calc_list);
while (list ($key, $val)=each($calc_list)){
if ($val==get_option("res_calc")) $adder="selected" ; else $adder="";
	$content .= '<option '.$adder.' value="'.$val.'">'.$key;
}
	$content .= '</select>
	</td></tr>';
	*/
	$content .='<tr><td>'.__("Terms of booking","wp-reservation").'</td><td><textarea   name="res_terms" rows="10" cols="40">'
	.get_option("res_terms") ."</textarea ></td></tr></table>";
	
	
	if ($GLOBALS["version"] == "pro")	$content .= pro_settings();
	
	$content .= '<div class="tablenav">

	<div class="alignleft actions">
	<input type="hidden" value="1" name="change_settings" >
	<input type="submit" value="'.__("Save","wp-reservation").'" name="doaction2" id="doaction2" class="button-primary" />
	</div>

	<br class="clear" />
	</div>
	</form>';
	echo $content;
}

function adm_pro()
{
$content='<h2>'.__("PRO version","wp-reservation").'</h2>';
$content.=__("<div class=\"respro\">
<ul><a href=\"http://integra.work/wpresbuy/\" target=\"_blank\">Buy the PRO version for 5 USD</a></ul>

In the pro version you can: 
<ul>Accept payments for booking with a 6 payment systems: </ul>

<li><a target=\"blank\" href=\"http://www.paypal.com/\">PayPal (Visa, MasterCard, American Express, Diners Club)</a></li>
<li><a target=\"blank\"  href=\"http://www.payonlinesystem.com/\">PayOnlineSystem (Visa, MasterCard, American Express, Diners Club)</a></li>
<li><a target=\"blank\" href=\"http://robokassa.com/\">Robokassa (Yandex.Money, MoneyMail, RBK Money, SMS, Visa, MasterCard ...)</a></li> 
<li><a target=\"blank\" href=\"http://webmoney.com/\">WebMoney</a></li> 
<li><a target=\"blank\" href=\"https://ishop.qiwi.ru/\">Mobcash, QIWI</a></li> 
<li><a target=\"blank\" href=\"http://sbrf.ru/en/\">Sberbank</a></li>

<ul>Your customers will see their own orders and, if necessary, may abandon them. To do this, insert a special tag on a separate page {resuserpage}.</ul> 

<ul>You can display a page not the entire list of resources, but only a single resource, or a separate group with a special tag {reservation+id1,id2,...}. Or you can display all resources except for some {reservation-id1,id2,...}. </ul>

<ul>You can send an email client directly from the control panel order. </ul>

<ul>In the order form and the user profile is added an additional field \"phone\", to communicate with the client</ul>

<ul><a target=\"blank\" href=\"http://integra.work/wpresbuy\">How to buy</a></ul>

</div>","wp-reservation");

echo $content;
}



function treesort ()
{
	global $wpdb;
	$qry ="select  resourceid, parent , name, price, capacity from ".$wpdb->prefix . "res_resources order by resourceid";
	$arr=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );

	for ($i=0;$i<count($arr)-1;$i++) {
		$flag=$i;
		for ($j=$i+1;$j<count($arr);$j++) {
			if ($arr[$j][1]==$arr[$i][0]) 
			{
				array_splice($arr,$flag+1,0,array(($arr[$j])));
				unset($arr[$j+1]);
				$arr = array_values($arr);			
				$flag=$flag+1;
			} 
			
		}
		
	}


	$map=array();		
	$level=array();		
	for ($k=0;$k<count($arr);$k++)	
	{	
		
		if ($arr[$k][1]==0) array_push($map,0);
		$z=1;
		foreach($arr as $key) {
			if($arr[$k][1]==$key[0]) {	array_push($map,$z);			}
			$z++;
		}
		
	}	
	
	
	
	$treet="";
	for ($k=0;$k<count($map);$k++)	
	{	
		array_push($level,treelevel($map,$k,0));
		if ($map[$k]==0 and array_search(($k+1),$map) ) { 
			$treet[] = $k+1;
		}
	}	
	
	if (count($treet)>1)	$treet = implode ("|", $treet); else $treet=$treet[0];
	
	return array($arr, $map, $treet, $level);
}

function treelevel ($map,$k,$level)

{
	if ($map[$k]==0)  { return $level;} else  $level=treelevel($map,$map[$k]-1,$level+1);
	return $level;
}


function treeselect ($results,$level,$interf)
{
	$content="<select name=\"resource\" id=\"sel1\">
	<option value=\"0\">All</option>
	";
	
	foreach($results as $key=>$value) {
		if($results[$key][4]!=0 && $interf!=3) continue;
		if($level[$key]!=0 && $interf==4) continue;
		if($level[$key]!=1 && $interf==42) continue;
		$adder="";
		for($i=1;$i<=$level[$key];$i++)	$adder.="-";		
		$content.="<option value=".$results[$key][0]."> $adder".$results[$key][2]."</option>";
	}
	$content.="</select>";
	return $content;
}

function charr ($plarr1, $plarr) {
global $wpdb;
$charr=array();
		
		// find all childs and save it to $charr
		$qry ="select  resourceid, parent , name, price, capacity from ".$wpdb->prefix . "res_resources order by resourceid";
		$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );
		list($results,$map)=treesort($results);	
		
		foreach ($plarr as $plarrv ) {
			$flag=0;
			foreach ($map as $key=>$value) {
				if ($results[$key][0]==$plarrv || $flag>0) { 
					if ($flag==0) {$fmap=$value; $flag +=1;}
					if ($fmap==$value && $flag>1 || $fmap>$value) break;  else $flag += 1;
					// check repated values in array, if value uniqal add it to array		
					if(!in_array($results[$key][0],$charr))			array_push($charr,$results[$key][0]);
				}
			}
		}
		$charr=( implode(",",$charr))	 ;
		$charr=array($plarr1,$charr);
		return $charr;
}

function adm_resources ()
{
	global $wpdb;
	
	$content="";
	
	
	list($results,$map)=treesort();
	//print_r($results);
	
	if ($_REQUEST['action']=="add") {
		if (empty($_REQUEST['price'])) $_REQUEST['price']=0;
		if (empty($_REQUEST['capacity'])) $_REQUEST['capacity']=1;
		$qry ="insert into ".$wpdb->prefix . "res_resources"." (parent, name,  price, capacity) values ('".$_REQUEST['idradd']."','".$_REQUEST['name']."','".$_REQUEST['price']."','".$_REQUEST['capacity']."')";
		$wpdb->query($wpdb->prepare($qry,"") );
		$qry = "update ".$wpdb->prefix . "res_resources set price=NULL, capacity=NULL where resourceid='".$_REQUEST['idradd']."'";
		$wpdb->query($wpdb->prepare($qry,"") );
		foreach($map as $key=>$value) {
			if ($results[$value-1][0]>0) {
				$qry = "update ".$wpdb->prefix . "res_resources set price=NULL, capacity=NULL where resourceid='".$results[$value-1][0]."'";
				
				$wpdb->query($wpdb->prepare($qry,"") );
			}
		}
		$content .= "<div class=alert>".__("Record added","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="-1" ) {
		$content .= "<div class=alert>".__("You have not selected any of the operations","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="edit" and count($_REQUEST['post'])>0) {

		foreach ($_REQUEST['post'] as $key=>$value) {
			$qry ="update ".$wpdb->prefix . "res_resources"." set name= '".$_REQUEST['name'][$key]."' ,  price = '".$_REQUEST['price'][$key]."' ,  capacity = '".$_REQUEST['capacity'][$key]."'  where resourceid='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
		}
		$content .=  "<div class=alert>".__("Selected records changed","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="delete") {
		$flag=0;
		foreach ($map as $key=>$value) {
			if ($results[$key][0]==$_REQUEST['idrdel'] || $flag>0) { 
				if ($flag==0) {$fmap=$value; $flag +=1;}
				if ($fmap==$value && $flag>1 || $fmap>$value) break; else $flag += 1;
				$qry ="delete from ".$wpdb->prefix . "res_resources"."  where resourceid='".$results[$key][0]."' ";
				$wpdb->query($wpdb->prepare($qry,"") );
			}
		}
		$content .= "<div class=alert>".__("Selected records deleted","wp-reservation")."</div>"		 ;
	}

		
	list($results,$map)=treesort();	
	
	$content .= '
	<h2>'.__("Resources","wp-reservation").'</h2>	';

	//wp_enqueue_script('jquery-form');
	
	
	require_once(RES_PLUGIN_PATH."/js/jQTreeTable/jqtreetable.php");

	
	 
$options = '{openImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/fop.png", shutImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/fcl.png", leafImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/item.png", lastOpenImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/fop.png", lastShutImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/fcl.png", lastLeafImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/item.png", vertLineImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/blank.gif", blankImg: "'.RES_PLUGIN_URL.'/js/jQTreeTable/images/blank.gif", collapse: false, column: 1, striped: true, highlight: true, state:true}';
//$tbodyid = "treet1";



//$content.= $_REQUEST['expand'].$_REQUEST['asd_x'];


//echo $content;

   $head = array(__("ID","wp-reservation")."&nbsp;&nbsp;&nbsp;", __("Name","wp-reservation"), __("Price","wp-reservation"),  __("Capacity","wp-reservation") , __("Operations","wp-reservation"));
    

    for ($i=0; $i<count($head); $i++){
    	$headrow .= '<th >'.$head[$i].'</th>';
    }
    $headrow ='<tr>'.$headrow.'</tr>';
	
$topform = '<div class="res_acthead"><form action="" id="expand"  method="POST"><input type="hidden" name="page" value="resources"><div ><a href="#" id="additem'.$fld[0].'" ><img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/addm.png" title="Add resource in root of tree" alt="Add resource in root of tree" ></a><input type=image src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/expand.png" name="expand" title="Expand" alt="Expand" ><input type=image src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/collapse.png" title="Collapse" alt="Collapse" name="collapse"></div></form></div>';
$topform =	 '<tr><th colspan="'.($i-1).'"></th><th  style="text-align:left">'.$topform.'</th></tr>';

	$x=1; 	//$x number of row in resource table (now deprecated)
	foreach ($results as $fld) {

      
      $tabstr .= '<tr><td><form id="treeform'.$fld[0].'" action="" method="post"><input type="hidden" name="resourceid" value="'.$fld[0].'">'.($fld[0]).'</td><td>&nbsp; <input name="name" id="name'.$fld[0].'" value="'.$fld[2].'"></td><td><input name="price" class="res_price" id="price'.$fld[0].'" value="'.$fld[3].'"></td><td><input name="capacity" class="res_capacity" id="capacity'.$fld[0].'" value="'.$fld[4].'"></td><td><div><a href="#" id="delitem'.$fld[0].'" ><img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/del.png"></a></div><div id="soutput'.$fld[0].'"><input type="image" src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/save.png" class="submit_form"></div><div id="toutput'.$fld[0].'"><img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/tick.png" ></div><div id="loutput'.$fld[0].'"><img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/load.gif"></div><div><a href="#" id="additem'.$fld[0].'" ><img src="'.RES_PLUGIN_URL.'/js/jQTreeTable/images/add.png"></a></div></form></td></tr>'."\n";
$outarr.="$fld[0],";
	  $x++;
	  
    }
$tbodyid = "treet";	
$str=	('<table class="tablemain"><thead>'.$topform.$headrow."</thead>\n<tfoot>".$headrow."</tfoot>\n".'<tbody id="'.$tbodyid.'">'.$tabstr."</tbody></table>\n");
$outarr="[".substr($outarr,0,-1)."]";

 $content .= <<<EOF
	<script type="text/javascript">
	jQuery(function() {
	var  outarr=$outarr;
	
for(i=0;i<=
EOF
.($x-2).
<<<EOF
;i++) {

	jQuery("#soutput"+outarr[i]).hide();	
	jQuery("#loutput"+outarr[i]).hide();	
	}
var name = jQuery("#name"),idradd=jQuery("#idradd"),idrdel=jQuery("#idrdel")
allFields = jQuery([]).add(name).add(idradd).add(idrdel);	
		jQuery("#dialogadd").dialog({
			bgiframe: true,
			height: 240,
			modal: true,
			autoOpen: false,
			buttons: {
				'Cancel': function() {
				allFields.val('').removeClass('ui-state-error');
					jQuery(this).dialog('close');
				},
				'Save': function() {
				document.formadd.submit();
				allFields.val('').removeClass('ui-state-error');
				
					jQuery(this).dialog('close');
				}
			},
			open: function() {
				idradd.val(Id).removeClass('ui-state-error');
			}
			,
			close: function() {
				allFields.val('').removeClass('ui-state-error');
			}
		});
		jQuery("#dialogdel").dialog({
			bgiframe: true,
			height: 240,
			modal: true,
			autoOpen: false,
			buttons: {
				'No': function() {
				 allFields.val('').removeClass('ui-state-error');
					jQuery(this).dialog('close');
				},
				'Yes': function() {
				document.formdel.submit();
				allFields.val('').removeClass('ui-state-error');
				
					jQuery(this).dialog('close');
				}
			},
			open: function() {
				idrdel.val(Id).removeClass('ui-state-error');
			}
			
		});


jQuery('a').click(function() {
var element = jQuery(this);
Id = element.attr("id");

if (Id.replace(/additem.*/,'additem')=="additem") {
Id=Id.replace(/^[^\d]+/,'');
jQuery('#dialogadd').dialog('open');

}
if (Id.replace(/delitem.*/,'delitem')=="delitem") {
Id=Id.replace(/^[^\d]+/,'');
jQuery('#dialogdel').dialog('open');
}
			})	;		
	});
	
	</script>



<div id="dialogadd" title="
EOF
.__("Adding resource","wp-reservation").
<<<EOF
">
	<p>
EOF
.__("Please input a resource name","wp-reservation").
<<<EOF
</p>
	<form action="" method="post"  name="formadd"><input name="name" id="name" size="33" value=""><input type="hidden" name="idradd" id="idradd" value=""><input type="hidden" name="action"  value="add"></form>
	
</div>

<div id="dialogdel" title="
EOF
.__("Deleting resource","wp-reservation").
<<<EOF
">
	<p>
EOF
.__("Are you sure you want to delete this entry, will also remove all children resources","wp-reservation").
<<<EOF
</p>
	<form action="" method="post"  name="formdel"><input type="hidden" name="idrdel" id="idrdel" value=""><input type="hidden" name="action"  value="delete"></form>
	
</div>
EOF;

$map=implode(", ", $map);


$body = $str;
$jq = new jQTreeTable();

   
//$tbodyid="treet3";
//$map="0, 0, 2, 3, 4, 4, 6, 4, 2, 9, 10, 0";
$vars = $jq->init($map,$options,$tbodyid);
  $content .= '<script type="text/javascript">
{'.$vars[0].'}
    </script>
'.$body;

$content .= <<<EOF
<script type="text/javascript">
jQuery('input').keypress(function() {
var element = jQuery(this);
Id = element.attr("id");
Id=Id.replace(/^[^\d]+/,'');
jQuery("#toutput"+Id).hide();
jQuery("#soutput"+Id).show();
});

jQuery('form').submit(function() { 
  var element = jQuery(this);
  Idelement = element.attr("id");
  Id=Idelement.replace(/^[^\d]+/,'');
   var options = { 
    target: "#loutput"+Id,
    beforeSubmit: showRequest, 
    success: showResponse, 
    timeout: 3000 
  };
  
    jQuery(this).ajaxSubmit(options); 
	if (Idelement=="addresource" || Idelement=="expand" ) return true; else
	  return false;
  }); 


function showRequest(formData, jqForm, options) { 


jQuery("#loutput"+Id).show();
jQuery("#soutput"+Id).hide();	
    var queryString = jQuery.param(formData); 
    return true; 
} 

function showResponse(responseText, statusText)  { 
jQuery("#loutput"+Id).hide();	 
jQuery("#toutput"+Id).show();	

}

</script>
EOF;


	echo $content;
}

function adm_orders()
{
	global $wpdb;

	$content="";
	
	if ($_REQUEST['add_order']) {
		$qry ="insert into ".$wpdb->prefix . "res_resources"." (name, price) values ('".$_REQUEST['name']."','".$_REQUEST['price']."')";
		$wpdb->query($wpdb->prepare($qry,"") );
		$content .= "<div class=alert>".__("Record added","wp-reservation")."</div>"		 ;
	}


	if ($_REQUEST['action']=="-1" ) {
		$content .= "<div class=alert>".__("You have not selected any of the operations","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="edit" and count($_REQUEST['post'])>0) {
		foreach ($_REQUEST['post'] as $key=>$value) {
		
		  $kolday=(strtotime($_REQUEST['dateend'][$key])-strtotime($_REQUEST['datebegin'][$key]))/(24*60*60)+1;
		  
		  
		  $qry="select  kol,  b.price  from  ".$wpdb->prefix . "res_orders_content b
INNER JOIN ".$wpdb->prefix . "res_resources a ON (a.resourceid = b.resourceid) where orderid=".$value;

	$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );

		if (count($results)>0) {
		$i=1;$kolm=0;$summ=0;
			foreach ($results as $fld) {
			$summ+=$fld[0]*$fld[1];
			$i++;
			}
		  
		}
		
				
			$qry ="update ".$wpdb->prefix . "res_orders"." set price = '".$summ*$kolday."' ,  datebegin = '".$_REQUEST['datebegin'][$key]."' ,  dateend = '".$_REQUEST['dateend'][$key]."',  comments = '".$_REQUEST['comments'][$key]."',  payed = '".$_REQUEST['payed'][$key]."' where orderid='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
		}
		$content .=  "<div class=alert>".__("Selected records changed","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="delete" and count($_REQUEST['post'])>0) {
		foreach ($_REQUEST['post'] as $key=>$value) {
			$qry ="delete from ".$wpdb->prefix . "res_orders"."  where orderid='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
			$qry ="delete from ".$wpdb->prefix . "res_orders_content"."  where orderid='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );


		}
		$content .=  "<div class=alert>".__("Selected records deleted","wp-reservation")."</div>"		 ;
	}





	$content .= '
	<h2>'.__("Orders","wp-reservation").'</h2>

	<form id="form2" name="form2" method="post" action="">
	<table class="widefat page fixed" style="">
	<thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<th width=20>№</th>
	<th>'.__("Client","wp-reservation").'</th>
	<th>'.__("Cost","wp-reservation").'</th>
	<th>'.__("Start date","wp-reservation").'</th>
	<th>'.__("End date","wp-reservation").'</th>
	<th>'.__("Comment","wp-reservation").'</th>
	<th>'.__("Payment System","wp-reservation").'</th>
	<th width=30>'.__("Paid","wp-reservation").'</th>
	</tr>
	</thead>
';
if (isset($_REQUEST['ordert'])) $adder=" and orderid = \"".$_REQUEST['ordert']."\" ";


	$qry="select orderid, userid , price , datebegin, dateend, comments , paysys, name,  payed,
max((if(meta_key = 'first_name', meta_value, NULL ))) first_name,
max((if(meta_key = 'last_name', meta_value, NULL ))) last_name,
max((if(meta_key = 'nickname', meta_value, NULL ))) nickname from
".$wpdb->prefix . "usermeta inner join ".$wpdb->prefix . "res_orders on (userid = user_id)
left join ".$wpdb->prefix . "res_paysys on (paysys = paysysid)
where meta_key in ('first_name','last_name','nickname' ) $adder 
group by orderid, userid , price , datebegin, dateend, comments , paysys, payed,name
";


	$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_A);

	$pageadr=$_REQUEST['page'];
	if (count($results)>0) {
		$i=1;
		foreach ($results as $fld) {

			extract($fld);

			$payedadder="";
			if ($payed > 0) $payedadder="checked";

			$content .= <<<EOF
			<tr>    <th scope="row" class="check-column"><input type="checkbox" name="post[$i]" value="$orderid" /></th>
			<td><a href="?page=$pageadr&subpage=suborder&userid=$userid&last_name=$last_name&first_name=$first_name&price=$price&datebegin=$datebegin&dateend=$dateend&orderid=$orderid">$orderid</a></td>
			<td><input type="hidden" name="orderid[$i]" value="$orderid" />
			<input type="hidden" name="userid[$i]" value="$userid" />
			<a href="user-edit.php?user_id=$userid" title="$nickname">$first_name $last_name</a>
			<a href="?page=$pageadr&subpage=mail&userid=$userid&last_name=$last_name&first_name=$first_name&price=$price&datebegin=$datebegin&dateend=$dateend&orderid=$orderid" title="
EOF
.__("Send email to customer","wp-reservation").
<<<EOF
			" 
EOF;
if ($GLOBALS["version"]	!= "pro")  $content.="onclick=\"alert('".__("This function working only in PRO version","wp-reservation")."'); return false;\"";
$content .= <<<EOF
			 ><img src="images/comment-grey-bubble.png"></a> 


			</td>
			<td><input type="hidden" name="price[$i]" value="$price"  />$price</td>
			<td><input type="text" name="datebegin[$i]" value="$datebegin"  /></td>
			<td><input type="text" name="dateend[$i]" value="$dateend"  /></td>
			<td><input type="text" name="comments[$i]" value="$comments"  /></td>
			<td>$name</td>
			<td><input type="checkbox" $payedadder name="payed[$i]" value="1"  /></td>

			</tr>
EOF;
			$i++;
		}
	}


	$content .= '

	</table>

	<div class="tablenav">

	<div class="alignleft actions">
	<select name="action">
	<option value="-1" selected="selected">'.__("Bulk Actions","wp-reservation").'</option>
	<option value="edit">'.__("Change","wp-reservation").'</option>
	<option value="delete">'.__("Delete","wp-reservation").'</option>
	</select>
	<input type="submit" value="'.__("Apply","wp-reservation").'" name="doaction" id="doaction" class="button-primary" />
	</div>

	<br class="clear" />
	</div>	
	
	</form>
';
	echo $content;
}


function adm_suborder()
{
	global $wpdb;
		
	$content="";
	$kolday=(strtotime($_REQUEST['dateend'])-strtotime($_REQUEST['datebegin']))/(24*60*60)+1;
	
	
	if ($_REQUEST['add_sub_order']) {
		if ($_REQUEST['resourceid'] == -1 or $_REQUEST['kol']== "" or $_REQUEST['price']=="") { $content .= "<div class=alert>".__("To add a record, you must specify all the parameters. Record was not added.","wp-reservation")."</div>"; } else {
			$qry ="insert into ".$wpdb->prefix . "res_orders_content"." (orderid, resourceid, kol, price) values ('".$_REQUEST['orderid']."','".$_REQUEST['resourceid']."','".$_REQUEST['kol']."','".$_REQUEST['price']."')";
			$wpdb->query($wpdb->prepare($qry,"") );
			$qry ="update ".$wpdb->prefix . "res_orders"." set price = price + '".$_REQUEST['kol']*$_REQUEST['price']."' where orderid='".$_REQUEST['orderid']."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
			$content .= "<div class=alert>".__("Record added","wp-reservation")."</div>"		 ;}
	}

	if ($_REQUEST['action']=="-1" ) {
		$content .= "<div class=alert>".__("You have not selected any of the operations","wp-reservation")."</div>"		 ;
	}


	if ($_REQUEST['action']=="edit" and count($_REQUEST['post'])>0) {
		$summ=0;$summold=0;
		
		foreach ($_REQUEST['post'] as $key=>$value) {
			$qry ="update ".$wpdb->prefix . "res_orders_content"." set resourceid= '".$_REQUEST['resourceid'][$key]."' ,  price = '".$_REQUEST['price'][$key]."' ,  kol = '".$_REQUEST['kol'][$key]."' where id='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
			$summ+=$_REQUEST['kol'][$key]*$_REQUEST['price'][$key];
			//$summold+=$_REQUEST['oldkol'][$key]*$_REQUEST['oldprice'][$key];
		}
		$qry ="update ".$wpdb->prefix . "res_orders"." set price =   ".($summ*$kolday)." where orderid='".$_REQUEST['orderid']."' ";	
		
		$wpdb->query($wpdb->prepare($qry,"") );

		
		$content .=  "<div class=alert>".__("Selected records changed","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="delete" and count($_REQUEST['post'])>0) {
		$summold=0;
		foreach ($_REQUEST['post'] as $key=>$value) {
			$qry ="delete from ".$wpdb->prefix . "res_orders_content"."  where id='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
			$summold+=$_REQUEST['oldkol'][$key]*$_REQUEST['oldprice'][$key];
		}
		$qry ="update ".$wpdb->prefix . "res_orders"." set price = price - '".$summold."' where orderid='".$_REQUEST['orderid'][$key]."' ";
		$wpdb->query($wpdb->prepare($qry,"") );	
		$content .=  "<div class=alert>".__("Selected records deleted","wp-reservation")."</div>"		 ;
	}


	$qryso= "select resourceid, name  from  ".$wpdb->prefix . "res_resources ";  	
	$resso=$wpdb->get_results($wpdb->prepare($qryso,""), ARRAY_N );

	$select=createselectoption ($fld[2], $resso, "resourceid",__("Select","wp-reservation"))	;

	$content .= '
	<h2>'.__("Composition of order","wp-reservation").' #'.$_REQUEST['orderid'].'</h2>
	<h3>'.__("Adding record","wp-reservation").'</h3>
	<form id="form1" name="form1" method="post" action="">
	<table class="widefat page fixed" style="width:650px">
	<thead>
	<tr >
	<th>'.__("Resources","wp-reservation").'</th>
	<th >'.__("Reserved places","wp-reservation").'</th>
	<th>'.__("Price per place","wp-reservation").'</th>
	</tr>
	</thead>
	<tr>
	<td>'.$select.'<input type="hidden" name="orderid" value='.$_REQUEST['orderid'].' /></td>
	<td><input type="text" name="kol"  /></td>
	<td><input type="text" name="price"  /></td>
	


	</tr>
	</table>
	<div class="tablenav">

	<div class="alignleft actions">
	<input type="hidden" value="1" name="add_sub_order" >
	<input type="submit" value="'.__("Add","wp-reservation").'" name="doaction" id="doaction" class="button-primary" />

	</div>

	<br class="clear" />
	</div>
	</form>

	<h3>'.__("Changing records","wp-reservation").'</h3>
	<form id="form2" name="form2" method="post" action="">
	<table class="widefat page fixed" style="width:650px">
	<thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<th>'.__("Resources","wp-reservation").'</th>
	<th >'.__("Reserved places","wp-reservation").'</th>
	<th>'.__("Price per place","wp-reservation").'</th>
	
	</tr>
	</thead>
';

	$qry="select id, orderid, b.resourceid, name ,  kol,  b.price  from  ".$wpdb->prefix . "res_orders_content b
INNER JOIN ".$wpdb->prefix . "res_resources a ON (a.resourceid = b.resourceid) where orderid=".$_REQUEST['orderid'];
	$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );

	if (count($results)>0) {
		$i=1;$kolm=0;$summ=0;
		foreach ($results as $fld) {
			$select=createselectoption ($fld[2], $resso, "resourceid[$i]","")	;
			$content .= <<<EOF
			<tr>    <th scope="row" class="check-column"><input type="checkbox" name="post[$i]" value="$fld[0]" /></th>
			<td><input type="hidden" name="id[$i]" value="$fld[0]" /><input type="hidden" name="orderid[$i]" value="$fld[1]" />$select </td>
			<td><input type="hidden" name="oldkol[$i]" value="$fld[4]"  /><input type="text" name="kol[$i]" value="$fld[4]"  /></td>
			<td><input type="hidden" name="oldprice[$i]" value="$fld[5]"  /><input type="text" name="price[$i]" value="$fld[5]"  /></td>
			
			</tr>
EOF;
			$kolm+=$fld[4];
			$summ+=$fld[4]*$fld[5];
			$i++;
		}
	}

	$content .= '
	<tfoot>
	<tr>
	<th > </th>
	<th>'.__("Total","wp-reservation").'</th>
	<th > '.__("places","wp-reservation").': '.$kolm.'</th>
	<th>'.__("price","wp-reservation").': '.$summ.'</th>
	
	</tr>
	</tfoot>
	</table>
	<div class="tablenav">

	<div class="alignleft actions">
	<select name="action">
	<option value="-1" selected="selected">'.__("Bulk Actions","wp-reservation").'</option>
	<option value="edit">'.__("Change","wp-reservation").'</option>
	<option value="delete">'.__("Delete","wp-reservation").'</option>
	</select>
	<input type="hidden" name="orderid" value='.$_REQUEST['orderid'].' />
	<input type="submit" value="'.__("Apply","wp-reservation").'" name="doaction" id="doaction" class="button-primary" />
	</div>	
	
	<br class="clear" />
	</div>

	</form>
';

	echo $content;
}

function adm_ordertable()
{
	global $wpdb;
	
$content = "";
if ($GLOBALS["version"]	== "pro")	$content=pro_ordertable(); else $content='<a target="_blank" href="http://integra.work/wpresbuy"><img src="'.RES_PLUGIN_URL.'/img/tablepro.png">';
	
	
	return $content ;	
	
}	

function createselectoption ($id, $arr, $name, $first)
{
	$content="<select name=$name>";
	if (($first)!="") $content.="<option $adder value=\"-1\">$first</option>";
	foreach ($arr as $opt) {
		if ($opt[0]==$id) $adder="selected"; else $adder="";
		$content.="<option $adder value=\"$opt[0]\">$opt[1]</option>";
		
	}
	$content.=	"</select>";
	return $content;
}

function adm_makeorder0() {

	$today = date("Ymd");  $today2 = date("d.m.Y"); 
	
	$content = "";

	$content .= <<<EOF
<h2>
EOF
	.__("Reserve","wp-reservation").
	<<<EOF
	</h2>
	<p>
EOF
	.__("To see prices and availability, enter your arrival date and length of stay in the search form below.","wp-reservation").
	<<<EOF
	</p>
	
	<form name="res_order"  action="" method=post ><input type="hidden" name=page value="makeorder1" >
	
	<input size="10" id="datebegin2" name="datebegin" value="$today2" /> <button class="button-primary" id="f_btn2" onclick="return false;">...</button> 
	<select name=kolday class="koldays" >
EOF;
	$maxdays=get_option("res_days");
	for($i=1;$i<=$maxdays;$i++)
	{$content .= "<option value=\"$i\" >$i</option>";}	
	$content .= <<<EOF
	</select>
	


	
	
	
	
	<input  class="button-primary"  type="submit" value="
EOF
.__("Continue","wp-reservation").
<<<EOF
	"   ></form>

	<script type="text/javascript">//<![CDATA[

	var cal = Calendar.setup({
onSelect: function(cal) { cal.hide() }
		,
min:$today 

		
		
		
	});
	cal.manageFields("f_btn2", "datebegin2", "%d.%m.%Y");


	//]]></script>	
	
EOF;

	echo $content ;
};

function adm_makeorder1() {
	global $wpdb;
	$content = "";

	if ($_REQUEST['page']	== "makeorder1") {
		list($day, $month, $year) = sscanf($_REQUEST['datebegin'], "%02d.%02d.%04d"); 
		$datebegin=$year."-". $month."-". $day;
		$dateend=date("Y-m-d",strtotime($datebegin)+($_REQUEST['kolday']-1)*24*60*60);
	} else {
		
		$datebegin=$_REQUEST['datebegin'];
		$dateend=$_REQUEST['kolday'];
	}
	


	$content .= "<p>".__("Please select resources you want to book, and desired amount.","wp-reservation")."</p>";

	
	

	$qry="select a.resourceid, name ,   a.price ";
	
	$dt=strtotime ($datebegin);
	
	
	
	$bt = '<form name="res_order" id="res_order"  action="" method="post"> <table class="widefat page fixed" width=100%  > <thead>	<tr ><th style=\"width:200px\" > </th>';
	while (strtotime($dateend)>=$dt)  {
		$qdt=date("Y-m-d",$dt);
		$qdtout=date("d/m",$dt);

		$qry.=",    capacity - sum(if ( '$qdt' between datebegin and dateend , kol, 0)) `$qdt`  ";
		$bt .= "<th  >$qdtout</th>";
		$dt=$dt+24*60*60;
	}
	$bt .= '<th  >'.__("price","wp-reservation").'</th><th  >'.__("persons","wp-reservation").'</th></tr></thead>';
	$qry.="   from  
	".$wpdb->prefix . "res_orders  c
INNER JOIN  ".$wpdb->prefix . "res_orders_content   b 
ON (c.orderid =
	b.orderid)
	RIGHT JOIN  ".$wpdb->prefix . "res_resources   a ON (b.resourceid =
	a.resourceid)
	where capacity>0 
		group by name, a.resourceid, a.price ";

	

	$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );

	$rowflag=1;  $j=1;
	if (count($results)>0) {
		
		$content .= $bt;
		foreach ($results as $fld) {
			$content .= '<tr class="row'.$rowflag.'">';
			$k=1;
			$min=$fld[3];
			foreach ($fld as $fld2) {

				switch ($k) {
				case 1:   $resource=$fld2; break;
				case 2: $content .= "<td >".$fld2."</td>"; break;
				case 3: $price = $fld2;  break;
				default:  $content .= "<td >$fld2</td>"; if ($fld2<$min) $min=$fld2; break;
				}
				$k++;
			}
			
			$content .= '<td>'.$price.'</td><td><input type=hidden name=price['.$resource.'] value="'.$price.'"><input type=hidden name=resource_name['.$resource.'] value="'.$fld[1].'">';
			
			//if ($minorder<=$min ) 
			{ 
				$content .= '<select col=30  name=reskol['.$resource.']> <option value="0">'.__("select","wp-reservation").'</option>   ';
				if ($j>1) $javaif .= " && (obj.elements['reskol[$resource]'].value==0) "  ; else $javaif = " (obj.elements['reskol[$resource]'].value==0) ";
				for ($i=1;$i<=$min; $i++) {
					$content .= '<option value="'.$i.'">'.$i.'</option>';			
				}
				$content .= '</select>' ; 
			} 
			
			$content .= '</td></tr>';			
			$j++;
			$rowflag = $rowflag*(-1);

		}

		$content .= '<tr><td colspan='.(count($fld)-2).'></td><td align=right><br><input onclick="return checkForm();"  type="submit" value="'.__("Book now","wp-reservation").'"   ></td></tr></table><div align=right><input type="hidden" name=page value="makeorder2" ><input type="hidden" name="datebegin" value="'.$datebegin.'" ><input type="hidden" name="dateend" value="'.$dateend.'" ><input type="hidden" name="kolday" value="'.$_REQUEST['kolday'].'" > <BR></div></form>';			

		$content .= <<<EOF
		<script language="javascript">
		<!--
		function checkForm() {
			var err = "";
			var obj = document.forms['res_order'];
			
			
			if ( true $javaif ) {
				err += "  ";
			}
			
			

			if (err!="") {
				err = "
EOF
.__("You must select at least one of the numbers","wp-reservation").
<<<EOF
				" + err;
				alert(err); return false;
			} else {

				return true;
			}
		}
		-->
		</script> 
EOF;
		
		
	}
	else $content .= "<p>".__("No information about free resources","wp-reservation")."</p>";
	echo $content ;
}

function adm_makeorder2 () {
	global $wpdb;
	$content = "";

	$reskol=$_REQUEST['reskol'];
	$price=$_REQUEST['price'];
	$kolday=$_REQUEST['kolday'];

	foreach ($_REQUEST['reskol'] as $key=>$value )
	{
		$sumprice+=$reskol[$key]*$price[$key]*$kolday;

	}

	$qry ="insert into ".$wpdb->prefix . "res_orders"." (userid, price, datebegin, dateend) values ('1','$sumprice','".$_REQUEST['datebegin']."','".$_REQUEST['dateend']."')";
	
	
	$wpdb->query($wpdb->prepare($qry,"") );	
	$orderid= mysql_insert_id();

	foreach ($_REQUEST['reskol'] as $key=>$value )
	{
		if ($reskol[$key]>0 ) {
			$qry ="insert into ".$wpdb->prefix . "res_orders_content"." (orderid, resourceid, kol, price) values ('$orderid','$key','$reskol[$key]','$price[$key]')";
			$wpdb->query($wpdb->prepare($qry,"") );	}
	}

	$content.=	"<p>".__("Created an order","wp-reservation")." №$orderid </p>";
	echo $content;
}

function adm_offers()
{
	global $wpdb;

	$content="";
	
	if ($_REQUEST['add_offers']) {
		if ($_REQUEST['resourceid'] == -1 or $_REQUEST['price']== "" or $_REQUEST['datebegin']=="" or $_REQUEST['dateend']=="" ) { $content .= "<div class=alert>".__("To add a record, you must specify all the parameters. Record was not added.","wp-reservation")."</div>"; } else {
			$qry ="insert into ".$wpdb->prefix . "res_offers"." (resourceid, price, datebegin, dateend , description) values ('".$_REQUEST['resourceid']."','".$_REQUEST['price']."','".$_REQUEST['datebegin']."','".$_REQUEST['dateend']."','".$_REQUEST['description']."')";
			$wpdb->query($wpdb->prepare($qry,"") );
			
			$content .= "<div class=alert>".__("Record added","wp-reservation")."</div>"		 ;}
	}

	if ($_REQUEST['action']=="-1" ) {
		$content .= "<div class=alert>".__("You have not selected any of the operations","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="edit" and count($_REQUEST['post'])>0) {
		
		foreach ($_REQUEST['post'] as $key=>$value) {
			$qry ="update ".$wpdb->prefix . "res_offers"." set resourceid= '".$_REQUEST['resourceid'][$key]."' ,  price = '".$_REQUEST['price'][$key]."' ,  datebegin = '".$_REQUEST['datebegin'][$key]."',   dateend = '".$_REQUEST['dateend'][$key]."',  description = '".$_REQUEST['description'][$key]."' where id='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
			
		}
		
		
		

		$content .=  "<div class=alert>".__("Selected records changed","wp-reservation")."</div>"		 ;
	}

	if ($_REQUEST['action']=="delete" and count($_REQUEST['post'])>0) {
		$summold=0;
		foreach ($_REQUEST['post'] as $key=>$value) {
			$qry ="delete from ".$wpdb->prefix . "res_offers"."  where id='".$value."' ";
			$wpdb->query($wpdb->prepare($qry,"") );
			
		}
		
		$content .=  "<div class=alert>".__("Selected records deleted","wp-reservation")."</div>"		 ;
	}


	$qryso= "select resourceid, name  from  ".$wpdb->prefix . "res_resources ";  	
	$resso=$wpdb->get_results($wpdb->prepare($qryso,""), ARRAY_N );

	$select=createselectoption ($fld[2], $resso, "resourceid",__("select","wp-reservation"))	;

	$content .= '
	<h2>'.__("Special offers","wp-reservation").'</h2>
	<h3>'.__("Adding record","wp-reservation").'</h3>
	<form id="form1" name="form1" method="post" action="">
	<table class="widefat page fixed" >
	<thead>
	<tr >
	<th>'.__("Resources","wp-reservation").'</th>
	<th>'.__("Price per place","wp-reservation").'</th>	
	<th>'.__("Start date","wp-reservation").'</th>	
	<th>'.__("End date","wp-reservation").'</th>	
	<th>'.__("Comment","wp-reservation").'</th>	
	</tr>
	</thead>
	<tr>
	<td>'.$select.'</td>
	<td><input type="text" name="price"  /></td>
	<td><input type="text" name="datebegin"  /></td>
	<td><input type="text" name="dateend"  /></td>
	<td><input type="text" name="description"  /></td>
	


	</tr>
	</table>
	<div class="tablenav">

	<div class="alignleft actions">
	<input type="hidden" value="1" name="add_offers" >
	<input type="submit" value="'.__("Add","wp-reservation").'" name="doaction" id="doaction" class="button-primary" />

	</div>

	<br class="clear" />
	</div>
	</form>

	<h3>'.__("Changing records","wp-reservation").'</h3>
	<form id="form2" name="form2" method="post" action="">
	<table class="widefat page fixed" >
	<thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<th>'.__("Resources","wp-reservation").'</th>
	<th>'.__("Price per place","wp-reservation").'</th>	
	<th>'.__("Start date","wp-reservation").'</th>	
	<th>'.__("End date","wp-reservation").'</th>	
	<th>'.__("Comment","wp-reservation").'</th>	

	
	</tr>
	</thead>
';


	$qry="select id, b.resourceid,  b.price, datebegin, dateend,description  from  ".$wpdb->prefix . "res_offers b
INNER JOIN ".$wpdb->prefix . "res_resources a ON (a.resourceid = b.resourceid)" ;
	$results=$wpdb->get_results($wpdb->prepare($qry,""), ARRAY_N );

	

	
	
	

	if (count($results)>0) {
		$i=1;$kolm=0;$summ=0;
		foreach ($results as $fld) {
			$select=createselectoption ($fld[1], $resso, "resourceid[$i]","")	;
			$content .= <<<EOF
			<tr>    <th scope="row" class="check-column"><input type="checkbox" name="post[$i]" value="$fld[0]" /></th>
			<td><input type="hidden" name="id[$i]" value="$fld[0]" />$select </td>
			<td><input type="text" name="price[$i]" value="$fld[2]"  /></td>
			<td><input type="text" name="datebegin[$i]" value="$fld[3]"  /></td>
			<td><input type="text" name="dateend[$i]" value="$fld[4]"  /></td>
			<td><input type="text" name="description[$i]" value="$fld[5]"  /></td>
			
			</tr>
EOF;
			$kolm+=$fld[4];
			$summ+=$fld[4]*$fld[5];
			$i++;
		}
	}



	$content .= '
	</table>
	<div class="tablenav">

	<div class="alignleft actions">
	<select name="action">
	<option value="-1" selected="selected">'.__("Bulk Actions","wp-reservation").'</option>
	<option value="edit">'.__("Change","wp-reservation").'</option>
	<option value="delete">'.__("Delete","wp-reservation").'</option>
	</select>
	<input type="submit" value="'.__("Apply","wp-reservation").'" name="doaction" id="doaction" class="button-primary" />
	</div>	

	<br class="clear" />
	</div>

	</form>
';

	echo $content;
}


?>