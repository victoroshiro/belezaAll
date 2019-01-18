const API = location.protocol + "//" + location.hostname + ":" +  location.port;
const CEPAPI = "https://api.postmon.com.br/v1/cep/";
const MAPSKEY = "AIzaSyA31qhfrQoLVgQe7DVC3lVrofvQIB8x554";

var DataTable, crop, gallery;

if($(".dataTable").length){
    DataTable = $(".dataTable").DataTable({
        responsive: true,
        dom: 'Brftip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        order: [],
        language: {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        }
    });
}

function changeCities(states, cities, submit, city){
    var state = states.val();
    submit.attr("disabled", "disabled");

    $.ajax({
        url: API + "/api/state/" + state + "/cities/",
        dataType: "json",
        success: function(data){
            cities.empty();

            for(let i = 0; i < data.length; i = i + 1){
                if(city == data[i].name){
                    cities.append("<option value='" + data[i].id + "' selected>" + data[i].name + "</option>");
                }
                else{
                    cities.append("<option value='" + data[i].id + "'>" + data[i].name + "</option>");
                }
            }

            $(".bootstrap-select select").selectpicker("refresh");           

            submit.removeAttr("disabled");
        },
        error: function(data){
            console.log(data);
        }
    });
}

function getLocation(cep, success, error){
    var location = {};

    $.ajax({
        url: CEPAPI + cep,
        data: $(this).serialize(),
        success: function(data){
            location.cep = data.cep;
            location.street = data.logradouro;
            location.neighborhood = data.bairro;
            location.state = data.estado;
            location.city = data.cidade;

            if(typeof success === "function"){
                success(location);
            }
        },
        error: function(data){
            if(typeof error === "function"){
                error(data);
            }
        }
    });
}

function changeAdressForm(location){
    var states = $("select[name='state'] option");
    $("input[name='street']").val(location.street).parents(".form-line").addClass("focused");
    $("input[name='neighborhood']").val(location.neighborhood).parents(".form-line").addClass("focused");

    for(let i = 0; i < states.length; i = i + 1){
        if(states[i].value == location.state){
            $("select[name='state']").get(0).selectedIndex = i;
            break;
        }
    }

    changeCities($("select[name='state']"), $("select[name='city']"), $("form button, form input[type='submit']"), location.city);
}

function getCoords(form, success, error){
    $.ajax({
        url: "https://maps.google.com/maps/api/geocode/json?key=" + MAPSKEY + "&address=" + form.find("input[name='street']").val() + ", " + form.find("input[name='number']").val() + " - " + form.find("input[name='neighborhood']").val() + " - " + form.find("input[name='cep']").val(),
        type: "GET",
        dataType: "json",
        success: function(data){
            if(data.status == "OK" && data.results[0] && data.results[0].geometry.location){
                form.find("input[name='coord_x']").val(data.results[0].geometry.location.lat);
                form.find("input[name='coord_y']").val(data.results[0].geometry.location.lng);

                if(success){
                    success();
                }
            }
            else{
                if(error){
                    getCoordsWithoutPrecision(form, success, error);
                }
            }
        },
        error: function(data){
            console.log(data);

            if(error){
                getCoordsWithoutPrecision(form, success, error);
            }
        }
    });
}

function getCoordsWithoutPrecision(form, success, error){
    $.ajax({
        url: "https://maps.google.com/maps/api/geocode/json?key=" + MAPSKEY + "&address=" + form.find("input[name='cep']").val(),
        type: "GET",
        dataType: "json",
        success: function(data){
            if(data.status == "OK" && data.results[0] && data.results[0].geometry.location){
                form.find("input[name='coord_x']").val(data.results[0].geometry.location.lat);
                form.find("input[name='coord_y']").val(data.results[0].geometry.location.lng);

                if(success){
                    success();
                }
            }
            else{
                if(error){
                    error();
                }
            }
        },
        error: function(data){
            console.log(data);

            if(error){
                error();
            }
        }
    });
}

gallery = $("#img-gallery");

function verifyGallery(){
    if(gallery.find(".gallery-item, .gallery-fake-item").length >= gallery.attr("data-limit")){
        gallery.find(".add-image").fadeOut();
    }
    else{
        gallery.find(".add-image").fadeIn();
    }
}

