<?php


	class ProductUnit extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'product_unit', "product_unit_name" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'product_unit_id'    , 'i', '=', true, true);
            $this->table->addField( 'product_unit_code'  , 's', '=', true);
            $this->table->addField( 'product_unit_name'  , 's', '=', true);
            $this->table->addField( 'product_unit_update', 's', '=');
            $this->table->addField( 'product_unit_date'  , 's', '=');

            self::addCustomFieldSelect('(select count(product_id) from product where product.product_unit_id = product_unit.product_unit_id) as count_products');

            parent::__construct( $params );
		}

	}

