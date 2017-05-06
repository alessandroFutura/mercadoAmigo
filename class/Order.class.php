<?php


class Order extends Model
{
    /**
     * User constructor.
     * @param $params
     */
    public function __construct($params )
    {
        //parâmetros - $table_name, $orderByDefault, $allow_insert = true, $allow_update = true, $allow_delete = true
        $this->table = new Table( 'order', 'order_code' );

        //parâmetros - $field_name, $field_type, $query_operation, $field_required = false, $field_is_id = false, $field_logic = false, $query_visible = true
        $this->table->addField( 'order_id'           , 'i', '=', true, true);
        $this->table->addField( 'order_status_id'    , 'i', '=', true);
        $this->table->addField( 'user_id'            , 'i', '=', true);
        $this->table->addField( 'person_id'          , 'i', '=', true);
        $this->table->addField( 'order_code'         , 's', '=', true);
        $this->table->addField( 'order_addition'     , 'd', '=', true);
        $this->table->addField( 'order_discount'     , 'd', '=', true);
        $this->table->addField( 'order_observation'  , 's', '=');
        $this->table->addField( 'order_update'       , 's', '=');
        $this->table->addField( 'order_date'         , 's', '=');

        // get_OrderStatus
        $this->table->fields["order_status_id"]->link_class   = "OrderStatus";
        $this->table->fields["order_status_id"]->link_id_name = "order_status_id";
        $this->table->fields["order_status_id"]->link_type    = "get";

        // get_User
        $this->table->fields["user_id"]->link_class   = "User";
        $this->table->fields["user_id"]->link_id_name = "user_id";
        $this->table->fields["user_id"]->link_type    = "get";

        // get_Person
        $this->table->fields["person_id"]->link_class   = "Person";
        $this->table->fields["person_id"]->link_id_name = "person_id";
        $this->table->fields["person_id"]->link_type    = "get";

        // get_ProductUnit
        $this->table->fields["order_id"]->link_class   = "OrderItem";
        $this->table->fields["order_id"]->link_id_name = "order_id";
        $this->table->fields["order_id"]->link_type    = "getList";

        parent::__construct( $params );
    }

    public function getMessage( $order, $params )
    {
        GLOBAL $user;

        $order->order_value_items = 0;
        if( @$order->OrderItem ){
            foreach( $order->OrderItem as $item ){
                $order->order_value_items += $item->order_item_amount * $item->order_item_value;
            }
        }
        $order->order_value_total = number_format($order->order_value_items + $order->order_addition - $order->order_discount,2,'.','');

        $style = (Object)Array(
            "h" => "
                    font-family: sans-serif;
                    color: #025935;
                    text-align: center;
                    margin-top: 60px;
                ",
            "p" => "
                    font-family: sans-serif;
                    color: #025935;
                    text-align: center;
                    font-size: 14px;
                ",
            "div" => "
                    display: block; 
                    width: 70%; 
                    margin: 60px auto 0 auto;
                ",
            "table" => "
                    width: 100%; 
                    border: 1px solid #013721;
                    border-left: none;
                    border-bottom: none;
                    font-family: sans-serif
                ",
            "th" => "
                    padding:4px; 
                    background-color: #025935; 
                    color: white; 
                    border: 1px solid #013721;
                    border-top: none;
                    border-right: none;
                    padding: 7px;
                    letter-spacing: 1px;
                ",
            "td" => "
                    border:1px solid #013721;
                    border-top: none;
                    border-right: none;
                    padding: 7px;
                    text-align: center;
                "
        );

        $head = " <meta charset='UTF-8'><h1 style='{$style->h}'>{$params["title"]}</h3>
                      <p style='{$style->p}'>Olá! Um novo pedido foi realizado através do Escritório Virtual do Mercado Amigo!</p> 
                      <p style='{$style->p}'>Seguem informações do pedido:</p>";
        $body = "";
        $foot = "";
        $date = date("d/m/Y H:i:s");

        $head .= "
                <div style='{$style->div}'>
                   <h2 style='{$style->h}'>Usuário</h3>
                   <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                       <thead>
                           <th style='{$style->th}'>Nome</th>
                           <th style='{$style->th}'>Perfil</th>
                           <th style='{$style->th}'>Data</th>  
                       <thead>
                       <tbody>
                           <tr>
                               <td style='{$style->td}'>{$user->user_name}</td>
                               <td style='{$style->td}'>{$user->UserProfile->user_profile_name}</td>
                               <td style='{$style->td}'>{$date}</td>
                           </tr>
                       </tbody>
                   </table>
                </div>
                <h3>Cliente</h3>
                <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                    <thead>
                        <th style='{$style->th}'>Código</th>
                        <th style='{$style->th}'>Nome</th>
                        <th style='{$style->th}'>Documento</th>                     
                    <thead>
                    <tbody>
                        <tr>
                            <td style='{$style->td}'>{$order->Person->person_code}</td>
                            <td style='{$style->td}'>{$order->Person->person_name}</td>
                            <td style='{$style->td}'>{$order->Person->person_cpf}</td>
                        </tr>
                    </tbody>
                </table>
                <h3>Pedido</h3>
                <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                    <thead>
                        <th style='{$style->th}'>Código</th>
                        <th style='{$style->th}'>Status</th>
                        <th style='{$style->th}'>Acrescimo</th>                       
                        <th style='{$style->th}'>Desconto</th>                       
                        <th style='{$style->th}'>Total</th>                       
                    <thead>
                    <tbody>
                        <tr>
                            <td style='{$style->td}'>{$order->order_code}</td>
                            <td style='{$style->td}'>{$order->OrderStatus->order_status_name}</td>
                            <td style='{$style->td}'>{$order->order_addition}</td>
                            <td style='{$style->td}'>{$order->order_discount}</td>
                            <td style='{$style->td}'><b>{$order->order_value_total}</b></td>
                        </tr>
                    </tbody>
                </table>
            ";

        foreach( $order->OrderItem as $key => $item ){
            $index = $key+1;
            $body .= "
                    <tr>
                        <td style='{$style->td}'>{$index}</td>
                        <td style='{$style->td}'>{$item->Kit->kit_name}</td>
                        <td style='{$style->td}'>{$item->order_item_amount}</td>
                        <td style='{$style->td}'>{$item->order_item_value_total}</td>
                    </tr>
                ";
        }

        $body = "
                <h3>Itens</h3>
                <table cellspacing='0' cellpadding='0' style='{$style->table}'>
                    <thead>
                        <th style='{$style->th}'>&nbsp;</th>
                        <th style='{$style->th}'>Kit</th>
                        <th style='{$style->th}'>Quantidade</th>
                        <th style='{$style->th}'>Valor Total</th>
                    <thead>
                    <tbody>
                        {$body}
                    </tbody>
                </table>
            ";

        return $head . $body . $foot;
    }

}