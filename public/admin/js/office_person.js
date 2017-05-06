var timeError;

$(document).ready(function(){
    setConfigOfficePerson();
})

function setConfigOfficePerson() {

    $('.treeview').removeClass('active');
    $('#menuOffice').addClass('active');

    $('#form_person [data-mask]').inputmask();
    $('.datePicker').datepicker({
        autoclose: true
    });

    $('#personModalLabel').text('Adicionar Pessoa na Minha Rede');
    $('#btnSubmitEdu').text('Adicionar Pessoa na Minha Rede');
    $('#btnSubmitEdu').click(function(){
        submitPerson();
    });

    $('select[name="filter_uf[]"]').change(function () {
        if (!$(this).val()) {
            return;
        }

        var parent = $(this).parents('.address').first();
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

                if( cityID > 0 || city.attr('city_id') != null ) {
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

    $('select[name="filter_city[]"]').change(function () {
        if (!$(this).val()) {
            return;
        }

        var district = $(this).parents('.address').first().find('select[name="district_id[]"]');
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

                if (districtID > 0 || district.attr('district_id') != null ) {
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

    $('input[name="address_cep[]"]').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            getCep($(this));
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

}

function submitPerson()
{
    clearTimeout(timeError);
    $('span.erro_message').hide();
    erro = false;

    var person = {
        person_active: 'on',
        person_name: $('#person_name').val(),
        person_nickname: null,
        person_type: 'F',
        person_cnpj: null,
        person_cpf: $('#person_cpf').val(),
        person_rg: $('#person_rg').val(),
        person_birth: $('#person_birth').val().replace('_',''),
        person_gender: $('#person_gender').val(),
        person_category: new Array(1001),
        person_address: [{
            district_id: $('select[name="district_id[]"]').val(),
            address_main: 'Y',
            address_cep: $('input[name="address_cep[]"]').val(),
            address_public_place: $('input[name="address_public_place[]"]').val(),
            address_number: $('input[name="address_number[]"]').val(),
            address_complement: $('input[name="address_complement[]"]').val()
        }],
        person_contact: [{
            person_contact_type_id: 1003,
            person_contact_main: 'Y',
            person_contact_value: $('#contact_mail').val(),
            person_contact_name: null
        },{
            person_contact_type_id: 1001,
            person_contact_main: 'N',
            person_contact_value: $('#contact_cel1').val(),
            person_contact_name: $('#contact_cel1_op').val()
        }],
        person_bank : {
            bank_code: $('#bank_code').val(),
            person_bank_agency: $('#bank_agency').val(),
            person_bank_account: $('#bank_account').val(),
            person_bank_type: $('#bank_type').val()
        }
    };

    if( $('#contact_phone').val().length ){
        person.person_contact.push({
            person_contact_type_id: 1002,
            person_contact_main: 'N',
            person_contact_value: $('#contact_phone').val(),
            person_contact_name: null
        });
    }

    if( $('#contact_cel2').val().length ){
        person.person_contact.push({
            person_contact_type_id: 1003,
            person_contact_main: 'N',
            person_contact_value: $('#contact_cel2').val(),
            person_contact_name: $('#contact_cel2_op').val()
        });
    }

    if( $('#rede_type').length ){
        person.person_rede = $('#rede_type').val();
    }

    if( !person.person_bank.person_bank_type.length ){
        $('#bank_type').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#bank_type').focus();
        erro = true;
    }

    if( !person.person_bank.person_bank_account.length ){
        $('#bank_account').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#bank_account').focus();
        erro = true;
    }

    if( !person.person_bank.person_bank_agency.length ){
        $('#bank_agency').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#bank_agency').focus();
        erro = true;
    }

    if( !person.person_bank.bank_code.length ){
        $('#bank_code').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#bank_code').focus();
        erro = true;
    }

    if( !person.person_contact[1].person_contact_value.length ){
        $('#contact_cel1').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#contact_cel1').focus();
        erro = true;
    }

    if( !person.person_contact[0].person_contact_value.length ){
        $('#contact_mail').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#contact_mail').focus();
        erro = true;
    }
    else if ((person.person_contact[0].person_contact_value.indexOf("@") == -1) || ( person.person_contact[0].person_contact_value.indexOf(".") == -1 )){
        $('#contact_mail').parent('div').find('span.erro_message').text('Preencha o seu e-mail corretamente.').fadeIn();
        $('#contact_mail').focus();
        erro = true;
    }

    if( !person.person_address[0].address_number ){
        $('input[name="address_number[]"]').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('input[name="address_number[]"]').focus();
        erro = true;
    }

    if( !person.person_address[0].address_public_place ){
        $('input[name="address_public_place[]"]').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('input[name="address_public_place[]"]').focus();
        erro = true;
    }

    if( person.person_address[0].district_id == null ){
        if( $('select[name="filter_uf[]"]').val() == null ){
            $('select[name="filter_uf[]"]').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            $('select[name="filter_uf[]"]').focus();
        } else if( $('select[name="filter_city[]"]').val() == null ){
            $('select[name="filter_city[]"]').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            $('select[name="filter_city[]"]').focus();
        } else{
            $('select[name="district_id[]"]').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
            $('select[name="district_id[]"]').focus();
        }
        erro = true;
    }

    if( person.person_gender == null ){
        $('#person_gender').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#person_gender').focus();
        erro = true;
    }

    if( person.person_birth.length && person.person_birth.length < 10 ){
        $('#person_birth').parents('.form-group').first().find('span.erro_message').text('Preencha a Data Corretamente.').fadeIn();
        $('#person_birth').focus();
        erro = true;
    }

    if( !$('#person_cpf').inputmask("isComplete") ){
        $('#person_cpf').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#person_cpf').focus();
        erro = true;
    }

    if( !person.person_name.length ){
        $('#person_name').parent('div').find('span.erro_message').text('Este campo é obrigatório.').fadeIn();
        $('#person_name').focus();
        erro = true;
    }

    if( erro ){
        timeError = setTimeout(function(){
            $('span.erro_message').fadeOut();
        },5000);
        return;
    }

    $('.overlay').fadeIn();
    $.ajax({
        url: URI_PUBLIC + 'admin/office.php?module=addNewClient',
        type: 'POST',
        dataType: 'json',
        data: { data: person },
        success: function( data ) {
            resetFormCaralho();
            $('.overlay').fadeOut();
            showAlert('success', 'Pessoa Adicionada a Rede com Sucesso!');
        },
        error: function(data) {
            // var data = jQuery.parseJSON(data.responseText).data;
            // $('.overlay').fadeOut();
            // if ( (result) && (result.error) ){
            //     switch( data.status.erro_code ){
            //         case 101: $('#person_cpf').parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn();
            //         case 102: $('#person_rg').parent('div').find('span.erro_message').text('Documento já cadastrado.').fadeIn();
            //     }
            //     showAlert('warning', result.error);
            // } else {
            //     console.log(data);
            //     alert("Algo de errado ocorreu!");
            // }
        }
    });
}

function resetFormCaralho()
{
    $('#form_person').trigger('reset');
    $('select[name="person_gender"], select[name="filter_uf[]"], select[name="filter_city[]"], select[name="district_id[]"], select[name="bank_code"], select[name="bank_type"]').val(null).trigger('change');
}

function searchCep( a )
{
    getCep( $(a).parent().prev() );
}

function getCep( inputCEP ){

    address = inputCEP.parents('.address').first();
    if($.trim( inputCEP.val()) == ''){
        showAlert('warning', 'Favor informar o cep.');
        return;
    } else if( !(inputCEP.inputmask("isComplete")) ){
        showAlert('warning', 'Cep incompleto.');
        return;
    }
    $('.overlay').fadeIn(function() {
        $.ajax({
            url: "http://viacep.com.br/ws/" + inputCEP.val() + "/json/",
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('.overlay').fadeOut();
                if (data.erro != null) {
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
            error: function (data) {
                $('.overlay').fadeOut();
                console.log(data);
                showAlert('warning', 'Algo de errado ocorreu!');
            }
        });
    });
}