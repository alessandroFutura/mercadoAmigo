<?php


	class Person extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'person', 'person_name' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'person_id'            , 'i', '=', true, true);
            $this->table->addField( 'person_active'        , 's', '=', true, false, true);
            $this->table->addField( 'person_code'          , 's', '=', true);
            $this->table->addField( 'person_type'          , 's', '=', true);
            $this->table->addField( 'person_cnpj'          , 's', '=');
            $this->table->addField( 'person_cpf'           , 's', '=');
            $this->table->addField( 'person_rg'            , 's', '=');
            $this->table->addField( 'person_name'          , 's', '=', true);
            $this->table->addField( 'person_nickname'      , 's', '=', true);
            $this->table->addField( 'person_birth'         , 's', '=', true);
            $this->table->addField( 'person_gender'        , 's', '=', true);
            $this->table->addField( 'person_update'        , 's', '=');
            $this->table->addField( 'person_date'          , 's', '=');

            // parâmetros - $class_name, $param_name, $table_name, $field_name
            $this->table->addTableLink( 'PersonCategoryLink', 'person_category', 'person_category_link', 'person_category_id');

            parent::__construct( $params );
		}

        public function setWhereCategory( $category_id ){
            $sql = "(SELECT COUNT(person_category_link.person_id) FROM person_category_link WHERE person.person_id = person_category_link.person_id AND person_category_link.person_category_id = '{$category_id}') > 0";

            $this->customWhere[] = $sql;
        }

        public function getMessage( $parent, $child, $params )
        {
            GLOBAL $user;

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

            $head = " <meta charset='UTF-8'><h1 style='{$style->h}'>{$params["title"]}</h3>
                        <p style='{$style->p}'>Um novo cliente acaba de ser cadastrado no Mercado Amigo.</p> 
                        <p style='{$style->p}'>Abaixo seguem as informações do novo cliente e seu indicador.</p>";
            $body = "";
            $foot = "";

            $head .= "
                <div style='{$style->div}'>
                   <h2 style='{$style->h}'>Cliente</h3>
                   <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                       <thead>
                           <th style='{$style->th}'>Código</th>
                           <th style='{$style->th}'>Nome</th>
                           <th style='{$style->th}'>Apelido</th>
                           <th style='{$style->th}'>Documento</th>
                       <thead>
                       <tbody>
                           <tr>
                               <td style='{$style->td}'>{$child->person_code}</td>
                               <td style='{$style->td}'>{$child->person_name}</td>
                               <td style='{$style->td}'>{$child->person_nickname}</td>
                               <td style='{$style->td}'>" . ($child->person_type == 'J' ? $child->person_cnpj : $child->person_cpf) . "</td>
                           </tr>
                       </tbody>
                   </table>
                   <h2 style='{$style->h}'>Indicado por</h3>
                   <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                        <thead>
                            <th style='{$style->th}'>Código</th>
                            <th style='{$style->th}'>Nome</th>
                            <th style='{$style->th}'>Apelido</th>
                            <th style='{$style->th}'>Documento</th>                       
                        <thead>
                        <tbody>
                            <tr>
                                <td style='{$style->td}'>{$parent->person_code}</td>
                                <td style='{$style->td}'>{$parent->person_name}</td>
                                <td style='{$style->td}'>{$parent->person_nickname}</td>
                                <td style='{$style->td}'>" . ($parent->person_type == 'J' ? $parent->person_cnpj : $parent->person_cpf) . "</td>
                            </tr>
                        </tbody>
                   </table>
                   <h2 style='{$style->h}'>Dados da Rede</h3>
                   <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                        <thead>
                            <th style='{$style->th}'>Cadastro</th>                       
                        <thead>
                        <tbody>
                            <tr>
                                <td style='{$style->td}'>{$params["rede"]}</td>
                            </tr>
                        </tbody>
                   </table>
                </div>
           ";

            return $head . $body . $foot;
        }
    }

