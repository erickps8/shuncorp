var xmlHttp;
var tipo;
var clientes;
var frel;
var buscagrupo;
var subgrupo;
var prod;
var periodo;

/*--------- grava dados do contatos ------------------*/
function gerarRelatorio(){
	$('#dadosRelatorio').css({display : "none"});
	$('#aguarde').css({display : "block"});
	
	tpbusca     	= $("select[name=tpbusca] option:selected").val();
	representante  	= $("select[name=representante] option:selected").val();
	televenda 		= $("select[name=televenda] option:selected").val();
	grupovenda		= $("select[name=grupovenda] option:selected").val();
	dataini			= $("input[name=dataini]").val();
	datafim			= $("input[name=datafim]").val();
	
	
    $.post('/admin/relatorios/buscarentabilidade', {
    	tpbusca     	: $("select[name=tpbusca] option:selected").val(),
    	representante  	: $("select[name=representante] option:selected").val(),
    	televenda 		: $("select[name=televenda] option:selected").val(),
    	grupovenda		: $("select[name=grupovenda] option:selected").val(),
    	dataini			: $("input[name=dataini]").val(),
    	datafim			: $("input[name=datafim]").val()},
    function(resposta) {
		
		texto = resposta.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
		
		
		
		if(texto == "erro"){	
			jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
			document.getElementById('carregarGraficos').style.display = "none";
		}else{
			
			texto = texto.split("|");
			arrayticks 	= texto[0].split(";");
			arrayVend 	= texto[1].split(";");
			arrayMarg 	= texto[2].split(";");
			arrayPend 	= texto[3].split(";");
			
			var d1 = [];
			var d2 = [];
			var d3 = [];
			var ticks = [];
			
			ii = 0;
			for (var i = 0; i < arrayticks.length; i+=1){
				ticks.push([i,i+1]);
				
			}	
			
			for (var i = 0; i < arrayVend.length; i+=1){
				d1.push([i,arrayVend[i]]);
			}
			
			for (var i = 0; i < arrayMarg.length; i+=1){
				d2.push([i,arrayMarg[i]]);
			}
			
			for (var i = 0; i < arrayPend.length; i+=1){
				d3.push([i,arrayPend[i]]);
			}
				
				
			exibeRelatoriogeral(d1,d2,d3,ticks);
			$('#tabela').html(texto[4]);
			
        }
        
        
    });	
}

function exibeRelatoriogeral(data,data2,data3,mascara){
	$('#dadosRelatorio').css({display : "block"});
	$('#aguarde').css({display : "none"});
	  
		//{label: "Pendências", data: data2}
	
	var plot = $.plot($(".bars"), [
		{label: "Custo", data: data},
		{label: "Margem", data: data3}		
		], 
	    {
			series: {
				stack: true,
				bars: {
					show: true,
					barWidth: 0.6,
					align: "center"
				}
           	},
           	xaxis: { 
			   	ticks: mascara
			},
			legend: {
		        show: true
		    }
           
         });

};
