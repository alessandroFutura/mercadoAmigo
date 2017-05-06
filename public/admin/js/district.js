var bChangeCity = false,
    cityID      = 3468;

$(document).ready(function() {
    setConfig();
    //$('#filter_uf').val('RJ').trigger('change');
});

function setConfig(){
    $('.treeview').removeClass('active');
    $('#menuDistrict').addClass('active');

    $(".select2").select2();

    var table = $('#table_district').DataTable( {
        "language": { "url": URI_PUBLIC_FILES + "DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 2, 3 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 2, 3 ],
            "width": "10%"
        }]
    });

    table.on( 'init', function () {
        var select = $('select[name="table_district_length"]').clone(true);

        $('#table_district_length').find('label').text('');
        $('#table_district_length').find('label').append('<span class="registersView visible-xs">Exibir </span>').append(select)
            .append('<span class="hidden-xs"> Resultados por página </span>');
    } );

    $('#districtModal').modal({
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
            deleteDistrict($(this).attr('districtid'));
        },
        onCancel: function(){

        }
    });

    $('#form_district input, #form_district span.select2').bind("click focus", function(event){
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#districtModal').on('hide.bs.modal', function(e) {
        $('#form_district input, #form_district span.select2').each(function(){
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });

    $('#form_user').on('submit', function(e){
        e.stopPropagation();
        e.preventDefault();
    });

    $('#uf_id').change(function(){
        getCity( $(this).val(), $('#city_id') )
    });
    $('#filter_uf').change(function(){
        getCity( $(this).val(), $('#filter_city') )
    });

    $('#filter_city').change(function(){
        cityID = $(this).val();

        atualizar();
    })
}

function getCity(ufId, city){

    if ( ufId == null){
        return;
    }

    city.find('option').remove();
    city.prop('disabled', true);
    if ( cityID > 0 ){
        city.append('<option value="" disabled="disabled">Selecione a Cidade</option>');
    } else{
        city.append('<option value="" disabled="disabled" selected="selected">Selecione a Cidade</option>');
    }

    $.ajax({
        url: URI_PUBLIC + 'admin/city.php?module=getListCityUF',
        data: {uf_id: ufId, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var l_city = jQuery.parseJSON(data).data;

            city.prop('disabled', false);

            $.each(l_city, function( index, value ) {
                if ( cityID == value.city_id ) {
                    city.append('<option value="' + value.city_id + '" selected="selected">' + value.city_name + '</option>');
                } else {
                    city.append('<option value="' + value.city_id + '">' + value.city_name + '</option>');
                }
            });

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

function cadDistrict() {
    var modal = $('#districtModal');

    modal.off('show.bs.modal').on('show.bs.modal', function(e) {
        $('#districtModalLabel').text('Cadastrar Bairro');
        $('#btnSubmit').text('Novo Bairro');
        $('#lastUpdate').css('display', 'none');
        $('#district_id').val('');
        $("#uf_id").val($("#filter_uf").val()).trigger("change");
        $("#city_id").val(null).trigger("change").prop('disabled', true);

        $('#form_district')[0].reset();
    });

    modal.modal('show');
}

function editDistrict(districtId) {

    $.ajax({
        url: URI_PUBLIC + 'admin/district.php?module=edit',
        data: {district_id: districtId, get_City: 1, json : 1},
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            var district  = jQuery.parseJSON(data).data,
                modal = $('#districtModal');

            modal.off('show.bs.modal').on('show.bs.modal', function(e) {
                $('#districtModalLabel').text('Editar Bairro');
                $('#btnSubmit').text('Atualizar Bairro');
                $('#lastUpdate').css('display', district.district_update == null ? 'none' : 'block')
                    .text('Última atualização: ' + district.district_update);
                $('#district_id').val(district.district_id);
                $('#district_name').val(district.district_name);

                cityID = district.city_id;

                $('#uf_id').val(district.City.uf_id).trigger('change');
            });

            modal.modal('show');
        },
        error: function(data) {
            console.log(data);
            alert("Algo de errado ocorreu!");
        }
    });
}

function submitDistrict() {
    var erro        = false,
        district_id = $('#district_id').val(),
        district_url,
        msg;

    if($.trim($('#district_name').val()) == ''){
        $('#district_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }
    if($.trim($('#city_id').val()) == ''){
        $('#city_id').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        erro = true;
    }

    if (erro){
        return;
    }

    if (district_id.length > 1) {
        district_url = URI_PUBLIC + 'admin/district.php?module=edit&district_id=' + district_id;
        msg = 'Bairro editado com sucesso!';
    } else {
        district_url = URI_PUBLIC + 'admin/district.php?module=insert';
        msg = 'Bairro adicionado com sucesso!';
    }

    $('.overlay').fadeIn();

    var formData = new FormData($('#form_district')[0]);
    formData.append('json', '1' );

    $.ajax({
        url: district_url,
        type: 'POST',
        dataType: 'html',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            var modal = $('#districtModal');

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
    var uf_id   = $('#filter_uf').val(),
        city_id = $('#filter_city').val();

    $('.loading').fadeIn();

    $.ajax({
        url: URI_PUBLIC + 'admin/district.php?module=getList',
        type: 'POST',
        dataType: 'html',
        data: {ajax: 1, city_id: city_id},
        success: function(data) {
            $('#districtTemplate').html(data).promise().done(function(){
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                setConfig();

                $('#filter_uf').val(uf_id).trigger('change');

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

function deleteDistrict(districtId) {
    $.ajax({
        url: URI_PUBLIC + 'admin/district.php?module=del',
        type: 'POST',
        dataType: 'html',
        data: {district_id: districtId},
        success: function(data) {
            showAlert('success', 'Bairro excluído com sucesso!');

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
