$(document).ready(function() {
    setConfig();
});

function setConfig(){
    $('.treeview').removeClass('active');
    $('#menuUser').addClass('active');

    var table = $('#table_profile').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 2, 3 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 0 ],
            "width": "50%"
        },{
            "targets": [ 1 ],
            "width": "30%"
        },{
            "targets": [ 2, 3 ],
            "width": "10%"
        }
     ]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_profile_length"]').clone(true);

        $('#table_profile_length').find('label').text('');
        $('#table_profile_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
            .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    $('#profileModal').modal({
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
            deleteProfile($(this).attr('userprofileid'));
        },
        onCancel: function(){

        }
    });

    $('#form_profile input').bind("click focus", function(event){
        $(this).parent('div').find('span.erro_message').fadeOut();
    });

    $('#profileModal').on('hide.bs.modal', function(e) {
        $('#form_profile input').each(function(){
            $(this).parent('div').find('span.erro_message').fadeOut();
        });
    });

    $('#form_profile').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });

    $('#profileModal').on('hidden.bs.modal', function () {
        $(this).find('input[type="checkbox"]').prop('checked',false);
    });
}

function cadProfile() {
    var modal = $('#profileModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#profileModalLabel').text('Cadastrar Perfil');
        $('#btnSubmit').text('Novo Perfil');
        $('#lastUpdate').css('display', 'none');
        $('#user_profile_id').val('');
        $('#form_profile')[0].reset();
    });

    modal.modal('show');
}

function editProfile(userProfileId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/userProfile.php?module=edit',
        data: {user_profile_id: userProfileId, json : 1},
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            user_profile = data.data;
            $.each(user_profile.UserProfileAccess,function( k, profile_access ){
                $.each(profile_access,function( j, access ){
                    if( j != 'name' ){
                        if( access.data_type == 'bool' ){
                            $('input[name="'+k+'_'+j+'"]').prop('checked',(access.value=='Y'));
                        } else{
                            $('input[name="'+k+'_'+j+'"]').val(access.value);
                        }
                    }
                });
            });
            $('#profileModal').off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#profileModalLabel').text('Editar Perfil');
                $('#btnSubmit').text('Atualizar Perfil');
                $('#lastUpdate').css('display', user_profile.user_profile_update == null ? 'none' : 'block').text('Última atualização: ' + user_profile.user_profile_update);
                $('#user_profile_id').val(user_profile.user_profile_id);
                $('#user_profile_name').val(user_profile.user_profile_name);
            });
            $('#profileModal').modal('show');
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

function submitProfile() {
    var erro            = false,
        user_profile_id = $('#user_profile_id').val(),
        user_profile_url,
        msg;

    if($.trim($('#user_profile_name').val()) == ''){
        $('#user_profile_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        return;
    }

    if (user_profile_id.length > 1) {
        user_profile_url = URI_PUBLIC + 'admin/userProfile.php?module=edit&user_profile_id=' + user_profile_id;
        msg = 'Perfil editado com sucesso!';
    } else {
        user_profile_url = URI_PUBLIC + 'admin/userProfile.php?module=insert';
        msg = 'Perfil adicionado com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_profile')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: user_profile_url,
        type: 'POST',
        dataType: 'html',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            var modal = $('#profileModal');

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
        url: URI_PUBLIC + 'admin/userProfile.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#userProfileTemplate').html(data).promise().done(function(){
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

function deleteProfile(userProfileid) {
    $.ajax({
        url: URI_PUBLIC + 'admin/userProfile.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {user_profile_id: userProfileid, json : 1},
        success: function(data) {
            showAlert('success', 'Perfil excluído com sucesso!');

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
