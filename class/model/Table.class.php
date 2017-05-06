<?php

/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 28/12/2016
 * Time: 12:05
 */
class Table
{
    public $table_name;     // Nome da Tabela
    public $allow_insert;   // Se é permitido fazer Insert na tabela
    public $allow_update;   // Se é permitido fazer Update na tabela
    public $allow_delete;   // Se é permitido fazer Delete na tabela
    public $fields;         // Lista de campos da tabela - classe Field.class
    public $orderByDefault; // Ordenação padrão para o Select
    public $table_link;     // Lista de tabelas de ligação um para muitos

    /**
     * Table constructor.
     * @param      $table_name
     * @param      $orderByDefault
     * @param bool $allow_insert
     * @param bool $allow_update
     * @param bool $allow_delete
     */
    public function __construct( $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true ){
        $this->table_name     = $table_name;
        $this->orderByDefault = $orderByDefault;
        $this->allow_insert   = $allow_insert;
        $this->allow_update   = $allow_update;
        $this->allow_delete   = $allow_delete;
        $this->fields         = [];
        $this->table_link     = [];
    }

    /**
     * @param      $field_name
     * @param      $field_type
     * @param      $query_operation
     * @param bool $field_required
     * @param bool $field_is_id
     * @param bool $field_logic
     * @param bool $query_visible
     */
    public function addField( $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true ){

        $this->fields[$field_name] = new Field( $field_name, $field_type, $query_operation, $field_required, $field_is_id, $field_logic, $query_visible );

    }

    /**
     * @param $class_name
     * @param $param_name
     * @param $table_name
     * @param $field_name
     */
    public function addTableLink( $class_name, $param_name, $table_name, $field_name ){

        $this->table_link[] = new TableLink( $class_name, $param_name, $table_name, $field_name );

    }
}