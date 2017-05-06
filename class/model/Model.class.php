<?php

/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 28/12/2016
 * Time: 11:55
 */

const QUERY_OPERATION_INSERT = 'INSERT';
const QUERY_OPERATION_UPDATE = 'UPDATE';
const QUERY_OPERATION_DELETE = 'DELETE';

abstract class Model
{
    public $table;               // Estrutura da tabela - classe Table.class
    public $params;              // Lista de parâmetros passados
    public $paramsQuery;         // Lista de parâmetros a ser utilizados na query
    public $paramsGet;           // Lista de parâmetros de Get
    public $json;                // Define se o retorno será em Json
    public $dateUpdate;          // Define se será atualizado a "Data de Update"
    public $requiredFieldsUpdate;// Define se será verificado os campos obrigatórios para o update
    public $orderBy;             // Campo de ordenação para a query
    public $limit;               // Limit para a query
    public $fieldsVisible;       // Lista dos campos visíveis que serão retornados na query
    public $customFieldSelect;   // Campos personalizados para o select (ex: sub-sql)
    public $customWhere;         // Condições personalizadas para o where (ex: exists)
    public $trataAcentos;        // Define se será tratado os acentos no Update e Insert
    public $tableBinder;         // Lista de Tabelas de ligação 1 para muitos (ex: person -> address)

    /**
     * Model constructor.
     * @param $params
     */
    protected function __construct( $params ){
        $this->params               = $params;
        $this->dateUpdate           = true;
        $this->requiredFieldsUpdate = true;
        $this->orderBy              = "";
        $this->limit                = "";
        $this->trataAcentos         = true;

        self::setParams();
    }

    /**
     * Seta os parâmetros recebidos no formato necessario
     */
    private function setParams(){
        $this->json = @$this->params["json"];

        $l_params     = [];
        $l_params_get = [];
        foreach ( $this->params as $key => $value ){

            // verifica se o parâmetro existe na tabela
            if ( (@$this->table->fields[$key]) && ($value || $this->table->fields[$key]->field_logic) ) {

                // Alimenta o valor
                $this->table->fields[$key]->query_value = $this->table->fields[$key]->field_logic ? ($value && $value != 'N') ? 'Y' : 'N' : $value;

                // adiciona no array os parâmetros
                $l_params[$key] = $this->table->fields[$key];
            }
            if ( substr($key, 0, 4) == 'get_' ){
                $l_params_get[$key] = $value;
            }
            if ( $key == 'order' ){
                $this->orderBy = $value;
            }
            if ( $key == 'limit' ){
                $this->limit = $value;
            }
        }

        // atribuiu na variavel de parâmetros para ser utilizada nas query
        $this->paramsQuery = $l_params;
        // Lista de parâmetro Get
        $this->paramsGet   = $l_params_get;

        if ( !@$this->orderBy ){
            $this->orderBy = $this->table->orderByDefault;
        }

        $this->customWhere = [];
    }

    protected function addCustomFieldSelect( $sql ){
        if ( !@$this->customFieldSelect ){
            $this->customFieldSelect = [];
        }
        $this->customFieldSelect[] = $sql;
    }

    /**
     * Pega todos os campo logicos e que não estão na lista de parâmetros e adiciona com valor 'N'
     * CheckBox vazio não é enviado para o post
     */
    private function setLogicFields(){
        foreach ( $this->table->fields as $field ){
            if ( $field->field_logic && !@$this->params[$field->field_name] ) {
                $field->query_value = 'N';
                $this->paramsQuery[$field->field_name] = $field;
            }
        }
    }

    /**
     * Verifica se todos os campos obrigatórios foram passados
     * @param $queryOperation
     */
    private function checkRequiredFields( $queryOperation ){
        GLOBAL $jsonStatus;

        $erro = [];
        foreach ( $this->table->fields as $field ){

            // verifica se o campo é obrigatório e foi passado
            // ignora campo ID se for insert
            if (
                ( $field->field_required && !@$this->paramsQuery[$field->field_name] ) &&
               !( $field->field_is_id && ($queryOperation == QUERY_OPERATION_INSERT) )
               ){
                $erro[] = $field->field_name;
            }
        }

        if ( $erro ) {
            header('HTTP/1.0 401 Unauthorized');
            Json::get( $jsonStatus[401], array("message" => "Campos obrigatórios não informados: " . implode(',', $erro), "description" => "") );
        }
    }

