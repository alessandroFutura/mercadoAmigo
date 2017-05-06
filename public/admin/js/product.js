var modal_update = false;

$(document).ready(function() {
    setConfig();
});

function setConfig(){
    var modal = $('#productModal');

    $('.treeview').removeClass('active');
    $('#menuProduct').addClass('active');

    $('.select2').select2();
    $('[data-mask]').inputmask();
    $('.datePicker').datepicker({
        autoclose: true
    });
    $(".datePicker").datepicker().on('show.bs.modal', function(event) {
        // prevent datepicker from firing bootstrap modal "show.bs.modal"
        event.stopPropagation();
    });

    var table = $('#table_product').DataTable( {
        "language": { "url": URI_PUBLIC + "admin/js/DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 5, 6 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 5, 6 ],
            "width": "30px"
        }],
        "order": [[2, 'asc' ]]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_product_length"]').clone(true);

        $('#table_product_length').find('label').text('');
        $('#table_product_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select).append('<span class="hidden-xs"> Resultados por página </span>');
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
            deleteProduct($(this).attr('productid'));
        },
        onCancel: function(){

        }
    });

    $('#form_product input, #form_product span.select2').click(function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    modal.on('hide.bs.modal', function(e) {
        $('#form_product input, #form_product span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });

        $('.tab_erro_cad').fadeOut();
        $('.tab_erro_address').fadeOut();
        $('.tab_erro_contact').fadeOut();
    });

    $('#form_product').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    modal.on('shown.bs.modal', function () {
        $('.modal-body').scrollTop(0);
    })
}

function cadProduct() {
    var modal = $('#productModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#productModalLabel').text('Cadastrar Produto');
        $('#btnSubmit').text('Novo Produto');
        $('#lastUpdate').css('display', 'none');
        $('#product_id').val('');
        $('#product_active').prop('checked',true);
        $('#product_unit_id').val(null).trigger('change');
        $('#form_product')[0].reset();
        $('#delete_contact').val('');
        $('#delete_address').val('');
        $('#productModal .nav-tabs').hide();
        $('#productValues').show();
    });

    $('.nav-tabs a[href="#tab_cad"]').tab('show');
    modal.modal('show');
}

