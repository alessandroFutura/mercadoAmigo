<?php


class PersonCategoryLink extends Model
{
    public function __construct( $params )
    {
        //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
        $this->table = new Table( 'person_category_link', "person_id" );

        //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
        $this->table->addField( 'person_category_link_id'   , 'i', '=', true, true);
        $this->table->addField( 'person_id'                 , 'i', '=', true);
        $this->table->addField( 'person_category_id'        , 'i', '=', true);
        $this->table->addField( 'person_category_link_date' , 's', '=');

        parent::__construct( $params );
    }

}

?>