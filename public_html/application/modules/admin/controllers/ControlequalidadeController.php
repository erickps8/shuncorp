<?php
class Admin_ControlequalidadeController extends Zend_Controller_Action {
		
	public function init()
	{
        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
        	//$this->_redirect('/');
        }
	}
	
	function relatorioinspencaoAction(){
	    $this->_helper->layout->disableLayout();
	    $bok   = new KangcomprasModel();
	    $bo    = new KangcomprasprodModel();
	    
	    $params = $this->_getAllParams();
	    $ped = $bok->fetchRow("id_kang_compra = '".$params['ped']."'");
	    
	    $pdf = new ControlequalidadeBO();
	    
	    foreach ($bo->fetchAll("id_kang_compra = '".$params['ped']."'") as $prodkang){
	    
    	    foreach (ProdutosBO::buscaProduto(array('tipo' => 2, 'produto' => md5($prodkang->id_prod))) as $produto);
    	    
    	    $arrayData = array(
                'codigo'           => $produto->CODIGO,
    	        'id_prod'          => $produto->ID,
                'pk'               => "PK".substr("000000".$params['ped'],-6,6),
                'qt'               => $prodkang->qt,
                'data'             => substr($ped->data,0,10), //substr(str_replace("-","/",$ped->data),0,10),
    	    );
    	    
    	    $pdf->AddPage("R","A4");
    	    $pdf->SetMargins(7,7,7);
    	    $pdf->SetAutoPageBreak(false);
    	    $pdf->Ln(); 
    	    
    	    if($produto->id_gruposprodsub == 9){ //-- forjado cubo
    	        $pdf->topo($arrayData,'QCR1 - FORGED COMPONENT INSPECTION REPORT (锻件检测报告)');
    	        $pdf->titulocolunas($arrayData);
    	        
    	        $pdf->qcr1($arrayData);
    	        
    	        $pdf->rodape();
    	    }elseif($produto->id_gruposprod == 1){ //-- rolamento 
    	        $pdf->topo($arrayData,'QCR3 - BEARING INSPECTION REPORT (轴承检测报告)');
    	        $pdf->titulocolunas($arrayData);
    	         
    	        $pdf->qcr3($arrayData);
    	         
    	        $pdf->rodape();
    	    }elseif($produto->id_gruposprod == 2){ //-- cubo
    	        $pdf->topo($arrayData,'QCR2 - FINISHED PRODUCT INSPECTION REPORT (成品检测报告)');
    	        $pdf->titulocolunas($arrayData);
    	        
    	        $pdf->qcr2($arrayData);
    	        
    	        $pdf->rodape();
    	    } 
	    }
	    
	    $pdf->Output();
	    
	    exit();
	}
	
	function gerarreportqcr1Action(){	    
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    
	    exit();
	}
}