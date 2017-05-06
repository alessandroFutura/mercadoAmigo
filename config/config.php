<?php

	define( "PATH_SITE", "C:/wamp/www/mercado-amigo/" );
    //define( "PATH_SITE", "/home/omercadoamigo/omercadoamigo.com.br/" );
	define( "PATH_CLASSES", PATH_SITE . "classes/" );
    define( "PATH_CLASS", PATH_SITE . "class/" );
	define( "PATH_PHPMAILER", PATH_CLASSES . "PHPMailer/" );
	define( "PATH_SMARTY", PATH_SITE . "smarty/" );
	define( "PATH_TEMPLATES", PATH_SITE . "templates/" );
	define( "PATH_TEMPLATES_ADMIN", PATH_TEMPLATES . "admin/" );
	define( "PATH_PLUGIN", PATH_SITE . "plugin/" );
	define( "PATH_PUBLIC", PATH_SITE . "public/" );
	define( "PATH_FILES", PATH_PUBLIC . "admin/files/" );
	define( "PATH_DOWNLOADS", PATH_FILES . "downloads/" );

	define( "LANGUAGE", "pt" );
	define( "DEBUG", TRUE );

    define( "URI_PUBLIC" , "http://{$_SERVER["HTTP_HOST"]}/mercado-amigo/public/" );
    //define( "URI_PUBLIC" , "http://{$_SERVER["HTTP_HOST"]}/" );
	define( "URI_CSS" , URI_PUBLIC . "css/" );
	define( "URI_JS" , URI_PUBLIC . "js/" );
	define( "URI_IMAGES" , URI_PUBLIC . "images/" );
	define( "URI_FILES" , URI_PUBLIC . "admin/files/" );

	define( "URI_PUBLIC_ADMIN" , URI_PUBLIC . "admin/" );
	define( "URI_CSS_ADMIN" , URI_PUBLIC_ADMIN . "css/" );
	define( "URI_JS_ADMIN" , URI_PUBLIC_ADMIN . "js/" );
	define( "URI_IMAGES_ADMIN" , URI_PUBLIC_ADMIN . "images/" );

	define( "SCRIPT_NAME", basename($_SERVER["SCRIPT_FILENAME"], '.php'));

	if( substr(str_replace("/mercado-amigo/public/","",$_SERVER["SCRIPT_NAME"]),0,5) == "admin" ){
		define( "ADMIN", 1 );
	}

	define( "WIDHT_THUMB_MAX", 5000 );
	define( "HEIGHT_THUMB_MAX", 3500 );

	define( "WIDHT_THUMB_SMALL", 160 );
	define( "HEIGHT_THUMB_SMALL", 120 );

	define( "QUALITY_THUMB", 75 );
