$(function(){
	$('#btnGarantia').live('click', function(){	
		$('#carregarGraficos').show();
		
		var cliente;
		var tiporel;
		
		if($('input[name=idcliente]').length){
			cliente = $('input[name=idcliente]').val();
		}else{
			cliente = $("select[name=clientes] option:selected").val();
		}
		
		if($("select[name=tipo]").length){
			tiporel = $("select[name=tipo] option:selected").val();
		}else{
			tiporel = 1;
		}
		
		var periodorel = $("select[name=periodo] option:selected").val();
		
	    $.post('/admin/venda/buscarelatoriogarantia', {
	    	tipo: 			tiporel,
	    	clientes: 		cliente,
	    	frel: 			$("select[name=frel] option:selected").val(),
	    	buscagrupo: 	$("select[name=buscagrupo] option:selected").val(),
	    	buscagruposub: 	$("select[name=buscagruposub] option:selected").val(),
	    	idempresa: 		$('input[name=idempresa]').val(),
	    	garantaisfil: 	$("input[name='garantiasfiliais']:checked").val(),
	    	prod:	 		$("input[name=prod]").val(),
	    	periodo:		periodorel},
	    function(resposta) {
				
			texto = resposta.replace(/^\s+|\s+$/g,"");
			texto = unescape(texto);
			
			if(texto == "erro"){	
				jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
				$('#carregarGraficos').hide();
			}else{
				
				if(tiporel != 3){
					//naopagoscli|pagoscli|vendascli|vendasztl|pagosztl|cortesia
					texto = texto.split("|");
					
					var proced = parseInt(texto[1])+parseInt(texto[5]);
					
					if(parseInt(texto[1])==0 && parseInt(texto[5])==0){
						$("#graph1").html('Nenhum produto pago neste período!');
					};
					
					var data = [];
					data[0] = { label: "Não procedente", data: parseInt(texto[0]) };
					data[1] = { label: "Procedente", data: parseInt(texto[1]) };
					data[2] = { label: "Cortesia", data: parseInt(texto[5]) };
					
					var data2 = [];
					data2[0] = { label: "Vendas", data: parseInt(texto[3]) };
					data2[1] = { label: "Procedente", data: parseInt(texto[4]) };			
					
					var data3 = [];
					data3[0] = { label: "Compras", data: parseInt(texto[2]) };
					data3[1] = { label: "Procedente", data: proced };
					
					exibeRelatorio(data,data2,data3);
					
					listaRelatorio();
				}else{
					
					texto = texto.split("-");
					
					
					var d1 = [];
					var d2 = [];
					var d3 = [];
					data = new Date();
					
					var ticks = [];
					
					for (var i = periodorel; i > 0; i -= 1){
						ticks.push([i,((data.getMonth()+1)+"/"+data.getFullYear())]);
						
						//--- dados das devolucoes em garantias --------------------------
						
						var dp1 = 0;
						arrTexto = texto[0].split("|");
						for (var j = 0; j < arrTexto.length; j += 1){
							dadosArray = arrTexto[j].split(":");
							
							if(dadosArray[1] == ((data.getMonth()+1)+"/"+data.getFullYear())){
								dp1 = dadosArray[0];
							}
						}
						
						d1.push([i, dp1]);
						
						//--- dados das garantias pagas --------------------------
						var dp2 = 0;
						arrTexto = texto[1].split("|");
						for (var j = 0; j < arrTexto.length; j += 1){
							dadosArray = arrTexto[j].split(":");
							
							if(dadosArray[1] == ((data.getMonth()+1)+"/"+data.getFullYear())){
								dp2 = dadosArray[0];
							}
						}
						
						d2.push([i, dp2]);
						
						//--- dados das vendas --------------------------
						var dp3 = 0;
						arrTexto = texto[2].split("|");
						for (var j = 0; j < arrTexto.length; j += 1){
							dadosArray = arrTexto[j].split(":");
							
							if(dadosArray[1] == ((data.getMonth()+1)+"/"+data.getFullYear())){
								dp3 = dadosArray[0];
							}
						}
						
						d3.push([i, dp3]);
						
						data.setMonth(data.getMonth() - 1);
					}
					
					exibeRelatoriogeral(d1,d2,d3,ticks,periodorel);
				}
	        }
	    });	
	});

	function showTooltip(x, y, contents) {
		$("<div id='tooltip'>" + contents + "</div>").css({
			position: "absolute",
			display: "none",
			top: y + 5,
			left: x + 5,
			border: "1px solid #fdd",
			padding: "2px",
			"background-color": "#fee",
			opacity: 0.80
		}).appendTo("body").fadeIn(200);
	}

	function exibeRelatoriogeral(data,data2,data3,mascara,maxdata){
		$('#resultadoGarantiasgeral').show();
		$('#resultadoGarantias').hide();
		$('#dadosGarantias').hide();
		  
		var plot = $.plot($(".chart"), [
			{label: "Devoluções", data: data, lines: { show: true }, yaxis: 2},
			{label: "Pagas", data: data2, lines: { show: true }, yaxis: 2},
			{label: "Vendas", data: data3, lines: { show: true }, yaxis: 1}
			], 
		    {
				series: {
	               lines: { show: true },
	               points: { show: true }
	           	},
	           	grid: {
	    			hoverable: true,
	    			clickable: true
	    		},
	           	xaxis: { 
				   	max: maxdata,
					ticks: mascara
				},
				yaxes: [
				    {position: 'left'},
				    {position: 'right'}	
				]
	           
	         });


		$('#carregarGraficos').hide();

		var previousPoint = null;
		$(".chart").bind("plothover", function (event, pos, item) {
			
			if (item) {
				if (previousPoint != item.dataIndex) {

					previousPoint = item.dataIndex;

					$("#tooltip").remove();
					var x = item.datapoint[0].toFixed(0),
					y = item.datapoint[1].toFixed(0);

					showTooltip(item.pageX, item.pageY,
					    item.series.label + ":" + y);
				}
			} else {
				$("#tooltip").remove();
				previousPoint = null;            
			}
			
		});
		
	};
	
	function exibeRelatorio(data,data2,data3){
		$('#resultadoGarantias').show();
		$('#resultadoGarantiasgeral').hide();
		
		$.plot($("#graph1"), data, 
		{
			series: {
		        pie: {
		            show: true,
		            radius: 1,
		            label: {
	                    show: true,
	                    radius: 1,
	                    formatter: function(label, series){
	                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent*100)/100+'%</div>';
	                    },
	                    background: { opacity: 0.8 }
	                }
		        }
		    },

			legend: {
		        show: false
		    }
				
		});

		$.plot($("#graph2"), data2, 
		{
			series: {
		        pie: {
		            show: true,
		            radius: 1,
		            label: {
	                    show: true,
	                    radius: 1,
	                    formatter: function(label, series){
	                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent*100)/100+'%</div>';
	                    },
	                    background: { opacity: 0.8 }
	                }
		        }
		    },

			legend: {
		        show: false
		    }
				
		});

		$.plot($("#graph3"), data3, 
		{
			series: {
		        pie: {
		            show: true,
		            radius: 1,
		            label: {
	                    show: true,
	                    radius: 1,
	                    formatter: function(label, series){
	                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent*100)/100+'%</div>';
	                    },
	                    background: { opacity: 0.8 }
	                }
		        }
		    },

			legend: {
		        show: false
		    }
				
		});

		$('#carregarGraficos').hide();
		
	}
	
	function listaRelatorio(){
		$('#dadosGarantias').show();
		
		
		var cliente;
		var tiporel;
		
		if($('input[name=idcliente]').length){
			cliente = $('input[name=idcliente]').val();
		}else{
			cliente = $("select[name=clientes] option:selected").val();
		}
		
		if($("select[name=tipo]").length){
			tiporel = $("select[name=tipo] option:selected").val();
		}else{
			tiporel = 1;
		}
		
		var periodorel = $("select[name=periodo] option:selected").val();
		
		
	    $.post('/admin/venda/buscalistarelatoriogarantia', {
	    	tipo: 			tiporel,
	    	clientes: 		cliente,
	    	frel: 			$("select[name=frel] option:selected").val(),
	    	buscagrupo: 	$("select[name=buscagrupo] option:selected").val(),
	    	subgrupo: 		$("select[name=subgrupo] option:selected").val(),
	    	prod:	 		$("input[name=prod]").val(),
	    	idempresa: 		$('input[name=idempresa]').val(),
	    	garantaisfil: 	$("input[name='garantiasfiliais']:checked").val(),
	    	periodo:	periodorel},
	    function(resposta) {
				
			if(resposta == "erro"){	
				jAlertreload('Erro ao lista os produtos do relatório! Tente novamente.', 'Erro!');
				$('#dadosGarantias').hide();
			}else{
				document.getElementById('dadosGarantias').innerHTML=resposta;			
	        }
			
	    });	
	}
		
});
