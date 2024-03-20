
	function buscaGenerica(url, div){
		$.ajax({
		  url: url,
		  success: function(data) {
			  $("#"+div).html(data);
			  $("#"+div).show();
		  }
		});
	}
