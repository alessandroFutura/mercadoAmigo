<?php

/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 27/12/2016
 * Time: 15:37
 */

const OPERATION_SELECT = "SELECT";
const OPERATION_INSERT = "INSERT";
const OPERATION_UPDATE = "UPDATE";
const OPERATION_DELETE = "DELETE";

class DB
{
    private $dataBase;          // Conexão com o Banco de Dados
    private $sql;               // Sql a ser executada
    private $tableName;         // nome da tabela a ser executada a query
    private $params;            // lista dos parâmetros passados
    private $resultQuery;       // Resultado da query executada
    private $fieldString;       // lista dos campos to tipo string para fazer tratamento dos acentos
    private $stmt;              // Query preparada para receber os parâmetros
    private $operation;         // Operação a ser executada - Select / Insert / Update / Delete
    private $dateUpdate;        // Define se será atualizado a Data de Update
    private $json;              // Define se o retorno será em Json
    private $orderBy;           // Campo de ordenação para a query
    private $limit;             // Limit para a query
    private $fieldsVisible;     // Lista dos campos visíveis que serão retornados na query
    private $customFieldSelect; // Campos personalizados para o select (ex: sub-sql)
    private $customWhere;       // Condições personalizadas para o where (ex: exists)
    private $trataAcentos;      // Define se será tratado os acentos no Update e Insert

    /**
     * DB constructor.
     * @param      $host
     * @param      $user
     * @param      $pass
     * @param      $dbName
     * @param bool $json
     */
    public function __construct($host, $user, $pass, $dbName, $json = false){
        GLOBAL $jsonStatus, $smarty;

        try
        {
            $this->dataBase = new mysqli($host, $user, $pass, $dbName);
            $this->dataBase->query("SET `time_zone` = '".date('P')."'");

            mysqli_set_charset($this->dataBase, "utf8");
        }
        catch (mysqli_sql_exception $e)
        {
            if (mysqli_connect_errno()) {
                if ( $json ){
                    header('HTTP/1.0 420 Method failed');
                    Json::get( $jsonStatus[401], array("message" => "Não foi possível conectar ao Bando de Dados.", "description" => $e->getMessage() ) );
                } else {
                    $smarty->assign( "error", "Não foi possível conectar ao Bando de Dados. " . $e->getMessage() );
                    echo $e->getMessage();
                }
                die();
            }
        }
    }

    /**
     * DB destructor.
     */
    public function __destruct()
    {
        if ($this->dataBase){
            $this->dataBase->close();
        }
    }

    /**
     * Monta a SQL de Select
     */
    private function mountSqlSelect(){
        $fields = [];
        foreach ($this->fieldsVisible as $field) {
            $fields[] = $field->field_name;
        }
        if ( @$this->customFieldSelect ){
            foreach ($this->customFieldSelect as $field) {
                $fields[] = $field;
            }
        }

        $this->sql = "SELECT " . implode(',', $fields) . " FROM `" . $this->tableName . "` WHERE 1=1";

        foreach ($this->params as $p) {
             $this->sql .= " AND " . $p->field_name . " " . $p->query_operation . " ?" ;
        }

        if ( $this->customWhere ) {
            foreach ($this->customWhere as $where) {
                $this->sql .= " AND " . $where;
            }
        }

        $this->sql .= $this->orderBy ? " ORDER BY " . $this->orderBy : "";
        $this->sql .= $this->limit   ? " LIMIT "    . $this->limit   : "";
    }

    /**
     * Monta a SQL de Insert
     */
    private function mountSqlInsert(){
        $this->sql = "INSERT INTO `" . $this->tableName . "` (";

        $val = "VALUES (";
        foreach ($this->params as $p) {
            $this->sql .= $p->field_name . ( $p === end($this->params) ? ", {$this->tableName}_date ) " : ", " );
            $val       .= " ?"           . ( $p === end($this->params) ? ", now() ) " : ", " );
        }

        $this->sql .= $val;
    }

