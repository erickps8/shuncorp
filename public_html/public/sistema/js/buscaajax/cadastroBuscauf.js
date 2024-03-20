
$(function(){   
	/**
	 * Busca de estados pelo pais
	 */	
	
	$(".select-pais").change(function(){		
		var idpais = $(this).val();
		var campo  = $(this).attr('data-campo');
		
		$.ajax({
			url: "/admin/cadastro/buscaufbypais/idpais/"+idpais,
			success: function(data) {
				$("#"+campo).html(data);
			}
		});
	});
	
	$(".select-uf").change(function(){		
		var iduf 	= $(this).val();
		var campo   = $(this).attr('data-campo');
		
		$.ajax({
			url: "/admin/cadastro/buscacidadebyuf/iduf/"+iduf,
			success: function(data) {
				$("#"+campo).html(data);
			}
		});
	});
});

function buscaEstados(id, tp, div, divcid, nomesel){	
	$.ajax({
	  url: "/admin/cadastro/buscauf/id/"+id+"/tipo/"+tp+"/divcid/"+divcid+"/nomesel/"+nomesel,
	  success: function(data) {
		  $("#"+div).html(data);
	  }
	});
}
