<?php
/**
 * Action Helper para gerar PDF a partir de HTML usando HTML2PDF
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 * @uses HTML2FPDF (http://html2fpdf.sourceforge.net)
 * @uses FPDF (www.fpdf.org)
 *
 * @author Rubens Gadelha
 *
 * @param string $html
 * @param 'I'|'D'|'F'|'S' $dest
 *
 * Saidas:
 * I: Envia para a saída padrão
 * D: Download do arquivo
 * F: Salva em um arquivo local
 * S: Retorna como string
 */
 
class Zend_Controller_Action_Helper_Html2Pdf extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($html, $output, $dest = 'D')
    {
		$pdf = new HTML2FPDF();
		$pdf->AddPage();
		$pdf->WriteHTML(utf8_decode($html));
		$pdf->Output($output, $dest);
    }
}
?>