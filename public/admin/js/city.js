$(document).ready(function() {
    setConfig();
});

function setConfig(){
    $('.treeview').removeClass('active');
    $('#menuCity').addClass('active');

    $(".select2").select2();

    var table = $('#table_city').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },

        // "columnDefs": [ {
        //     "targets": [ 3, 4 ],
        //     "searchable": false,
        //     "orderable": false
        // },{
        //     "targets": [ 1, 3, 4 ],
        //     "width": "10%"
        // }]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_city_length"]').clone(true);

        $('#table_city_length').find('label').text('');
        $('#table_city_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
            .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    // $('#cityModal').modal({
    //     show: false,
    //     backdrop: 'static'
    // });
    //
    // $('a[name="btnDel"]').confirmation({
    //     title: 'Deseja realmente excluir?',
    //     placement: 'left',
    //     btnOkClass: 'btn btn-primary',
    //     btnCancelClass: 'btn btn-default pull-right',
    //     btnOkLabel: 'Sim',
    //     btnCancelLabel: 'Não',
    //     onConfirm: function(){
    //         deleteCity($(this).attr('cityid'));
    //     },
    //     onCancel: function(){
    //
    //     }
    // });
    //
    // $('#form_city input, #form_city span.select2').bind("click focus", function(event){
    //     $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    // });
    //
    // $('#cityModal').on('hide.bs.modal', function(e) {
    //     $('#form_city input, #form_city span.select2').each(function(){
    //         $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    //     });
    // });
    //
    // $('#form_user').on('submit', function(e){
    //     e.stopPropagation();
    //     e.preventDefault();
    // });

    $('#filter_uf').change(function(){
        atualizar();
    })
}

// function cadCity() {
//     var modal = $('#cityModal');
//
//     modal.off('show.bs.modal').on('show.bs.modal', function(e) {
//         $('#cityModalLabel').text('Cadastrar Cidade');
//         $('#btnSubmit').text('Nova Cidade');
//         $('#lastUpdate').css('display', 'none');
//         $('#city_id').val('');
//         $("#uf_id").val(null).trigger("change");
//
//         $('#form_city')[0].reset();
//     });
//
//     modal.modal('show');
// }
//
// function editCity(cityId) {
//
//     $.ajax({
//         url: URI_PUBLIC + 'admin/city.php?module=edit',
//         data: {city_id: cityId, json : 1},
//         type: 'GET',
//         dataType: 'html',
//         success: function(data) {
//             var city  = jQuery.parseJSON(data).data,
//                 modal = $('#cityModal');
//
//             modal.off('show.bs.modal').on('show.bs.modal', function(e) {
//                 $('#cityModalLabel').text('Editar Cidade');
//                 $('#btnSubmit').text('Atualizar Cidade');
//                 $('#lastUpdate').css('display', city.city_update == null ? 'none' : 'block')
//                     .text('Última atualização: ' + city.city_update);
//                 $('#city_id').val(city.city_id);
//                 $('#city_name').val(city.city_name);
//                 $('#uf_id').val(city.uf_id).trigger("change");
//                 $('#city_ibge').val(city.city_ibge);
//             });
//
//             modal.modal('show');
//         },
//         error: function(data) {
//             console.log(data);
//             alert("Algo de errado ocorreu!");
//         }
//     });
// }
//
// function submitCity() {
//     var erro    = false,
//         city_id = $('#city_id').val(),
//         city_url,
//         msg;
//
//     if($.trim($('#city_name').val()) == ''){
//         $('#city_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
//         erro = true;
//     }
//     if($.trim($('#uf_id').val()) == ''){
//         $('#uf_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
//         erro = true;
//     }
//
//     if (erro){
//         return;
//     }
//
//     if (city_id.length > 1) {
//         city_url = URI_PUBLIC + 'admin/city.php?module=edit&city_id=' + city_id;
//         msg = 'Cidade editada com sucesso!';
//     } else {
//         city_url = URI_PUBLIC + 'admin/city.php?module=insert';
//         msg = 'Cidade adicionada com sucesso!';
//     }
//
//     $('.overlay').fadeIn();
//
//     var formData = new FormData($('#form_city')[0]);
//     formData.append('json', '1' );
//
//     $.ajax({
//         url: city_url,
//         type: 'POST',
//         dataType: 'html',
//         data: formData,
//         cache: false,
//         contentType: false,
//         processData: false,
//         success: function(data) {
//             var modal = $('#cityModal');
//
//             $('.overlay').fadeOut();
//
//             modal.on('hidden.bs.modal', function(e) {
//                 atualizar();
//             });
//
//             modal.modal('hide');
//
//             showAlert('success', msg);
//         },
//         error: function(data) {
//             var result = jQuery.parseJSON(data.responseText);
//
//             $('.overlay').fadeOut();
//
//             if ( result ){
//                 console.log(result);
//                 showAlert('warning', result.data.message);
//             } else {
//                 console.log(data);
//                 alert("Algo de errado ocorreu!");
//             }
//         }
//     });
// }

function atualizar(){
    var uf_id = $('#filter_uf').val();

    $('.loading').fadeIn();

    $.ajax({
        url: URI_PUBLIC + 'admin/city.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1, uf_id: uf_id},
        success: function(data) {
            $('#cityTemplate').html(data).promise().done(function(){
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#filter_uf').val(uf_id).trigger('change');

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

// function deleteCity(cityId) {
//     $.ajax({
//         url: URI_PUBLIC + 'admin/city.php?module=del',
//         type: 'POST',
//         dataType: 'html',
//         data: {city_id: cityId},
//         success: function(data) {
//             showAlert('success', 'Cidade excluída com sucesso!');
//
//             atualizar();
//         },
//         error: function(data) {
//             var result = jQuery.parseJSON(data.responseText);
//
//             if ( result ){
//                 console.log(result);
//                 showAlert('warning', result.data.message);
//             } else {
//                 console.log(data);
//                 alert("Algo de errado ocorreu!");
//             }
//         }
//     });
// }
