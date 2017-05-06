<?php
    // iniciar as configuraçõees do site
    include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

    // controle das ações do plugin partner
    switch( $_GET["module"] )
    {
        case "getList" :
            if ( @$_POST['uf_id'] ){
                $params = $_POST;
                $params["get_UF"] = 1;

                $city = new City( $params );
                $smarty->assign( "l_city", $city->getList() );

                $_POST['uf_id'] = null;
            } else {
                $smarty->assign( "l_city", [] );
            };

            $uf = new UF( $_POST );
            $smarty->assign( "l_uf", $uf->getList() );

            $smarty->assign("page_name", "Cidade");
            $smarty->assign("page_description", "Cadastro de Cidades");

            // chamando o template
            if (!@$_POST['ajax']){
                $smarty->display( PATH_PLUGIN . "city/templates/city_getList.html" );
            } else{
                $smarty->display( PATH_PLUGIN . "city/templates/city_template.html" );
            }
        break;
        case "insert" :
            if (@$_POST) {
                $city = new City( $_POST );
                $city->insert();
            }
        break;
        case "edit" :
            if ( @$_GET["city_id"] ){
                if ( @$_POST["city_id"] ){
                    $city = new City( $_POST );
                    $city->update();
                }

                $city = new City( $_GET );
                $smarty->assign( "city", $city->get());
            } else{
                die($errorMessage["no_parameters_get"]);
            }
        break;
        case "del" :
            $city = new City( $_POST );
            $city->delete();
            die();
        break;
        case "getListCityUF" :
            if ( @$_GET['uf_id'] || @$_GET['uf_code'] ) {
                $city = new City($_GET);

                if ( @$_GET['uf_code'] ){
                    $city->setWhereUfCode($_GET['uf_code']); 
                }

                $smarty->assign("l_city", $city->getList());
            }
        break;
    }
