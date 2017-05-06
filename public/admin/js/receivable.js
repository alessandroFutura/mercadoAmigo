var timer;

$(document).ready(function() {
    setConfig();
});

function setConfig(){

    $('#menuReceivable').addClass('active');
    $('[data-mask]').inputmask();
    $('.datePicker').datepicker({
        autoclose: true
    });
    $(".datePicker").datepicker().on('show.bs.modal', function(event) {
        event.stopPropagation();
    });

    $(".select2").select2();

    var table = $('#table_receivable').DataTable( {
        "language": { "url": URI_PUBLIC + "admin/js/DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 0, 7, 8, 9 ],
            "searchable": false,
            "orderable": false
        }],
        "order": [[1, 'asc' ]]
    });


    $('#receivableModal').modal({
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
            deleteReceivable($(this).attr('receivableid'));
        },
        onCancel: function(){

        }
    });

    $('#form_receivable input, #form_receivable span.select2').bind("click focus", function(event){
        $(this).parent('div').find('span.erro_message').fadeOut();
    });

    $('#receivableModal').on('hide.bs.modal', function(e) {
        $('#form_receivable input, #form_receivable span.select2').each(function(){
            $(this).parent('div').find('span.erro_message').fadeOut();
        });
    });

    $('#form_receivable').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });

    $(':file').filestyle({
        buttonBefore : true,
        icon : false,
        buttonName : 'btn-primary',
        buttonText : '  Selecione o arquivo PDF'
    });
}

function cadReceivable() {
    $('#receivableModal').off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#receivableModalLabel').text('Cadastrar Título');
        $('#btnSubmit').text('Novo Título');
        $('#lastUpdate').css('display', 'none');
        $('#receivable_id').val('');
        $('#receivable_pass').val('');
        $('#receivable_code').val('').prop('readonly',false);
        $('#receivable_value').val('').prop('readonly',false);
        $('#receivable_pass_confirm').val('');
        $('#modality_id').val(null).trigger('change');
        $('#receivable_drop').prop('checked',false);
        $('#receivable_drop_date').val('');
        $('label[form="receivable_drop"]').text('Baixar Título');
        $('#person_id').val(null).trigger('change').prop('disabled',false);
        $('#boxPassword').css('display', 'block');
        $('label[for="pdf"]').text( 'Anexar Boleto' );
        $('.tabOrder, .tabPerson').hide();
        $('#form_receivable')[0].reset();
        $('#form_receivable').find('input, select, label[for="pdf"]').prop('disabled',false);
        $('#btnSubmit').prop('disabled',false);
    });

    $('#receivableModal').modal('show');
}

function editReceivable(receivableId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/receivable.php?module=get',
        data: {receivable_id: receivableId},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var receivable = jQuery.parseJSON(data).data,
                modal = $('#receivableModal'),
                l_business = [];

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#receivableModalLabel').text('Editar Título');
                $('#btnSubmit').text('Atualizar Título');
                $('#lastUpdate').css('display', receivable.receivable_update == null ? 'none' : 'block').text('Última atualização: ' + receivable.receivable_update);
                $('#receivable_id').val(receivable.receivable_id);
                $('#order_id').val(receivable.order_id);
                $('#receivable_code').val(receivable.receivable_code).prop('readonly',true);
                $('#modality_id').val(receivable.modality_id).trigger('change');
                $('#receivable_drop').prop('checked',(receivable.receivable_drop == 'Y'));
                $('#receivable_drop_date').val(receivable.receivable_drop_date);
                $('label[form="receivable_drop"]').text( receivable.receivable_drop == 'Y' ? 'Título Baixado' : 'Baixar Título' );
                $('#person_id').val(receivable.person_id).trigger('change').prop('disabled',true);
                $('#receivable_value').val(receivable.receivable_value).prop('readonly',true);
                $('#receivable_deadline').val(receivable.receivable_deadline);
                $('#receivable_payment_date').val(receivable.receivable_payment_date);
                $('label[for="pdf"]').text( receivable.receivable_file != null ? 'Atualizar Boleto' : 'Anexar Boleto' );
                $('.tabOrder, .tabPerson').hide();

                if( receivable.receivable_drop == 'Y' ){
                    $('#form_receivable').find('input, select, label[for="pdf"]').prop('disabled',true);
                    $('#btnSubmit').prop('disabled',true);
                }
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

function submitReceivable() {
    var erro      = false,
        receivable_id   = $('#receivable_id').val(),
        receivable_url,
        msg;

    clearTimeout(timer);

    if($.trim($('#receivable_code').val()) == ''){
        $('#receivable_code').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#person_id').val()) == ''){
        $('#person_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#receivable_value').val()) == '' || parseFloat($('#receivable_value').val()) <= 0 ){
        $('#receivable_value').parents('.form-group').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#receivable_deadline').val()) == ''){
        $('#receivable_deadline').parents('.form-group').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if( $('#receivable_drop').is(':checked') && !$('#receivable_payment_date').val().length ){
        $('#receivable_payment_date').parents('.form-group').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        timer = setTimeout(function(){
            $('span.erro_message').fadeOut();
        },3000);
        return;
    }

    if (receivable_id.length) {
        receivable_url = URI_PUBLIC + 'admin/receivable.php?module=edit&receivable_id=' + receivable_id;
        msg = 'Título editado com sucesso!';
    } else {
        receivable_url = URI_PUBLIC + 'admin/receivable.php?module=insert';
        msg = 'Título adicionado com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_receivable')[0]);
    formData.append('json', '1' );
    formData.append('person_id', $('#person_id').val() );

    $.ajax({
        url: receivable_url,
        type: 'POST',
        dataType: 'html',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            var modal = $('#receivableModal');

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
        url: URI_PUBLIC + 'admin/receivable.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#receivableTemplate').html(data).promise().done(function(){
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

function deleteReceivable(receivableid) {
    $.ajax({
        url: URI_PUBLIC + 'admin/receivable.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {receivable_id: receivableid, json : 1},
        success: function(data) {
            showAlert('success', 'Título excluído com sucesso!');

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
