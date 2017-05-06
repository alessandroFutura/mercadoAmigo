<?php
	/*
	PAGINA DE LOGOUT DO ADMIN
	- reseta a sessão e redireciona para o login
	*/

	// iniciar as configuraçõees do site
	include "../../config/start.php";

	// verificar admin na variavel de sessão
	Session::logout();

?>
