<?php


class ProductCost extends Model
{
    public function __construct( $params )
    {
        //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
        $this->table = new Table( 'product_cost', "product_cost_date DESC" );

        //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
        $this->table->addField( 'product_cost_id'    , 'i', '=', true, true);
        $this->table->addField( 'user_id'            , 'i', '=', true);
        $this->table->addField( 'provider_id'        , 'i', '=', true);
        $this->table->addField( 'product_id'         , 'i', '=', true);
        $this->table->addField( 'product_cost_value' , 'd', '=');
        $this->table->addField( 'product_cost_date'  , 's', '=');

        // get_User
        $this->table->fields["user_id"]->link_class   = "User";
        $this->table->fields["user_id"]->link_id_name = "user_id";
        $this->table->fields["user_id"]->link_type    = "get";

        // get_Person
        $this->table->fields["provider_id"]->link_class   = "Person";
        $this->table->fields["provider_id"]->link_id_name = "person_id";
        $this->table->fields["provider_id"]->link_type    = "get";

        parent::__construct( $params );
    }

}

