<?php


	class CheckingAccount extends Model
	{
        /**
         * CheckingAccount constructor.
         * @param $params
         */
        public function __construct($params )
		{
		    //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'checking_account', 'checking_account_date DESC' );

		    //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'checking_account_id'          , 'i', '=', true, true);
            $this->table->addField( 'person_id'                    , 'i', '=', true);
            $this->table->addField( 'person_origin_id'             , 'i', '=', true);
            $this->table->addField( 'receivable_id'                , 'i', '=', true);
            $this->table->addField( 'order_id'                     , 'i', '=', true);
            $this->table->addField( 'modality_id'                  , 'i', '=', true);
            $this->table->addField( 'checking_account_value'       , 's', '=', true);
            $this->table->addField( 'checking_account_status'      , 's', '=', true);
            $this->table->addField( 'checking_account_status_date' , 's', '=');
            $this->table->addField( 'checking_account_update'      , 's', '=');
            $this->table->addField( 'checking_account_date'        , 's', '=');

            // get_Person
            $this->table->fields["person_id"]->link_class   = "Person";
            $this->table->fields["person_id"]->link_id_name = "person_id";
            $this->table->fields["person_id"]->link_type    = "get";

            // get_Receivable
            $this->table->fields["receivable_id"]->link_class   = "Receivable";
            $this->table->fields["receivable_id"]->link_id_name = "receivable_id";
            $this->table->fields["receivable_id"]->link_type    = "get";

            // get_Order
            $this->table->fields["order_id"]->link_class   = "Order";
            $this->table->fields["order_id"]->link_id_name = "order_id";
            $this->table->fields["order_id"]->link_type    = "get";


            parent::__construct( $params );
		}

	}