function editProduct(productId) {

    $('#tab_cost table tbody, #tab_price table tbody').html('');
    $.ajax({
        url: URI_PUBLIC + 'admin/product.php?module=get',
        data: {product_id: productId},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var product = jQuery.parseJSON(data).data,
                modal  = $('#productModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#productModalLabel').text('Editar Produto');
                $('#btnSubmit').text('Atualizar Produto');
                $('#lastUpdate').css('display', product.product_update == null ? 'none' : 'block').text('Última atualização: ' + product.product_update);
                $('#product_id').val(product.product_id);
                $('#product_active').prop('checked', product.product_active == 'Y' );
                $('#product_code').val(product.product_code);
                $('#product_name').val(product.product_name);
                $('#product_ean').val(product.product_ean);
                $('#product_unit_id').val(product.product_unit_id).trigger('change');
                $('#product_description').val(product.product_description);
                $('#productModal .nav-tabs').show();
                $('#productValues').hide();
                $.each(product.cost,function(key,cost){
                    $('#tab_cost table tbody').append('<tr><td>'+cost.User.user_name+'</td><td>'+cost.Person.person_name+'</td><td>'+cost.product_cost_value+'</td><td>'+cost.product_cost_date+'</td></tr>');
                });
                $.each(product.price,function(key,price){
                    $('#tab_price table tbody').append('<tr><td>'+price.User.user_name+'</td><td>'+price.product_price_value+'</td><td>'+price.product_price_date+'</td></tr>');
                });
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

function newCost(){

    var erro = false;

    var cost = {
        product_id: $('#product_id').val(),
        provider_id: $('#new_provider_id').val(),
        product_cost_value: $('#new_product_cost_value').val()
    };

    if( !cost.provider_id.length ){
        $('#new_provider_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( parseFloat(cost.product_cost_value) < 0 ){
        $('#new_product_cost_value').parent('div').find('span.erro_message').text('Informe um valor válido.').fadeIn();
        erro = true;
    }

    if( erro ){
        return;
    }
    console.log(cost);
    $('.overlay').fadeIn();
    $.ajax({
        url: URI_PUBLIC + 'admin/product.php?module=insertCost',
        data: { cost: cost },
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var cost = data.data;
            $('#tab_cost table tbody').prepend('<tr><td>'+cost.User.user_name+'</td><td>'+cost.Person.person_name+'</td><td>'+cost.product_cost_value+'</td><td>'+cost.product_cost_date+'</td></tr>');
            tr = $('#table_product tbody tr[data-productid="'+cost.product_id+'"]');
            tr.find('td[data-label="Custo"]').text(cost.product_cost_value);
            $('.overlay').fadeOut();
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
            $('.overlay').fadeOut();
        }
    });

}

function newPrice(){

    var erro = false;

    var price = {
        product_id: $('#product_id').val(),
        product_price_value: $('#new_product_price_value').val()
    };

   if( parseFloat(price.product_price_value) < 0 ){
        $('#new_product_cost_value').parent('div').find('span.erro_message').text('Informe um valor válido.').fadeIn();
        erro = true;
    }

    if( erro ){
        return;
    }
    console.log(price);
    $('.overlay').fadeIn();
    $.ajax({
        url: URI_PUBLIC + 'admin/product.php?module=insertPrice',
        data: { price: price },
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var price = data.data;
            $('#tab_price table tbody').prepend('<tr><td>'+price.User.user_name+'</td><td>'+price.product_price_value+'</td><td>'+price.product_price_date+'</td></tr>');
            tr = $('#table_product tbody tr[data-productid="'+price.product_id+'"]');
            tr.find('td[data-label="Preco"]').text(price.product_price_value);
            $('.overlay').fadeOut();
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
            $('.overlay').fadeOut();
        }
    });

}

function submitProduct() {
    var erro        = false,
        post_url,
        tabCad      = $('.nav-tabs a[href="#tab_cad"]');
    var product = {
        product_id: $('#product_id').val(),
        product_active: $('#product_active').prop('checked') ? 'on' : null,
        product_name: $('#product_name').val(),
        product_unit_id: $('#product_unit_id').val(),
        product_ean: $('#product_ean').val(),
        product_description: $('#product_description').val(),
        product_provider_id: $('#provider_id').val().length ? $('#provider_id').val() : null,
        product_cost_value: parseFloat($('#product_cost_value').val()) > 0 ? parseFloat($('#product_cost_value').val()) : null,
        product_price_value: parseFloat($('#product_price_value').val()) > 0 ? parseFloat($('#product_price_value').val()) : null
    };

    var msg;

    if( !product.product_name.length ){
        tabCad.tab('show');
        $('#product_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if( !product.product_unit_id.length ){
        tabCad.tab('show');
        $('#product_unit_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        $('.tab_erro_cad').fadeIn();
        return;
    } else{
        $('.tab_erro_cad').fadeOut();
    }

    if (product.product_id.length > 1) {
        post_url = URI_PUBLIC + 'admin/product.php?module=edit&product_id=' + product_id;
        msg = 'Produto editado com sucesso!';
    } else {
        post_url = URI_PUBLIC + 'admin/product.php?module=insert';
        msg = 'Produto adicionado com sucesso!';
    }

    $('.overlay').fadeIn();

    console.log(product);
    $.ajax({
        url: post_url,
        type: 'POST',
        dataType: 'json',
        data: { data: product },
        success: function(data) {
            var modal = $('#productModal');
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

            if ( (result) && (result.error) ){
                result.error_cnpj ? product_cnpj.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn() : '';
                result.error_cpf  ? product_cpf.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn()  : '';
                result.error_rg   ? product_rg.parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn()   : '';

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
        url: URI_PUBLIC + 'admin/product.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#productTemplate').html(data).promise().done(function(){
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

function deleteProduct(productId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/product.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {product_id: productId},
        success: function(data) {
            showAlert('success', 'Produto excluída com sucesso!');

            atualizar();
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

// function newContact(){
//     var num     = pad($('.itemContact').length +1, 2),
//         contact = $('#contactModel').clone(true);
//
//     contact.addClass('itemContact');
//     contact.find('input[name="product_contact_id[]"]').val('');
//
//     addNumContact( contact, num );
//
//     $('#accordionContact').append(contact);
//
//     $('.itemContact').find('.collapse.in').collapse('toggle');
//
//     eventContact( contact );
//
//     return contact;
// }
//
// function addNumContact( contact, num ){
//     contact.css('display', 'block').attr('id', 'itemContact'+num);
//     contact.find('div.panel-heading').attr('id', 'headingContact'+num)
//         .find('a').attr('href', '#boxContact'+num).attr('aria-controls', 'boxContact'+num)
//         .find('span.contact_title').text('Contato ' + num);
//     contact.find('div.panel-collapse').attr('id', 'boxContact'+num).attr('aria-labelledby', 'headingContact'+num);
//     contact.find('input[name="product_contact_main[]"]').attr('id', 'product_contact_main'+num)
//         .parent('div').find('label').attr('for', 'product_contact_main'+num);
// }
//
// function eventContact( contact ) {
//
//     contact.find(".select2").select2();
//
//     contact.find('a[name="btnDelContact"]').confirmation({
//         title: 'Deseja realmente excluir o Contato?',
//         placement: 'left',
//         btnOkClass: 'btn btn-primary',
//         btnCancelClass: 'btn btn-default pull-right',
//         btnOkLabel: 'Sim',
//         btnCancelLabel: 'Não',
//         onConfirm: function(event, element){
//             var count = 1,
//                 itemContact,
//                 bMain = element.parents('.itemContact').find('input[name="product_contact_main[]"]').prop('checked');
//
//             element.parents('.itemContact').remove();
//
//             itemContact = $('.itemContact');
//             product_contact_id = contact.find('input[name="product_contact_id[]"]').val();
//             if( product_contact_id.length ){
//                 id = $('#delete_contact').val().length ? $('#delete_contact').val().split(',') : new Array();
//                 id.push( product_contact_id );
//                 $('#delete_contact').val( id.join(',') );
//             }
//             if ( itemContact.length > 0 ){
//                 itemContact.each(function(){
//                     addNumContact( $(this), pad(count, 2) );
//                     count++;
//                 });
//             } else {
//                 newContact();
//             }
//
//             if ( bMain ){
//                 $('.itemContact').eq(0).find('input[name="product_contact_main[]"]').prop('checked', true).trigger('change');
//             }
//         },
//         onCancel: function(){
//
//         }
//     });
//
//     contact.find('input[name="product_contact_main[]"]').change(function (){
//         var itemContact = $('.itemContact');
//
//         itemContact.find('input[name="product_contact_main[]"]').prop('checked', false);
//
//         itemContact.find('.glyphicon-star').removeClass('glyphicon-star').addClass('glyphicon-star-empty');
//
//         $(this).prop('checked', true);
//         $(this).parents('.itemContact').find('.glyphicon-star-empty').removeClass('glyphicon-star-empty').addClass('glyphicon-star');
//     });
//
//     $('#form_product_contact input, #form_product_contact span.select2').click(function(event){
//         $(this).parents('div.form-group').find('span.erro_message').fadeOut();
//     });
//
//     $('#productModal').on('hide.bs.modal', function(e) {
//         $('#form_product_contact input, #form_product_contact span.select2').each(function(){
//             $(this).parents('div.form-group').find('span.erro_message').fadeOut();
//         });
//     });
// }