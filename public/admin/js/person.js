var validBank = false;
var newPersonRede = false;
var timer;

$(document).ready(function() {
    setConfigPerson();
});

function setConfigPerson(){

    $('.treeview').removeClass('active');
    $('#menuPerson').addClass('active');

    $('#form_person .select2, #form_bank .select2, #form_rede .select2').select2();
    $('#form_person [data-mask]').inputmask();
    $('.datePicker').datepicker({
        autoclose: true
    });
    $(".datePicker").datepicker().on('show.bs.modal', function(event) {
        // prevent datepicker from firing bootstrap modal "show.bs.modal"
        event.stopPropagation();
    });

    var table = $('#table_person').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 5, 6 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 4 ],
            "width": "92px"
        },{
            "targets": [ 5 ],
            "width": "66px"
        },{
            "targets": [ 6 ],
            "width": "52px"
        },{
            "targets": [ 5, 6 ],
            "width": "30px"
        }],
        "order": [[2, 'asc' ]]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_person_length"]').clone(true);

        $('#table_person_length').find('label').text('');
        $('#table_person_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select).append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    if( $('#personModal').length ){

        var modal = $('#personModal');
        modal.modal({
            show: false,
            backdrop: 'static'
        });
        modal.on('hide.bs.modal', function(e){
            $('#form_person input, #form_person span.select2').each(function(){
                $(this).parents('div.form-group').find('span.erro_message').fadeOut();
            });
            $('.tab_erro_cad').fadeOut();
            $('.tab_erro_address').fadeOut();
            $('.tab_erro_contact').fadeOut();
        });
        modal.on('shown.bs.modal', function () {
            $('.modal-body').scrollTop(0);
        });

    }

    $('a[name="btnDel"]').confirmation({
        title: 'Deseja realmente excluir?',
        placement: 'left',
        btnOkClass: 'btn btn-primary',
        btnCancelClass: 'btn btn-default pull-right',
        btnOkLabel: 'Sim',
        btnCancelLabel: 'Não',
        onConfirm: function(){
            deletePerson($(this).attr('personid'));
        },
        onCancel: function(){

        }
    });

    $('#form_person input, #form_person span.select2').click(function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#form_person').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    $('#person_type').change(function(){
        var person_cnpj = $('#person_cnpj'),
            person_cpf  = $('#person_cpf'),
            person_rg   = $('#person_rg');

        if ($(this).val() == 'F'){
            person_cpf.prop('readonly' , false);
            person_rg.prop('readonly'  , false);
            person_cnpj.prop('readonly', true);
            person_cnpj.val('');
        } else {
            person_cnpj.prop('readonly', false);
            person_cpf.prop('readonly' , true);
            person_rg.prop('readonly'  , true);
            person_cpf.val('');
            person_rg.val('');
        }
    });

}

function cadPerson() {
    var modal = $('#personModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#personModalLabel').text('Cadastrar Pessoa');
        $('#btnSubmit').text('Nova Pessoa');
        $('#lastUpdate').css('display', 'none');
        $('#person_id').val('');
        $('#person_category').val(null).trigger('change');
        $('#person_type').val('F').trigger('change');
        $('.itemAddress').remove();
        newAddress().find('input[name="address_main[]"], input[name="address_delivery[]"]').prop('checked', true).trigger('change');
        $('.itemContact').remove();
        newContact().find('input[name="person_contact_main[]"]').prop('checked', true).trigger('change');
        $('#form_person')[0].reset();
        $('select[name="filter_city[]"]').removeAttr('city_id');
        $('select[name="district_id[]"]').removeAttr('district_id');
        $('#delete_contact').val('');
        $('#delete_address').val('');

        $('#form_bank')[0].reset();
        $('#bank_code').val(null).trigger('change');
        $('#person_bank_type').val(null).trigger('change');

        if( $('#form_rede').length ){
            $('#form_rede')[0].reset();
            $('#rede_type').val(null).trigger('change');
        }
    });

    $('.nav-tabs a[href="#tab_cad"]').tab('show');
    modal.modal('show');
}

