<?php
	/*
	PAGINA INICIAL DO ADMIN
	*/

	// iniciar as configurações do site
	include "../../config/start.php";

    // verificar admin na variavel de sessão
    Session::isUser();

	if( $user->user_profile_id != 1001 ){
		header("Location: " . URI_PUBLIC_ADMIN . "escritorio/" );
		die();
	}

    $user = new User([]);
    unset($user->table->fields);
	$user->table->addField( 'user_id', 'i', '=', true, true );
	$user->table->addField( 'user_active', 's', '=', true, false, true);
	$user->customFieldSelect[0] = '(select count(*) from user) as count_users';
	$user = $user->get();

	$receivable = new Receivable([]);
	unset($receivable->table->fields);
	$receivable->table->addField( 'receivable_id', 'i', '=', true, true );
	$receivable->customFieldSelect[0] = '(select count(*) from receivable) as count_receivables';
	$receivable = $receivable->get();

	$account = new CheckingAccount([]);
	unset($account->table->fields);
	$account->table->addField( 'checking_account_id', 'i', '=', true, true );
	$account->customFieldSelect[0] = '(select count(*) from checking_account) as count_account';
	$account = $account->get();

	$kit = new Kit([]);
	unset($kit->table->fields);
	$kit->table->addField( 'kit_id', 'i', '=', true, true );
	$kit->customFieldSelect[0] = '(select count(*) from kit) as count_kits';
	$kit = $kit->get();

	$product = new Product([]);
	unset($product->table->fields);
	$product->table->addField( 'product_id', 'i', '=', true, true );
	$product->customFieldSelect[0] = '(select count(*) from product) as count_products';
	$product = $product->get();

	$order = new Order([]);
	unset($order->table->fields);
	$order->table->addField( 'order_id', 'i', '=', true, true );
	$order->customFieldSelect[0] = '(select count(*) from `order`) as count_orders';
	$order = $order->get();

	$person = new Person([]);
	unset($person->table->fields);
	$person->table->addField( 'person_id', 'i', '=', true, true );
	$person->customFieldSelect[0] = '(select count(*) from `person`) as count_persons';
	$person = $person->get();

	$uf = new UF([]);
	unset($uf->table->fields);
	$uf->table->addField( 'uf_id', 'i', '=', true, true );
	$uf->customFieldSelect[0] = '(select count(*) from `uf`) as count_ufs';
	$uf = $uf->get();

	$city = new City([]);
	unset($city->table->fields);
	$city->table->addField( 'city_id', 'i', '=', true, true );
	$city->customFieldSelect[0] = '(select count(*) from `city`) as count_citys';
	$city = $city->get();

	$district = new District([]);
	unset($district->table->fields);
	$district->table->addField( 'district_id', 'i', '=', true, true );
	$district->customFieldSelect[0] = '(select count(*) from `district`) as count_districts';
	$district = $district->get();

	$smarty->assign( "count_users", @$user->count_users ? $user->count_users : 0 );
	$smarty->assign( "count_receivables", @$receivable->count_receivables ? $receivable->count_receivables : 0 );
	$smarty->assign( "count_account", @$account->count_account ? $account->count_account : 0 );
	$smarty->assign( "count_kits", @$kit->count_kits ? $kit->count_kits : 0 );
	$smarty->assign( "count_products", @$product->count_products ? $product->count_products : 0 );
	$smarty->assign( "count_orders", @$order->count_orders ? $order->count_orders : 0 );
	$smarty->assign( "count_persons", @$person->count_persons ? $person->count_persons : 0 );
	$smarty->assign( "count_ufs", @$uf->count_ufs ? $uf->count_ufs : 0 );
	$smarty->assign( "count_citys", @$city->count_citys ? $city->count_citys : 0 );
	$smarty->assign( "count_districts", @$district->count_districts ? $district->count_districts : 0 );

	$smarty->assign("page_name", "Home");
	$smarty->assign("page_description", "Página Inicial");

	// chamando o template da home
	$smarty->display( PATH_TEMPLATES_ADMIN ."home.html" );
?>
