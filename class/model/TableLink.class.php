<?php


class TableLink
{
    public $class_name; // nome da classe da tabela de ligação
    public $param_name; // nome do parametro passado
    public $table_name; // nome da tabela de ligação
    public $field_name; // nome do campo da tabela de ligação
    public $custom_fields; // Campo customizados

    /**
     * Exemplo: person_category_link
     * class_name = PersonCategoryLink (nome da classe que contem a regras da tabela de ligação)
     * param_name = person_category (parâmetro passado pelo front-end)
     * field_name = category_id
     * $custom_fields = ( 'category_type', 's', '1' )
     */

    /**
     * TableLink constructor.
     * @param $class_name
     * @param $param_name
     * @param $table_name
     * @param $field_name
     */
    public function __construct(  $class_name, $param_name, $table_name, $field_name ){
        $this->class_name = $class_name;
        $this->param_name = $param_name;
        $this->table_name = $table_name;
        $this->field_name = $field_name;
    }

    /**
     * @param $field_name
     * @param $field_type
     * @param $field_value
     */
    public function addCustomField( $field_name, $field_type, $field_value ){

        $this->custom_fields[$field_name] = new Field( $field_name, $field_type, '' );
        $this->custom_fields[$field_name]->query_value = $field_value;

    }
}

