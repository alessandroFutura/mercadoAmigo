<?php

//	ini_set('display_errors', 1);
//	ini_set('log_errors', 1);
//	error_reporting(E_ALL);
	//error_reporting(0);

    session_name('mercadoamigo');
	session_start();

	date_default_timezone_set("America/Sao_Paulo");

	/*header('Content-Type: application/json; charset:utf8;');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, x-session-token');*/

	include "config.php";
	include "func.php";
	include "language.php";

	include PATH_CLASSES . "Mail.class.php";
	include PATH_CLASSES . "Json.class.php";
	include PATH_CLASSES . "Session.class.php";
	include PATH_SMARTY . "Smarty.class.php";
//	include PATH_PLUGIN . "user/classes/User.class.php";

    include PATH_CLASS . "model/DB.class.php";

    spl_autoload_register(function ($class_name) {
    	if ( file_exists(PATH_CLASS . $class_name . '.class.php') ){
            require_once PATH_CLASS . $class_name . '.class.php';
        } else {
            require_once PATH_CLASS . 'model/' . $class_name . '.class.php';
        }
    });

	$jsonStatus = $status[LANGUAGE];
	$errorMessage = $error[LANGUAGE];

    $smarty = new Smarty();

    define("MYSQL_CONN_ERROR", "Unable to connect to database.");
    // Ensure reporting is setup correctly
    mysqli_report(MYSQLI_REPORT_STRICT);

    $db = new DB( "localhost", "root", "", "mercadoamigo" );

	normalizeParams();

	searchRedirect();

    include "global.php";

//    header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
//header('Access-Control-Allow-Origin: *');
//
//header('Access-Control-Allow-Methods: GET, POST');
//
//header("Access-Control-Allow-Headers: X-Requested-With");