    /**
     * Monta a SQl de Update
     */
    private function mountSqlUpdate(){
        $this->sql = "UPDATE `" . $this->tableName . "` SET ";

        $field_id = self::getFieldID();

        $set = "";
        foreach ($this->params as $p) {
            if (!$p->field_is_id) {
                $set .= $set > "" ? ", " : "";
                $set .= $p->field_name . " = ?" ;
            }
        }

        if ($this->dateUpdate){
            $set .= ", " . $this->tableName . "_update = now()";
        }

        $this->sql .= $set . " WHERE " . $field_id->field_name . " = ?";
    }

    /**
     * Monta a SQL de Delete
     */
    private function mountSqlDelete(){
        $custom_where = [];

        if (@$this->params['custom_where_delete']){
            $custom_where = $this->params['custom_where_delete'];
            unset( $this->params['custom_where_delete'] );
        };

        $field_id = self::getFieldID();

        $this->sql = "DELETE FROM `" . $this->tableName . "` WHERE " . $field_id->field_name . " = ?";

        if ( @$custom_where ){
            foreach ( $custom_where as $custom_field_where) {
                $this->sql .= " AND " . $custom_field_where->field_name . " = ?";
            }
        }

        // Limpo a lista de parâmetro para garantir que tem apenas a chave primária
        $this->params = [];
        $this->params[$field_id->field_name] = $field_id;

        // Adiciona o where customizado
        if ( @$custom_where ){
            foreach ( $custom_where as $custom_field_where) {
                $this->params[$custom_field_where->field_name] = $custom_field_where;
            }
        }
    }

    /**
     * Pega o Campo de ID
     * @return mixed
     */
    private function getFieldID(){
        GLOBAL $jsonStatus;

        foreach($this->params as $p ){
            if ($p->field_is_id){
                // joga o ID para ultimo no array devido a ordem nos parâmetros da sql de Update
                unset($this->params[$p->field_name]);
                $this->params[$p->field_name] = $p;
                return $p;
            }
        }

        header('HTTP/1.0 401 Unauthorized');
        Json::get( $jsonStatus[401], array("message" => "Chave primária não informada!", "description" => "" ) );
        die();
    }

    /**
     * Pega os campos do tipo String para fazer tratamento de acentos
     */
    private function getFieldsString(){
        $this->fieldString = [];

        while ($field = $this->resultQuery->fetch_field()) {

            if ( ($field->type == 252) || ($field->type == 253) ){
                $this->fieldString[] = $field->name;
            }
        }
    }

    /**
     * Seta os parâmetros para a query
     */
    private function setBind_Params(){

        if ( !sizeof($this->params) ) {
            return;
        }

        $param_type = "";  // Lista do tipo de parâmetro - i = int / d = double / s = string / b = blob
        $param_val  = array();
        foreach($this->params as $key => $value) {
            // tratamento de valores
            $val[$key] = $this->params[$key]->field_type == 'd' ? numberToMysql($this->params[$key]->query_value) : $this->params[$key]->query_value;

            // tratamento do Like para o select
            if ($this->operation == OPERATION_SELECT){
                $val[$key] = $this->params[$key]->query_operation == 'like' ? "%". $val[$key] ."%" : $val[$key];
            }

            if ( $this->trataAcentos && ( ($this->operation == OPERATION_UPDATE) || ($this->operation == OPERATION_INSERT) ) ){
                $val[$key] = utf8_encode($val[$key]);
            }

            $param_type     .= $this->params[$key]->field_type;
            $param_val[$key] = &$val[$key];
        }

        // Adiciona os tipos no inicio do array
        array_unshift($param_val, $param_type);

        // Passa os parâmetros
        call_user_func_array(array($this->stmt, "bind_param"), $param_val);
    }

