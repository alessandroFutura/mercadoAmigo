<?php


	class City extends Model
	{
		public function __construct( $params )
		{
            //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
		    $this->table = new Table( 'city', "city_name" );

            //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
		    $this->table->addField( 'city_id'    , 'i', '=', true, true);
            $this->table->addField( 'uf_id'      , 'i', '=', true);
            $this->table->addField( 'city_name'  , 's', '=', true);
            $this->table->addField( 'city_ibge'  , 's', '=');
            $this->table->addField( 'city_update', 's', '=');
            $this->table->addField( 'city_date'  , 's', '=');

            // get_UserProfile
            $this->table->fields["uf_id"]->link_class   = "UF";
            $this->table->fields["uf_id"]->link_id_name = "uf_id";
            $this->table->fields["uf_id"]->link_type    = "get";

            parent::__construct( $params );
		}

        public function setWhereUfCode( $uf_code ){
            $sql = "uf_id = (SELECT uf.uf_id FROM uf WHERE uf.uf_code = '{$uf_code}')";

            $this->customWhere[] = $sql;
        }
	}

