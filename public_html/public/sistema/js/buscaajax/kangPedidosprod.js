$(document).ready(function(){
	
	$('#codigo').blur(function(){
		$('#resultado').html("");
		
		$.ajax({
			url: "/admin/kang/buscanovoprodutoprod/cod/"+$('#codigo').val()+"/ped/"+$('#ped').val(),
			success: function(data) {
				if(data == 'sucesso'){
					window.location = window.location; 
				}else{
					$("#resultado").html(data);
					$('#codigo').val("");
				}
			}
		});		
	});
		
	$('#codigo').keypress(function(e) {
	    if(e.which == 13) $('#codigo').blur();
	});
	
});