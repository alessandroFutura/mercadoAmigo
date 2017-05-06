$(document).ready(function() {
    setConfig();
});

function setConfig(){
    //$('.treeview').removeClass('active');
    $('#menuUser').addClass('active');

    $(".select2").select2();

    var table = $('#table_user').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 5, 6 ],
            "searchable": false,
            "orderable": false
        } ],
        "order": [[0, 'desc' ], [1, 'asc']]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_user_length"]').clone(true);

        $('#table_user_length').find('label').text('');
        $('#table_user_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
            .append('<span class="hidden-xs"> Resultados por página </span>');
    } );


    $('#userModal').modal({
        show: false,
        backdrop: 'static'
    });

    $('a[name="btnDel"]').confirmation({
        title: 'Deseja realmente excluir?',
        placement: 'left',
        btnOkClass: 'btn btn-primary',
        btnCancelClass: 'btn btn-default pull-right',
        btnOkLabel: 'Sim',
        btnCancelLabel: 'Não',
        onConfirm: function(){
            deleteUser($(this).attr('userid'));
        },
        onCancel: function(){

        }
    });

    $('#form_user input, #form_user span.select2').bind("click focus", function(event){
        $(this).parent('div').find('span.erro_message').fadeOut();
    });

    $('#userModal').on('hide.bs.modal', function(e) {
        $('#form_user input, #form_user span.select2').each(function(){
            $(this).parent('div').find('span.erro_message').fadeOut();
        });
    });

    $('#form_user').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });
}

function cadUser() {
    $('#userModal').off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#userModalLabel').text('Cadastrar Usuário');
        $('#btnSubmit').text('Novo Usuário');
        $('#lastUpdate').css('display', 'none');
        $('#user_id').val('');
        $('#user_accept_contract').val('');
        $('#user_pass').val('');
        $('#user_pass_confirm').val('');
        $('#person_id').val(null).trigger('change');
        $('#boxPassword').css('display', 'block');

        $('#form_user')[0].reset();
    });

    $('#userModal').modal('show');
}

function editUser(userId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/user.php?module=edit',
        data: {user_id: userId, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var user = jQuery.parseJSON(data).data,
                modal = $('#userModal'),
                l_business = [];

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#userModalLabel').text('Editar Usuário');
                $('#btnSubmit').text('Atualizar Usuário');
                $('#lastUpdate').css('display', user.user_update == null ? 'none' : 'block').text('Última atualização: ' + user.user_update);
                $('#user_id').val(user.user_id);
                $('#user_accept_contract').val( user.user_accept_contract == "Y" ? "1" : "" );
                $('#user_profile_id').val(user.user_profile_id).trigger('change');
                $('#user_user').val(user.user_user);
                $('#user_name').val(user.user_name);
                $('#user_mail').val(user.user_mail);
                $('#person_id').val(user.person_id).trigger('change');
                $('#user_active').prop('checked', user.user_active == 'Y');
                $('#user_pass').val('');
                $('#user_pass_confirm').val('');
                $('#boxPassword').css('display', 'none');
            });

            modal.modal('show');
        },
        error: function(data) {
            var result = jQuery.parseJSON(data.responseText);

            if ( result ){
                console.log(result);
                showAlert('warning', result.data.message);
            } else {
                console.log(data);
                alert("Algo de errado ocorreu!");
            }
        }
    });
}

function submitUser() {
    var erro      = false,
        user_id   = $('#user_id').val(),
        user_mail = $('#user_mail').val(),
        user_url,
        msg;

    if($.trim($('#user_user').val()) == ''){
        $('#user_user').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    } else if (!validar($('#user_user').val())){
        $('#user_user').parent('div').find('span.erro_message').text('Usuário inválido.').fadeIn();
        erro = true;
    }

    if( $('#user_profile_id').val() == null ){
        $('#user_profile_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if( $('#person_id').val() == null ){
        $('#person_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#user_name').val()) == ''){
        $('#user_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if($.trim($('#user_mail').val()) == ''){
        $('#user_mail').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    } else if( (user_mail.indexOf('@') == -1) || (user_mail.substr(user_mail.indexOf('@') + 1, user_mail.length).indexOf('.') == -1) ){
        $('#user_mail').parent('div').find('span.erro_message').text('E-mail inválido.').fadeIn();
        erro = true;
    }

    if (user_id.length == 0) {
        if( $('#user_pass').val() == '' && !user_id.length ){
            $('#user_pass').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            erro = true;
        } else if($.trim($('#user_pass').val()).length < 4){
            $('#user_pass').parent('div').find('span.erro_message').text('A senha deve conter no mínimo 4 caracteres.').fadeIn();
            erro = true;
        }
        if( ($.trim($('#user_pass').val()) == '') || ( $.trim($('#user_pass_confirm').val()) != $.trim($('#user_pass').val()) ) ){
            $('#user_pass_confirm').parent('div').find('span.erro_message').text('As senhas não conferem.').fadeIn();
            erro = true;
        }
    } else{
        if( $.trim($('#user_pass_confirm').val()) != $.trim($('#user_pass').val()) ){
            $('#user_pass_confirm').parent('div').find('span.erro_message').text('As senhas não conferem.').fadeIn();
            erro = true;
        }
    }

    if (erro){
        return;
    }

    if (user_id.length > 1) {
        user_url = URI_PUBLIC + 'admin/user.php?module=edit&user_id=' + user_id;
        msg = 'Usuário editado com sucesso!';
    } else {
        user_url = URI_PUBLIC + 'admin/user.php?module=insert';
        msg = 'Usuário adicionado com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_user')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: user_url,
        type: 'POST',
        dataType: 'html',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            var modal = $('#userModal');

            $('.overlay').fadeOut();

            modal.on('hidden.bs.modal', function(e) {
                atualizar();
            });

            modal.modal('hide');

            showAlert('success', msg);
        },
        error: function(data) {
            var result = jQuery.parseJSON(data.responseText);

            $('.overlay').fadeOut();

            if ( result ){
                console.log(result);
                showAlert('warning', result.data.message);
            } else {
                console.log(data);
                alert("Algo de errado ocorreu!");
            }
        }
    });
}

function atualizar(){
    $.ajax({
        url: URI_PUBLIC + 'admin/user.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#userTemplate').html(data).promise().done(function(){
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                setConfig();
            });
        },
        error: function(data) {
            var result = jQuery.parseJSON(data.responseText);

            if ( result ){
                console.log(result);
                showAlert('warning', result.data.message);
            } else {
                console.log(data);
                alert("Algo de errado ocorreu!");
            }
        }
    });
}

function deleteUser(userid) {
    $.ajax({
        url: URI_PUBLIC + 'admin/user.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {user_id: userid, json : 1},
        success: function(data) {
            showAlert('success', 'Usuário excluído com sucesso!');

            atualizar();
        },
        error: function(data) {
            var result = jQuery.parseJSON(data.responseText);

            if ( result ){
                console.log(result);
                showAlert('warning', result.data.message);
            } else {
                console.log(data);
                alert("Algo de errado ocorreu!");
            }
        }
    });
}
