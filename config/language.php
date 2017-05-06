<?php

$status = Array(
    "pt" => Array(
        200 => (Object)[
            "code" => 200,
            "message" => "Ok."
        ],
        203 => (Object)[
            "code" => 203,
            "message" => "Não-autorizado."
        ],
        400 => (Object)[
            "code" => 400,
            "message" => "Requisição inválida."
        ],
        401 => (Object)[
            "code" => 401,
            "message" => "Não autorizado."
        ],
        404 => (Object)[
            "code" => 404,
            "message" => "Não encontrado."
        ],
        417 => (Object)[
            "code" => 417,
            "message" => "Falha na expectativa."
        ],
        420 => (Object)[
            "code" => 420,
            "message" => "Falha no Método."
        ]
    )
);

$error = Array(
    "pt" => Array(
        "disabled_user"      => "O usuário está desativado.",
        "no_parameters_post" => "Parâmetro POST não encontrado.",
        "no_parameters_get"  => "Parâmetro GET não encontrado.",
        "not_found"          => "Nenhum usuário encontrado.",
        "unauthenticated"    => "Usuário não autenticado.",
        "logout"             => "Usuário deslogado com sucesso!",
        "session_not_found"  => "Nenhuma sessão de usuário encontrada.",
        "sql_error"          => "Erro na execução sql.",
        "event_duplicate"    => "Evento duplicado! Por favor, verifique.",
        "slide_integrate"    => "Existem slides vinculados a categoria! Por favor, verifiquei!",
        "district_integrate" => "Existem CEPs vinculados ao bairro! Por favor, verifiquei!",
        "city_integrate"     => "Existem CEPs vinculados a cidade! Por favor, verifiquei!",
        "cep_integrate"      => "Existem Endereços vinculados ao CEP! Por favor, verifiquei!"
    )
);
//
//foreach( $status as $st )
//{
//    foreach( $st as $t )
//    {
//        $t->message = utf8_encode($t->message);
//    }
//}
//
//foreach( $error as &$er )
//{
//    foreach( $er as &$e )
//    {
//        $e = utf8_encode($e);
//    }
//}