    /**
     * Executa a query em questão
     */
    private function executeQuery(){
        GLOBAL $jsonStatus, $smarty;

        // Prepara a sql para receber os parâmetros
        if ( $this->stmt = $this->dataBase->prepare($this->sql) ){

            self::setBind_Params();

            // Executa a query
            if (!$this->stmt->execute()) {
                if ( $this->json ){
                    header('HTTP/1.0 420 Method failed');
                    Json::get( $jsonStatus[420], array("message" => "Falha na execução da Método", "description" => $this->stmt->error_list) );
                } else {
                    $smarty->assign( "error", $this->stmt->error_list );
                }
                die();
            }
        } else {
            if ( $this->json ){
                header('HTTP/1.0 420 Method failed');
                Json::get( $jsonStatus[420], array("message" => "Falha na execução da Método", "description" => "MySQL error ({$this->dataBase->errno}):<br> {$this->dataBase->error} <br>Query: {$this->sql}") );
            } else {
                $smarty->assign( "error", "MySQL error ({$this->dataBase->errno}):<br> {$this->dataBase->error} <br>Query: {$this->sql}" );
                echo "MySQL error ({$this->dataBase->errno}):<br> {$this->dataBase->error} <br>Query: {$this->sql}";
            }
            die();
        }
    }

    /**
     * @param $tableName
     * @param $params
     * @param $json
     * @param $fieldsVisible
     * @param $orderBy
     * @param $limit
     * @param $customFieldSelect
     * @param $customWhere
     * @return array
     */
    public function querySelect($tableName, $params, $json, $fieldsVisible, $orderBy, $limit, $customFieldSelect, $customWhere){
        $this->tableName         = $tableName;
        $this->params            = $params;
        $this->json              = $json;
        $this->fieldsVisible     = $fieldsVisible;
        $this->orderBy           = $orderBy;
        $this->limit             = $limit;
        $this->customFieldSelect = $customFieldSelect;
        $this->customWhere       = $customWhere;
        $this->operation         = OPERATION_SELECT;

        self::mountSqlSelect();

        self::executeQuery();

        // Pega o resultado da query
        $this->resultQuery = $this->stmt->get_result();

        self::getFieldsString();

        // Resultado a ser retornado
        $result = [];

        while ( $row = $this->resultQuery->fetch_object() ){

//            // tratamento de acentos para os campos de string
//            foreach ($this->fieldString as $fieldName) {
//                $row->{$fieldName} = utf8_encode($row->{$fieldName});
//            }

            $result[] = $row;
        }

        $this->stmt->close();

        return $result;
    }

    /**
     * @param $tableName
     * @param $params
     * @param $json
     * @param $trataAcentos
     * @return mixed
     */
    public function queryInsert($tableName, $params, $json, $trataAcentos){
        $this->tableName = $tableName;
        $this->params    = $params;
        $this->json      = $json;
        $this->operation = OPERATION_INSERT;
        $this->trataAcentos = $trataAcentos;

        self::mountSqlInsert();

        self::executeQuery();

        return $this->stmt->insert_id;
    }

    /**
     * @param $tableName
     * @param $params
     * @param $json
     * @param $dateUpdate
     * @param $trataAcentos
     */
    public function queryUpdate($tableName, $params, $json, $dateUpdate, $trataAcentos ){
        $this->tableName  = $tableName;
        $this->params     = $params;
        $this->json       = $json;
        $this->operation  = OPERATION_UPDATE;
        $this->dateUpdate = $dateUpdate;
        $this->trataAcentos = $trataAcentos;

        self::mountSqlUpdate();

        self::executeQuery();
    }

    /**
     * @param $tableName
     * @param $params
     * @param $json
     */
    public function queryDelete( $tableName, $params, $json ){
        $this->tableName = $tableName;
        $this->params    = $params;
        $this->json      = $json;
        $this->operation = OPERATION_DELETE;

        self::mountSqlDelete();

        self::executeQuery();
    }
}