    /**
     * Verifica se o campo de chave primária foi informado
     */
    private function checkIdField(){
        GLOBAL $jsonStatus;

        $erro = true;
        foreach ( $this->table->fields as $field ){

            // verifica se o campo é ID foi passado e possui valor
            if ( $field->field_is_id && @$this->paramsQuery[$field->field_name] && @$this->paramsQuery[$field->field_name]->query_value ){
                $erro = false;
                break;
            }
        }

        if ( $erro ) {
            header('HTTP/1.0 401 Unauthorized');
            Json::get( $jsonStatus[401], array("message" => "Chave primária não informada!", "description" => "") );
        }
    }

    private function getIdField(){
        GLOBAL $jsonStatus;

        foreach ( $this->table->fields as $field ){
            if ( $field->field_is_id ){
                return $field;
            }
        }

        header('HTTP/1.0 401 Unauthorized');
        Json::get( $jsonStatus[401], array("message" => "Chave primária não informada!", "description" => "") );
        return null;
    }

    /**
     * verifica se existe algum Campo vinculado a uma classe
     * @param $className
     * @return null
     */
    private function getFieldLinkClass( $className ){
        foreach( $this->table->fields as $field ){
            if ( $field->link_class == $className ) {
                return $field;
            }
        }

        if ( @$this->table->table_link ) {
            foreach ($this->table->table_link as $table_link) {

                if ( $table_link->class_name == $className  ) {
                    $field_id = self::getIdField();

                    $field_id->link_class   = $table_link->class_name;
                    $field_id->link_id_name = $field_id->field_name;
                    $field_id->link_type    = "getList";

                    return $field_id;
                }
            }
        }

        return null;
    }

    /**
     * Verifica se tem permissão para realizar a operação em questão
     * @param $queryOperation
     */
    private function checkPermission( $queryOperation ){
        GLOBAL $jsonStatus, $smarty;

        if (
            ( ($queryOperation == QUERY_OPERATION_INSERT) && (!$this->table->allow_insert) ) ||
            ( ($queryOperation == QUERY_OPERATION_UPDATE) && (!$this->table->allow_update) ) ||
            ( ($queryOperation == QUERY_OPERATION_DELETE) && (!$this->table->allow_delete) )
           )
        {
            if ( $this->json ){
                header('HTTP/1.0 401 Unauthorized');
                Json::get( $jsonStatus[401], array("message" => "Operação não permitida!", "description" => "") );
            } else {
                $smarty->assign( "error", "Operação não permitida!" );
            }
            die();
        }
    }

    /**
     * Pega a lista de campos visiveis que serão retornados na query
     */
    private function getFieldVisible(){
        $this->fieldsVisible = [];

        foreach ( $this->table->fields as $field ){
            if ( $field->query_visible ){
                $this->fieldsVisible[] = $field;
            }
        }
    }

    /**
     * Pega o filtro para as tabelas de ligação com Exists
     */
    private function getCustomWhere(){
        if ( @$this->table->table_link ) {
            foreach ($this->table->table_link as $table_link) {
                if ( @$this->params[$table_link->param_name] ) {

                $field_id = self::getIdField();
                $sql      = " EXISTS (SELECT TABLE_LINK.{$field_id->field_name} FROM `{$table_link->table_name}` as TABLE_LINK " .
                            "         WHERE TABLE_LINK.{$field_id->field_name} = {$this->table->table_name}.{$field_id->field_name} " .
                            "         AND   TABLE_LINK.{$table_link->field_name} in ({$this->params[$table_link->param_name]}) )";

                $this->customWhere[] = $sql;
                }
            }
        }
    }

