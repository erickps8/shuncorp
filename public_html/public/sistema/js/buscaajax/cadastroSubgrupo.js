$(function(){
	$('select[name=buscagrupo]').change(function(){
    	var grupo = $('select[name=buscagrupo] option:selected').val();
    	var tipo  = this.id;
    	
    	if(grupo == 0){
    		var select = '<div class="styled-select" style="width: 220px"><select name="buscagrupo" id="buscagrupo" style="width: 242px"><option value="0">Subgrupo</option></select></div>';
    		$('#subgrupo').html(select);
    	}else{
	    	$.ajax({                
				url: "/admin/cadastro/buscasubgrupo/id_grupo/"+grupo+"/tipo/"+tipo,
				success: function(data){
					$('#subgrupo').html(data);
				}
			});
    	}
    });
});

function Mostrasubgrupos(id,tipo){

	$.ajax({
	  url: "/admin/cadastro/buscasubgrupo/id_grupo/"+id+"/tipo/"+tipo,
	  success: function(data) {
		  $("#subgrupo").html(data);
	  }
	});	
}
