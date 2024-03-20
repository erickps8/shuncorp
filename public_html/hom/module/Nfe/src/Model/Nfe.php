<?php

namespace Nfe\Model;

class Nfe
{
    public $id;
    public $serie;
    public $data;
    public $cfop;
    public $naturezaop;
    public $tipo;
    public $finalidade;
    public $id_nfecomplementa;
    public $chavecomplementa;
    public $id_cliente;
    public $cnpj;
    public $inscricao;
    public $empresa;
    public $endereco;
    public $numero;
    public $bairro;
    public $cep;
    public $data_saida;
    public $codcidade;
    public $cidade;
    public $uf;
    public $pais;
    public $codpais;
    public $fone;
    public $baseicms;
    public $vlicms;
    public $basest;
    public $vlst;
    public $frete;
    public $freteperc;
    public $seguro;
    public $desconto;
    public $descontoperc;
    public $outrasdesp;
    public $totalipi;
    public $totalprodutos;
    public $totalnota;
    public $id_transportadoras;
    public $transportadora;
    public $tipofrete;
    public $transantt;
    public $transplaca;
    public $transufplaca;
    public $transcnpj;
    public $transendereco;
    public $transcidade;
    public $transuf;
    public $transie;
    public $quantidade;
    public $especie;
    public $marca;
    public $pesobruto;
    public $pesoliquido;
    public $obs;
    public $chave;
    public $cn;
    public $dv;
    public $csticms;
    public $cstipi;
    public $cstpis;
    public $cstcofins;
    public $basepis;
    public $totalpis;
    public $basecofins;
    public $totalcofins;
    public $di;
    public $datadi;
    public $localdesembarque;
    public $ufdesembarque;
    public $datadesembarque;
    public $codexportador;
    public $totalii;
    public $status;
    public $motivocanc;
    public $autorizacao;
    public $id_pagfrete;
    public $id_pagcomissao;
    public $valorfrete;
    public $valorcomissao;
    public $ufexporta;
    public $localexporta;
    public $etapa;
    public $valortotaltrib;
    public $diviatransp;
    public $totalicmsdesoneracao;
    public $totalicmsoperacao;
    public $percdiferimento;
    public $totalicmsdiferimento;
    public $suframa;
    public $email;
    public $tipopagamento;
    public $afrmm;
    public $nferef;

