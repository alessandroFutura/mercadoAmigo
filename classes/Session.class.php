<?php

class Session
{
    public function __construct( $user )
    {
        $this->user = $user;
    }

    public static function login()
    {
        GLOBAL $jsonStatus;

        if( @$_POST["user_user"] && @$_POST["user_pass"] )
        {

            $user = new User( Array(
                "user_user"      => $_POST["user_user"],
                "user_pass"      => md5( $_POST["user_pass"] ),
                "json"           => @$_POST["json"] ? 1 : null,
                "set_user_login" => 1,
            ));
            $user = $user->get();

            if( !is_null( $user ) )
            {
                Session::saveSession( $user );
                if ( @$_GET['site'] ){
                    Json::get( $jsonStatus[200], "Logado com sucesso!" );
                } else {
                    header( "location: " . URI_PUBLIC_ADMIN . "home/" );
                }
            }
            else if( @$_GET['site'] )
            {
                header('HTTP/1.0 401 Unauthorized');
                die();
            }
        }
    }

    public static function isLogged()
    {
        // verifica se existe a sessão
        if( isset( $_SESSION["user_id"] ) ) {
            if( @$_GET["site"] ){
                die();
            } else{
                header("location: " . URI_PUBLIC_ADMIN . "home/");
            }
        }
    }

    public static function isUser()
    {
        // verifica se existe a sessão
        if( !isset( $_SESSION["user_id"] ) ){
            if( @$_GET["site"] ){
                header('HTTP/1.0 401 Unauthorized');
                die();
            } else {
                header( "location: " . URI_PUBLIC_ADMIN . "login/" );
            }
        }

        if( @$_GET["site"] ){
            die();
        }

        return true;
    }

    public static function saveSession( $user )
    {
        // Save session
        $_SESSION["user_id"]   = $user->user_id;
        $_SESSION["user_name"] = $user->user_name;
    }

    public static function updateSession()
    {
        /*// invocando a variavel do smarty
        GLOBAL $smarty;
        // verificando se existe o post
        if( @$_POST["action"] )
        {
            // post de dados de acesso?
            if( @$_POST["action"] == "access" )
                User::edit( $_SESSION["user"]["id"], null, $_POST["user"], md5( $_POST["pass"] ) );
            // post de dados pessoais?
            if( @$_POST["action"] == "pessoal" )
                User::edit( $_SESSION["user"]["id"], null, null, null, $_POST["name"], $_POST["mail"] );
            // atualizando o user
            $user = User::get( 0, $_SESSION["user"]["id"] );
            // atualizando a sess�o
            Session::saveSession( $user );
            // criando a mensagem de aviso
            $smarty->assign( "message", "Dados editados com sucesso!" );
        }*/
    }

    public static function logout()
    {
        // Clear session
        //unset( $_SESSION["user_id"] );
        session_unset();
        header( "location: " . URI_PUBLIC_ADMIN . "login/" );
    }
}

?>