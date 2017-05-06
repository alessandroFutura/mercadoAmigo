$(document).ready(function() {
    setConfig();
});

function setConfig(){
    //$('.treeview').removeClass('active');
    $('#menuOrder').addClass('active');

    //$(".select2").select2();
    $(".my-colorpicker1").colorpicker({
        color: '#3C8DBC'
    }).on('changeColor',function(){
        $('#color').css('border-left','40px solid '+$('#order_status_color').val());
    });

    var table = $('#table_status').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 0, 4, 5 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 0, 1, 3, 4, 5],
            "width": "10%"
        }]
    });

    table.on( 'init', function () {
        // var select = $('select[name="table_order_status_length"]').clone(true);
        //
        // $('#table_order_status_length').find('label').text('');
        // $('#table_order_status_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
        //     .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    $('#order_statusModal').modal({
        show: false,
        backdrop: 'static'
    });

    $('a[name="btnDel"]').confirmation({
        title: 'Confirmar a Exclusão?',
        placement: 'left',
        btnOkClass: 'btn btn-primary',
        btnCancelClass: 'btn btn-default pull-right',
        btnOkLabel: 'Sim',
        btnCancelLabel: 'Não',
        onConfirm: function(){
            deleteStatus($(this).attr('statusid'));
        },
        onCancel: function(){

        }
    });

    $('#form_order_status input, #form_order_status span.select2').bind("click focus", function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#order_statusModal').on('hide.bs.modal', function(e) {
        $('#form_order_status input, #form_order_status span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });

    $('#form_order_status').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });
}

function cadStatus() {
    var modal = $('#statusModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#statusModalLabel').text('Cadastrar Status de Pedido');
        $('#btnSubmit').text('Novo Status de Pedido');
        $('#lastUpdate').css('display', 'none');
        $('#order_status_id').val('');
        $('#form_status')[0].reset();
        $('#order_status_color').val('#3C8DBC').trigger('change');
        $('#color').css('border-left','40px solid #3C8DBC');
        $('#statusModal input, #btnSubmit').prop('disabled',false);
    });

    modal.modal('show');
}

function editStatus(order_statusId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/order_status.php?module=edit',
        data: {order_status_id: order_statusId, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var order_status = jQuery.parseJSON(data).data,
                modal  = $('#statusModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#statusModalLabel').text('Editar Status de Pedido');
                $('#btnSubmit').text('Editar Status de Pedido');
                $('#lastUpdate').css('display', 'none');
                //$('#lastUpdate').css('display', order_status.order_status_update == null ? 'none' : 'block').text('Última atualização: ' + order_status.order_status_update);
                $('#order_status_id').val(order_status.order_status_id);
                $('#order_status_active').prop('checked',order_status.order_status_active == 'Y');
                $('#order_status_code').val(order_status.order_status_code);
                $('#order_status_name').val(order_status.order_status_name);
                $('#order_status_color').val(order_status.order_status_color).trigger('change');
                $('#order_status_start').prop('checked',order_status.order_status_start == 'Y');
                $('#order_status_editable').prop('checked',order_status.order_status_editable == 'Y');
                $('#order_status_end').prop('checked',order_status.order_status_end == 'Y');
                $('#order_status_mail_admin').prop('checked',order_status.order_status_mail_admin == 'Y');
                $('#order_status_mail_client').prop('checked',order_status.order_status_mail_client == 'Y');
                $('#color').css('border-left','40px solid'+order_status.order_status_color);
                if( order_status.order_status_super == 'Y' ){
                    $('#statusModal input, #btnSubmit').prop('disabled',true);
                } else{
                    $('#statusModal input, #btnSubmit').prop('disabled',false);
                }
            });

            modal.modal('show');
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function submitOrderStatus() {
    var erro  = false,
        order_status_id = $('#order_status_id').val(),
        order_status_url,
        msg;

    if($.trim($('#order_status_code').val()) == ''){
        $('#order_status_code').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#order_status_name').val()) == ''){
        $('#order_status_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#order_status_color').val()) == ''){
        $('#order_status_color').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        return;
    }

    if (order_status_id.length > 1) {
        order_status_url = URI_PUBLIC + 'admin/order_status.php?module=edit&order_status_id=' + order_status_id;
        msg = 'Status editado com sucesso!';
    } else {
        order_status_url = URI_PUBLIC + 'admin/order_status.php?module=insert';
        msg = 'Status adicionado com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_order_status')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: order_status_url,
        type: 'POST',
        dataType: 'json',
        data: $('#form_status').serialize(),
        success: function(data) {
            var modal = $('#statusModal');

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
    $('.loading').fadeIn();

    $.ajax({
        url: URI_PUBLIC + 'admin/order_status.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#statusTemplate').html(data).promise().done(function(){
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                setConfig();

                $('.loading').fadeOut();
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

function deleteStatus(order_statusId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/order_status.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {order_status_id: order_statusId},
        success: function(data) {
            showAlert('success', 'Status excluída com sucesso!');

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