    public function exchangeArray(array $data)
    {
        $this->id                   = (!empty($data['id'])) ? $data['id'] : null;
        $this->serie                = (!empty($data['serie'])) ? $data['serie'] : null;
        $this->data                 = (!empty($data['data'])) ? $data['data'] : null;
        $this->cfop                 = (!empty($data['cfop'])) ? $data['cfop'] : null;
        $this->naturezaop           = (!empty($data['naturezaop'])) ? $data['naturezaop'] : null;
        $this->tipo                 = (!empty($data['tipo'])) ? $data['tipo'] : null;
        $this->finalidade           = (!empty($data['finalidade'])) ? $data['finalidade'] : null;
        $this->id_nfecomplementa    = (!empty($data['id_nfecomplementa'])) ? $data['id_nfecomplementa'] : null;
        $this->chavecomplementa     = (!empty($data['chavecomplementa'])) ? $data['chavecomplementa'] : null;
        $this->id_cliente           = (!empty($data['id_cliente'])) ? $data['id_cliente'] : null;
        $this->cnpj                 = (!empty($data['cnpj'])) ? $data['cnpj'] : null;
        $this->inscricao            = (!empty($data['inscricao'])) ? $data['inscricao'] : null;
        $this->empresa              = (!empty($data['empresa'])) ? $data['empresa'] : null;
        $this->endereco             = (!empty($data['endereco'])) ? $data['endereco'] : null;
        $this->numero               = (!empty($data['numero'])) ? $data['numero'] : null;
        $this->bairro               = (!empty($data['bairro'])) ? $data['bairro'] : null;
        $this->cep                  = (!empty($data['cep'])) ? $data['cep'] : null;
        $this->data_saida           = (!empty($data['data_saida'])) ? $data['data_saida'] : null;
        $this->codcidade            = (!empty($data['codcidade'])) ? $data['codcidade'] : null;
        $this->cidade               = (!empty($data['cidade'])) ? $data['cidade'] : null;
        $this->uf                   = (!empty($data['uf'])) ? $data['uf'] : null;
        $this->pais                 = (!empty($data['pais'])) ? $data['pais'] : null;
        $this->codpais              = (!empty($data['codpais'])) ? $data['codpais'] : null;
        $this->fone                 = (!empty($data['fone'])) ? $data['fone'] : null;
        $this->baseicms             = (!empty($data['baseicms'])) ? $data['baseicms'] : null;
        $this->vlicms               = (!empty($data['vlicms'])) ? $data['vlicms'] : null;
        $this->basest               = (!empty($data['basest'])) ? $data['basest'] : null;
        $this->vlst                 = (!empty($data['vlst'])) ? $data['vlst'] : null;
        $this->frete                = (!empty($data['frete'])) ? $data['frete'] : null;
        $this->freteperc            = (!empty($data['freteperc'])) ? $data['freteperc'] : null;
        $this->seguro               = (!empty($data['seguro'])) ? $data['seguro'] : null;
        $this->desconto             = (!empty($data['desconto'])) ? $data['desconto'] : null;
        $this->descontoperc         = (!empty($data['descontoperc'])) ? $data['descontoperc'] : null;
        $this->outrasdesp           = (!empty($data['outrasdesp'])) ? $data['outrasdesp'] : null;
        $this->totalipi             = (!empty($data['totalipi'])) ? $data['totalipi'] : null;
        $this->totalprodutos        = (!empty($data['totalprodutos'])) ? $data['totalprodutos'] : null;
        $this->totalnota            = (!empty($data['totalnota'])) ? $data['totalnota'] : null;
        $this->id_transportadoras   = (!empty($data['id_transportadoras'])) ? $data['id_transportadoras'] : null;
        $this->transportadora       = (!empty($data['transportadora'])) ? $data['transportadora'] : null;
        $this->tipofrete            = (!empty($data['tipofrete'])) ? $data['tipofrete'] : null;
        $this->transantt            = (!empty($data['transantt'])) ? $data['transantt'] : null;
        $this->transplaca           = (!empty($data['transplaca'])) ? $data['transplaca'] : null;
        $this->transufplaca         = (!empty($data['transufplaca'])) ? $data['transufplaca'] : null;
        $this->transcnpj            = (!empty($data['transcnpj'])) ? $data['transcnpj'] : null;
        $this->transendereco        = (!empty($data['transendereco'])) ? $data['transendereco'] : null;
        $this->transcidade          = (!empty($data['transcidade'])) ? $data['transcidade'] : null;
        $this->transuf              = (!empty($data['transuf'])) ? $data['transuf'] : null;
        $this->transie              = (!empty($data['transie'])) ? $data['transie'] : null;
        $this->quantidade           = (!empty($data['quantidade'])) ? $data['quantidade'] : null;
        $this->especie              = (!empty($data['especie'])) ? $data['especie'] : null;
        $this->marca                = (!empty($data['marca'])) ? $data['marca'] : null;
        $this->pesobruto            = (!empty($data['pesobruto'])) ? $data['pesobruto'] : null;
        $this->pesoliquido          = (!empty($data['pesoliquido'])) ? $data['pesoliquido'] : null;
        $this->obs                  = (!empty($data['obs'])) ? $data['obs'] : null;
        $this->chave                = (!empty($data['chave'])) ? $data['chave'] : null;
        $this->cn                   = (!empty($data['cn'])) ? $data['cn'] : null;
        $this->dv                   = (!empty($data['dv'])) ? $data['dv'] : null;
        $this->csticms              = (!empty($data['csticms'])) ? $data['csticms'] : null;
        $this->cstipi               = (!empty($data['cstipi'])) ? $data['cstipi'] : null;
        $this->cstpis               = (!empty($data['cstpis'])) ? $data['cstpis'] : null;
        $this->cstcofins            = (!empty($data['cstcofins'])) ? $data['cstcofins'] : null;
        $this->basepis              = (!empty($data['basepis'])) ? $data['basepis'] : null;
        $this->totalpis             = (!empty($data['totalpis'])) ? $data['totalpis'] : null;
        $this->basecofins           = (!empty($data['basecofins'])) ? $data['basecofins'] : null;
        $this->totalcofins          = (!empty($data['totalcofins'])) ? $data['totalcofins'] : null;
        $this->di                   = (!empty($data['di'])) ? $data['di'] : null;
        $this->datadi               = (!empty($data['datadi'])) ? $data['datadi'] : null;
        $this->localdesembarque     = (!empty($data['localdesembarque'])) ? $data['localdesembarque'] : null;
        $this->ufdesembarque        = (!empty($data['ufdesembarque'])) ? $data['ufdesembarque'] : null;
        $this->datadesembarque      = (!empty($data['datadesembarque'])) ? $data['datadesembarque'] : null;
        $this->codexportador        = (!empty($data['codexportador'])) ? $data['codexportador'] : null;
        $this->totalii              = (!empty($data['totalii'])) ? $data['totalii'] : null;
        $this->status               = (!empty($data['status'])) ? $data['status'] : null;
        $this->motivocanc           = (!empty($data['motivocanc'])) ? $data['motivocanc'] : null;
        $this->autorizacao          = (!empty($data['autorizacao'])) ? $data['autorizacao'] : null;
        $this->id_pagfrete          = (!empty($data['id_pagfrete'])) ? $data['id_pagfrete'] : null;
        $this->id_pagcomissao       = (!empty($data['id_pagcomissao'])) ? $data['id_pagcomissao'] : null;
        $this->valorfrete           = (!empty($data['valorfrete'])) ? $data['valorfrete'] : null;
        $this->valorcomissao        = (!empty($data['valorcomissao'])) ? $data['valorcomissao'] : null;
        $this->ufexporta            = (!empty($data['ufexporta'])) ? $data['ufexporta'] : null;
        $this->localexporta         = (!empty($data['localexporta'])) ? $data['localexporta'] : null;
        $this->etapa                = (!empty($data['etapa'])) ? $data['etapa'] : null;
        $this->valortotaltrib       = (!empty($data['valortotaltrib'])) ? $data['valortotaltrib'] : null;
        $this->diviatransp          = (!empty($data['diviatransp'])) ? $data['diviatransp'] : null;
        $this->totalicmsdesoneracao = (!empty($data['totalicmsdesoneracao'])) ? $data['totalicmsdesoneracao'] : null;
        $this->totalicmsoperacao    = (!empty($data['totalicmsoperacao'])) ? $data['totalicmsoperacao'] : null;
        $this->percdiferimento      = (!empty($data['percdiferimento'])) ? $data['percdiferimento'] : null;
        $this->totalicmsdiferimento = (!empty($data['totalicmsdiferimento'])) ? $data['totalicmsdiferimento'] : null;
        $this->suframa              = (!empty($data['suframa'])) ? $data['suframa'] : null;
        $this->email                = (!empty($data['email'])) ? $data['email'] : null;
        $this->tipopagamento        = (!empty($data['tipopagamento'])) ? $data['tipopagamento'] : null;
        $this->afrmm                = (!empty($data['afrmm'])) ? $data['afrmm'] : null;
        $this->nferef               = (!empty($data['nferef'])) ? $data['nferef'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id'                    => $this->id,
            'cfop'                  => $this->cfop,
            'naturezaop'            => $this->naturezaop,
            'serie'                 => $this->serie,
            'data'                  => $this->data,
            'cfop'                  => $this->cfop,
            'naturezaop'            => $this->naturezaop,
            'tipo'                  => $this->tipo,
            'finalidade'            => $this->finalidade,
            'id_nfecomplementa'     => $this->id_nfecomplementa,
            'chavecomplementa'      => $this->chavecomplementa,
            'id_cliente'            => $this->id_cliente,
            'cnpj'                  => $this->cnpj,
            'inscricao'             => $this->inscricao,
            'empresa'               => $this->empresa,
            'endereco'              => $this->endereco,
            'numero'                => $this->numero,
            'bairro'                => $this->bairro,
            'cep'                   => $this->cep,
            'data_saida'            => $this->data_saida,
            'codcidade'             => $this->codcidade,
            'cidade'                => $this->cidade,
            'uf'                    => $this->uf,
            'pais'                  => $this->pais,
            'codpais'               => $this->codpais,
            'fone'                  => $this->fone,
            'baseicms'              => $this->baseicms,
            'vlicms'                => $this->vlicms,
            'basest'                => $this->basest,
            'vlst'                  => $this->vlst,
            'frete'                 => $this->frete,
            'freteperc'             => $this->freteperc,
            'seguro'                => $this->seguro,
            'desconto'              => $this->desconto,
            'descontoperc'          => $this->descontoperc,
            'outrasdesp'            => $this->outrasdesp,
            'totalipi'              => $this->totalipi,
            'totalprodutos'         => $this->totalprodutos,
            'totalnota'             => $this->totalnota,
            'id_transportadoras'    => $this->id_transportadoras,
            'transportadora'        => $this->transportadora,
            'tipofrete'             => $this->tipofrete,
            'transantt'             => $this->transantt,
            'transplaca'            => $this->transplaca,
            'transufplaca'          => $this->transufplaca,
            'transcnpj'             => $this->transcnpj,
            'transendereco'         => $this->transendereco,
            'transcidade'           => $this->transcidade,
            'transuf'               => $this->transuf,
            'transie'               => $this->transie,
            'quantidade'            => $this->quantidade,
            'especie'               => $this->especie,
            'marca'                 => $this->marca,
            'pesobruto'             => $this->pesobruto,
            'pesoliquido'           => $this->pesoliquido,
            'obs'                   => $this->obs,
            'chave'                 => $this->chave,
            'cn'                    => $this->cn,
            'dv'                    => $this->dv,
            'csticms'               => $this->csticms,
            'cstipi'                => $this->cstipi,
            'cstpis'                => $this->cstpis,
            'cstcofins'             => $this->cstcofins,
            'basepis'               => $this->basepis,
            'totalpis'              => $this->totalpis,
            'basecofins'            => $this->basecofins,
            'totalcofins'           => $this->totalcofins,
            'di'                    => $this->di,
            'datadi'                => $this->datadi,
            'localdesembarque'      => $this->localdesembarque,
            'ufdesembarque'         => $this->ufdesembarque,
            'datadesembarque'       => $this->datadesembarque,
            'codexportador'         => $this->codexportador,
            'totalii'               => $this->totalii,
            'status'                => $this->status,
            'motivocanc'            => $this->motivocanc,
            'autorizacao'           => $this->autorizacao,
            'id_pagfrete'           => $this->id_pagfrete,
            'id_pagcomissao'        => $this->id_pagcomissao,
            'valorfrete'            => $this->valorfrete,
            'valorcomissao'         => $this->valorcomissao,
            'ufexporta'             => $this->ufexporta,
            'localexporta'          => $this->localexporta,
            'etapa'                 => $this->etapa,
            'valortotaltrib'        => $this->valortotaltrib,
            'diviatransp'           => $this->diviatransp,
            'totalicmsdesoneracao'  => $this->totalicmsdesoneracao,
            'totalicmsoperacao'     => $this->totalicmsoperacao,
            'percdiferimento'       => $this->percdiferimento,
            'totalicmsdiferimento'  => $this->totalicmsdiferimento,
            'suframa'               => $this->suframa,
            'email'                 => $this->email,
            'tipopagamento'         => $this->tipopagamento,
            'afrmm'                 => $this->afrmm,
            'nferef'                => $this->nferef,
        ];
    }

}