$(document).ready(function() {
    setConfig();
});

function setConfig(){
    //$('.treeview').removeClass('active');
    $('#menuPerson').addClass('active');

    //$(".select2").select2();

    var table = $('#table_contact_type').DataTable( {
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
        // var select = $('select[name="table_person_contact_type_length"]').clone(true);
        //
        // $('#table_person_contact_type_length').find('label').text('');
        // $('#table_person_contact_type_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
        //     .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    $('#person_contact_typeModal').modal({
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
            deleteCategory($(this).attr('contact_typeid'));
        },
        onCancel: function(){

        }
    });

    $('#form_person_contact_type input, #form_person_contact_type span.select2').bind("click focus", function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#person_contact_typeModal').on('hide.bs.modal', function(e) {
        $('#form_person_contact_type input, #form_person_contact_type span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });

    $('#form_person_contact_type').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });
}

function cadCategory() {
    var modal = $('#contact_typeModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#contact_typeModalLabel').text('Cadastrar Tipo de Contato');
        $('#btnSubmit').text('Novo Tipo de Contato');
        $('#lastUpdate').css('display', 'none');
        $('#person_contact_type_id').val('');

        $('#form_contact_type')[0].reset();
    });

    modal.modal('show');
}

function editCategory(person_contact_typeId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/person_contact_type.php?module=edit',
        data: {person_contact_type_id: person_contact_typeId, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var person_contact_type = jQuery.parseJSON(data).data,
                modal  = $('#contact_typeModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#contact_typeModalLabel').text('Editar Tipo de Contato');
                $('#btnSubmit').text('Editar Tipo de Contato');
                $('#lastUpdate').css('display', person_contact_type.person_contact_type_update == null ? 'none' : 'block')
                    .text('Última atualização: ' + person_contact_type.person_contact_type_update);
                $('#person_contact_type_id').val(person_contact_type.person_contact_type_id);
                $('#person_contact_type_name').val(person_contact_type.person_contact_type_name);
            });

            modal.modal('show');
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function submitContactType() {
    var erro  = false,
        person_contact_type_id = $('#person_contact_type_id').val(),
        person_contact_type_url,
        msg;

    if($.trim($('#person_contact_type_name').val()) == ''){
        $('#person_contact_type_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        return;
    }

    if (person_contact_type_id.length > 1) {
        person_contact_type_url = URI_PUBLIC + 'admin/person_contact_type.php?module=edit&person_contact_type_id=' + person_contact_type_id;
        msg = 'Categoria editada com sucesso!';
    } else {
        person_contact_type_url = URI_PUBLIC + 'admin/person_contact_type.php?module=insert';
        msg = 'Categoria adicionada com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_person_contact_type')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: person_contact_type_url,
        type: 'POST',
        dataType: 'json',
        data: $('#form_contact_type').serialize(),
        success: function(data) {
            var modal = $('#contact_typeModal');

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
        url: URI_PUBLIC + 'admin/person_contact_type.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1},
        success: function(data) {
            $('#contact_typeTemplate').html(data).promise().done(function(){
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

function deleteCategory(person_contact_typeId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/person_contact_type.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {person_contact_type_id: person_contact_typeId},
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