    /**
     * Verifica se existe algum campo que é unico
     * e verifica se existe algum registro com o mesmo valor
     * @param $queryOperation
     */
    private function checkUniqueFields( $queryOperation ){
        GLOBAL $jsonStatus, $smarty;

        foreach ( $this->table->fields as $field ){
            if ( $field->field_unique ){
                $params = [];
                $params[$field->field_name] = $field->query_value;

                if ( $queryOperation == QUERY_OPERATION_UPDATE ) {
                    $field_id = self::getIdField();

                    $params[$field_id->field_name] = $field_id->query_value;
                }

                $class_name = get_class($this);
                $unique     = new $class_name( $params );

                if ( $queryOperation == QUERY_OPERATION_UPDATE) {
                    $unique->paramsQuery[$field_id->field_name]->query_operation = '<>';
                }

                $unique = $unique->get();

                if ( @$unique ){
                    if ( $this->json ){
                        header('HTTP/1.0 401 Unauthorized');
                        Json::get( $jsonStatus[401], array("message" => $field->field_unique_msg, "description" => "") );
                    } else {
                        $smarty->assign( "error", $field->field_unique_msg );
                    }
                    die();
                }
            }
        }
    }

    /**
     * Realiza o select na tabela para o Get e GetList
     * @param $one_record
     * @return array|null
     */
    private function select( $one_record ){
        GLOBAL $db, $jsonStatus, $smarty;

        self::getFieldVisible();

        self::getCustomWhere();

        $result = $db->querySelect($this->table->table_name, $this->paramsQuery, $this->json, $this->fieldsVisible,
                                   $this->orderBy, $this->limit, $this->customFieldSelect, $this->customWhere);

        if( sizeof($result) ) {

            // Pega as classes de ligação de acordo com os Get passados
            foreach ( $this->paramsGet as $key => $get ){
                // Pega o nome da Classe
                $className = substr($key, 4, strlen($key));
                // Verifica se existe algum campo vinculado a esta classe
                $field     = self::getFieldLinkClass( $className );

                if ( @$field ) {
                    // Pega a classe para cada registro
                    foreach ( $result as $row ){
                        // verifica se o registro possui valor no campo de ligação
                        if ( @$row->{$field->field_name} ){
                            $params_get = $this->paramsGet;
                            $params_get[$field->link_id_name] = $row->{$field->field_name};

                            $row->{$className} = new $className( $params_get );
                            $row->{$className} = $field->link_type == 'get' ? $row->{$className}->get() : $row->{$className}->getList();
                        }
                    }
                }
            }

            // Verifica se existe algum field com uma função de tratamento para o seu valor
            foreach ( $this->table->fields as $field ){
                // verifica se existe a função
                if ( @$field->func_process_value){
                    // percorre os registros
                    foreach ( $result as $row ){
                        // verifica se possui valor
                        if ( @$row->{$field->field_name} ){
                            // pega o nome da função
                            $func = $field->func_process_value;
                            // executa a função
                            $row->{$field->field_name} = $func( $row->{$field->field_name} );
                        }
                    }
                }
            }

            if ( @$this->tableBinder ){
                foreach ( $this->tableBinder as $table ) {

                    if ( @$this->paramsGet["get_{$table->class_name}"] ){
                        foreach ( $result as $row ) {
                            $params = $this->paramsGet;;
                            $params[$table->field_name] = $row->{$table->field_name};

                            $row->{$table->class_name} = new $table->class_name($params);
                            $row->{$table->class_name} = $table->return_type == 'get' ? $row->{$table->class_name}->get() : $row->{$table->class_name}->getList();
                        }
                    }
                }
            }

            if ( $one_record){
                $ret = $result[0];
            } else {
                $ret = $result;
            }

            if( $this->json ) {
                Json::get( $jsonStatus[200], $ret );
            } else {
                return $ret;
            }
        }
        else {
            if( $this->json ) {
                header('HTTP/1.0 404 Not Found');
                Json::get( $jsonStatus[404], array("message" => "Registro(s) não encontrado(s)!", "description" => "") );
            } else {
                $smarty->assign( "error", "Registro(s) não encontrado(s)!" );
            }
        }
        return null;
    }

    public function get( ) {

        return self::select( true ) ;

    }

    public function getList( ){

        return self::select( false ) ;

    }

