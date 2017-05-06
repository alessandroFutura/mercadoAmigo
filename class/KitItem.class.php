<?php


	class KitItem extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'kit_item', 'kit_item_id' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'kit_item_id'           , 'i', '=', true, true);
            $this->table->addField( 'kit_id'                , 'i', '=', true);
            $this->table->addField( 'product_id'            , 'i', '=', true);
            $this->table->addField( 'kit_item_amount'       , 'i', '=', true);
            $this->table->addField( 'kit_item_value'        , 'd', '=', true);
            $this->table->addField( 'kit_item_value_total'  , 'd', '=', true);
            $this->table->addField( 'kit_item_update'       , 's', '=');
            $this->table->addField( 'kit_item_date'         , 's', '=');

            // get_Product
            $this->table->fields["product_id"]->link_class   = "Product";
            $this->table->fields["product_id"]->link_id_name = "product_id";
            $this->table->fields["product_id"]->link_type    = "get";

            parent::__construct( $params );
		}

	}

