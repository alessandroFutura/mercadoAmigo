$(document).ready(function() {
    setConfig();
});

function setConfig(){
    //$('.treeview').removeClass('active');
    $('#menuUF').addClass('active');

    // $(".select2").select2();

    var table = $('#table_uf').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" }
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
        var select = $('select[name="table_uf_length"]').clone(true);

        $('#table_uf_length').find('label').text('');
        $('#table_uf_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
            .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    // $('#ufModal').modal({
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
    //         deleteUF($(this).attr('ufid'));
    //     },
    //     onCancel: function(){
    //
    //     }
    // });
    //
    // $('#form_uf input, #form_uf span.select2').bind("click focus", function(event){
    //     $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    // });
    //
    // $('#ufModal').on('hide.bs.modal', function(e) {
    //     $('#form_uf input, #form_uf span.select2').each(function(){
    //         $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    //     });
    // });
    //
    // $('#form_user').on('submit', function(e){
    //     e.stopPropagation();
    //     e.preventDefault();
    // });
}
//
// function cadUF() {
//     var modal = $('#ufModal');
//
//     modal.off('show.bs.modal').on('show.bs.modal', function(e) {
//         $('#ufModalLabel').text('Cadastrar UF');
//         $('#btnSubmit').text('Nova UF');
//         $('#lastUpdate').css('display', 'none');
//         $('#uf_id').val('');
//
//         $('#form_uf')[0].reset();
//     });
//
//     modal.modal('show');
// }
//
// function editUF(ufId) {
//
//     $.ajax({
//         url: URI_PUBLIC + 'admin/uf.php?module=edit',
//         data: {uf_id: ufId, json : 1},
//         type: 'GET',
//         dataType: 'html',
//         success: function(data) {
//             var uf    = jQuery.parseJSON(data).data,
//                 modal = $('#ufModal');
//
//             modal.off('show.bs.modal').on('show.bs.modal', function(e) {
//                 $('#ufModalLabel').text('Editar UF');
//                 $('#btnSubmit').text('Atualizar UF');
//                 $('#lastUpdate').css('display', uf.uf_update == null ? 'none' : 'block')
//                     .text('Última atualização: ' + uf.uf_update);
//                 $('#uf_id').val(uf.uf_id);
//                 $('#uf_name').val(uf.uf_name);
//                 $('#uf_code').val(uf.uf_code);
//                 $('#uf_ibge').val(uf.uf_ibge);
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
// function submitUF() {
//     var erro  = false,
//         uf_id = $('#uf_id').val(),
//         uf_url,
//         msg;
//
//     if($.trim($('#uf_name').val()) == ''){
//         $('#uf_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
//         erro = true;
//     }
//     if($.trim($('#uf_code').val()) == ''){
//         $('#uf_code').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
//         erro = true;
//     }
//
//     if (erro){
//         return;
//     }
//
//     if (uf_id.length > 1) {
//         uf_url = URI_PUBLIC + 'admin/uf.php?module=edit&uf_id=' + uf_id;
//         msg = 'UF editada com sucesso!';
//     } else {
//         uf_url = URI_PUBLIC + 'admin/uf.php?module=insert';
//         msg = 'UF adicionada com sucesso!';
//     }
//
//     $('.overlay').fadeIn();
//
//     var formData = new FormData($('#form_uf')[0]);
//     formData.append('json', '1' );
//
//     $.ajax({
//         url: uf_url,
//         type: 'POST',
//         dataType: 'html',
//         data: formData,
//         cache: false,
//         contentType: false,
//         processData: false,
//         success: function(data) {
//             var modal = $('#ufModal');
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
//
// function atualizar(){
//     $.ajax({
//         url: URI_PUBLIC + 'admin/uf.php?module=getList',
//         type: 'POST',
//         dataType: 'html',
//         data: {ajax: 1},
//         success: function(data) {
//             $('#ufTemplate').html(data).promise().done(function(){
//                 $('body').removeClass('modal-open');
//                 $('.modal-backdrop').remove();
//
//                 setConfig();
//             });
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
//
// function deleteUF(ufId) {
//     $.ajax({
//         url: URI_PUBLIC + 'admin/uf.php?module=del',
//         type: 'POST',
//         dataType: 'html',
//         data: {uf_id: ufId},
//         success: function(data) {
//             showAlert('success', 'UF excluída com sucesso!');
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
