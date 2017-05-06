$(document).ready(function() {
    setConfig();
});

function setConfig(){
    //$('.treeview').removeClass('active');
    $('#menuPerson').addClass('active');

    //$(".select2").select2();

    var table = $('#table_category').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 1, 2 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 1, 2],
            "width": "10%"
        }]
    });

    table.on( 'init', function () {
        // var select = $('select[name="table_person_category_length"]').clone(true);
        //
        // $('#table_person_category_length').find('label').text('');
        // $('#table_person_category_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
        //     .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    $('#person_categoryModal').modal({
        show: false,
        backdrop: 'static'
    });

    $('a[name="btnDel"]').confirmation({
        title: 'Confirmar Exclusão?',
        placement: 'left',
        btnOkClass: 'btn btn-primary',
        btnCancelClass: 'btn btn-default pull-right',
        btnOkLabel: 'Sim',
        btnCancelLabel: 'Não',
        onConfirm: function(){
            deleteCategory($(this).attr('categoryid'));
        },
        onCancel: function(){

        }
    });

    $('#form_person_category input, #form_person_category span.select2').bind("click focus", function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#person_categoryModal').on('hide.bs.modal', function(e) {
        $('#form_person_category input, #form_person_category span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });

    $('#form_person_category').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });
}

function cadCategory() {
    var modal = $('#categoryModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#categoryModalLabel').text('Cadastrar Categoria de Pessoa');
        $('#btnSubmit').text('Nova Categoria');
        $('#lastUpdate').css('display', 'none');
        $('#person_category_id').val('');

        $('#form_category')[0].reset();
    });

    modal.modal('show');
}

function editCategory(person_categoryId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/person_category.php?module=edit',
        data: {person_category_id: person_categoryId, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var person_category = jQuery.parseJSON(data).data,
                modal  = $('#categoryModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#categoryModalLabel').text('Editar Categoria de Pessoa');
                $('#btnSubmit').text('Editar Categoria');
                $('#lastUpdate').css('display', person_category.person_category_update == null ? 'none' : 'block')
                    .text('Última atualização: ' + person_category.person_category_update);
                $('#person_category_id').val(person_category.person_category_id);
                $('#person_category_name').val(person_category.person_category_name);
            });

            modal.modal('show');
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function submitCategory() {
    var erro  = false,
        person_category_id = $('#person_category_id').val(),
        person_category_url,
        msg;

    if($.trim($('#person_category_name').val()) == ''){
        $('#person_category_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        return;
    }

    if (person_category_id.length > 1) {
        person_category_url = URI_PUBLIC + 'admin/person_category.php?module=edit&person_category_id=' + person_category_id;
        msg = 'Categoria editada com sucesso!';
    } else {
        person_category_url = URI_PUBLIC + 'admin/person_category.php?module=insert';
        msg = 'Categoria adicionada com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_person_category')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: person_category_url,
        type: 'POST',
        dataType: 'json',
        data: $('#form_category').serialize(),
        success: function(data) {
            var modal = $('#categoryModal');

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
        url: URI_PUBLIC + 'admin/person_category.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#categoryTemplate').html(data).promise().done(function(){
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

function deleteCategory(person_categoryId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/person_category.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {person_category_id: person_categoryId},
        success: function(data) {
            showAlert('success', 'Categoria excluída com sucesso!');

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
