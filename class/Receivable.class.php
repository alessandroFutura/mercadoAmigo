<?php


	class Receivable extends Model
	{
        /**
         * User constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $receivableByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'receivable', 'receivable_code' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'receivable_id'           , 'i', '=', true, true);
            $this->table->addField( 'order_id'                , 'i', '=', true);
		    $this->table->addField( 'person_id'               , 'i', '=', true);
            $this->table->addField( 'modality_id'             , 'i', '=', true);
            $this->table->addField( 'receivable_code'         , 's', '=', true);
            $this->table->addField( 'receivable_value'        , 'd', '=', true);
            $this->table->addField( 'receivable_deadline'     , 's', '=');
            $this->table->addField( 'receivable_payment_date' , 's', '=');
            $this->table->addField( 'receivable_drop'         , 's', '=', true, false, true);
            $this->table->addField( 'receivable_drop_date'    , 's', '=');
            $this->table->addField( 'receivable_file'         , 's', '=');
            $this->table->addField( 'receivable_update'       , 's', '=');
            $this->table->addField( 'receivable_date'         , 's', '=');

            // get_ReceivableStatus
            $this->table->fields["order_id"]->link_class   = "Order";
            $this->table->fields["order_id"]->link_id_name = "order_id";
            $this->table->fields["order_id"]->link_type    = "get";

            // get_Person
            $this->table->fields["person_id"]->link_class   = "Person";
            $this->table->fields["person_id"]->link_id_name = "person_id";
            $this->table->fields["person_id"]->link_type    = "get";

            parent::__construct( $params );
		}

	}

