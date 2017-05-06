<?php
	/*
	PAGINA DE LOGIN DO ADMIN
	*/

	// iniciar as configurações do site
	include "../../config/start.php";

	// esta logado?
	Session::isLogged();

	// efetuar o login
	Session::login();

	// chamando o template da home
	$smarty->display( PATH_TEMPLATES_ADMIN . "login.html" );
?>
