<?php
    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            $uf = new UF( $_POST );
            $smarty->assign( "l_uf", $uf->getList() );

            $smarty->assign("page_name", "UF");
            $smarty->assign("page_description", "Cadastro de UF");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "uf/templates/uf_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "uf/templates/uf_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $uf = new UF( $_POST );
                $uf->insert();
            }
        break;
        case "edit" :
            if ( @$_GET["uf_id"] ){
                if ( @$_POST["uf_id"] ){
                    $uf = new UF( $_POST );
                    $uf->update();
                }

                $uf = new UF( $_GET );
                $smarty->assign( "uf", $uf->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $uf = new UF( $_POST );
            $uf->delete();
            die();
        break;
    }
