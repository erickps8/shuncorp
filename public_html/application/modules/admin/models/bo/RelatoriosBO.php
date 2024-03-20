<?php
	class RelatoriosBO{
		function custovendasPlcontas(){
			
			$bo = new RelatoriosModel();
			$rel = $bo->fetchRow();
			
			?>
			<div style="overflow: auto; height: 350px;">
			<div class="widget">
				<form id="planoContas">
				<table style="width: 100%;" class="tableStatic" >
    				<thead>
    					<tr>
	    					<td style="width: 10%">Nav</td>
	    					<td >Plano de conta</td>
	    					<td >Opções</td>
						</tr>
    				</thead>
	    			<tbody>
					<?php
					foreach (FinanceiroBO::listartodosPlanoscontas() as $plcontas){
						$color = "";
						if($plcontas->utilizavel != 1) $color = "#ccc"; 
						?>
		    			<tr  >
		    				<td style="text-align: center; background-color: <?php echo $color?>"><?php echo $plcontas->navegacao?></td>
		    				<td style="text-align: left; background-color: <?php echo $color?>"><?php echo $plcontas->nome?></td>
		    				<td style="text-align: center; background-color: <?php echo $color?>">
		    					<?php 
		    					if($plcontas->utilizavel == 1){
									$check = ''; 
		    						if(strpos($rel->plcontas,",".$plcontas->id.",") !== false){
										$check = 'checked="checked"';
									}
		    						?>
		    						<input type="checkbox" <?php echo $check?> name="<?php echo $plcontas->id?>">
		    					<?php } ?>
		    				</td>
		    			</tr>
		    		<?php								
		    		}					
		    		?>
	    			</tbody>
	    		</table>
	    		</form>
	    	</div>
	    	</div>
	    	<div style="text-align: right; margin-right: 7px; border-top: 1px solid #d5d5d5; padding-top: 10px">
	    		<input type="button" value="Salvar" id="btnSalvaplcontas" class="greenBtn">
	    	</div>
	    	
    		<?php		
		}		
			
		
		function gravarcustovendasPlcontas($params){
				
			$bo = new RelatoriosModel();			
			$stringpl = "";
			foreach (FinanceiroBO::listartodosPlanoscontas() as $plcontas){
				if(isset($params[$plcontas->id]) and $params[$plcontas->id]){
					$stringpl .= ",".$plcontas->id;
				}			
			}

			$stringpl .= ",";
			
			$id = $bo->update(array("plcontas"=>$stringpl),"id > 0");
			
			if(!$id) throw new Zend_Exception("Erro ao gravar os planos de contas");
		}			
	}
?>