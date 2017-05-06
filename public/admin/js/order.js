var order = {
    order_id: null,
    person_id: null,
    order_active: null,
    order_code: null,
    order_addition: 0,
    order_discount: 0,
    order_observation: null,
    order_value: 0,
    OrderItem : [],
    OrderItemDel: []
};

var timer;

var modal_update = false;

$(document).ready(function(){
    setConfig();
});

function setConfig(){
    var modal = $('#orderModal');

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('.treeview').removeClass('active');
    $('#menuOrder').addClass('active');

    $('.select2').select2();
    $('[data-mask]').inputmask();
    $('.datePicker').datepicker({
        autoclose: true
    });
    $(".datePicker").datepicker().on('show.bs.modal', function(event) {
        // prevent datepicker from firing bootstrap modal "show.bs.modal"
        event.stopPropagation();
    });

    var table = $('#table_order').DataTable( {
        "language": { "url": URI_PUBLIC + "admin/js/DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 0, 5, 6 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 1, 3, 4 ],
            "width": "60px"
        },{
            "targets": [ 0, 5, 6 ],
            "width": "30px"
        }],
        "order": [[2, 'asc' ]]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_order_length"]').clone(true);

        $('#table_order_length').find('label').text('');
        $('#table_order_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select).append('<span class="hidden-xs"> Resultados por página </span>');
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
            deleteOrder($(this).attr('orderid'));
        },
        onCancel: function(){

        }
    });

    $('#form_order input, #form_order span.select2').click(function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    modal.on('hide.bs.modal', function(e) {
        $('#form_order input, #form_order span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });

        $('.tab_erro_cad').fadeOut();
        $('.tab_erro_address').fadeOut();
        $('.tab_erro_contact').fadeOut();
    });

    $('#form_order').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    modal.on('shown.bs.modal', function () {
        $('.modal-body').scrollTop(0);
    });

    $('#order_addition, #order_discount').on('keyup keypress', function(e) {
        if( !$(this).val().length ){
            $(this).val(0);
        }
        updateOrderValues();
    });

    $('#order_item_amount').blur(function(){
        if( !$(this).val().length ) {
            $(this).val(1);
        } else if( parseInt($(this).val()) < 1 ) {
            $(this).val(1);
        }
    });
}

function cadOrder() {
    var modal = $('#orderModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#orderModalLabel').text('Cadastrar Pedido');
        $('#btnSubmit').text('Novo Pedido');
        $('#lastUpdate').css('display', 'none');
        $('#order_id').val('');
        $('#order_active').prop('checked',true);
        $('#person_id').val(null).trigger('change');
        $('#form_order')[0].reset();
        $('#delete_contact').val('');
        $('#delete_address').val('');
        $('#order_observation').val('');
        $('#orderValues').show();
        $('#btnAddCost, .btnDelItem, #orderModal input, #person_id, #kit_id').prop('disabled',false);
        $('#btnSubmit, #orderModal select#order_status_id, #orderModal textarea').prop('disabled',false);
        $('#div_statusorder').hide();
    });

    order.order_addition = 0;
    order.order_discount = 0;
    order.order_value = 0;
    order.OrderItem  = [];
    order.OrderItemDel = [];
    delete order.order_update;
    delete order.order_date;

    $('#tab_items table tbody').html('');
    $('#kit_id option').attr('disabled',false);
    $('#kit_id').select2('destroy').select2();
    $('#aTabItems span').text('0');

    modal.modal('show');
}

