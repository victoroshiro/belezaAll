function setPushOneSignal(token){
    $.ajax({
        method: "POST",
        url: "/prestador-servico/push/",
        data: "push=" + token,
        error: function(data){
            swal("Erro", data.responseText, "error");
        }
    });
}