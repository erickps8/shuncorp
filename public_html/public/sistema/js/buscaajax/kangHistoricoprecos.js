$(document).ready(function(){

	$('#btnbusca').click(function(){
		
		var fornecedor = $("select[name=buscacli] option:selected").val();
		var codigo	   = $("input[name=codigo]").val();
		
		if(fornecedor != "0" || codigo != ""){
		
			$("#resultado").show(500);
			$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
			
			$.post('/admin/relatorios/buscahistoricopreco', {
				fornecedor 		: $("select[name=buscacli] option:selected").val(),
				codigo			: $("input[name=codigo]").val(),
		    },
		    function(resposta) {	    	
		    	$('#resultado').html(resposta);
				       
		    });
		}else{
			$("#resultado").show(500);
			$('#resultado').html('<center><br>Selecione um critério para pesquisa!</center>');
		}
	});
	
	$('#btnbusca').trigger('click');
	
	//-- impressao ----------------
	$('#btnImprimir').click(function(){
		
		var fornecedor = $("select[name=buscacli] option:selected").val();
		var codigo	   = $("input[name=codigo]").val();
		
		if(fornecedor != "0" || codigo != ""){
			window.open('/admin/relatorios/historicoprecosimp/fornecedor/'+fornecedor+'/codigo/'+codigo);
		}else{
			jAlert('Selecione um critério para pesquisa!','Erro!');
		}
	});
});