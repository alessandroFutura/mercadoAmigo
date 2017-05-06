var kit = {
    kit_id: null,
    kit_active: null,
    kit_code: null,
    kit_name: null,
    kit_addition: 0,
    kit_discount: 0,
    kit_items_value: 0,
    kit_value: 0,
    KitItem : [],
    KitItemDel: []
};

var modal_update = false;

$(document).ready(function() {
    setConfig();
});

function setConfig(){
    var modal = $('#kitModal');

    $('.treeview').removeClass('active');
    $('#menuKit').addClass('active');

    $('.select2').select2();
    $('[data-mask]').inputmask();
    $('.datePicker').datepicker({
        autoclose: true
    });
    $(".datePicker").datepicker().on('show.bs.modal', function(event) {
        // prevent datepicker from firing bootstrap modal "show.bs.modal"
        event.stopPropagation();
    });

    var table = $('#table_kit').DataTable( {
        "language": { "url": URI_PUBLIC + "admin/js/DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 4, 5 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 4, 5 ],
            "width": "30px"
        }],
        "order": [[0, 'asc' ]]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_kit_length"]').clone(true);

        $('#table_kit_length').find('label').text('');
        $('#table_kit_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select).append('<span class="hidden-xs"> Resultados por página </span>');
    } );


    modal.modal({
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
            deleteKit($(this).attr('kitid'));
        },
        onCancel: function(){

        }
    });

    $('#form_kit input, #form_kit span.select2').click(function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    modal.on('hide.bs.modal', function(e) {
        $('#form_kit input, #form_kit span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });

        $('.tab_erro_cad').fadeOut();
        $('.tab_erro_address').fadeOut();
        $('.tab_erro_contact').fadeOut();
    });

    $('#form_kit').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    modal.on('shown.bs.modal', function () {
        $('.modal-body').scrollTop(0);
    });

    $('#kit_addition, #kit_discount').on('keyup keypress', function(e) {
        if( !$(this).val().length ){
            $(this).val(0);
        }
        updateKitValues();
    });

    $('#kit_value').on('keyup keypress', function(e) {
        if( $(this).val().length > 0 && parseFloat($(this).val()) > 0 ){
            kit.kit_value = parseFloat($(this).val()).toFixed(2);
            updateKitValues();
        }
    });

    $('#kit_value').on('blur', function(e) {
        if (!$(this).val().length || parseFloat($(this).val()) < 0) {
            //updateKitValues();
            $(this).val(parseFloat(kit.kit_value).toFixed(2));
        }
    });
}

function cadKit() {
    var modal = $('#kitModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#kitModalLabel').text('Cadastrar Kit');
        $('#btnSubmit').text('Novo Kit');
        $('#lastUpdate').css('display', 'none');
        $('#kit_id').val('');
        $('#kit_active').prop('checked',true);
        $('#kit_unit_id').val(null).trigger('change');
        $('#form_kit')[0].reset();
        $('#delete_contact').val('');
        $('#delete_address').val('');
        $('#kitValues').show();
    });

    kit.kit_addition = 0;
    kit.kit_discount = 0;
    kit.kit_items_value = 0;
    kit.kit_value = 0;
    kit.KitItem  = [];
    kit.KitItemDel = [];
    delete kit.kit_update;
    delete kit.kit_date;

    $('#tab_items table tbody').html('');
    $('#product_id option').attr('disabled',false);
    $('#product_id').select2('destroy').select2();
    $('#aTabItems span').text('0');

    modal.modal('show');
}

