#!/usr/bin/php
<?php
include_once("/aplic/ztlbrasil.com.br/public/legado/classes/bd.class.php");

$bd = new BancoDados();
$bd->AbreConexao();
$bd->Query('select * from produtos where (precoajuste != "" || percentajuste != "") and dt_ajuste = "'.date("Y-m-d").'"');
$bd2 = new BancoDados();
$bd2->AbreConexao();

if($bd->NumRows!=0):
	
	while($ar = $bd->FetchArray()):				
		if(($ar['precoajuste'] != "") and ($ar['precoajuste'] != 0)): 
			$novopreco = $ar['precoajuste'];
		elseif (($ar['percentajuste'] != "") and ($ar['percentajuste'] != 0)):
			$novopreco = $ar['PRECO_UNITARIO']+($ar['PRECO_UNITARIO']*($ar['percentajuste']/100));
		endif;
		
		echo 'insert into tb_historicopcvenda (data ,moeda ,valor ,id_produtos, id_func) values 
		("'.date("Y-m-d").'","BRL","'.$novopreco.'","'.$ar['ID'].'", "'.$ar['id_userajuste'].'")';
		echo "<br>";
		
		
		$bd2->Query('insert into tb_historicopcvenda (data ,moeda ,valor ,id_produtos, id_func) values 
		("'.date("Y-m-d").'","BRL","'.$novopreco.'","'.$ar['ID'].'", "'.$ar['id_userajuste'].'")');

		echo 'update produtos set PRECO_UNITARIO = "'.$novopreco.'",precoajuste = "",percentajuste = "",dt_ajuste = "" where ID ='.$ar['ID'];
		echo "<br>";
		
		$bd2->Query('update produtos set PRECO_UNITARIO = "'.$novopreco.'",precoajuste = "",percentajuste = "",dt_ajuste = "" where ID ='.$ar['ID']);
				
		echo $ar['CODIGO'];
		echo "<br>";
		echo "<br>";
	endwhile;
endif;

$bd->FechaConexao();
$bd2->FechaConexao();

/*foreach ($bo->fetchAll('(precoajuste != "" || percentajuste != "") and dt_ajuste = "'.date("Y-m-d").'"') as $lista):
	
	if(($lista->precoajuste != "") and ($lista->precoajuste != 0)): 
		$novopreco = $lista->precoajuste;
	elseif (($lista->percentajuste != "") and ($lista->percentajuste != 0)):
		$novopreco = $lista->PRECO_UNITARIO+($lista->PRECO_UNITARIO*($lista->percentajuste/100));
	endif;
	
	$arrayhistv['valor']		= $novopreco;
	$arrayhistv['data'] 		= date("Y-m-d H:i:s");
	$arrayhistv['moeda'] 		= "BRL";
	$arrayhistv['id_produtos'] 	= $lista->ID;
	$arrayhistv['id_func'] 		= $lista->id_userajuste;
	$bohisv->insert($arrayhistv);
	
	$arrayp['PRECO_UNITARIO']   = $novopreco;
	$arrayp['precoajuste']   	= "";
	$arrayp['percentajuste']   	= "";
	$arrayp['dt_ajuste']   		= "";
	$bo->update($arrayp, "ID = ".$lista->ID);
					
endforeach;*/