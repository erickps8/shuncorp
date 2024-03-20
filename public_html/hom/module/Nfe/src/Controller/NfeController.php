<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Nfe\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Nfe\Model\NfeTable;
use Nfe\Form\NfeForm;
use Nfe\Model\Nfe;
use NFePHP\NFe\Make;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Part;

use Zend\Mime\Message as MessageMine;

class NfeController extends AbstractActionController
{
    /**
     * @var NfeTable
     */
    private $table;
    private $form;
    
    public function __construct(NfeTable $table, NfeForm $form)
    {
        $this->table = $table;
        $this->form  = $form;        
    }
    
    public function indexAction()
    {
        $nfeTable = $this->table;
        
        $nfeData = $nfeTable->find(397);
        
        $nfe = new Make();
        
        $hora       = localtime(time(), true);
        $zona       = ($hora['tm_isdst']) ? "-02:00" : "-03:00";
        
        $dataemi = substr($nfeData->data_saida,0,10)."T".substr($nfeData->data_saida,11,8).$zona;
                
        //-- dados --
        $std = new \stdClass();
        $std->versao = '4.00';
        $std->Id = '';
        $std->pk_nItem = null;
        
        $nfe->taginfNFe($std);
        
        //-- identificacao --
        $std = new \stdClass();
        $std->cUF       = 42;
        $std->cNF       = '80070008';
        $std->natOp     = 'VENDA';
        $std->mod       = 55;
        $std->serie     = 1;
        $std->nNF       = $nfeData->id;
        $std->dhEmi     = $dataemi;
        $std->dhSaiEnt  = null;
        $std->tpNF      = 1;
        $std->idDest    = 1;
        $std->cMunFG    = 3518800;
        $std->tpImp     = 1;
        $std->tpEmis    = 1;
        $std->cDV       = 2;
        $std->tpAmb     = 2;
        $std->finNFe    = 1;
        $std->indFinal  = 0;
        $std->indPres   = 0;
        $std->procEmi   = '0';
        $std->verProc   = 'SisHBR 2.0';
        $std->dhCont    = null;
        $std->xJust     = null;
        
        $elem = $nfe->tagide($std);
        
        //-- nfe referenciada --
        $std = new \stdClass();
        $std->refNFe = '35150271780456000160550010000253101000253101';
        
        $elem = $nfe->tagrefNFe($std);
        
        //-- dados do emitente --
        $std = new \stdClass();
        $std->xNome     = 'HBR BRASIL IND EXP IMP EIRELI';
        $std->xFant     = 'HBR BRASIL';
        $std->IE        = '000257254650';
        $std->IEST      = null;
        $std->IM        = null;
        $std->CNAE      = null;
        $std->CRT       = 3;
        $std->CNPJ      = '19580028000173';
                
        $elem = $nfe->tagemit($std);
        
        //-- endereço emitente --
        $std = new \stdClass();
        $std->xLgr      = 'RUA JOAO PIUS SCHINDLER';
        $std->nro       = '765';
        $std->xCpl      = null;
        $std->xBairro   = 'BATEIAS DE BAIXO';
        $std->cMun      = '4203303';
        $std->xMun      = 'CAMPO ALEGRE';
        $std->UF        = 'SC';
        $std->CEP       = '89294000';
        $std->cPais     = '1058';
        $std->xPais     = 'Brasil';
        $std->fone      = '4736327633';
        
        $elem = $nfe->tagenderEmit($std);
        
        //-- dados destinatario --
        $std = new \stdClass();
        $std->xNome;
        $std->indIEDest;
        $std->IE;
        $std->ISUF;
        $std->IM;
        $std->email;
        $std->CNPJ; //indicar apenas um CNPJ ou CPF ou idEstrangeiro
        $std->CPF;
        $std->idEstrangeiro;
        
        $elem = $nfe->tagdest($std);
        
        //-- endereço destinatario -- 
        $std = new \stdClass();
        $std->xLgr;
        $std->nro;
        $std->xCpl;
        $std->xBairro;
        $std->cMun;
        $std->xMun;
        $std->UF;
        $std->CEP;
        $std->cPais;
        $std->xPais;
        $std->fone;
        
        $elem = $nfe->tagenderDest($std);
        
        //-- produtos 
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->cProd;
        $std->cEAN;
        $std->xProd;
        $std->NCM;
        
        $std->cBenef; //incluido no layout 4.00
        
        $std->EXTIPI;
        $std->CFOP;
        $std->uCom;
        $std->qCom;
        $std->vUnCom;
        $std->vProd;
        $std->cEANTrib;
        $std->uTrib;
        $std->qTrib;
        $std->vUnTrib;
        $std->vFrete;
        $std->vSeg;
        $std->vDesc;
        $std->vOutro;
        $std->indTot;
        $std->xPed;
        $std->nItemPed;
        $std->nFCI;
        
        $elem = $nfe->tagprod($std);
        
        //-- dados info adicional produtos
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        
        $std->infAdProd = 'informacao adicional do item';
        
        $elem = $nfe->taginfAdProd($std);
        
        // ST --
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->CEST;
        $std->indEscala; //incluido no layout 4.00
        $std->CNPJFab; //incluido no layout 4.00
        
        $elem = $nfe->tagCEST($std);
        
        //-- DI --
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->nDI;
        $std->dDI;
        $std->xLocDesemb;
        $std->UFDesemb;
        $std->dDesemb;
        $std->tpViaTransp;
        $std->vAFRMM;
        $std->tpIntermedio;
        $std->CNPJ;
        $std->UFTerceiro;
        $std->cExportador;
        
        $elem = $nfe->tagDI($std);
        
        //-- Adicoes da DI --
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->nDI; //numero da DI
        $std->nAdicao;
        $std->nSeqAdic;
        $std->cFabricante;
        $std->vDescDI;
        $std->nDraw;
        
        $elem = $nfe->tagadi($std);
        
        //-- tributos
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->vTotTrib = 1000.00;
        
        $elem = $nfe->tagimposto($std);
        
        // ICMS
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->orig;
        $std->CST;
        $std->modBC;
        $std->vBC;
        $std->pICMS;
        $std->vICMS;
        $std->pFCP;
        $std->vFCP;
        $std->vBCFCP;
        $std->modBCST;
        $std->pMVAST;
        $std->pRedBCST;
        $std->vBCST;
        $std->pICMSST;
        $std->vICMSST;
        $std->vBCFCPST;
        $std->pFCPST;
        $std->vFCPST;
        $std->vICMSDeson;
        $std->motDesICMS;
        $std->pRedBC;
        $std->vICMSOp;
        $std->pDif;
        $std->vICMSDif;
        $std->vBCSTRet;
        $std->pST;
        $std->vICMSSTRet;
        $std->vBCFCPSTRet;
        $std->pFCPSTRet;
        $std->vFCPSTRet;
        
        $elem = $nfe->tagICMS($std);
        
        // ST
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->orig = 0;
        $std->CST = '60';
        $std->vBCSTRet = 1000.00;
        $std->vICMSSTRet = 190.00;
        $std->vBCSTDest = 1000.00;
        $std->vICMSSTDest = 1.00;
        
        $elem = $nfe->tagICMSST($std);
        
        // IPI
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->clEnq = null;
        $std->CNPJProd = null;
        $std->cSelo = null;
        $std->qSelo = null;
        $std->cEnq = '999';
        $std->CST = '50';
        $std->vIPI = 150.00;
        $std->vBC = 1000.00;
        $std->pIPI = 15.00;
        $std->qUnid = null;
        $std->vUnid = null;
        
        $elem = $nfe->tagIPI($std);
        
        // II
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->vBC = 1000.00;
        $std->vDespAdu = 100.00;
        $std->vII = 220.00;
        $std->vIOF = null;
        
        $elem = $nfe->tagII($std);
        
        // PIS
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->CST = '07';
        $std->vBC = null;
        $std->pPIS = null;
        $std->vPIS = null;
        $std->qBCProd = null;
        $std->vAliqProd = null;
        
        $elem = $nfe->tagPIS($std);
        
        // COFINS
        $std = new \stdClass();
        $std->item = 1; //item da NFe
        $std->CST = '07';
        $std->vBC = null;
        $std->pCOFINS = null;
        $std->vCOFINS = null;
        $std->qBCProd = null;
        $std->vAliqProd = null;
        
        $elem = $nfe->tagCOFINS($std);
        
        // totais impostos
        $std = new \stdClass();
        $std->vBC = 1000.00;
        $std->vICMS = 1000.00;
        $std->vICMSDeson = 1000.00;
        $std->vFCP = 1000.00; //incluso no layout 4.00
        $std->vBCST = 1000.00;
        $std->vST = 1000.00;
        $std->vFCPST = 1000.00; //incluso no layout 4.00
        $std->vFCPSTRet = 1000.00; //incluso no layout 4.00
        $std->vProd = 1000.00;
        $std->vFrete = 1000.00;
        $std->vSeg = 1000.00;
        $std->vDesc = 1000.00;
        $std->vII = 1000.00;
        $std->vIPI = 1000.00;
        $std->vIPIDevol = 1000.00; //incluso no layout 4.00
        $std->vPIS = 1000.00;
        $std->vCOFINS = 1000.00;
        $std->vOutro = 1000.00;
        $std->vNF = 1000.00;
        $std->vTotTrib = 1000.00;
        
        $elem = $nfe->tagICMSTot($std);
        
        // transporte
        $std = new \stdClass();
        $std->modFrete = 1;
        
        $elem = $nfe->tagtransp($std);
        
        $std = new \stdClass();
        $std->xNome = 'Rodo Fulano';
        $std->IE = '12345678901';
        $std->xEnder = 'Rua Um, sem numero';
        $std->xMun = 'Cotia';
        $std->UF = 'SP';
        $std->CNPJ = '12345678901234';//só pode haver um ou CNPJ ou CPF, se um deles é especificado o outro deverá ser null
        $std->CPF = null;
        
        $elem = $nfe->tagtransporta($std);
        
        $std = new \stdClass();
        $std->placa = 'ABC1111';
        $std->UF = 'RJ';
        $std->RNTC = '999999';
        
        $elem = $nfe->tagveicTransp($std);
        
        $std = new \stdClass();
        $std->placa = 'BCB0897';
        $std->UF = 'SP';
        $std->RNTC = '123456';
        $std->vagao = null;
        $std->balsa = null;
        
        $elem = $nfe->tagreboque($std);
        
        // Volumes
        $std = new \stdClass();
        $std->item = 1; //indicativo do numero do volume
        $std->qVol = 2;
        $std->esp = 'caixa';
        $std->marca = 'OLX';
        $std->nVol = '11111';
        $std->pesoL = 10.50;
        $std->pesoB = 11.00;
        
        $elem = $nfe->tagvol($std);
        
        // Faturas
        $std = new \stdClass();
        $std->nFat = '1233';
        $std->vOrig = 1254.22;
        $std->vDesc = null;
        $std->vLiq = 1254.22;
        
        $elem = $nfe->tagfat($std);
        
        $std = new \stdClass();
        $std->nDup = '1233-1';
        $std->dVenc = '2017-08-22';
        $std->vDup = 1254.22;
        
        $elem = $nfe->tagdup($std);
        
        // Info adicionais 
        $std = new \stdClass();
        $std->infAdFisco = 'informacoes para o fisco';
        $std->infCpl = 'informacoes complementares';
        
        $elem = $nfe->taginfAdic($std);
        
        
        
        
        
        $nfe->monta();
        
        echo $nfe->getChave();        
                
        return $this->response;
    }
     