function editOrder(orderId) {

    $('#tab_cost table tbody, #tab_price table tbody').html('');
    $.ajax({
        url: URI_PUBLIC + 'admin/order.php?module=get',
        data: {order_id: orderId},
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            order = data.data;
            var modal  = $('#orderModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#orderModalLabel').text('Editar Pedido');
                $('#btnSubmit').text('Atualizar Pedido');
                $('#lastUpdate').css('display', order.order_update == null ? 'none' : 'block').text('Última atualização: ' + order.order_update);
                $('#order_id').val(order.order_id);
                $('#person_id').val(order.person_id).trigger('change');
                $('#order_status_id').val(order.order_status_id).trigger('change').prop('disabled',($('#order_status_id').attr('data-disabled')=='Y'));
                $('#order_active').prop('checked', order.order_active == 'Y' );
                $('#order_code').val(order.order_code);
                $('#order_addition').val(order.order_addition);
                $('#order_discount').val(order.order_discount);
                $('#order_observation').val(order.order_observation).prop('disabled',($('#order_status_id').attr('data-disabled')=='Y'));
                $('#tab_items table tbody').html('');
                $('#div_statusorder').show();
                order.order_value = 0;
                order.OrderItemDel = [];
                $.each(order.OrderItem,function(key,item){
                    item.order_item_value_total = item.order_item_amount * item.order_item_value;
                    order.order_value += item.order_item_value_total;
                    $('#tab_items table tbody').append('<tr><td>'+(key+1)+'</td><td>'+item.Kit.kit_name+'</td><td><input min="1" max="99"  class="form-control" name="item_amount[]" value="'+item.order_item_amount+'" data-inputmask="\'alias\': \'integer\'" data-mask></td><td>'+parseFloat(item.order_item_value).toFixed(2)+'</td><td>'+parseFloat(item.order_item_value_total).toFixed(2)+'</td><td><i onclick="delItem(this)" class="glyphicon glyphicon-trash"></i></a></td><td><i onclick="infoItem(this)" class="glyphicon glyphicon-info-sign"></i></a></td></tr>');
                    $('#kit_id option[value="'+item.kit_id+'"]').attr('disabled',true);
                    delete item.Kit;
                });
                inputItemsEvent();
                updateOrderValues();

                if( order.OrderStatus.order_status_editable == "N" || order.OrderStatus.order_status_end == "Y" ){
                    $('#btnAddCost, .btnDelItem, #orderModal input, #person_id, #kit_id').prop('disabled',true);
                    $('#tab_items .glyphicon-trash').removeClass('glyphicon-trash').addClass('glyphicon-lock').removeAttr('onclick');
                } else{
                    $('#btnAddCost, .btnDelItem, #orderModal input, #person_id, #kit_id').prop('disabled',false);
                }
                if( order.OrderStatus.order_status_end == "Y" ) {
                    $('#btnSubmit, #orderModal select#order_status_id, #orderModal textarea').prop('disabled',true);
                } else{
                    $('#btnSubmit, #orderModal select#order_status_id, #orderModal textarea').prop('disabled',false);
                }
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
    order.order_value -= order.OrderItem[index].order_item_value_total;
    order.OrderItemDel.push(order.OrderItem[index].order_item_id);
    $('#kit_id option[value="'+order.OrderItem[index].kit_id+'"]').attr('disabled',false);

    if( index ){
        if( index+1 == order.OrderItem.length ){
            order.OrderItem.pop();
        } else{
            order.OrderItem.splice(index-1,1);
        }
    } else{
        order.OrderItem.shift();
    }
    $('#tab_items table tbody tr').eq(index).remove();
    $('#tab_items table tbody tr').each(function(key){
        $(this).find('td').eq(0).text(key+1);
    });

    $('#kit_id').select2('destroy').select2();
    updateOrderValues();
}

function newItem(){

    var erro = false;

    var item = {
        order_id: $('#order_id').val().length ? $('#order_id').val() : null,
        kit_id: $('#kit_id').val(),
        kit_name: $('#kit_id option:selected').attr('data-name'),
        order_item_amount: parseInt($('#order_item_amount').val()),
        order_item_value: parseFloat($('#kit_id option:selected').attr('data-price'))
    };
    item.order_item_value_total = item.order_item_amount * item.order_item_value;

    if( !item.kit_id.length ){
        $('#kit_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( item.order_item_amount < 1 ){
        $('#order_item_amount').parent('div').find('span.erro_message').text('Informe um valor válido.').fadeIn();
        erro = true;
    }

    if( erro ){
        setTimeout(function(){
            $('span.erro_message').fadeOut();
        },3000)
        return;
    }

    $('#kit_id option:selected').attr('disabled','disabled');
    $('#kit_id').val(null).trigger('change').select2('destroy').select2();
    $('#order_item_amount').val('1');
    $('#tab_items table tbody').append('<tr><td>'+(order.OrderItem.length+1)+'</td><td>'+item.kit_name+'</td><td><input min="1" max="99" class="form-control" name="item_amount[]" value="'+item.order_item_amount+'" data-inputmask="\'alias\': \'integer\'" data-mask></td><td>'+parseFloat(item.order_item_value).toFixed(2)+'</td><td>'+parseFloat(item.order_item_value_total).toFixed(2)+'</td><td><i onclick="delItem(this)" class="glyphicon glyphicon-trash"></i></td><td><i onclick="infoItem(this)" class="glyphicon glyphicon-info-sign"></i></a></td></tr>')

    inputItemsEvent();

    order.order_value += item.order_item_value_total;
    order.OrderItem.push(item);
    inputItemsEvent();
    updateOrderValues();
}

function inputItemsEvent()
{
    $('input[name="item_amount[]"]').unbind('blur').blur(function(){
        if( !$(this).val().length ) {
            $(this).val(1).trigger('keyup');
        }
    });
    $('input[name="item_amount[]"]').unbind('keyup keypress').on('keyup keypress', function(e) {
        if( parseInt($(this).val()) < 1 ){
            $(this).val('1');
        }
        if( $(this).val().length ) {
            index = $(this).parents('tr').first().index();
            console.log(index);
            order.order_value -= order.OrderItem[index].order_item_value_total;
            order.OrderItem[index].order_item_amount = parseInt($(this).val());
            order.OrderItem[index].order_item_value_total = order.OrderItem[index].order_item_amount * order.OrderItem[index].order_item_value;
            order.order_value += order.OrderItem[index].order_item_value_total;
            $('#tab_items table tbody tr').eq(index).find('td').eq(4).text(order.OrderItem[index].order_item_value_total.toFixed(2));
            updateOrderValues();
        }
    });
}
function updateOrderValues()
{
    order.order_addition = parseFloat($('#order_addition').val());
    order.order_discount = parseFloat($('#order_discount').val());
    $('#aTabItems span').text(order.OrderItem.length);
    $('#order_value').val((order.order_value+order.order_addition-order.order_discount).toFixed(2));
}

function submitOrder() {
    var erro = false;
    var erroItem = false;
    var post_url;

    order.order_id = $('#order_id').val();
    order.person_id = $('#person_id').val();
    order.order_status_id = $('#order_status_id').val();
    order.order_code = $('#order_code').val();
    order.order_addition = $('#order_addition').val();
    order.order_discount = $('#order_discount').val();
    order.order_value_total = $('#order_value').val();
    order.order_observation = $('#order_observation').val();

    var msg;
    clearTimeout(timer);

    if( $('#person_id').length && $('#person_id').val() == null ){
        $('#person_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        $('.tab_erro_cad').fadeIn();
        $('.nav-tabs a[href="#tab_cad"]').trigger('click');
        timer = setTimeout(function(){
            $('span.erro_message').fadeOut();
        },3000);
        return;
    } else{
        $('.tab_erro_cad').fadeOut();
    }

    if( !order.OrderItem.length ){
        $('#person_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erroItem = true;
    }

    if (erroItem){
        $('.tab_erro_items').fadeIn();
        $('.nav-tabs a[href="#tab_items"]').trigger('click');
        timer = setTimeout(function(){
            $('span.erro_message').fadeOut();
        },3000);
        return;
    } else{
        $('.tab_erro_items').fadeOut();
    }

    if (order.order_id.length > 1) {
        post_url = URI_PUBLIC + 'admin/order.php?module=edit&order_id=' + order_id;
        msg = 'Pedido editado com sucesso!';
    } else {
        post_url = URI_PUBLIC + 'admin/order.php?module=insert';
        msg = 'Pedido adicionado com sucesso!';
    }
    console.log(order);

    $('.overlay').fadeIn();

    console.log(order);
    $.ajax({
        url: post_url,
        type: 'POST',
        dataType: 'json',
        data: { data: order },
        success: function(data) {
            var modal = $('#orderModal');
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
        }
    });
}

function atualizar(){
    $.ajax({
        url: URI_PUBLIC + 'admin/order.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#orderTemplate').html(data).promise().done(function(){
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

function deleteOrder(orderId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/order.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {order_id: orderId},
        success: function(data) {
            showAlert('success', 'Pedido excluído com sucesso!');

            atualizar();
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}