$(document).ready(function() {
    setConfig();
});

function setConfig(){
    //$('.treeview').removeClass('active');
    $('#menuProduct').addClass('active');

    //$(".select2").select2();

    var table = $('#table_product_unit').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 2, 3 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 2, 3],
            "width": "10%"
        }]
    });

    table.on( 'init', function () {
        // var select = $('select[name="table_product_unit_length"]').clone(true);
        //
        // $('#table_product_unit_length').find('label').text('');
        // $('#table_product_unit_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
        //     .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    $('#unitModal').modal({
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
            deleteUnit($(this).attr('unitid'));
        },
        onCancel: function(){

        }
    });

    $('#form_unit input, #form_unit span.select2').bind("click focus", function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#unitModal').on('hide.bs.modal', function(e) {
        $('#form_unit input, #form_unit span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });

    $('#form_unit').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });
}

function cadUnit() {
    var modal = $('#unitModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#unitModalLabel').text('Cadastrar Unidade');
        $('#btnSubmit').text('Novo Unidade');
        $('#lastUpdate').css('display', 'none');
        $('#product_unit_id').val('');

        $('#form_unit')[0].reset();
    });

    modal.modal('show');
}

function editUnit(product_unitId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/product_unit.php?module=edit',
        data: {product_unit_id: product_unitId, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var product_unit = jQuery.parseJSON(data).data,
                modal  = $('#unitModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#unitModalLabel').text('Editar Unidade');
                $('#btnSubmit').text('Editar Unidade');
                $('#lastUpdate').css('display', product_unit.product_unit_update == null ? 'none' : 'block').text('Última atualização: ' + product_unit.product_unit_update);
                $('#product_unit_id').val(product_unit.product_unit_id);
                $('#product_unit_code').val(product_unit.product_unit_code);
                $('#product_unit_name').val(product_unit.product_unit_name);
            });

            modal.modal('show');
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function submitUnit() {
    var erro  = false,
        product_unit_id = $('#product_unit_id').val(),
        product_unit_url,
        msg;

    if($.trim($('#product_unit_code').val()) == ''){
        $('#product_unit_code').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if($.trim($('#product_unit_name').val()) == ''){
        $('#product_unit_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        return;
    }

    if (product_unit_id.length > 1) {
        product_unit_url = URI_PUBLIC + 'admin/product_unit.php?module=edit&product_unit_id=' + product_unit_id;
        msg = 'Categoria editada com sucesso!';
    } else {
        product_unit_url = URI_PUBLIC + 'admin/product_unit.php?module=insert';
        msg = 'Categoria adicionada com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_unit')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: product_unit_url,
        type: 'POST',
        dataType: 'json',
        data: $('#form_unit').serialize(),
        success: function(data) {
            var modal = $('#unitModal');

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
        url: URI_PUBLIC + 'admin/product_unit.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#unitTemplate').html(data).promise().done(function(){
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

function deleteUnit(product_unitId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/product_unit.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {product_unit_id: product_unitId},
        success: function(data) {
            showAlert('success', 'Unidade excluída com sucesso!');

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