    public function novoAction()
    {
        
        $form = $this->form;
        $form->get('submit')->setValue('Salvar Nova');
        
        $request = $this->getRequest();
        
        if(!$request->isPost()){
            return new ViewModel([
                'form' => $form,
            ]);
        }
        
        $form->setData($request->getPost());
        
        if (!$form->isValid()) {
            return ['form' => $form];
        }
        
        $nfe = new Nfe();
        $nfe->exchangeArray($form->getData());
        $this->table->save($nfe);
        
        return $this->redirect()->toRoute('nfe');
    }
    
    public function editarAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        
        if (!$id) {
            return $this->redirect()->toRoute('nfe');
        }
        
        try {
            $nfe = $this->table->find($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('nfe');
        }
        
        $form = new NfeForm();
        $form->bind($nfe);
        $form->get('submit')->setAttribute('value', 'Salvar');
        
        $request = $this->getRequest();
        
        if (!$request->isPost()) {
            return [
                'id' => $id,
                'form' => $form
            ];
        }
        
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return [
                'id' => $id,
                'form' => $form
            ];
        }
        
        $this->table->save($nfe);
        return $this->redirect()->toRoute('nfe');
    }
    
    public function deletarAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('nfe');
        }
        
        $this->table->delete($id);
        return $this->redirect()->toRoute('nfe');
        
    }
    
    public function enviaremailAction(){
        
        $message = new Message();
        
        $message->addTo('cleiton@hbr.com.br')
            ->addFrom('cleiton@shuncorp.com')
            ->setSubject('Test send mail using ZF2')
            ->setEncoding('UTF-8');        
            
        $options   = new SmtpOptions(array(
            'host'	=> 'smtp.shuncorp.com',
            'connection_class'  => 'login',
            'connection_config' => array(
                'ssl'      => 'tls',
                'username' => 'cleiton@shuncorp.com',
                'password' => '01020304aA'
            ),
            'port' => 587,
        ));
            
        $html = new Part('<b>heii, <i>sorry</i>, i\'m going late</b>');
        $html->type = "text/html";
        
        $body = new MessageMine();
        $body->addPart($html);
        
        $message->setBody($body);
        
        $transport = new Smtp();
        $transport->setOptions($options);
        
        echo $transport->send($message);
            
        return $this->response;
        
        $registry->set("mailSmtp", array('port' => 587, 'auth' => 'login', 'username' => 'info@hbr.ind.br', 'password' => '01020304aA'));
    }
}