
function buscaSubmenu(id){
		
	$.ajax({
    	url: "/admin/publico/buscamenu/idmenu/"+id,
    	success: function(data) {
    		var texto = data;
    		texto = texto.replace(/\n/g, '');
    		texto = unescape(texto);
    		
    		if(texto == 1){
    			jConfirm("Sua sessão expirou! Faça um novo acesso.","Erro!", function(r) {
    				if(r==true){
    					window.location='/';
    				}
    			});
    		}else{
    			$("#"+id).html(data);
    		}
    	}
    });
	
}

function buscaPedidosabertos(){
	
	$.ajax({
    	url: "/admin/index/buscapedidosabertos",
    	success: function(data) {
    		$('#npedidosabertos').html(data);
    	}
    });	
}


/*--------- grava relatos de erros - sugestao ------------------*/
function gravarReporte(){	
	$.post('/admin/index/gravarreporte', { 
		pagina: 	$("input[name=pagina]").val(), 
		reporte: 	$("#reporte").val(),
		prioridade: $("select[name=prioridade] option:selected").val()}, 
    function(resposta) {
        if (resposta != false) {
        	jAlert('Erro ao salvar o reporte! Tente novamente.','Erro!');
        }else {
        	jAlert('Reporte salvo com sucesso!','Sucesso!');        	        	
        }
    });	
}

/*$.ajax({
	url: '/admin/administracao/qtatividade',
	success: function(data) {
		var dados = data.split("|");
		var total = parseInt(dados[0])+parseInt(dados[1]);
		$('#countatividades').html(total);
	}
});

$.ajax({
	url: '/admin/cadastro/contadorinteracoes/pendentes/1/user/1/todos/1',
	success: function(data) {
		var total = parseInt(data);
		$('#countinteracoes').html(total);
	}
});*/
