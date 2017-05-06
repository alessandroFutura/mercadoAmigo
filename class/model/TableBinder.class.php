<?php


class TableBinder
{
    public $class_name;  // nome da classe da tabela de ligação
    public $field_name;  // nome do campo da tabela de ligação
    public $return_type; // Tipo de retorno get ou getList

    /**
     * Exemplo: address
     * class_name = Address (nome da classe que contem a regras da tabela)
     * field_name = person_id
     * param_name = getList
     */

    /**
     * TableBinder constructor.
     * @param $class_name
     * @param $field_name
     * @param $return_type
     */
    public function __construct( $class_name, $field_name, $return_type ){
        $this->class_name  = $class_name;
        $this->field_name  = $field_name;
        $this->return_type = $return_type;
    }

}