function editPerson(personId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/person.php?module=get',
        data: {person_id: personId},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var person = jQuery.parseJSON(data).data,
                modal  = $('#personModal'),
                l_cat  = [];

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#personModalLabel').text('Editar Pessoa');
                $('#btnSubmit').text('Atualizar Pessoa');
                $('#lastUpdate').css('display', person.person_update == null ? 'none' : 'block').text('Última atualização: ' + person.person_update);
                $('#person_id').val(person.person_id);
                $('#person_active').prop('checked', person.person_active == 'Y' );
                $('#person_code').val(person.person_code);
                $('#person_name').val(person.person_name);
                $('#person_nickname').val(person.person_nickname);
                $('#person_type').val(person.person_type).trigger('change');
                $('#person_cnpj').val(person.person_cnpj);
                $('#person_cpf').val(person.person_cpf);
                $('#person_rg').val(person.person_rg);
                $('#person_birth').val(person.person_birth);
                $('#person_gender').val(person.person_gender).trigger('change');

                if( person.bank != null ){
                    $('#person_bank_id').val(person.bank.person_bank_id);
                    $('#bank_code').val(person.bank.bank_code).trigger('change');
                    $('#person_bank_agency').val(person.bank.person_bank_agency).trigger('change');
                    $('#person_bank_account').val(person.bank.person_bank_account).trigger('change');
                    $('#person_bank_type').val(person.bank.person_bank_type).trigger('change');
                } else{
                    $('#form_bank')[0].reset();
                    $('#person_bank_id').val('');
                    $('#bank_code').val(null).trigger('change');
                    $('#person_bank_type').val(null).trigger('change');
                }

                if(person.category){
                    for( var i = 0; i < person.category.length; i++) {
                        l_cat.push(person.category[i].person_category_id);
                    }
                }

                $("#person_category").val(l_cat).trigger('change');

                $('.itemAddress').remove();
                if(person.address){
                    for (var i = 0; i < person.address.length; i++) {
                        var address = newAddress();
                        address.find('input[name="address_id[]"]').val(person.address[i].address_id);
                        address.find('input[name="address_main[]"]').prop('checked', person.address[i].address_main == 'Y');
                        address.find('input[name="address_delivery[]"]').prop('checked', person.address[i].address_delivery == 'Y');
                        address.find('input[name="address_cep[]"]').val(person.address[i].address_cep);
                        address.find('select[name="filter_city[]"]').attr('city_id',person.address[i].District.city_id);
                        address.find('select[name="district_id[]"]').attr('district_id',person.address[i].district_id);
                        address.find('select[name="filter_uf[]"]').val(person.address[i].District.City.UF.uf_code).trigger('change');
                        address.find('input[name="address_public_place[]"]').val(person.address[i].address_public_place);
                        address.find('input[name="address_number[]"]').val(person.address[i].address_number);
                        address.find('input[name="address_complement[]"]').val(person.address[i].address_complement);

                        if( person.address[i].address_main == 'Y' ){
                            address.find('input[name="address_main[]"]').trigger('change');
                        }
                        if( person.address[i].address_delivery == 'Y' ){
                            address.find('input[name="address_delivery[]"]').trigger('change');
                            address.find('.glyphicon-plane').addClass('address-delivery');
                        }
                    }
                }

                $('.itemContact').remove();
                if(person.contact){
                    for( var i = 0; i < person.contact.length; i++ ){
                        var contact = newContact();
                        contact.find('input[name="person_contact_id[]"]').val(person.contact[i].person_contact_id);
                        contact.find('input[name="person_contact_main[]"]').prop('checked', person.contact[i].person_contact_main == 'Y');
                        contact.find('select[name="person_contact_type_id[]"]').val(person.contact[i].person_contact_type_id).trigger('change');
                        contact.find('input[name="person_contact_value[]"]').val(person.contact[i].person_contact_value);
                        contact.find('input[name="person_contact_name[]"]').val(person.contact[i].person_contact_name);
                        if ( person.contact[i].person_contact_main == 'Y' ){
                            contact.find('input[name="person_contact_main[]"]').trigger('change');
                        }
                    }
                }

            });

            $('.nav-tabs a[href="#tab_cad"]').tab('show');
            modal.modal('show');
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function submitPerson() {
    var erro        = false,
        erroAddress = false,
        erroContact = false,
        erroBank    = false,
        erroRede    = false,
        post_url,
        tabCad      = $('.nav-tabs a[href="#tab_cad"]');
        tabAddress  = $('.nav-tabs a[href="#tab_addres"]');
        tabContact  = $('.nav-tabs a[href="#tab_contact"]');
        tabBank     = $('.nav-tabs a[href="#tab_bank"]');
        tabRede     = $('.nav-tabs a[href="#tab_rede"]');

    var person = {
        person_id: $('#person_id').val(),
        person_active: $('#person_active').prop('checked') ? 'on' : null,
        person_name: $('#person_name').val(),
        person_nickname: $('#person_nickname').val(),
        person_type: $('#person_type').val(),
        person_cnpj: $('#person_cnpj').val(),
        person_cpf: $('#person_cpf').val(),
        person_rg: $('#person_rg').val(),
        person_birth: $('#person_birth').val(),
        person_gender: $('#person_gender').val(),
        person_category: $('#person_category').val(),
        person_address: [],
        person_contact: [],
        delete_address: $('#delete_address').val().length ? $('#delete_address').val().split(',') : null,
        delete_contact: $('#delete_contact').val().length ? $('#delete_contact').val().split(',') : null,
        person_bank : {
            person_bank_id: $('#person_bank_id').val(),
            person_id: $('#person_id').val(),
            bank_code: $('#bank_code').val(),
            person_bank_agency: $('#person_bank_agency').val(),
            person_bank_account: $('#person_bank_account').val(),
            person_bank_type: $('#person_bank_type').val()
        },
        person_rede: {
            rede_type: $('#rede_type').val()
        }
    };

    var itemAddress = $('.itemAddress');
    var itemContact = $('.itemContact');
    var msg;

    if( !person.person_name.length ){
        $('#person_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( person.person_type == 'F' && !$('#person_cpf').inputmask("isComplete") ){
        $('#person_cpf').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( person.person_type == 'J' && !$('#person_cnpj').inputmask("isComplete") ){
        $('#person_cnpj').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( $('#person_category').is(':visible') && person.person_category == null ){
        $('#person_category').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        tabCad.tab('show');
        $('.tab_erro_cad').fadeIn();
    } else{
        $('.tab_erro_cad').fadeOut();
    }

    itemAddress.find('.collapse.in').collapse('toggle');

    itemAddress.each(function () {
        var inputCep = $(this).find('input[name="address_cep[]"]');
        var inputPublicPlace = $(this).find('input[name="address_public_place[]"]');
        var inputNumber = $(this).find('input[name="address_number[]"]');
        var address = {
            address_id: $(this).find('input[name="address_id[]"]').val(),
            person_id: person.person_id,
            district_id: $(this).find('select[name="district_id[]"]').val(),
            address_main: $(this).find('input[name="address_main[]"]').prop('checked') ? 'on' : null,
            address_delivery: $(this).find('input[name="address_delivery[]"]').prop('checked') ? 'on' : null,
            address_cep: inputCep.val(),
            address_public_place: inputPublicPlace.val(),
            address_number: inputNumber.val(),
            address_complement: $(this).find('input[name="address_complement[]"]').val()
        };

        var bErroAddress = false;

        // Verifica se o Main esta marcado e se tem algum campo vazio
        if ( ( address.address_main || address.address_delivery ) && ( (address.address_cep == null) || (address.address_public_place == '') || (address.address_number == '')) ){
            bErroAddress = true;
        // Verifica se existe algum campo preenchido, mas n estão todos preenchidos
        } else if ( (
                      ( (address.address_cep != null) || (address.address_public_place != '') || (address.address_number != '') ) &&
                     !( (address.address_cep != null) && (address.address_public_place != '') && (address.address_number != '') )
                    )
        ){
            bErroAddress = true;
        }

        if (bErroAddress){
            $(this).find('a.panel-title').addClass('panel-title-erro').find('span.erro_message_address').text('Endereço incompleto.').fadeIn();

            $('.nav-tabs a[href="#tab_address"]').tab('show');
            erro        = true;
            erroAddress = true;

            if ( address.address_cep == null ){
                inputCep.parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            }
            if ( address.address_public_place == '' ){
                inputPublicPlace.parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            }
            if ( address.address_number == '' ){
                inputNumber.parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            }
        } else {
            $(this).find('a.panel-title').removeClass('panel-title-erro').find('span.erro_message_address').fadeOut();

            if ( (address.address_cep != null) && (address.address_place != '') && (address.address_number != '') ){
                person.person_address.push(address);
            }
        }
    });

    if (erroAddress){
        tabAddress.tab('show');
        $('.tab_erro_address').fadeIn();
    } else{
        $('.tab_erro_address').fadeOut();
    }

    itemContact.find('.collapse.in').collapse('toggle');

    itemContact.each(function () {
        var inputValue = $(this).find('input[name="person_contact_value[]"]');
        var contact = {
            person_contact_id: $(this).find('input[name="person_contact_id[]"]').val(),
            person_id: person.person_id,
            person_contact_type_id: $(this).find('select[name="person_contact_type_id[]"]').val(),
            person_contact_main: $(this).find('input[name="person_contact_main[]"]').prop('checked') ? 'on' : null,
            person_contact_value: inputValue.val(),
            person_contact_name: $(this).find('input[name="person_contact_name[]"]').val()
        };
        var bErroContact = false;

        if ( contact.person_contact_main && ( (contact.person_contact_value == '') ) ) {
            bErroContact = true;
        }

        if (bErroContact){
            $(this).find('a.panel-title').addClass('panel-title-erro').find('span.erro_message_contact').text('Contato incompleto.').fadeIn();

            $('.nav-tabs a[href="#tab_contact"]').tab('show');
            erro        = true;
            erroContact = true;

            if ( contact.person_contact_value == '' ){
                inputValue.parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            }
        } else {
            $(this).find('a.panel-title').removeClass('panel-title-erro').find('span.erro_message_contact').fadeOut();
            if ( (contact.person_contact_value != '') ){
                person.person_contact.push(contact);
            }
        }
    });

    if (erroContact){
        tabContact.tab('show');
        $('.tab_erro_contact').fadeIn();
    } else{
        $('.tab_erro_contact').fadeOut();
    }

    if( validBank ) {
        if ( person.person_bank.bank_code == null ) {
            $('#bank_code').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            erroBank = true;
            erro = true;
        }
        if( !person.person_bank.person_bank_agency.length ){
            $('#person_bank_agency').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            erroBank = true;
            erro = true;
        }
        if( !person.person_bank.person_bank_account.length ){
            $('#person_bank_account').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            erroBank = true;
            erro = true;
        }
        if( person.person_bank.person_bank_type == null ){
            $('#person_bank_type').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            erroBank = true;
            erro = true;
        }
    }

    if (erroBank) {
        tabBank.tab('show');
        $('.tab_erro_bank').fadeIn();
    } else {
        $('.tab_erro_bank').fadeOut();
    }

    if( validBank ) {
        if (person.person_rede.rede_type == null) {
            $('#rede_type').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            erroRede = true;
            erro = true;
        }
    }

    if (erroRede) {
        tabRede.tab('show');
        $('.tab_erro_rede').fadeIn();
    } else {
        $('.tab_erro_rede').fadeOut();
    }

    if (erro){
        $('.modal-body').scrollTop(0);
        clearTimeout(timer);
        timer = setTimeout(function(){
            $('.nav-tabs a i, .erro_message').fadeOut();
        },3000);
        return;
    }

    if( !newPersonRede ) {
        if (person.person_id.length > 1) {
            post_url = URI_PUBLIC + 'admin/person.php?module=edit&person_id=' + person_id;
            msg = 'Pessoa editada com sucesso!';
        } else {
            post_url = URI_PUBLIC + 'admin/person.php?module=insert';
            msg = 'Pessoa adicionada com sucesso!';
        }
    } else{
        post_url = URI_PUBLIC + 'admin/office.php?module=addNewClient';
        msg = 'Pessoa cadastrada na Rede com sucesso!';
    }

    $('.overlay').fadeIn();

    console.log(person);
    $.ajax({
        url: post_url,
        type: 'POST',
        dataType: 'json',
        data: { data: person },
        success: function(data) {
            if( $('#personModal').length ) {
                var modal = $('#personModal');
                modal.on('hidden.bs.modal', function (e) {
                    atualizar();
                });
                $('.overlay').fadeOut();
                modal.modal('hide');
            } else{
                setConfigOfficePerson();
            }

            showAlert('success', msg);
        },
        error: function(data) {
            var result = jQuery.parseJSON(data.responseText).data;

            $('.overlay').fadeOut();

            if ( (result) && (result.error) ){
                result.error_cnpj ? person_cnpj.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn() : '';
                result.error_cpf  ? person_cpf.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn()  : '';
                result.error_rg   ? person_rg.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn()   : '';

                $('.nav-tabs a[href="#tab_cad"]').tab('show');
                showAlert('warning', result.error);
            } else {
                console.log(data);
                alert("Algo de errado ocorreu!");
            }
        }
    });
}

function atualizar(){
    $.ajax({
        url: URI_PUBLIC + 'admin/person.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#personTemplate').html(data).promise().done(function(){
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                setConfigPerson();
            });
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function deletePerson(personId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/person.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {person_id: personId},
        success: function(data) {
            showAlert('success', 'Pessoa excluída com sucesso!');

            atualizar();
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function newContact(){
    var num     = pad($('.itemContact').length +1, 2),
        contact = $('#contactModel').clone(true);

    contact.addClass('itemContact');
    contact.find('input[name="person_contact_id[]"]').val('');

    addNumContact( contact, num );

    $('#accordionContact').append(contact);

    $('.itemContact').find('.collapse.in').collapse('toggle');

    eventContact( contact );

    return contact;
}

function addNumContact( contact, num ){
    contact.css('display', 'block').attr('id', 'itemContact'+num);
    contact.find('div.panel-heading').attr('id', 'headingContact'+num)
        .find('a').attr('href', '#boxContact'+num).attr('aria-controls', 'boxContact'+num)
        .find('span.contact_title').text('Contato ' + num);
    contact.find('div.panel-collapse').attr('id', 'boxContact'+num).attr('aria-labelledby', 'headingContact'+num);
    contact.find('input[name="person_contact_main[]"]').attr('id', 'person_contact_main'+num)
        .parent('div').find('label').attr('for', 'person_contact_main'+num);
}

function eventContact( contact ) {

    contact.find(".select2").select2();

    contact.find('a[name="btnDelContact"]').confirmation({
        title: 'Deseja realmente excluir o Contato?',
        placement: 'left',
        btnOkClass: 'btn btn-primary',
        btnCancelClass: 'btn btn-default pull-right',
        btnOkLabel: 'Sim',
        btnCancelLabel: 'Não',
        onConfirm: function(event, element){
            var count = 1,
                itemContact,
                bMain = element.parents('.itemContact').find('input[name="person_contact_main[]"]').prop('checked');

            element.parents('.itemContact').remove();

            itemContact = $('.itemContact');
            person_contact_id = contact.find('input[name="person_contact_id[]"]').val();
            if( person_contact_id.length ){
                id = $('#delete_contact').val().length ? $('#delete_contact').val().split(',') : new Array();
                id.push( person_contact_id );
                $('#delete_contact').val( id.join(',') );
            }
            if ( itemContact.length > 0 ){
                itemContact.each(function(){
                    addNumContact( $(this), pad(count, 2) );
                    count++;
                });
            } else {
                newContact();
            }

            if ( bMain ){
                $('.itemContact').eq(0).find('input[name="person_contact_main[]"]').prop('checked', true).trigger('change');
            }
        },
        onCancel: function(){

        }
    });

    contact.find('input[name="person_contact_main[]"]').change(function (){
        var itemContact = $('.itemContact');

        itemContact.find('input[name="person_contact_main[]"]').prop('checked', false);

        itemContact.find('.glyphicon-star').removeClass('glyphicon-star').addClass('glyphicon-star-empty');

        $(this).prop('checked', true);
        $(this).parents('.itemContact').find('.glyphicon-star-empty').removeClass('glyphicon-star-empty').addClass('glyphicon-star');
    });

    $('#form_person_contact input, #form_person_contact span.select2').click(function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#personModal').on('hide.bs.modal', function(e) {
        $('#form_person_contact input, #form_person_contact span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });
}