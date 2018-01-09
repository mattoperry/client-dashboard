<?php
namespace VIP\Client_Dashboard;

/** determine environment **/
if ( $_SERVER['HTTP_HOST'] === 'localhost' ) {
	define( 'ENV', 'DEV' );
}else{
	define( 'ENV', 'PROD' );
}

/** load everything **/
require_once( __DIR__ . '/../inc/config.php');
require_once( __DIR__ . '/../inc/class-dashboard.php');
require_once( __DIR__ . '/../inc/class-data.php');
require_once( __DIR__ . '/../inc/class-data-events.php');
require_once( __DIR__ . '/../inc/class-data-objects.php');

$dashboard = new Dashboard;
$dashboard->do_request();