function Mostravendas(id,dt){
	
	if (id.length==0){
		document.getElementById(str).innerHTML="";
		return;
	}
	
	$.ajax({
	  url: "/admin/venda/buscamediavendas/idprod/"+id+"/dt/"+dt,
	  success: function(data) {
		  $('#m_'+id).html(data);
	  }
	});
	
}


function Mostracompra(id){
	
	if (id.length==0){
		document.getElementById(str).innerHTML="";
		return;
	}
	
	$.ajax({
	  url: "/admin/venda/buscamediacompras/idprod/"+id,
	  success: function(data) {
		  $('#c_'+id).html(data);
	  }
	});
}

