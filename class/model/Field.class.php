<?php

/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 28/12/2016
 * Time: 12:10
 */
class Field
{
    public $field_name;        // Nome do Campo
    public $field_is_id;       // Se o campo é o ID da tabela
    public $field_type;        // Tipo de parâmetro - i = int / d = double / s = string / b = blob
    public $field_required;    // Se o campo é Obrigatório
    public $field_logic;       // Se true, será passado para a query "Y" se for <> de null ou "N" caso seja = null
    public $field_unique;      // Define que não poderá haver nenhum registro com o mesmo valor
    public $field_unique_msg;  // Mensagem de erro a ser apresentada
    public $query_operation;   // Operador a ser utilizado na query ( = / > / < / <> / >= / like ...)
    public $query_value;       // Valor a ser passado na query
    public $query_visible;     // O campo será retornado na query
    public $link_class;        // Classe de ligação com o campo
    public $link_id_name;      // Nome do campo de ID da tabela de ligação
    public $link_type;         // Tipo de retorno Get ou GetList
    public $func_process_value;// Função para processamento do valor a ser retornado

    /**
     * Field constructor.
     * @param      $field_name
     * @param      $field_type
     * @param      $query_operation
     * @param bool $field_required
     * @param bool $field_is_id
     * @param bool $field_logic
     * @param bool $query_visible
     */
    public function __construct($field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true){
        $this->field_name       = $field_name;
        $this->field_is_id      = $field_is_id;
        $this->field_type       = $field_type;
        $this->field_required   = $field_required;
        $this->field_logic      = $field_logic;
        $this->query_operation  = $query_operation;
        $this->query_visible    = $query_visible;
        $this->field_unique     = false;

        // apenas para garantir o que a chave primária esteja como obrigatória
        $this->field_required = $field_is_id ? true : $field_is_id;
    }
}