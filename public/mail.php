<?php

    include "../config/start.php";

    if( !@$_GET || !@$_POST ){
        header('HTTP/1.0 417 Expectation failed');
        die();
    }

//    header('Content-Type: application/json');
//    header('Access-Control-Allow-Origin: *');
//    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, x-session-token');

    switch( $_GET["action"] ) {
        case "contact":
            $mail = new Mail(Array(
                "subject" => utf8_encode("Nova Mensagem de Contato Através do Site"),
                "recipients" => Array(
                    Array(
                        "mail_address" => "contato@omercadoamigo.com.br",
                        "mail_recipient" => utf8_encode("O Mercado Amigo")
                    )
                ),
                "message" => "
                    <b>Nome:</b> {$_POST["name"]}<br/>
                    <b>Email:</b> {$_POST["email"]}<br/>
                    <b>Mensagem:</b> {$_POST["message"]}<br/>
                "
            ));

            if( !$mail->sendMail() ){
                header('HTTP/1.0 420 Method Failure');
            }
        break;
        case "register":

            $pessoa = (Object)$_POST;
            $pessoa->dados = (Object)$pessoa->dados;
            $pessoa->endereco = (Object)$pessoa->endereco;
            $pessoa->endereco->estado = (Object)$pessoa->endereco->estado;
            $pessoa->endereco->cidade = (Object)$pessoa->endereco->cidade;
            $pessoa->endereco->bairro = (Object)$pessoa->endereco->bairro;
            $pessoa->entrega = (Object)$pessoa->entrega;
            $pessoa->entrega->estado = (Object)$pessoa->entrega->estado;
            $pessoa->entrega->cidade = (Object)$pessoa->entrega->cidade;
            $pessoa->entrega->bairro = (Object)$pessoa->entrega->bairro;
            $pessoa->contato = (Object)$pessoa->contato;
            $pessoa->contato->cel1 = (Object)$pessoa->contato->cel1;
            $pessoa->contato->cel2 = (Object)$pessoa->contato->cel2;
            $pessoa->bancario = (Object)$pessoa->bancario;

            $code = new Code(Array(
                "code_name" => "person_code"
            ));
            $code = $code->get();

            $pessoa->dados->codigo = substr( "00000{$code->code_value}", -6 );

            $code = new Code(Array(
                "code_id" => $code->code_id,
                "code_value" => ( $code->code_value + 1 ),
            ));
            $code->update();

            $person = new Person(Array(
                "person_active" => "on",
                "person_code" => $pessoa->dados->codigo,
                "person_type" => "F",
                "person_cpf" => $pessoa->dados->cpf,
                "person_rg" => $pessoa->dados->rg,
                "person_name" => $pessoa->dados->nome,
                "person_gender" => $pessoa->dados->sexo,
                "person_birth" => toUsDateFormat($pessoa->dados->nascimento)
            ));
            $pessoa->dados->id = $person->insert();

            $address = new Address(Array(
                "person_id" => $pessoa->dados->id,
                "district_id" => $pessoa->endereco->bairro->id,
                "address_main" => "on",
                "address_delivery" => ( $pessoa->enderecos_iguais == "true" ? "on" : NULL ),
                "address_cep" => $pessoa->endereco->cep,
                "address_public_place" => $pessoa->endereco->logradouro,
                "address_number" => $pessoa->endereco->numero,
                "address_complement" => ( $pessoa->endereco->complemento ? $pessoa->endereco->complemento : NULL )
            ));
            $address->insert();

            if( $pessoa->enderecos_iguais == "false" ) {
                $address = new Address(Array(
                    "person_id" => $pessoa->dados->id,
                    "district_id" => $pessoa->entrega->bairro->id,
                    "address_main" => NULL,
                    "address_delivery" => "on",
                    "address_cep" => $pessoa->entrega->cep,
                    "address_public_place" => $pessoa->entrega->logradouro,
                    "address_number" => $pessoa->entrega->numero,
                    "address_complement" => ($pessoa->entrega->complemento ? $pessoa->entrega->complemento : NULL)
                ));
                $address->insert();
            }

            $bank_code = explode( " - ", $pessoa->bancario->banco );
            $person_bank = new PersonBank(Array(
                "person_id" => $pessoa->dados->id,
                "bank_code" => $bank_code[0],
                "person_bank_agency" => $pessoa->bancario->agencia,
                "person_bank_account" => $pessoa->bancario->conta,
                "person_bank_type" => ( $pessoa->bancario->tipo == "Conta Corrente" ? "CC" : "CP" )
            ));
            $person_bank->insert();

            $person_category_link = new PersonCategoryLink(Array(
                "person_id" => $pessoa->dados->id,
                "person_category_id" => 1004
            ));
            $person_category_link->insert();

            $person_contact = Array(
                Array(
                    "person_id" => $pessoa->dados->id,
                    "person_contact_type_id" => 1003,
                    "person_contact_main" => "Y",
                    "person_contact_value" => $pessoa->contato->email,
                    "person_contact_name" => NULL
                ), Array(
                    "person_id" => $pessoa->dados->id,
                    "person_contact_type_id" => 1001,
                    "person_contact_main" => "N",
                    "person_contact_value" => $pessoa->contato->cel1->numero,
                    "person_contact_name" => "{$pessoa->contato->cel1->operadora} WhatsApp({$pessoa->contato->cel1->whatsapp})"
                )
            );
            if( @$pessoa->contato->telefone ){
                $person_contact[] = Array(
                    "person_id" => $pessoa->dados->id,
                    "person_contact_type_id" => 1002,
                    "person_contact_main" => "N",
                    "person_contact_value" => $pessoa->contato->telefone,
                    "person_contact_name" => NULL
                );
            }
            if( @$pessoa->contato->cel2->numero ){
                $person_contact[] = Array(
                    "person_id" => $pessoa->dados->id,
                    "person_contact_type_id" => 1001,
                    "person_contact_main" => "N",
                    "person_contact_value" => $pessoa->contato->cel2->numero,
                    "person_contact_name" => ( $pessoa->contato->cel2->operadora ? "{$pessoa->contato->cel2->operadora} " : "" ) . ( $pessoa->contato->cel2->whatsapp ? " WhatsApp({$pessoa->contato->cel2->whatsapp})" : "" )
                );
            }

            foreach( $person_contact as $pc ){
                $contact = new PersonContact($pc);
                $contact->insert();
            }

            $message = "";
            $noInfo = "<i>não informado</i>";

            $equalAddress = $_POST["enderecos_iguais"];
            unset($_POST["enderecos_iguais"]);

            $message .= "<b>DADOS PESSOAIS</b><br/>";
            $message .= "<b>Código:</b> {$pessoa->dados->codigo}<br/>";
            $message .= "<b>Nome:</b> {$pessoa->dados->nome}<br/>";
            $message .= "<b>CPF:</b> {$pessoa->dados->cpf}<br/>";
            $message .= "<b>RG:</b> {$pessoa->dados->rg}<br/>";
            $message .= "<b>Data de Nascimento:</b> {$pessoa->dados->nascimento}</br>";
            $message .= "<b>Sexo:</b> {$pessoa->dados->sexo}</br>";
            $message .= "<br/>";
            $message .= "<b>ENDEREÇO PESSOAL</b><br/>";
            $message .= "<b>CEP:</b> {$pessoa->endereco->cep}<br/>";
            $message .= "<b>Estado:</b> {$pessoa->endereco->estado->uf} - {$pessoa->endereco->estado->nome}<br/>";
            $message .= "<b>Cidade:</b> {$pessoa->endereco->cidade->nome}<br/>";
            $message .= "<b>Bairro:</b> {$pessoa->endereco->bairro->nome}<br/>";
            $message .= "<b>EndereÇo:</b> {$pessoa->endereco->logradouro}<br/>";
            $message .= "<b>NÚmero:</b> {$pessoa->endereco->numero}<br/>";
            $message .= "<b>Complemento:</b> " . ( $pessoa->endereco->complemento ? $pessoa->endereco->complemento : $noInfo ) . "<br/>";
            $message .= "<br/>";
            $message .= "<b>ENDEREÇO DE ENTREGA</b><br/>";
            $message .= "<b>CEP:</b> {$pessoa->entrega->cep}<br/>";
            $message .= "<b>Estado:</b> {$pessoa->entrega->estado->uf} - {$pessoa->entrega->estado->nome}<br/>";
            $message .= "<b>Cidade:</b> {$pessoa->entrega->cidade->nome}<br/>";
            $message .= "<b>Bairro:</b> {$pessoa->entrega->bairro->nome}<br/>";
            $message .= "<b>Endereço:</b> {$pessoa->entrega->logradouro}<br/>";
            $message .= "<b>Número:</b> {$pessoa->entrega->numero}<br/>";
            $message .= "<b>Complemento:</b> " . ( $pessoa->entrega->complemento ? $pessoa->entrega->complemento : $noInfo ) . "<br/>";
            $message .= "<br/>";
            $message .= "<b>DADOS DE CONTATO</b><br/>";
            $message .= "<b>Email:</b> {$pessoa->contato->email}<br/>";
            $message .= "<b>Telefone:</b> " . ( $pessoa->contato->telefone ? $pessoa->contato->telefone : $noInfo ) . "<br/>";
            $message .= "<b>Celular 1:</b> {$pessoa->contato->cel1->numero}({$pessoa->contato->cel1->operadora}) - WhatsApp({$pessoa->contato->cel1->whatsapp})<br/>";
            $message .= "<b>Celular 2:</b> " . ( $pessoa->contato->cel2->numero ? $pessoa->contato->cel2->numero : $noInfo ) . ( $pessoa->contato->cel2->operadora ? " ({$pessoa->contato->cel1->operadora})" : "" ) . ( $pessoa->contato->cel2->whatsapp ? " - Whatsapp({$pessoa->contato->cel2->whatsapp})" : "" ) . " <br/>";
            $message .= "<br/>";
            $message .= "<b>DADOS BANCÁRIOS</b><br/>";
            $message .= "<b>Banco:</b> {$pessoa->bancario->banco}<br/>";
            $message .= "<b>Agência:</b> {$pessoa->bancario->agencia}<br/>";
            $message .= "<b>Conta:</b> {$pessoa->bancario->conta}<br/>";
            $message .= "<b>Tipo:</b> {$pessoa->bancario->tipo}</br>";

            $message = utf8_encode($message);

            $mail = new Mail(Array(
                "subject" => utf8_encode("Novo Cadastro Através do Site"),
                "recipients" => Array(
                    Array(
                        "mail_address" => "contato@omercadoamigo.com.br",
                        "mail_recipient" => utf8_encode("O Mercado Amigo")
                    )
                ),
                "message" => $message
            ));

            if( !$mail->sendMail() ){
                header('HTTP/1.0 420 Method Failure');
            }

        break;
    }

?>