function initGallery(){
    if(gallery.length){
        gallery.lightGallery({
            thumbnail: true,
            selector: "a"
        });
    
        verifyGallery();
    }
}

initGallery();

$("#img-modal input[type='file']").change(function(e){
    var width = $(this).attr("data-width");
    var height = $(this).attr("data-height");

    if(e.target.files[0]){
        var reader = new FileReader();
        reader.onload = function(){
            $("#img-modal .modal-body .croppie-container").remove();
            $("#img-modal .modal-body").append("<img class='crop-img' />");
            $("#img-modal .crop-img").attr("src", reader.result);

            crop = $("#img-modal .crop-img").croppie({
                viewport: { width: Number(width), height: Number(height) },
                boundary: { width: 850, height: 450 }
            });

            $("#img-modal .save-img").removeAttr("disabled");
        };
        reader.readAsDataURL(event.target.files[0]);
    }
});

function resetCrop(){
    $("#img-modal input[type='file']").val("");
    $("#img-modal .modal-body .croppie-container").remove();
    $("#img-modal .save-img").attr("disabled", "disabled");
}

$("#img-modal .close-img").click(function(){
    $("#img-modal").modal("toggle");

    resetCrop();
});

function removeFromGallery(e){
    e.preventDefault();

    $(e.target).parents(".gallery-item, .gallery-fake-item").remove();
    gallery.data("lightGallery").destroy(true);

    setTimeout(function(){
        initGallery();
    });
}

$("#img-modal .save-img").click(function(){
    if(crop){
        gallery.data("lightGallery").destroy(true);

        crop.croppie("result", "base64").then(function(base64){
            $("<div class='gallery-item col-xs-3 col-lg-3 col-md-3 col-sm-6'>\
                    <a href='" + base64 + "'>\
                        <img class='img-responsive thumbnail' src='" + base64 + "' />\
                        <i onclick='removeFromGallery(event)' class='material-icons'>close</i>\
                    </a>\
                </div>").insertBefore("#img-gallery .add-image");
        });

        $("#img-modal").modal("toggle");

        resetCrop();

        setTimeout(function(){
            initGallery();
        });
    }
});

function getGallery(){
    var result = [];

    $("#img-gallery .gallery-item img").each(function(){
        result.push(this.src);
    });

    return JSON.stringify(result);
}

function getOldGallery(){
    var result = [];

    $("#img-gallery .gallery-fake-item img").each(function(){
        result.push(this.getAttribute("data-id"));
    });

    return JSON.stringify(result);
}

function removeFromTable(e){
    if(e.parents("tbody").find("tr").length <= 1){
        e.parents(".tab-pane").append("<p>Não há senhas aguardando</p>");
        e.parents(".tab-pane").find(".table-responsive").remove();
    }
    else{
        e.parents("tr").remove();
    }
}

function validCNPJ(cnpj) {
 
    cnpj = cnpj.replace(/[^\d]+/g,'');
 
    if(cnpj == '') return false;
     
    if (cnpj.length != 14)
        return false;
 
    // Elimina CNPJs invalidos conhecidos
    if (cnpj == "00000000000000" || 
        cnpj == "11111111111111" || 
        cnpj == "22222222222222" || 
        cnpj == "33333333333333" || 
        cnpj == "44444444444444" || 
        cnpj == "55555555555555" || 
        cnpj == "66666666666666" || 
        cnpj == "77777777777777" || 
        cnpj == "88888888888888" || 
        cnpj == "99999999999999")
        return false;
         
    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0,tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0))
        return false;
         
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1))
          return false;
           
    return true;
}

function validCPF(strCPF){
    strCPF = strCPF.replace(/[^\d]+/g,'');

    var Soma;
    var Resto;
    Soma = 0;
	if (strCPF == "00000000000") return false;
    
	for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
	Resto = (Soma * 10) % 11;
	
    if ((Resto == 10) || (Resto == 11))  Resto = 0;
    if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
	
	Soma = 0;
    for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
    Resto = (Soma * 10) % 11;
	
    if ((Resto == 10) || (Resto == 11))  Resto = 0;
    if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
    return true;
}