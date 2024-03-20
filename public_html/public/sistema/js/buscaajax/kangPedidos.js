$(document).ready(function(){
	
	var pagina = 1;
	
	//-- Ppaginacao ------------------------------------------------
	$('.btnPaginator').live('click', function() {
		pagina = $(this).text();		
		$('#btnbusca').trigger('click');
	});
	
	$('#btnbusca').click(function(){
		
		$("#resultado").show(500);
		$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;">'+
		'<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
		
		$.post('/admin/kang/buscapedidos', {
			buscaid			: $("input[name=buscaid]").val(),
			order			: $("input[name=order]").val(),
			buscasit 		: $("select[name=buscasit] option:selected").val(),
			buscacli 		: $("select[name=buscacli] option:selected").val(),
			dataini			: $("input[name=dataini]").val(),
			datafin			: $("input[name=datafin]").val(),
			codigo			: $("input[name=codigo]").val(),
			page			: pagina
	    },
	    function(resposta) {
	    	
	    	$('#resultado').html(resposta);
			       
	    });	
	});
	
	$('#btnbusca').trigger('click');
});