
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $('#passModal').modal({
        show: false,
        backdrop: 'static'
    });

    $('#form_pass input').click(function(event){
        $(this).parent('div').find('span.erro_message').fadeOut();
    });

    $('#passModal').on('hide.bs.modal', function(e) {
        $('#form_pass input').each(function(){
            $(this).parent('div').find('span.erro_message').fadeOut();
        });
    });

    $('.treeview').removeClass('active');

    if( $('#contractModal').length ){
        $('#contractModal').modal({
            backdrop: 'static',
            keyboard: false
        }).modal('show');
    }
});

function contractSubmit() {
    $('.overlay').fadeIn();
    $.ajax({
        url: URI_PUBLIC + 'admin/user.php?module=contractAccept',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#contractModal').modal('hide');
            $('.overlay').fadeOut();
        },
        error: function(data) {
            $('#contractModal').modal('hide');
            $('.overlay').fadeOut();
        }
    });
}

function changePass2(userId){
    $('#passModal').off('show.bs.modal').on('show.bs.modal', function(e) {

        $('#pass_user_id').val(userId);
        $('#pass_old').val('');
        $('#pass').val('');
        $('#pass_confirm').val('');

    })

    $('#passModal').modal('show');
}

function submitPass2(){
    var erro   = false,
        userId = $('#pass_user_id').val();
    $('.erro_message').text('').hide();
    if ($.trim($('#pass_old').val()) == ''){
        $('#pass_old').next().text('Este campo é obrigatório.').fadeIn();
        erro = true;
    };

    if ($.trim($('#pass').val()) == ''){
        $('#pass').next().text('Este campo é obrigatório.').fadeIn();
        erro = true;
    } else if($.trim($('#pass').val()).length < 4){
        $('#pass').next().text('A senha deve conter no mínimo 4 caracteres.').fadeIn();
        erro = true;
    };

    if( ($.trim($('#pass').val()) == '') || ( $.trim($('#pass_confirm').val()) != $.trim($('#pass').val()) ) ){
        $('#pass_confirm').next().text('As senhas não conferem.').fadeIn();
        erro = true;
    };

    if(erro){
        return;
    }

    $.ajax({
        url: URI_PUBLIC + 'admin/user.php?module=editPass',
        data: {user_id: userId, user_pass: $('#pass').val(), user_pass_old: $('#pass_old').val()},
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if ( !parseInt(data.data.status)){
                $('#pass_old').parent('div').find('span.erro_message').text('Senha Inválida.').fadeIn();
            } else {
                $('#passModal').modal('hide');
                showAlert('success', 'Senha alterada com sucesso!');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }
        },
        error: function(data) {
            showAlert('warning', 'Algo de errado ocorreu!');
        }
    });
}

function showAlert(tipo, message, title = ''){
    $('#alter-info').removeClass('alert-danger').removeClass('alert-info').removeClass('alert-warning').removeClass('alert-success');
    $('#alter-info i').removeClass('glyphicon-ban-circle').removeClass('glyphicon-info-sign').removeClass('glyphicon-warning-sign').removeClass('glyphicon-ok');

    if (tipo == 'danger'){
        $('#alter-info').addClass('alert-danger');
        $('#alter-info i').addClass('glyphicon-ban-circle');
    } else if (tipo == 'success'){
        $('#alter-info').addClass('alert-success');
        $('#alter-info i').addClass('glyphicon-ok');
    } else if (tipo == 'warning'){
        $('#alter-info').addClass('alert-warning');
        $('#alter-info i').addClass('glyphicon-warning-sign');
    } else { // info
        $('#alter-info').addClass('alert-info');
        $('#alter-info i').addClass('glyphicon-info-sign');
    }

    $('#alter-info h4').css('display', title.length > 1 ? 'block' : 'none');

    $('#alter-info h4').text(title);
    $('#alter-info span').text(message);
    $('#alter-info').fadeIn( 400, function(){
        setTimeout(function() {
            $('#alter-info').fadeOut();
        }, 3000);
    });
}


function validar(campo) {
    var regex = '[^a-zA-Z0-9]+';

    if(campo.match(regex)) {
        //encontrou então não passa na validação
        return false;
    }
    else {
        //não encontrou caracteres especiais
        return true;
    }
}

function changePass(userId){
    $('#passModal').off('show.bs.modal').on('show.bs.modal', function(e) {

        $('#pass_user_id').val(userId);
        $('#pass_old').val('');
        $('#pass').val('');
        $('#pass_confirm').val('');

    })

    $('#passModal').modal('show');
}

function submitPass(){
    var erro   = false,
        userId = $('#pass_user_id').val();

    if ($.trim($('#pass_old').val()) == ''){
        $('#pass_old').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    };

    if ($.trim($('#pass').val()) == ''){
        $('#pass').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    } else if($.trim($('#pass').val()).length < 4){
        $('#pass').parent('div').find('span.erro_message').text('A senha deve conter no mínimo 4 caracteres.').fadeIn();
        erro = true;
    };

    if( ($.trim($('#pass').val()) == '') || ( $.trim($('#pass_confirm').val()) != $.trim($('#pass').val()) ) ){
        $('#pass_confirm').parent('div').find('span.erro_message').text('As senhas não conferem.').fadeIn();
        erro = true;
    };

    if (erro){
        return;
    }

    $.ajax({
        url: URI_PUBLIC + 'admin/user.php?module=editPass',
        data: {user_id: userId, user_pass: $('#pass').val(), user_pass_old: $('#pass_old').val()},
        type: 'POST',
        dataType: 'html',
        success: function(data) {
            var result = jQuery.parseJSON(data).data;

            if (!result.passChanged){
                $('#pass_old').parent('div').find('span.erro_message').text('Senha Inválida.').fadeIn();
            } else {
                $('#passModal').modal('hide');

                showAlert('success', 'Senha alterada com sucesso!');

                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}


function GetFilename(url)
{
   if (url)
   {
      var m = url.toString().match(/.*\/(.+?)\./);
      if (m && m.length > 1)
      {
         return m[1];
      }
   }
   return "";
}

function pad (str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}