function editKit(kitId) {

    $('#tab_cost table tbody, #tab_price table tbody').html('');
    $.ajax({
        url: URI_PUBLIC + 'admin/kit.php?module=get',
        data: {kit_id: kitId},
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            kit = data.data;
            var modal  = $('#kitModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#kitModalLabel').text('Editar Kit');
                $('#btnSubmit').text('Atualizar Kit');
                $('#lastUpdate').css('display', kit.kit_update == null ? 'none' : 'block').text('Última atualização: ' + kit.kit_update);
                $('#kit_id').val(kit.kit_id);
                $('#kit_active').prop('checked', kit.kit_active == 'Y' );
                $('#kit_code').val(kit.kit_code);
                $('#kit_name').val(kit.kit_name);
                $('#kit_addition').val(kit.kit_addition);
                $('#kit_discount').val(kit.kit_discount);
                $('#kit_value').val(kit.kit_value);
                $('#tab_items table tbody').html('');
                kit.kit_items_value = 0;
                kit.KitItemDel = [];
                $.each(kit.KitItem,function(key,item){
                    item.kit_item_value_total = item.kit_item_amount * item.kit_item_value;
                    kit.kit_items_value += item.kit_item_value_total;
                    $('#tab_items table tbody').append('<tr><td>'+(key+1)+'</td><td>'+item.Product.product_name+'</td><td><input type="text" class="form-control" name="item_amount[]" value="'+item.kit_item_amount+'" data-inputmask="\'alias\': \'integer\'" data-mask></td><td>'+item.kit_item_value+'</td><td>'+item.kit_item_value_total.toFixed(2)+'</td><td><i onclick="delItem(this)" class="glyphicon glyphicon-trash"></i></a></td></tr>');
                    $('#product_id option[value="'+item.product_id+'"]').attr('disabled',true);
                    delete item.Product;
                });
                inputItemsEvent();
                updateKitValues();
            });
            modal.modal('show');
        },
        error: function(data){
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function delItem(e)
{
    index = $(e).parents('tr').first().index();
    kit.kit_items_value -= kit.KitItem[index].kit_item_value_total;
    kit.kit_value -= kit.KitItem[index].kit_item_value_total;
    kit.KitItemDel.push(kit.KitItem[index].kit_item_id);
    $('#product_id option[value="'+kit.KitItem[index].product_id+'"]').attr('disabled',false);

    if( index ){
        if( index+1 == kit.KitItem.length ){
            kit.KitItem.pop();
        } else{
            kit.KitItem.splice(index-1,1);
        }
    } else{
        kit.KitItem.shift();
    }
    $('#tab_items table tbody tr').eq(index).remove();
    $('#tab_items table tbody tr').each(function(key){
        $(this).find('td').eq(0).text(key+1);
    });

    $('#product_id').select2('destroy').select2();
    updateKitValues();
}

function newItem(){

    var erro = false;

    var item = {
        kit_id: $('#kit_id').val().length ? $('#kit_id').val() : null,
        product_id: $('#product_id').val(),
        product_name: $('#product_id option:selected').attr('data-name'),
        kit_item_amount: parseInt($('#kit_item_amount').val()),
        kit_item_value: parseFloat($('#product_id option:selected').attr('data-price'))
    };
    item.kit_item_value_total = item.kit_item_amount * item.kit_item_value;

    if( !item.product_id.length ){
        $('#product_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( item.kit_item_amount < 1 ){
        $('#kit_item_amount').parent('div').find('span.erro_message').text('Informe um valor válido.').fadeIn();
        erro = true;
    }

    if( erro ){
        setTimeout(function(){
            $('span.erro_message').fadeOut();
        },3000)
        return;
    }

    $('#product_id option:selected').attr('disabled','disabled');
    $('#product_id').val(null).trigger('change').select2('destroy').select2();
    $('#kit_item_amount').val('1');
    $('#tab_items table tbody').append('<tr><td>'+(kit.KitItem.length+1)+'</td><td>'+item.product_name+'</td><td><input type="text" class="form-control" name="item_amount[]" value="'+item.kit_item_amount+'" data-inputmask="\'alias\': \'integer\'" data-mask></td><td>'+item.kit_item_value+'</td><td>'+item.kit_item_value_total.toFixed(2)+'</td><td><i onclick="delItem(this)" class="glyphicon glyphicon-trash"></i></a></tr>')

    kit.kit_items_value += item.kit_item_value_total;
    kit.kit_value += item.kit_item_value_total;
    kit.KitItem.push(item);
    inputItemsEvent();
    updateKitValues();
}

function inputItemsEvent()
{
    $('input[name="item_amount[]"]').blur(function(){
        if( !$(this).val().length ) {
            $(this).val(1).trigger('keyup');
        }
    });
    $('input[name="item_amount[]"]').unbind('keyup keypress').on('keyup keypress', function(e) {
        if( $(this).val().length ){
            index = $(this).parents('tr').first().index();
            console.log(index);
            kit.kit_items_value -= kit.KitItem[index].kit_item_value_total;
            kit.kit_value -= kit.KitItem[index].kit_item_value_total;
            kit.KitItem[index].kit_item_amount = parseInt($(this).val());
            kit.KitItem[index].kit_item_value_total = kit.KitItem[index].kit_item_amount * kit.KitItem[index].kit_item_value;
            kit.kit_items_value += kit.KitItem[index].kit_item_value_total;
            kit.kit_value += kit.KitItem[index].kit_item_value_total;
            $('#tab_items table tbody tr').eq(index).find('td').eq(4).text(kit.KitItem[index].kit_item_value_total.toFixed(2));
            updateKitValues();
        }
    });
}
function updateKitValues()
{
    kit.kit_addition = parseFloat($('#kit_addition').val());
    kit.kit_discount = parseFloat($('#kit_discount').val());
    $('#aTabItems span').text(kit.KitItem.length);
    $('#kit_items_value').val(kit.kit_items_value.toFixed(2));
    //$('#kit_value').val((parseFloat(kit.kit_value)+kit.kit_addition-kit.kit_discount).toFixed(2));
}

function submitKit() {
    var erro = false;
    var post_url;

    kit.kit_id = $('#kit_id').val();
    kit.kit_active = $('#kit_active').prop('checked') ? 'on' : null;
    kit.kit_code = $('#kit_code').val();
    kit.kit_name = $('#kit_name').val();
    kit.kit_addition = $('#kit_addition').val();
    kit.kit_discount = $('#kit_discount').val();

    var msg;

    if( !$('#kit_name').val().length ){
        $('#kit_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        $('.tab_erro_cad').fadeIn();
        return;
    } else{
        $('.tab_erro_cad').fadeOut();
    }

    if (kit.kit_id.length > 1) {
        post_url = URI_PUBLIC + 'admin/kit.php?module=edit&kit_id=' + kit_id;
        msg = 'Kit editado com sucesso!';
    } else {
        post_url = URI_PUBLIC + 'admin/kit.php?module=insert';
        msg = 'Kit adicionado com sucesso!';
    }
    console.log(kit);

    $('.overlay').fadeIn();

    console.log(kit);
    $.ajax({
        url: post_url,
        type: 'POST',
        dataType: 'json',
        data: { data: kit },
        success: function(data) {
            var modal = $('#kitModal');
            modal.on('hidden.bs.modal', function(e) {
                atualizar();
            });
            $('.overlay').fadeOut();
            modal.modal('hide');
            showAlert('success', msg);
        },
        error: function(data) {
            var result = jQuery.parseJSON(data.responseText).data;

            $('.overlay').fadeOut();

            // if ( (result) && (result.error) ){
            //     result.error_cnpj ? kit_cnpj.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn() : '';
            //     result.error_cpf  ? kit_cpf.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn()  : '';
            //     result.error_rg   ? kit_rg.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn()   : '';
            //
            //     $('.nav-tabs a[href="#tab_cad"]').tab('show');
            //     showAlert('warning', result.error);
            // } else {
            //     console.log(data);
            //     alert("Algo de errado ocorreu!");
            // }
        }
    });
}

function atualizar(){
    $.ajax({
        url: URI_PUBLIC + 'admin/kit.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#kitTemplate').html(data).promise().done(function(){
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                setConfig();
            });
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function deleteKit(kitId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/kit.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {kit_id: kitId},
        success: function(data) {
            showAlert('success', 'Kit excluída com sucesso!');

            atualizar();
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}