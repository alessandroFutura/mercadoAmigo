<?php


	class User extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'user', 'user_name' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'user_id'              , 'i', '=', true, true);
            $this->table->addField( 'user_profile_id'      , 'i', '=', true);
            $this->table->addField( 'person_id'            , 'i', '=', true);
            $this->table->addField( 'user_active'          , 's', '=', true, false, true);
            $this->table->addField( 'user_name'            , 's', '=', true);
            $this->table->addField( 'user_user'            , 's', '=', true);
            $this->table->addField( 'user_pass'            , 's', '=', true, false, false, false);
            $this->table->addField( 'user_mail'            , 's', '=', true);
            $this->table->addField( 'user_login'           , 's', '=');
            $this->table->addField( 'user_accept_contract' , 's', '=', true, false, true);
            $this->table->addField( 'user_update'          , 's', '=');
            $this->table->addField( 'user_date'            , 's', '=');

            // Define que não poderá haver nenhum registro com o mesmo valor
            //$this->table->fields["user_user"]->field_unique     = true;
            //$this->table->fields["user_user"]->field_unique_msg = "Já existe um usuário com este nome";

            // get_UserProfile
            $this->table->fields["user_profile_id"]->link_class   = "UserProfile";
            $this->table->fields["user_profile_id"]->link_id_name = "user_profile_id";
            $this->table->fields["user_profile_id"]->link_type    = "get";

            // get_Person
            $this->table->fields["person_id"]->link_class   = "Person";
            $this->table->fields["person_id"]->link_id_name = "person_id";
            $this->table->fields["person_id"]->link_type    = "get";

            parent::__construct( $params );
		}

		public function passRecover()
        {

        }

		public function get( )
		{
            GLOBAL $jsonStatus, $smarty;

            // para obrigar o retorno ser em objeto
   		    $json       = $this->json;
			$this->json = false;

            $user = parent::get();

            if( sizeof($user) )
            {
                if( $user->user_active == "N" && @$this->params["set_user_login"] )
                {
                    unset( $_SESSION["user"] );
                    unset( $_SESSION );

                    if( $json ) {
                        header('HTTP/1.0 203 Not authorized');
                        Json::get( $jsonStatus[203], "Usuário inativo!");
                    } else {
                        $smarty->assign( "error", "Usuário inativo!" );
                        return null;
                    }
                }
                elseif( @$this->params["set_user_login"] )
                {
                    $userLogin = new User( array(
                        "user_id"    => $user->user_id,
                        "user_login" => date("Y-m-d H:i:s")
                    ));

                    $userLogin->requiredFieldsUpdate = false;
                    $userLogin->update();

                    return $user;
                }

                if( $json ) {
                    Json::get( $jsonStatus[200], $user );
                } else {
                    return $user;
                }
            }
            else
            {
                if( $json ) {
                    header('HTTP/1.0 404 Not Found');
                    Json::get( $jsonStatus[404], "Usuário ou senha inválidos!");
                } else {
                    $smarty->assign( "error", "Usuário ou senha inválidos!" );
                }
            }
            return null;
		}

        public function getMailList()
        {
            $user = new User(Array(
                "user_profile_id" => 1001
            ));
            $l_user = $user->getList();

            $ret = [];
            foreach( $l_user as $user ){
                if( $user->user_id > 1001 ){
                    $ret[] = Array(
                        "mail_address" => $user->user_mail,
                        "mail_recipient" => $user->user_name
                    );
                }
            }

            return $ret;
        }

        public function getMessage( $user, $pass )
        {
            $style = (Object)Array(
                "h" => "
                    font-family: sans-serif;
                    color: #025935;
                    text-align: center;
                    margin-top: 60px;
                ",
                "p" => "
                    font-family: sans-serif;
                    color: #025935;
                    text-align: center;
                    font-size: 14px;
                ",
                "div" => "
                    display: block; 
                    width: 70%; 
                    margin: 60px auto 0 auto;
                ",
                "table" => "
                    width: 100%; 
                    border: 1px solid #013721;
                    border-left: none;
                    border-bottom: none;
                    font-family: sans-serif
                ",
                "th" => "
                    padding:4px; 
                    background-color: #025935; 
                    color: white; 
                    border: 1px solid #013721;
                    border-top: none;
                    border-right: none;
                    padding: 7px;
                    letter-spacing: 1px;
                ",
                "td" => "
                    border:1px solid #013721;
                    border-top: none;
                    border-right: none;
                    padding: 7px;
                    text-align: center;
                "
            );

            $head = " <meta charset='UTF-8'><h1 style='{$style->h}'>Confirmação de Cadastro</h3>
                        <p style='{$style->p}'>Olá! Seja Bem-Vindo ao Mercado Amigo!</p> 
                        <p style='{$style->p}'>Para acessar o seu Escritório Virtual, basta <a href='http://www.omercadoamigo.com.br/entrar'>clicar aqui</a> e informar os seus dados de acesso conforme abaixo:</p>";
            $body = "";
            $foot = "";

            $head .= "
                <div style='{$style->div}'>
                   <h2 style='{$style->h}'>Dados de Acesso</h3>
                   <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                       <thead>
                           <th style='{$style->th}'>Login</th>
                           <th style='{$style->th}'>Senha</th>
                       <thead>
                       <tbody>
                           <tr>
                               <td style='{$style->td}'>{$user->user_user}</td>
                               <td style='{$style->td}'>{$pass}</td>
                           </tr>
                       </tbody>
                   </table>
                </div>
           ";

            return $head . $body . $foot;
        }

	}

