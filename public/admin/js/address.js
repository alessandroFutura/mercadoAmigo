var cep = null;
var oDistrict = null;
var cityID = 0;
var districtID = 0;

function searchCep( a )
{
    getCep( $(a).parent().prev() );
}

function getCep( inputCEP ){

    address = inputCEP.parents('.collapse').first();
    if($.trim( inputCEP.val()) == ''){
        showAlert('warning', 'Favor informar o cep.');
        return;
    } else if( !(inputCEP.inputmask("isComplete")) ){
        showAlert('warning', 'Cep incompleto.');
        return;
    }

    $.ajax({
        url: "http://viacep.com.br/ws/" +inputCEP.val()+ "/json/",
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if( data.erro != null ){
                showAlert('warning', 'CEP não encontrado!');
            }
            else {
                cep = data;
                address.find('select[name="filter_city[]"]').removeAttr('city_id');
                address.find('select[name="district_id[]"]').removeAttr('district_id');
                address.find('select[name="filter_uf[]"]').val(cep.uf).trigger("change");
                address.find('input[name="address_public_place[]"]').val(cep.logradouro).parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeOut();
                address.find('input[name="address_number[]"]').focus();
            }
        },
        error: function(data) {
            console.log(data);
            showAlert('warning', 'Algo de errado ocorreu!');
        }
    });
}

