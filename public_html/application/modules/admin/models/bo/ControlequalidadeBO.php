<?php
    //require('fpdf/fpdf.php');
    require('fpdf/tfpdf.php');

	class ControlequalidadeBO extends tFPDF{		
	    var $widths;
	    var $aligns;
	    
	    function SetWidths($w)
	    {
	        //Set the array of column widths
	        $this->widths=$w;
	    }
	    
	    function SetAligns($a)
	    {
	        //Set the array of column alignments
	        $this->aligns=$a;
	    }
	    
	    function Row($data)
	    {
	        //Calculate the height of the row
	        $nb=0;
	        for($i=0;$i<count($data);$i++){
	            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
	        }
	        
	        $h=5*$nb;
	        //Issue a page break first if needed
	        $this->CheckPageBreak($h);
	        //Draw the cells of the row
	        for($i=0;$i<count($data);$i++)
	        {
	            $w=$this->widths[$i];
	            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
	            //Save the current position
	            $x=$this->GetX();
	            $y=$this->GetY();
	            //Draw the border
	            $this->Rect($x,$y,$w,$h);
	            //Print the text
	            $this->MultiCell($w,5,$data[$i],0,$a);
	            //Put the position to the right of the cell
	            $this->SetXY($x+$w,$y);
	        }
	        //Go to the next line
	        $this->Ln($h);
	    }
	    
	    function CheckPageBreak($h)
	    {
	        //If the height h would cause an overflow, add a new page immediately
	        if($this->GetY()+$h>$this->PageBreakTrigger)
	            $this->AddPage($this->CurOrientation);
	    }
	    
	    function NbLines($w,$txt)
	    {
	        //Computes the number of lines a MultiCell of width w will take
	        $cw=&$this->CurrentFont['cw'];
	        if($w==0)
	            $w=$this->w-$this->rMargin-$this->x;
	        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	        $s=str_replace("\r",'',$txt);
	        $nb=strlen($s);
	        if($nb>0 and $s[$nb-1]=="\n")
	            $nb--;
	        $sep=-1;
	        $i=0;
	        $j=0;
	        $l=0;
	        $nl=1;
	        while($i<$nb)
	        {
	            $c=$s[$i];
	            if($c=="\n")
	            {
	                $i++;
	                $sep=-1;
	                $j=$i;
	                $l=0;
	                $nl++;
	                continue;
	            }
	            if($c==' ')
	                $sep=$i;
	            
	            $l+=$cw[$c];
	            
	            if($l>$wmax)
	            {
	                if($sep==-1)
	                {
	                    if($i==$j)
	                        $i++;
	                }
	                else
	                    $i=$sep+1;
	                $sep=-1;
	                $j=$i;
	                $l=0;
	                $nl++;
	            }
	            else
	                $i++;
	        }
	        return $nl;
	    }
	    
	    function linhaitens($borda, $larg, $alt, $largl){
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($larg,$alt,'',$borda,0,'L',false);
	        $this->Cell($largl,$alt,'',$borda,1,'L',false);
	    }
	    
	    function topo($params = array(), $titulo = null){
	        $logo = "/home/shuncorp/public_html/public/sistema/imagens/report/logohbr_cinza.png";
	        $this->SetFillColor(209,209,209);
	        	        
	        $this->AddFont('MSJH','','msjh-Bold.ttf',true);
	        $this->SetFont('MSJH','',15);
	         
	        $this->Cell(37, 12, $this->Image($logo, $this->GetX(), $this->GetY(), 37), 'LTB');
	        $this->Cell(0,7,'HBR BRASIL',"RT",2,'C',true);
	         
	        $this->SetFont('MSJH','',12);
	         
	        $this->Cell(0,5,$titulo,"RB",1,'C',true);
	        
	        $this->SetFont('MSJH','',9);
	         
	        $this->Cell(58,5,'Item No (产品型号): '.$params['codigo'],1,0,'L',true);
	        $this->Cell(80,5,'Purchase Order (采购订单): '.$params['pk'],1,0,'L',true);
	        $this->Cell(80,5,'Order Quantity (订单数量): '.$params['qt'],1,0,'L',true);
	        $this->Cell(0,5,'Date Order (订单日期): '.$params['data'],1,1,'L',true);
	    }
	    
	    function titulocolunas($params = array()){
	        $this->SetFont('MSJH','',9);
	        
	        $this->Cell(6,20,'ID',1,0,'C',false);
	        $this->Cell(52,20,'Tested Items (检测项目)',1,0,'C',false);
	        
	        $this->MultiCell(28,5,'Dimensions on Drawing and Tolerance (mm) (图纸尺寸公差)',1,'C');
	        
	        $this->SetXY(93,27);
	        $this->MultiCell(170,15,'Checked Dimensions (实际检测尺寸)',1,'C');
	        $this->SetXY(93,42);
	        
	        $this->SetFont('Arial','',9);
	        
	        $w = 17;
	        $tw = 93;
	        $this->MultiCell($w,5,'1',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'2',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'3',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'4',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'5',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'6',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'7',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'8',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'9',1,'C');
	        $this->SetXY($tw+=$w,42);
	        $this->MultiCell($w,5,'10',1,'C');
	        
	        $this->SetFont('MSJH','',9);
	        
	        $this->SetXY(263,27);
	        $this->MultiCell(27,5,'According Nonconforming (OK/NOT) (合格/不合格)',1,'C');
	    }
	    
	    function rodape(){
	        $this->Cell(95,5,'CONCLUSION (结语):',1,0,'L',true);
	        $this->Cell(94,5,'INSPECTOR (检验员):',1,0,'L',true);
	        $this->Cell(94,5,'INSPECTION DATE (检验日期):',1,1,'L',true);
	         
	         
	        $this->Cell(0,2,'',0,1,'C',false);
	        
	        $this->SetFont('MSJH','',6);
	         
	        $this->Cell(0,3,'HBR BRASIL IND EXP IMP EIRELI',0,1,'C',false);
	        $this->Cell(0,3,'Rua João Pius Schindler, 765 - Bateias de Baixo - Campo Alegre - SC - Brazil - PC 89294-000',0,1,'C',false);
	        $this->Cell(0,3,'http://www.hbr.ind.br',0,1,'C',false);
	    }
	    
	    function qcr2($params = array()){
	        
	        $bo    = new ProdutosModel();
	        $bom   = new ProdutosmediasModel();
	        
	        $prodmedidas = $bom->fetchRow("id_prod = '".$params['id_prod']."'");
	         
	        $simdiametro = iconv('UTF-8', 'windows-1252', 'Ø');
	         
	        $maltCell = 5;
	        
	        $this->SetFont('MSJH','',8);
	        $this->Cell(6,10,'1',1,0,'C',false);
	         
	        //-- dimensoes diamentro ----------------
	        $this->Cell(52,5,'Diameter Bearing Accommodation','RL',0,'L',false);
	         
	        $this->SetFont('Arial','',10);
	        $this->Cell(16,10,$simdiametro.$prodmedidas->insp_diamentrorolamento,'LB',0,'R',false);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,'+'.$prodmedidas->insp_diamentrorolamentop,'RT',0,'LR',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	         
	        $this->SetXY(13,52);
	        $this->SetFont('MSJH','',8);
	        $this->Cell(52,5,'(轴承直径)','RBL',0,'L',false);
	         
	        $this->SetXY(81,52);
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,' -'.$prodmedidas->insp_diamentrorolamenton,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',8);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        //-- dimensoes altura ---------------------
	        $this->Cell(6,10,'2',1,0,'C',false);
	        $this->Cell(52,5,'Height Bearing Accommodation','RTL',0,'L',false);
	         
	        $this->SetFont('Arial','',10);
	        $this->Cell(16,10,$prodmedidas->insp_alturarolamento,'BTL',0,'R',false);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,'+'.$prodmedidas->insp_alturarolamentop,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	         
	        $this->SetXY(13,62);
	         
	        $this->SetFont('MSJH','',8);
	        $this->Cell(52,5,'(轴承高度)','RBL',0,'L',false);
	         
	        $this->SetXY(81,62);
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,' -'.$prodmedidas->insp_alturarolamenton,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',8);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        //-- dimensores freio piloto -----------------
	        $this->Cell(6,10,'3',1,0,'C',false);
	        $this->Cell(52,5,'Disc Brake Pilot Diameter','RLT',0,'L',false);
	        
	        $this->SetFont('Arial','',10);
	        $this->Cell(16,10,$simdiametro.$prodmedidas->insp_diamentrofreiopiloto,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,'+'.$prodmedidas->insp_diamentrofreiopilotop,'RT',0,'LR',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        $this->SetXY(13,72);
	        
	        $this->SetFont('MSJH','',8);
	        $this->Cell(52,5,'(装配尺寸)','RBL',0,'L',false);
	        $this->SetXY(81,72);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,' -'.$prodmedidas->insp_diamentrofreiopiloton,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',8);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        //-- dimensoes externa --------------------
	        $this->Cell(6,10,'4',1,0,'C',false);
	        $this->Cell(52,5,'External Diameter','RLT',0,'L',false);
	         
	        $this->SetFont('Arial','',10);
	        $this->Cell(16,10,$simdiametro.$prodmedidas->insp_diamentroexterno,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,'+'.$prodmedidas->insp_diamentroexternop,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        $this->SetXY(13,82);
	         
	        $this->SetFont('MSJH','',8);
	        $this->Cell(52,5,'(最大外径)','RBL',0,'L',false);
	        $this->SetXY(81,82);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,' -'.$prodmedidas->insp_diamentroexternon,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',8);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        //-- altura total ------------------
	        $this->Cell(6,10,'5',1,0,'C',false);
	        $this->Cell(52,5,'Total Height (总高)','RLT',0,'L',false);
	         
	        $this->SetFont('Arial','',10);
	        $this->Cell(16,10,$prodmedidas->insp_alturatotal,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,'+'.$prodmedidas->insp_alturatotalp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        $this->SetXY(13,92);
	         
	        $this->SetFont('MSJH','',8);
	        $this->Cell(52,5,'(齿数)','RBL',0,'L',false);
	        
	        $this->SetXY(81,92);
	        $this->SetFont('Arial','',8);
	        $this->Cell(12,5,' -'.$prodmedidas->insp_alturatotaln,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',8);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->SetAligns(array('C', 'L', 'C'));
	        $this->SetWidths(array(6,52,28,17,17,17,17,17,17,17,17,17,17,27));
	        $this->Row(array('6','Broach - Number of Teeth (齿数)', $prodmedidas->insp_dentesbroxa,'','','','','','','','','','',''));
	        $this->Row(array('7','Number of Holes (孔数)', $prodmedidas->insp_furos,'','','','','','','','','','',''));
	        $this->Row(array('8','Screwthread (螺纹)', substr($prodmedidas->insp_rosca,0,17),'','','','','','','','','','',''));
	        $this->Row(array('9','ABS Sensor (ABS齿圈)',substr($prodmedidas->insp_abs,0,17),'','','','','','','','','','',''));
	        $this->Row(array('10','Axial clearance (轴向游隙)',$prodmedidas->insp_folgaeixo,'','','','','','','','','','',''));
	        $this->Row(array('11','Bolt Specification (螺纹规格)',substr($prodmedidas->insp_parafuso,0,17),'','','','','','','','','','',''));
	        $this->Row(array('12','Bolt Hole Qty (螺纹孔数)',$prodmedidas->insp_parafusoqt,'','','','','','','','','','',''));
	        $this->Row(array('13','Knocked Wounded (磕碰伤)','','','','','','','','','','','',''));
	        $this->Row(array('14','Chamfers and Radii (倒直角和圆角)',substr($prodmedidas->insp_chanfroseraios,0,17),'','','','','','','','','','',''));
	        $this->Row(array('15','Grease (油脂)',substr($prodmedidas->insp_graxa,0,17),'','','','','','','','','','',''));
	         
	        $this->SetWidths(array(6,52,28,170,27));
	        $this->Row(array('16','Weight (重量)',$prodmedidas->insp_peso,'',''));
	        $this->Row(array('17','Material (材料)',substr($prodmedidas->insp_material,0,17),'',''));
	        $this->Row(array('18','Hardness (HRC) (硬度)',substr($prodmedidas->insp_dureza,0,17),'',''));
	         
	        $this->SetXY(7,162);
	        $this->Cell(6,8,'19',1,0,'C',false);
	        $this->Cell(52,4,'Components (specification-quantity)','R',0,'L',false);
	        
	        $this->Cell(28,8,substr($prodmedidas->insp_componentes,0,17),'RTL',0,'R',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	         
	        $this->SetXY(13,166);
	        $this->Cell(52,4,'(附件-规格-数量)','RBL',0,'L',false);
	         
	        $this->SetXY(83,165);
	        $this->Cell(10,5,'',0,0,'R',false);
	         
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->SetWidths(array(6,52,28,17,17,17,17,17,17,17,17,17,17,27));
	        $this->Row(array('20','Appearance, Other (备注，其他)','','','','','','','','','','','',''));
	        $this->Row(array('21','Outer Package (外包装)','','','','','','','','','','','',''));
	         	        
	    }
	    
	    function qcr1($params = array()){
	         
	        $bo    = new ProdutosModel();
	        $bom   = new ProdutosmediasModel();
	         
	        $prodmedidas = $bom->fetchRow("id_prod = '".$params['id_prod']."'");
	    
	        $simdiametro = iconv('UTF-8', 'windows-1252', 'Ø');
	    
	        $altT      = 51;
	        $tmsjh     = 8;
	        $tarial    = 8;
	        $tarial2   = 6;
	        $altCell   = 8;
	        $maltCell  = 4;
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->Cell(6,$altCell,'1',1,0,'C',false);
	    
	        $this->Cell(52,$altCell,'Dimension A (尺寸A)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoa,'LB',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoap,'RT',0,'LR',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    	
	        $this->SetXY(81,$altT);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoan,'RB',0,'L',false);
	    
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	    
	        $this->Cell(6,$altCell,'2',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension B (尺寸B)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaob,'BTL',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaobp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaobn,'RB',0,'L',false);
	    
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	    
	        $this->Cell(6,$altCell,'3',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension C (尺寸C)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoc,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaocp,'RT',0,'LR',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        
	        $this->SetXY(81,$altT+=$altCell);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaocn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	    
	        $this->Cell(6,$altCell,'4',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension D (尺寸D)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaod,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaodp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        
	        $this->SetXY(81,$altT+=$altCell);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaodn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	    
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension E (尺寸E)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoe,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoep,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	         
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoen,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension F (尺寸F)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaof,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaofp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaofn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension G (尺寸G)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaog,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaogp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaogn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension H (尺寸H)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoh,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaohp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaohn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension I (尺寸I)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoi,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoip,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoin,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension J (尺寸J)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoj,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaojp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaojn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension L (尺寸L)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaol,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaolp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoln,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension M (尺寸M)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaom,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaomp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaomn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension N (尺寸N)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaon,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaonp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaonn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension O (尺寸O)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoo,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoop,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoon,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension P (尺寸P)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaop,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaopp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaopn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension Q (尺寸Q)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoq,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoqp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoqn,'RB',0,'L',false);
	        
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	        
	    
	        $this->SetAligns(array('C', 'L', 'C'));
	        $this->SetWidths(array(6,52,28,17,17,17,17,17,17,17,17,17,17,27));
	        $this->Row(array('14','Chamfers and Radii (倒直角和圆角)',substr($prodmedidas->insp_chanfroseraios,0,17),'','','','','','','','','','',''));
	        	    
	        $this->SetWidths(array(6,52,28,170,27));
	        $this->Row(array('16','Weight (重量)',$prodmedidas->insp_peso,'',''));
	        $this->Row(array('17','Material (材料)',substr($prodmedidas->insp_material,0,17),'',''));
	        $this->Row(array('18','Hardness (HRC) (硬度)',substr($prodmedidas->insp_dureza,0,17),'',''));
	    
	        $this->SetWidths(array(6,52,28,17,17,17,17,17,17,17,17,17,17,27));
	        $this->Row(array('20','Appearance, Other (备注，其他)','','','','','','','','','','','',''));
	        $this->Row(array('21','Outer Package (外包装)','','','','','','','','','','','',''));
	        
	        $this->AddPage("R","A4");
	        $this->topo($params,'QCR1 - FORGED COMPONENT INSPECTION REPORT (锻件检测报告)');
	        
	        $img1 = "/home/shuncorp/public_html/public/sistema/imagens/report/report_gcr1_1.jpeg";
	        $this->Cell(141, 160, $this->Image($img1, 10, 35,130), 'LTB', 0,'C');
	        
	        $img2 = "/home/shuncorp/public_html/public/sistema/imagens/report/report_gcr1_2.jpeg";
	        $this->Cell(142, 160, $this->Image($img2, 155, 35, 130), 'RTB', 1, 'C');
	       
	    }
	    
	    function qcr3($params = array()){
	    
	        $bo    = new ProdutosModel();
	        $bom   = new ProdutosmediasModel();
	    
	        $prodmedidas = $bom->fetchRow("id_prod = '".$params['id_prod']."'");
	         
	        $simdiametro = iconv('UTF-8', 'windows-1252', 'Ø');
	         
	        $altT      = 52;
	        $tmsjh     = 8;
	        $tarial    = 10;
	        $tarial2   = 8;
	        $altCell   = 10;
	        $maltCell  = 5;
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->Cell(6,$altCell,'1',1,0,'C',false);
	         
	        $this->Cell(52,$altCell,'Dimension A (尺寸A)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoa,'LB',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoap,'RT',0,'LR',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoan,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'2',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension B (尺寸B)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaob,'BTL',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaobp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	         
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaobn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'3',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension C (尺寸C)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoc,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaocp,'RT',0,'LR',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	         
	        $this->SetXY(81,$altT+=$altCell);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaocn,'RB',0,'L',false);
	    
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'4',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension D (尺寸D)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaod,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaodp,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	         
	        $this->SetXY(81,$altT+=$altCell);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaodn,'RB',0,'L',false);
	    
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension E (尺寸E)',1,0,'L',false);
	         
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoe,'BLT',0,'R',false);
	         
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoep,'RT',0,'L',false);
	         
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	        	
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoen,'RB',0,'L',false);
	    
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension F (尺寸F)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaof,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaofp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaofn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension G (尺寸G)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaog,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaogp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaogn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension H (尺寸H)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoh,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaohp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaohn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension I (尺寸I)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoi,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaoip,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoin,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension J (尺寸J)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaoj,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaojp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaojn,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	        $this->Cell(6,$altCell,'5',1,0,'C',false);
	        $this->Cell(52,$altCell,'Dimension L (尺寸L)',1,0,'L',false);
	    
	        $this->SetFont('Arial','',$tarial);
	        $this->Cell(16,$altCell,$simdiametro.$prodmedidas->insp_dimensaol,'BLT',0,'R',false);
	    
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,'+'.$prodmedidas->insp_dimensaolp,'RT',0,'L',false);
	    
	        $this->linhaitens('LRT', 17, $maltCell, 27);
	    
	        $this->SetXY(81,$altT+=$altCell);
	        $this->SetFont('Arial','',$tarial2);
	        $this->Cell(12,$maltCell,' -'.$prodmedidas->insp_dimensaoln,'RB',0,'L',false);
	         
	        $this->SetFont('MSJH','',$tmsjh);
	        $this->linhaitens('RBL', 17, $maltCell, 27);
	         
	         
	        $this->SetAligns(array('C', 'L', 'C'));
	        $this->SetWidths(array(6,52,28,17,17,17,17,17,17,17,17,17,17,27));
	        $this->Row(array('14','Chamfers and Radii (倒直角和圆角)',substr($prodmedidas->insp_chanfroseraios,0,17),'','','','','','','','','','',''));
	    
	        $this->SetWidths(array(6,52,28,170,27));
	        $this->Row(array('16','Weight (重量)',$prodmedidas->insp_peso,'',''));
	        $this->Row(array('17','Material (材料)',substr($prodmedidas->insp_material,0,17),'',''));
	        $this->Row(array('18','Hardness (HRC) (硬度)',substr($prodmedidas->insp_dureza,0,17),'',''));
	         
	        $this->SetWidths(array(6,52,28,17,17,17,17,17,17,17,17,17,17,27));
	        $this->Row(array('20','Appearance, Other (备注，其他)','','','','','','','','','','','',''));
	        $this->Row(array('21','Outer Package (外包装)','','','','','','','','','','','',''));
	         
	        $this->AddPage("R","A4");
	        $this->topo($params,'QCR3 - BEARING INSPECTION REPORT (轴承检测报告)');
	         
	        $img1 = "/home/shuncorp/public_html/public/sistema/imagens/report/report_gcr3_b.jpg";
	        $this->Cell(283, 160, $this->Image($img1, 75, $this->GetY(),140), 1, 1,'C');
	         
	    }
	}
?>