    public function insert()
    {
        GLOBAL $db;

        // verifica se tem permissão de fazer Insert nesta tabela
        self::checkPermission( QUERY_OPERATION_INSERT );

        // Pega todos os campo logicos e que não estão na lista de parâmetros e adiciona com valor 'N'
        self::setLogicFields();

        // verifica se foi passado todos os campos obrigatórios
        self::checkRequiredFields( QUERY_OPERATION_INSERT );

        // verifica se existe algum campo unico e existe algum registro com o mesmo valor
        self::checkUniqueFields( QUERY_OPERATION_INSERT );

        // executa a query em questão
        $last_id = $db->queryInsert($this->table->table_name, $this->paramsQuery, $this->json, $this->trataAcentos);

        if ( @$this->table->table_link ) {
            foreach ( $this->table->table_link as $table_link ){

                if ( @$this->params[$table_link->param_name] ) {
                    $field_id = self::getIdField();

                    foreach ( $this->params[$table_link->param_name] as $param) {
                        $params = [];
                        $params[$field_id->field_name]   = $last_id;
                        $params[$table_link->field_name] = $param;

                        if ( @$table_link->custom_fields ){
                            foreach ( $table_link->custom_fields as $custom_field) {
                                $params[$custom_field->field_name] = $custom_field->query_value;
                            }
                        }

                        $class_link = new $table_link->class_name( $params );
                        $class_link->insert();
                    }
                }
            }
        }

        return $last_id;
    }

    public function update()
    {
        GLOBAL $db;

        // verifica se tem permissão de fazer Insert nesta tabela
        self::checkPermission( QUERY_OPERATION_UPDATE );

        // Verifica se foi passado a chave primária
        self::checkIdField();

        if ($this->requiredFieldsUpdate){
            // Pega todos os campo logicos e que não estão na lista de parâmetros e adiciona com valor 'N'
            self::setLogicFields();

            // verifica se foi passado todos os campos obrigatórios
            self::checkRequiredFields( QUERY_OPERATION_UPDATE );

            // verifica se existe algum campo unico e existe algum registro com o mesmo valor
            self::checkUniqueFields( QUERY_OPERATION_UPDATE );
        }

        // executa a query em questão
        $db->queryUpdate($this->table->table_name, $this->paramsQuery, $this->json, $this->dateUpdate, $this->trataAcentos);

        if ( @$this->table->table_link ) {
            foreach ( $this->table->table_link as $table_link ){

                if ( @$this->params[$table_link->param_name] ) {
                    $field_id = self::getIdField();

                    $params = [];
                    $params['custom_field_delete'] = $field_id;

                    if ( @$table_link->custom_fields ){
                        foreach ( $table_link->custom_fields as $custom_field){
                            $params['custom_where_delete'][] = $custom_field;
                        }
                    }

                    $class_link = new $table_link->class_name( $params );
                    $class_link->delete();

                    foreach ( $this->params[$table_link->param_name] as $param) {
                        $params = [];
                        $params[$field_id->field_name]   = $this->table->fields[$field_id->field_name]->query_value;
                        $params[$table_link->field_name] = $param;

                        if ( @$table_link->custom_fields ){
                            foreach ( $table_link->custom_fields as $custom_field) {
                                $params[$custom_field->field_name] = $custom_field->query_value;
                            }
                        }

                        $class_link = new $table_link->class_name( $params );
                        $class_link->insert();
                    }
                }
            }
        }
    }

    public function delete(){
        GLOBAL $db;

        // verifica se tem permissão de fazer Insert nesta tabela
        self::checkPermission( QUERY_OPERATION_DELETE );

        // verifica se foi passado um campo customizado para o delete
        if ( @$this->params['custom_field_delete'] ){
            $this->paramsQuery = [];
            $this->paramsQuery[] = $this->params['custom_field_delete'];
            if( @$this->params['custom_where_delete'] ) {
                $this->paramsQuery['custom_where_delete'] = $this->params['custom_where_delete'];
            }
        } else {
            // Verifica se foi passado a chave primária
            self::checkIdField();
        }

        // executa a query em questão
        $db->queryDelete($this->table->table_name, $this->paramsQuery, $this->json);
    }

    public function addTableBinder( $class_name, $field_name, $return_type ){

        $this->tableBinder[] = new TableBinder( $class_name, $field_name, $return_type );

    }

}