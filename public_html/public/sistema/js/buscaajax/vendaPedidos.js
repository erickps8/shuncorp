$(document).ready(function(){
	
	buscaPedidos();
	
	$('#btnbusca').click(function(){
		buscaPedidos(1);
	});
	
});

function buscaPedidos(pagina){
	$("#resultado").show(500);
	$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;">'+
	'<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
	
	$.post('/admin/venda/buscapedidos', {
		id				: $("input[name=id]").val(),
		nfe				: $("input[name=nfe]").val(),
		buscasit 		: $("select[name=buscasit] option:selected").val(),
		cliente 		: $("select[name=cliente] option:selected").val(),
		representante 	: $("select[name=representante] option:selected").val(),
		televenda 		: $("select[name=televenda] option:selected").val(),
		tpdata			: $("input[name=tpdata]:checked").val(),
		dataini			: $("input[name=dataini]").val(),
		datafim			: $("input[name=datafim]").val(),
		page			: pagina
    },
    function(resposta) {
    	
    	$('#resultado').html(resposta);
		       
    });	
}