function getCityDistrict(city){
    $.ajax({
        url: URI_PUBLIC + 'admin/district.php?module=getByIbgeCity',
        data: {district_name: cep.bairro, city_ibge: cep.ibge, json : 1},
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            oDistrict = data.data;
            city.val(oDistrict.city_id).trigger("change");
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

function newAddress(){

    var num = pad($('.itemAddress').length +1, 2);
    var address = $('#addressModel').clone(true);

    address.addClass('itemAddress');
    address.find('input[name="address_id[]"]').val('');
    address.find('.glyphicon-plane').removeClass('address-delivery');

    addNumAddress( address, num );
    $('#accordionAddress').append(address);
    $('.itemAddress').find('.collapse.in').collapse('toggle');

    eventAddress( address );

    return address;
}

function eventAddress( address ) {

    address.find(".select2").select2();
    address.find('input[name="address_cep[]"]').inputmask();

    address.find('a[name="btnDelAddress"]').confirmation({
        title: 'Deseja Realmente Excluir o Endereço?',
        placement: 'left',
        btnOkClass: 'btn btn-primary',
        btnCancelClass: 'btn btn-default pull-right',
        btnOkLabel: 'Sim',
        btnCancelLabel: 'Não',
        onConfirm: function (event, element) {
            var count = 1,
            itemAddres,
            bMain = element.parents('.itemAddress').find('input[name="address_main[]"]').prop('checked'),
            cMain = element.parents('.itemAddress').find('input[name="address_delivery[]"]').prop('checked');

            element.parents('.itemAddress').remove();

            itemAddres = $('.itemAddress');
            address_id = address.find('input[name="address_id[]"]').val();
            if( address_id.length ){
                console.log(address_id);
                id = $('#delete_address').val().length ? $('#delete_address').val().split(',') : new Array();
                id.push( address_id );
                $('#delete_address').val( id.join(',') );
            }
            if (itemAddres.length > 0) {
                itemAddres.each(function () {
                    addNumAddress($(this), pad(count, 2));
                    count++;
                });
            } else {
                newAddress();
            }

            if (bMain) {
                $('.itemAddress').eq(0).find('input[name="address_main[]"]').prop('checked', true).trigger('change');
            }
            if (cMain) {
                $('.itemAddress').eq(0).find('input[name="address_delivery[]"]').prop('checked', true).trigger('change');
            }
        },
        onCancel: function () {

        }
    });

    address.find('input[name="address_main[]"]').change(function () {
        var itemAddress = $('.itemAddress');

        itemAddress.find('input[name="address_main[]"]').prop('checked', false);

        itemAddress.find('.glyphicon-star').removeClass('glyphicon-star').addClass('glyphicon-star-empty');

        $(this).prop('checked', true);
        $(this).parents('.itemAddress').find('.glyphicon-star-empty').removeClass('glyphicon-star-empty').addClass('glyphicon-star');
    });

    address.find('input[name="address_delivery[]"]').change(function () {
        var itemAddress = $('.itemAddress');

        itemAddress.find('input[name="address_delivery[]"]').prop('checked', false);

        itemAddress.find('.glyphicon-plane').removeClass('address-delivery');

        $(this).prop('checked', true);
        $(this).parents('.itemAddress').find('.glyphicon-plane').addClass('address-delivery');
    });

    $('#form_address input, #form_address span.select2').click(function (event) {
        $(this).parents('div.form-group').find('span.erro_message').fadeOut();
    });

    $('#personModal').on('hide.bs.modal', function (e) {
        $('#form_address input, #form_address span.select2').each(function () {
            $(this).parents('div.form-group').find('span.erro_message').fadeOut();
        });
    });

    address.find('select[name="filter_uf[]"]').change(function () {
        if (!$(this).val()) {
            return;
        }

        var parent = $(this).parents('.panel-collapse').first();
        var city = parent.find('select[name="filter_city[]"]');
        var district = parent.find('select[name="district_id[]"]');

        district.find('option').remove();
        district.prop('disabled', true);

        city.find('option').remove();
        city.prop('disabled', false);
        city.append('<option value="" disabled="disabled" selected="selected">Selecione a Cidade</option>');

        $.ajax({
            url: URI_PUBLIC + 'admin/city.php?module=getListCityUF',
            data: {uf_code: $(this).val(), json: 1},
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                var l_city = data.data;

                $.each(l_city, function (index, value) {
                    city.append('<option value="' + value.city_id + '">' + value.city_name + '</option>');
                });

                if (cep != null) {
                    getCityDistrict(city);
                    cep = null;
                }

                if (cityID > 0 || city.attr('city_id').length) {
                    cityID = cityID ? cityID : city.attr('city_id');
                    city.val(cityID).trigger('change');
                    cityID = 0;
                }
            },
            error: function (data) {
                var result = jQuery.parseJSON(data.responseText);

                $('.overlay').fadeOut();

                if (result) {
                    console.log(result);
                    showAlert('warning', result.data.message);
                } else {
                    console.log(data);
                    alert("Algo de errado ocorreu!");
                }
            }
        });
    });

    address.find('select[name="filter_city[]"]').change(function () {
        if (!$(this).val()) {
            return;
        }

        var district = $(this).parents('.panel-collapse').first().find('select[name="district_id[]"]');
        district.find('option').remove();
        district.prop('disabled', false);
        district.append('<option value="" disabled="disabled" selected="selected">Selecione o Bairro</option>');

        $.ajax({
            url: URI_PUBLIC + 'admin/district.php?module=getListDistrictCity',
            data: {city_id: $(this).val(), json: 1},
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                var l_district = jQuery.parseJSON(data).data;

                $.each(l_district, function (index, value) {
                    district.append('<option value="' + value.district_id + '">' + value.district_name + '</option>');
                });

                if (oDistrict != null) {
                    district.val(oDistrict.district_id).trigger("change").parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeOut();
                    oDistrict = null;
                }

                if (districtID > 0 || district.attr('district_id').length) {
                    districtID = districtID ? districtID : district.attr('district_id');
                    district.val(districtID).trigger('change');
                    districtID = 0;
                }
            },
            error: function (data) {
                var result = jQuery.parseJSON(data.responseText);

                $('.overlay').fadeOut();

                if (result) {
                    console.log(result);
                    showAlert('warning', result.data.message);
                } else {
                    console.log(data);
                    alert("Algo de errado ocorreu!");
                }
            }
        });
    });

    address.find('input[name="address_cep[]"]').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            getCep($(this));
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
}

function addNumAddress( address, num ){
    address.css('display', 'block').attr('id', 'itemAddress'+num);
    address.find('div.panel-heading').attr('id', 'headingAddress'+num).find('a').attr('href', '#boxAddress'+num).attr('aria-controls', 'boxAddress'+num).find('span.address_title').text('Endereço ' + num);
    address.find('div.panel-collapse').attr('id', 'boxAddress'+num).attr('aria-labelledby', 'headingAddress'+num);
    address.find('input[name="address_main[]"]').attr('id', 'address_main'+num).parent('div').find('label').eq(0).attr('for', 'address_main'+num);
    address.find('input[name="address_delivery[]"]').attr('id', 'address_delivery'+num).parent('div').find('label').eq(1).attr('for', 'address_delivery'+num);
}