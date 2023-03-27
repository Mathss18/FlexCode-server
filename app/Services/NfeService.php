<?php

namespace App\Services;

use stdClass;
use Auth;
use DateTime;
use Illuminate\Support\Facades\Storage;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Keys;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Daevento;
use Illuminate\Support\Facades\Log;

class NfeService
{

    private $xmlFinal;
    private $recibo;
    private $protocolo;
    private $chave;
    private $success = false;
    private $error;
    private $soap;

    private $tools;

    public function __construct($config)
    {

        // $certificadoDigital = file_get_contents('..\app\Services\certFM.pfx');

        if (Storage::disk('local')->exists('public/' . session('tenant')->nome . '/configuracoes/certificadoDigital/certificado-digital.x-pkcs12')) {
            $path = Storage::disk('local')->path('public/' . session('tenant')->nome . '/configuracoes/certificadoDigital/certificado-digital.x-pkcs12');
            $certificadoDigital = file_get_contents($path);
        } else {
            throw new \Exception('Certificado digital não encontrado');
        }

        $this->config = $config;
        try {
            $this->tools = new Tools(json_encode($config), Certificate::readPfx($certificadoDigital, session('config')->senhaCertificadoDigital));
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        $this->soap = new SoapCurl(Certificate::readPfx($certificadoDigital, session('config')->senhaCertificadoDigital));
        $this->soap->timeout(600); // 10 minutos de timeout // Aumentar tbm na pastar vendor arquivo -> SoapBase.php
    }

    public function gerarNfe($dados, $favorecido, $produtos, $transportadora, $aliquota)
    {
        // return $dados;

        //Criar Nota Fiscal Vazia
        $nfe = new Make();

        //====================TAG INFO===================
        $infNfe = new stdClass();
        $infNfe->versao = '4.00'; //versão do layout (string)
        $infNfe->Id = null; //se o Id de 44 digitos não for passado será gerado automaticamente
        $infNfe->pk_nItem = null; //deixe essa variavel sempre como NULL

        $nfe->taginfNFe($infNfe);

        //====================TAG IDE===================
        $ide = new stdClass();
        $ide->cUF = $this->getCodigoMinicipio(); //codigo do estado
        $ide->nNF = session('config')->nNF + 1; //numero da nota fiscal
        $ide->cNF = rand(11111111, 99999999); //STR_PAD($ide->nNF + 1, '0', 8, STR_PAD_LEFT);
        $ide->natOp = $this->tirarAcentos($dados['natOp']['label']);
        $ide->mod = 55;
        $ide->serie = session('config')->serie;
        $ide->dhEmi = date('Y-m-d\TH:i:sP');
        $ide->dhSaiEnt = date('Y-m-d\TH:i:sP');
        $ide->tpNF = $dados['tpNF'];
        $ide->idDest = $favorecido['estado'] == session('config')->estado ? 1 : 2;
        $ide->cMunFG = session('config')->codigoMunicipio;
        $ide->tpImp = 1; //Formato de Impressão da DANFE 1-Retrato / 2-Paisagem
        $ide->tpEmis = 1;
        // $ide->cDV = 2; // Dígito Verificador da Chave de Acesso da NF-e
        $ide->tpAmb = session('config')->ambienteNfe;
        $ide->finNFe = $dados['finNFe']; //1-NF-e normal, 2-NF-e complementar, 3-NF-e de ajuste, 4-Devolução/Retorno
        $ide->indFinal = $dados['indFinal']; // 0-Normal; 1-Consumidor final;
        $ide->indPres = $dados['indPres'];
        $ide->indIntermed = null;
        $ide->procEmi = 0;
        $ide->verProc = '4.00';
        $ide->dhCont = null;
        $ide->xJust = null;

        $nfe->tagide($ide);

        //====================TAG REF NFE===================
        if (array_key_exists("refNFe", $dados)) {
            $nfeRef = new stdClass();
            $nfeRef->refNFe = $dados['refNFe'];
            // $nfeRef->refNFe = "35221109136351000107550010000028741885537470";
            $nfe->tagrefNFe($nfeRef);
        }

        $nfeRef = new stdClass();
        // $nfeRef->refNFe = $dados['refNFe'];
        $nfeRef->refNFe = "35230260199957000726550010000847991424906223";
        $nfe->tagrefNFe($nfeRef);



        //====================TAG EMITENTE===================
        $emit = new stdClass();
        $emit->xNome = $this->tirarAcentos(session('config')->nome);
        $emit->xFant = $this->tirarAcentos(session('config')->nomeFantasia);
        $emit->IE = session('config')->inscricaoEstadual;
        //$emit->IEST;
        //$emit->IM ;
        //$emit->CNAE;
        $emit->CRT =  session('config')->crt;
        $emit->CNPJ =  session('config')->tipoEmpresa == 'pj' ? session('config')->cpfCnpj : null;
        $emit->CPF = session('config')->tipoEmpresa == 'pf' ? session('config')->cpfCnpj : null;

        $nfe->tagemit($emit);

        //====================TAG ENDERECO EMITENTE===================
        $enderEmit = new stdClass();
        $enderEmit->xLgr = $this->tirarAcentos(session('config')->rua);
        $enderEmit->nro = $this->tirarAcentos(session('config')->numero);
        //$enderEmit->xCpl;
        $enderEmit->xBairro = $this->tirarAcentos(session('config')->bairro);
        $enderEmit->cMun = session('config')->codigoMunicipio;
        $enderEmit->xMun = $this->tirarAcentos(session('config')->cidade);
        $enderEmit->UF = session('config')->estado;
        $enderEmit->CEP = session('config')->cep;
        $enderEmit->cPais = '1058';
        $enderEmit->xPais = 'Brasil';
        $enderEmit->fone = session('config')->telefone;

        $nfe->tagenderEmit($enderEmit);

        //====================TAG DESTINATARIO===================
        $dest = new stdClass();
        $dest->xNome = $this->tirarAcentos($favorecido['nome']);
        $dest->indIEDest = $favorecido['tipoContribuinte'];
        $dest->IE = $favorecido['tipoContribuinte'] == 1 ? $favorecido['inscricaoEstadual'] : null;
        // $dest->ISUF;
        // $dest->IM;
        $dest->email = $favorecido['email'];
        if (strlen($favorecido['cpfCnpj']) == 14) {
            $dest->CNPJ = $favorecido['cpfCnpj'];
        } else {
            $dest->CPF = $favorecido['cpfCnpj'];
        }
        // $dest->idEstrangeiro;

        $nfe->tagdest($dest);

        //====================TAG ENDERECO DESTINATARIO===================
        $enderDest = new stdClass();
        $enderDest->xLgr = $this->tirarAcentos($favorecido->rua);
        $enderDest->nro = $favorecido->numero;
        //$enderDest->xCpl;
        $enderDest->xBairro = $this->tirarAcentos($favorecido->bairro);
        $enderDest->cMun = $favorecido->codigoMunicipio;
        $enderDest->xMun = $this->tirarAcentos($favorecido->cidade);
        $enderDest->UF = $favorecido->estado;
        $enderDest->CEP = str_replace("-", "", $favorecido->cep);
        $enderDest->cPais = '1058';
        $enderDest->xPais = 'Brasil';
        $enderDest->fone = $favorecido->telefone;

        $nfe->tagenderDest($enderDest);

        $vbc = 0;
        $vicms = 0;
        //====================TAG PRODUTO===================
        for ($i = 0; $i < count($dados['produtos']); $i++) {
            $prod = new stdClass();
            $prod->item = $i + 1; //item da NFe
            $prod->cProd = $produtos[$i]['codigoInterno'];
            $prod->cEAN =  $produtos[$i]['codigoBarras'] ?? 'SEM GTIN';
            $prod->xProd = $dados['produtos'][$i]['nome'];
            $prod->NCM =   $produtos[$i]['ncm'];

            //$prod->cBenef = null; //incluido no layout 4.00

            //$prod->EXTIPI;
            $prod->CFOP = $dados['produtos'][$i]['cfop'];
            $prod->uCom = $produtos[$i]['unidade_produto']['sigla'] ?? 'PC'; //Unidade do produto
            $prod->qCom = $dados['produtos'][$i]['quantidade']; //Quantidade do produto
            $prod->vUnCom = $dados['produtos'][$i]['preco']; // Valor total - %desconto
            $prod->cEANTrib = $produtos[$i]['codigoBarras'] ?? 'SEM GTIN';
            $prod->uTrib = $produtos[$i]['unidade_produto']['sigla'] ?? 'PC'; //Unidade do produto
            $prod->qTrib = $dados['produtos'][$i]['quantidade'];
            $prod->vUnTrib = $dados['produtos'][$i]['preco'];
            $prod->vProd = $dados['produtos'][$i]['total'];
            if ($dados['frete'] > 0.00) {
                if ($i == count($dados['produtos']) - 1) {
                    $prod->vFrete = number_format($dados['frete'], 2, '.', '');
                }
            }
            //$prod->vSeg = 0.00;
            //$prod->vDesc =  (($nfe2['precoProd'][$i] * $nfe3['porcento'])/100);
            //$prod->vOutro = 0.00;// change 0.00
            $prod->indTot = 1;
            //$prod->xPed;         //Numero de pedido do cliente
            //$prod->nItemPed;
            //$prod->nFCI;

            $nfe->tagprod($prod);

            //====================TAG INFORMACAO ADICIONAL PRODUTO===================
            // $adciProd = new stdClass();
            // $adciProd->item = $i+1; //item da NFe

            // $adciProd->infAdProd = 'informacao adicional do item';

            // $nfe->taginfAdProd($adciProd);

            //====================TAG IMPOSTO===================
            $imposto = new stdClass();
            $imposto->item = $i + 1; //item da NFe
            //$imposto->vTotTrib = 1000.00;

            $nfe->tagimposto($imposto);

            //====================TAG ICMS SIMPLES NACIONAL ===================
            $icms = new stdClass();
            $icms->item = $i + 1; //item da NFe
            $icms->orig = 0;
            //VERIFICA SE TEM IE OU NÃO
            if (
                $dados['produtos'][$i]['cfop'] == '5101' ||
                $dados['produtos'][$i]['cfop'] == '5102' ||
                $dados['produtos'][$i]['cfop'] == '6101' ||
                $dados['produtos'][$i]['cfop'] == '6102'
            ) {
                $icms->CSOSN = '101';
                $icms->pCredSN = $aliquota;
                $icms->vCredICMSSN = $dados['totalProdutos'] * ($aliquota / 100);
            } else if (
                $dados['produtos'][$i]['cfop'] == '5902' ||
                $dados['produtos'][$i]['cfop'] == '6912' ||
                $dados['produtos'][$i]['cfop'] == '5949' ||
                $dados['produtos'][$i]['cfop'] == '6910'
            ) {
                $icms->CSOSN = '400';
                $icms->pCredSN = $aliquota;
                $icms->vCredICMSSN = $dados['totalProdutos'] * ($aliquota / 100);
            } else {
                $icms->CSOSN = '900';
                $icms->pCredSN = $aliquota;
                $icms->vCredICMSSN = $dados['totalProdutos'] * ($aliquota / 100);
            }
            $vbc += $dados['produtos'][$i]['total'];
            $vicms += ($dados['produtos'][$i]['total'] * 18.0)/100;
            //$icms->modBCST = null;
            //$icms->pMVAST = null;
            //$icms->pRedBCST = null;
            // $icms->vBCST = $dados['produtos'][$i]['total'];
            //$icms->pICMSST = null;
            // $icms->vICMSST = ($dados['produtos'][$i]['total'] * 18.0)/100; // change COMENTAR A LINHA OU NULL
            //$icms->vBCFCPST = null; //incluso no layout 4.00
            //$icms->pFCPST = null; //incluso no layout 4.00
            //$icms->vFCPST = null; //incluso no layout 4.00
            //$icms->vBCSTRet = null;
            //$icms->pST = null;
            //$icms->vICMSSTRet = null;
            //$icms->vBCFCPSTRet = null; //incluso no layout 4.00
            //$icms->pFCPSTRet = null; //incluso no layout 4.00
            //$icms->vFCPSTRet = null; //incluso no layout 4.00
            $icms->modBC = 3;
            $icms->vBC = $dados['produtos'][$i]['total'];
            //$icms->pRedBC = null;
            $icms->pICMS = 18.00;
            $icms->vICMS = ($dados['produtos'][$i]['total'] * 18.0)/100; // change COMENTAR A LINHA OU NULL
            //$icms->pRedBCEfet = null;
            //$icms->vBCEfet = null;
            //$icms->pICMSEfet = null;
            //$icms->vICMSEfet = null;
            //$icms->vICMSSubstituto = null;

            $nfe->tagICMSSN($icms);

            //====================TAG PIS===================
            $pis = new stdClass();
            $pis->item = $i + 1; //item da NFe
            $pis->CST = 99;
            $pis->vBC = 0.00;
            $pis->pPIS = 0.00;
            $pis->vPIS = 0.00;
            //$pis->qBCProd = null;
            //$pis->vAliqProd = null;

            $nfe->tagPIS($pis);

            //====================TAG COFINS===================
            $cofis = new stdClass();
            $cofis->item = $i + 1; //item da NFe
            $cofis->CST = 99;
            $cofis->vBC = 0.00;
            $cofis->pCOFINS = 0.00;
            $cofis->vCOFINS = 0.00;
            //$cofis->qBCProd = null;
            //$cofis->vAliqProd = null;

            $nfe->tagCOFINS($cofis);

            //====================TAG IPI===================
            // $ipi = new stdClass();
            // $ipi->item =  $i + 1; //item da NFe
            // $ipi->clEnq = null;
            // $ipi->CNPJProd = null;
            // $ipi->cSelo = null;
            // $ipi->qSelo = null;
            // $ipi->cEnq = '999';
            // $ipi->CST = 99;
            // $ipi->vIPI = 0.00;
            // $ipi->vBC = 1000.00;
            // $ipi->pIPI = 0.00;
            // $ipi->qUnid = null;
            // $ipi->vUnid = null;

            // $nfe->tagIPI($ipi);

        }

        //====================TAG ICMSTOTAL===================
        $icmsTotal = new stdClass();
        $icmsTotal->vBC = $vbc;
        $icmsTotal->vICMS = $vicms; //change 480.21
        $icmsTotal->vICMSDeson = 0.00;
        $icmsTotal->vFCP = 0.00; //incluso no layout 4.00
        $icmsTotal->vBCST = 0.00;
        $icmsTotal->vST = 0.00;
        $icmsTotal->vFCPST = 0.00; //incluso no layout 4.00
        $icmsTotal->vFCPSTRet = 0.00; //incluso no layout 4.00
        $icmsTotal->vProd = $dados['totalProdutos'];
        $icmsTotal->vFrete = $dados['frete'];
        $icmsTotal->vSeg = 0.00;
        $icmsTotal->vDesc = 0.00;
        $icmsTotal->vII = 0.00;
        $icmsTotal->vIPI = 0.00; //change 133.39
        $icmsTotal->vIPIDevol = 0.00; //incluso no layout 4.00
        $icmsTotal->vPIS = 0.00;
        $icmsTotal->vCOFINS = 0.00;
        $icmsTotal->vOutro = 0.00; // change to 0.00
        $icmsTotal->vNF = $dados['totalFinal']; // total produtos + frete
        //$icmsTotal->vTotTrib = 0.00;

        $nfe->tagICMSTot($icmsTotal);

        //====================TAG TRANSP===================
        $transp = new stdClass();
        $transp->modFrete = $dados['modFrete']; //0-Por conta do emitente; 1-Por conta do destinatário/remetente; 2-Por conta de terceiros; 9-Sem frete. (V2.0)

        $nfe->tagtransp($transp);

        //====================TAG TRANSPORTADORA===================
        if ($transportadora) {
            $transpo = new stdClass();
            $transpo->xNome = $transportadora->nome;
            $transpo->IE = $transportadora->inscricaoEstadual;
            $transpo->xEnder = $this->tirarAcentos($transportadora->rua);
            $transpo->xMun = $transportadora->cidade;
            $transpo->UF = $transportadora->estado;
            if (strlen($transportadora->cpfCnpj) == 14) {
                $transpo->CNPJ = $transportadora->cpfCnpj;
            } else {
                $transpo->CPF = $transportadora->cpfCnpj;
            }

            $nfe->tagtransporta($transpo);
        }


        //====================TAG VOLUME===================
        $vol = new stdClass();
        //$vol->item = 1; //indicativo do numero do volume
        $vol->qVol = $dados['qVol'];
        $vol->esp = $dados['esp'];
        //$vol->marca = 'MARCA';
        //$vol->nVol = '11111';
        $vol->pesoL = $dados['pesoL'];
        $vol->pesoB = $dados['pesoB'];

        $nfe->tagvol($vol);


        if (count($dados['parcelas']) >= 1) {

            //====================TAG FATURA===================
            $fat = new stdClass();
            $fat->nFat = $ide->nNF;
            $fat->vOrig = array_reduce($dados['parcelas'], array($this, "sum"));
            $fat->vDesc = $dados['desconto'];
            $fat->vLiq =  $fat->vOrig - $fat->vDesc;
            $nfe->tagfat($fat);
            //====================TAG DUPLICATA===================

            for ($i = 0; $i < count($dados['parcelas']); $i++) {

                $dup = new stdClass();

                $dup->nDup = str_pad($i + 1, 3, "0", STR_PAD_LEFT);
                $date = DateTime::createFromFormat('d/m/Y', $dados['parcelas'][$i]['dataVencimento']);
                $dup->dVenc = $date->format('Y-m-d');
                $dup->vDup = $dados['parcelas'][$i]['valorParcela'];
                $nfe->tagdup($dup);
            }

            // $dup1 = new stdClass();
            // $dup1->nDup = '001';
            // $dup1->dVenc = '2022-07-27';
            // $dup1->vDup = 6323.88;
            // $nfe->tagdup($dup1);

            // $dup2 = new stdClass();
            // $dup2->nDup = '002';
            // $dup2->dVenc = '2022-08-03';
            // $dup2->vDup = 6323.88;
            // $nfe->tagdup($dup2);

            // $dup3 = new stdClass();
            // $dup3->nDup = '003';
            // $dup3->dVenc = '2022-08-10';
            // $dup3->vDup = 6323.88;
            // $nfe->tagdup($dup3);

            // $dup4 = new stdClass();
            // $dup4->nDup = '004';
            // $dup4->dVenc = '2022-08-17';
            // $dup4->vDup = 6323.88;
            // $nfe->tagdup($dup4);


            // $dup5 = new stdClass();
            // $dup5->nDup = '005';
            // $dup5->dVenc = '2022-08-24';
            // $dup5->vDup = 6323.88;
            // $nfe->tagdup($dup5);


            // $dup6 = new stdClass();
            // $dup6->nDup = '006';
            // $dup6->dVenc = '2022-08-31';
            // $dup6->vDup = 6323.88;
            // $nfe->tagdup($dup6);


            // $dup7 = new stdClass();
            // $dup7->nDup = '007';
            // $dup7->dVenc = '2022-09-07';
            // $dup7->vDup = 6323.92;
            // $nfe->tagdup($dup7);


        }

        //====================TAG PAGAMENTO===================
        $pag = new stdClass();
        //$std->vTroco = null; //incluso no layout 4.00, obrigatório informar para NFCe (65)

        $nfe->tagpag($pag);

        //====================TAG DETALHE PAGAMENTO===================
        if (count($dados['parcelas']) >= 1) {
            $tipoFormaPag = '01';
            $totalFinalFormaPag = $dados['totalFinal'];
        } else {
            $tipoFormaPag = '90';
            $totalFinalFormaPag = 0;
        }
        $detPag = new stdClass();
        $detPag->tPag = $tipoFormaPag; //01-Dinheiro; 02-Cheque; 03-Cartão de Crédito; 04-Cartão de Débito; 05-Crédito Loja; 10-Vale Alimentação; 11-Vale Refeição; 12-Vale Presente; 13-Vale Combustível; 99-Outros
        $detPag->vPag = $totalFinalFormaPag; //Obs: deve ser informado o valor pago pelo cliente change 0.00
        //$detPag->CNPJ = '12345678901234';
        //$detPag->tBand = '01';
        //$detPag->cAut = '3333333';
        //$detPag->tpIntegra = 1; //incluso na NT 2015/002
        //$detPag->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

        $nfe->tagdetPag($detPag);

        //====================INFO ADICIONAL===================
        $stdInfo = new stdClass();
        if (array_key_exists("infAdFisco", $dados)) {
            $stdInfo->infAdFisco = $dados['infAdFisco'] . " --- DOCUMENTO EMITIDO POR ME OU EPP OPTANTE PELO SIMPLES NACIONAL, CONFORME LEI COMPLEMENTAR 123/2006 II - NAO GERA DIREITO A CREDITO FISCAL DE IPI. III - PERMITE O APROVEITAMENTO DO CREDITO DE ICMS NO VALOR DE R$ " . $icms->vCredICMSSN . " CORRESPONDENTE A ALIQUOTA DE " . $aliquota . ", NOS TERMOS DO ART. 23 DA LC 123/2006";
        } else {
            $stdInfo->infAdFisco = " --- DOCUMENTO EMITIDO POR ME OU EPP OPTANTE PELO SIMPLES NACIONAL, CONFORME LEI COMPLEMENTAR 123/2006 II - NAO GERA DIREITO A CREDITO FISCAL DE IPI. III - PERMITE O APROVEITAMENTO DO CREDITO DE ICMS NO VALOR DE R$ " . $icms->vCredICMSSN . " CORRESPONDENTE A ALIQUOTA DE " . $aliquota . ", NOS TERMOS DO ART. 23 DA LC 123/2006";
        }

        $stdInfo->infCpl = $dados['infCpl'] ?? '';

        $nfe->taginfAdic($stdInfo);

        //====================MONTA A NOTA FISCAL ====================

        // dd($nfe->getErrors());
        // dd($nfe->dom->errors);

        try {
            $chave = $this->getChave($ide, $emit);
            $xml = $this->montar($nfe);
            $xmlAssinado = $this->assinar($xml);
            $this->transmitir($xmlAssinado, $chave);
            return [
                "chave" => $this->chave,
                "protocolo" => $this->protocolo,
                "recibo" => $this->recibo,
                "xml" => $this->xmlFinal,
                "success" => $this->success,
                "error" => $this->error,
                "tagErrors" => null,
                "tagDOM" => null
            ];
        } catch (\Throwable $th) {
            $this->error = $th;
            return [
                "chave" => $this->chave,
                "protocolo" => $this->protocolo,
                "recibo" => $this->recibo,
                "xml" => $this->xmlFinal,
                "success" => $this->success,
                "error" => $this->error,
                "tagErrors" => $nfe->getErrors(),
                "tagDOM" => $nfe->dom->errors
            ];
        }
    }

    public function montar($nfe)
    {
        try {
            $xml = $nfe->monta();
            return $xml;
        } catch (\Exception $ex) {
            $this->error = 'Erro ao montar: ' . $ex->getMessage();
            throw $ex;
        }
    }

    public function assinar($xml)
    {
        try {
            $xmlAssinado = $this->tools->signNFe($xml); // O conteúdo do XML assinado fica armazenado na variável $xmlAssinado
            Log::info(['xmlAssinado' => $xmlAssinado]);
            return $xmlAssinado;
        } catch (\Exception $ex) {
            //aqui você trata possíveis exceptions da assinatura
            $this->error = 'Problema ao assinar a NFe' . $ex->getMessage();
            throw $ex;
        }
    }

    public function transmitir($xmlAssinado, $chave)
    {
        try {
            $st = new Standardize();

            //Envia o lote
            $xmlTranmitido = $this->tools->sefazEnviaLote([$xmlAssinado], 1);
            Log::info(['xmlTransmitido' => $xmlTranmitido]);
            $std = $st->toStd($xmlTranmitido);
            if ($std->cStat != 103) {
                //erro registrar e voltar
                $this->error = ("[$std->cStat] $std->xMotivo");
            }
            $recibo = $std->infRec->nRec; // Vamos usar a variável $recibo para consultar o status da nota

            // sleep(5); // Dorme por 5 segundos para evitar sobrecarga do servidor

            $xmlFinal = $this->consultaRecibo($recibo, $xmlAssinado, $chave);
            return $xmlFinal;
        } catch (\Exception $ex) {
            $this->error = "Erro ao transmitir a NFe: " . $ex->getMessage();
            throw $ex;
        }
    }

    public function consultaRecibo($recibo, $xmlAssinado, $chave)
    {
        try {
            $protocolo = $this->tools->sefazConsultaRecibo($recibo);
            Log::info(['protocolo' => $protocolo]);
            //transforma o xml de retorno em um stdClass
            $st = new Standardize();
            $std = $st->toStd($protocolo);

            $this->protocolo = $std->protNFe->infProt->nProt ?? '';
            $this->recibo = $recibo ?? '';
            $this->chave = $chave ?? '';

            if ($std->cStat == '103') { //lote enviado
                //Lote ainda não foi precessado pela SEFAZ;
            }
            if ($std->cStat == '105') { //lote em processamento
                //tente novamente mais tarde
                sleep(5);
                $this->consultaRecibo($recibo, $xmlAssinado, $chave);
            }

            if ($std->cStat == '104') { //lote processado (tudo ok)

                if ($std->protNFe->infProt->cStat == '100') { //Autorizado o uso da NF-e
                    //Protocola o recibo no XML
                    $request = $xmlAssinado;
                    $response = $protocolo;

                    $xmlFinal = Complements::toAuthorize($request, $response);
                    $this->success = true;
                    $this->xmlFinal = $xmlFinal;
                    return $xmlFinal;
                } elseif (in_array($std->protNFe->infProt->cStat, ["110", "301", "302"])) { //DENEGADAS
                    $this->error = 'Problema ao consultar recibo. Situação:' . ' denegada ' . $std->protNFe->infProt->xMotivo . ' cstat: ' . $std->protNFe->infProt->cStat;
                    throw new \Exception($this->error);
                } else { //não autorizada (rejeição)
                    $this->error = 'Problema ao consultar recibo. Situação:' . ' rejeitada ' . $std->protNFe->infProt->xMotivo . ' cstat: ' . $std->protNFe->infProt->cStat;
                    throw new \Exception($this->error);
                }
            } else { //outros erros possíveis
                $this->error = 'Problema ao consultar recibo. Situação:' . ' rejeitada ' . $std->protNFe->infProt->xMotivo . ' cstat: ' . $std->protNFe->infProt->cStat;
                throw new \Exception($this->error);
            }
        } catch (\Exception $ex) {
            $this->error = 'Problema ao consultar recibo. ' . $ex->getMessage();
            throw $ex;
        }
    }

    public function getChave($ide, $emit)
    {
        $mes = date('m');
        $ano = date('y');
        //$chave = $ide->cUF.$ano.$mes.$emit->CNPJ.$ide->mod.'00'.$ide->serie.$cNFcomZero.$ide->tpEmis.$ide->cNF.'0';
        $chave = Keys::build($ide->cUF, $ano, $mes, $emit->CNPJ, $ide->mod, $ide->serie, $ide->nNF, $ide->tpEmis, $ide->cNF);
        return $chave;
    }

    public function gerarDanfe($chave)
    {
        $mes = date('m');
        $ano = date('Y');

        $xmlPath = Storage::disk('local')->path("public/" . session('tenant')->nome . "/nfe/{$mes}-${ano}/${chave}.xml");
        $xml = file_get_contents($xmlPath);

        if (Storage::disk('local')->exists("public/" . session('tenant')->nome . "/configuracoes/logo/logo.png")) {
            $logoPath = Storage::disk('local')->path("public/" . session('tenant')->nome . "/configuracoes/logo/logo.png");
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($logoPath)) ?? '';
        } else if (Storage::disk('local')->exists("public/" . session('tenant')->nome . "/configuracoes/logo/logo.jpg")) {

            $logoPath = Storage::disk('local')->path("public/" . session('tenant')->nome . "/configuracoes/logo/logo.jpg");
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($logoPath)) ?? '';
        } else {
            $logo = '';
        }


        try {
            $danfe = new Danfe($xml);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('Sistema Allmacoding - www.allmacoding.com (19) 98313-6930');
            //Gera o PDF
            $pdf = $danfe->render($logo);
            // header('Content-Type: application/pdf');
            // echo ($pdf);
            return $pdf;
        } catch (\Exception $ex) {
            return "Ocorreu um erro durante a geração da DANFE :" . $ex->getMessage();
            throw $ex;
        }
    }

    public function inutilizaNumerosNfe($dados)
    {

        try {
            $nSerie = $dados['serie'];
            $nIni = $dados['numeroInicial'];
            $nFin = $dados['numeroFinal'];
            $xJust = $dados['justificativa'];
            $response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

            //você pode padronizar os dados de retorno atraves da classe abaixo
            //de forma a facilitar a extração dos dados do XML
            //NOTA: mas lembre-se que esse XML muitas vezes será necessário,
            //      quando houver a necessidade de protocolos
            $stdCl = new Standardize($response);
            //nesse caso $std irá conter uma representação em stdClass do XML
            $std = $stdCl->toStd();
            //nesse caso o $arr irá conter uma representação em array do XML
            $arr = $stdCl->toArray();
            //nesse caso o $json irá conter uma representação em JSON do XML
            $json = $stdCl->toJson();

            $std1 = new Standardize($response);
            $retorno = $std1->toStd();
            $cStat = $retorno->infInut->cStat;
            if ($cStat == '102' || $cStat == '563') { //validou
                $fileRandomName = now();
                $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . 'nfeInutilizadas' . '/' . $fileRandomName . '.xml', $response);
                if ($fileUploaded) {
                    $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . 'nfeInutilizadas' . '/' . $fileRandomName . '.xml';
                    return $url;
                }
            } else {
                throw new \Exception('Problema ao inutilizar. Situação:' . $retorno->infInut->xMotivo . ' cstat: ' . $retorno->infInut->cStat);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function corrigirNfe($dados)
    {
        try {
            $this->tools->model('55');

            $chave = $dados['chave']; //Chave da Nfe
            $xCorrecao = $dados['justificativa']; //Justificativa da correção
            $nSeqEvento = $dados['nSeqEvento'] + 1; //Numero do evento, ou seja qual o n° de cartas já feito
            $response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
            $mes = date('m');
            $ano = date('Y');

            //você pode padronizar os dados de retorno atraves da classe abaixo
            //de forma a facilitar a extração dos dados do XML
            //NOTA: mas lembre-se que esse XML muitas vezes será necessário,
            //      quando houver a necessidade de protocolos
            $stdCl = new Standardize($response);
            //nesse caso $std irá conter uma representação em stdClass do XML
            $std = $stdCl->toStd();
            //nesse caso o $arr irá conter uma representação em array do XML
            $arr = $stdCl->toArray();
            //nesse caso o $json irá conter uma representação em JSON do XML
            $json = $stdCl->toJson();

            //verifique se o evento foi processado
            if ($std->cStat != 128) {
                throw new \Exception('Erro Ao Tirar Carta de Correção!  Erro numero:', $std->cStat);
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '135' || $cStat == '136') {
                    //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . 'nfeCorrecoes' . '/' . "${mes}-${ano}/" . $chave . '.xml', $xml);
                    if ($fileUploaded) {
                        $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . 'nfeCorrecoes' . '/' . "${mes}-${ano}/" . $chave . '.xml';
                        return $url;
                    }
                } else {
                    throw new \Exception('Erro Ao Tirar Carta de Correção!  Erro numero: ' . $std->cStat . ' / ' . $std->retEvento->infEvento->cStat);
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function gerarCartaCorrecaoPdf($chave)
    {
        $mes = date('m');
        $ano = date('Y');
        $path =  Storage::disk('local')->path("public/" . session('tenant')->nome . '/' . 'nfeCorrecoes' . '/' . "${mes}-${ano}/" . $chave . '.xml');
        $xml = file_get_contents($path);
        $logo = $this->getLogo();


        try {
            $daevento = new Daevento($xml, $this->config);
            $daevento->debugMode(false);
            $daevento->creditsIntegratorFooter('Sistema Allmacoding - www.allmacoding.com (19) 98313-6930');
            $pdf = $daevento->render($logo);
            $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . 'nfeCorrecoes' . '/' . "${mes}-${ano}/" . $chave . '.pdf', $pdf);
            if ($fileUploaded) {
                $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . 'nfeCorrecoes' . '/' . "${mes}-${ano}/" . $chave . '.pdf';
                return $url;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function cancelarNfe($dados)
    {
        try {
            $this->tools->model('55');

            $chave = $dados['chave']; //Chave da Nfe
            $xJust = $dados['justificativa']; //Justificativa da correção
            $nProt = $dados['protocolo']; //Numero do protocolo
            $mes = date('m');
            $ano = date('Y');

            $response = $this->tools->sefazCancela($chave, $xJust, $nProt);

            //você pode padronizar os dados de retorno atraves da classe abaixo
            //de forma a facilitar a extração dos dados do XML
            //NOTA: mas lembre-se que esse XML muitas vezes será necessário,
            //      quando houver a necessidade de protocolos
            $stdCl = new Standardize($response);
            //nesse caso $std irá conter uma representação em stdClass do XML
            $std = $stdCl->toStd();
            //nesse caso o $arr irá conter uma representação em array do XML
            $arr = $stdCl->toArray();
            //nesse caso o $json irá conter uma representação em JSON do XML
            $json = $stdCl->toJson();

            //verifique se o evento foi processado
            if ($std->cStat != 128) {
                //houve alguma falha e o evento não foi processado
                throw new \Exception('Erro Ao Cancelar Nota!  Erro numero:', $std->cStat);
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
                    //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . 'nfeCanceladas' . '/' . "${mes}-${ano}/" . $chave . '.xml', $xml);
                    if ($fileUploaded) {
                        $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . 'nfeCanceladas' . '/' . "${mes}-${ano}/" . $chave . '.xml';
                        return $url;
                    }
                } else {
                    //houve alguma falha no evento
                    throw new \Exception('Erro Ao Cancelar Nota!  Erro numero:', $std->cStat);
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    function getLogo()
    {
        $logo = '';
        if (Storage::disk('local')->exists("public/" . session('tenant')->nome . "/configuracoes/logo/logo.png")) {
            $logoPath = Storage::disk('local')->path("public/" . session('tenant')->nome . "/configuracoes/logo/logo.png");
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($logoPath)) ?? '';
        } else if (Storage::disk('local')->exists("public/" . session('tenant')->nome . "/configuracoes/logo/logo.jpg")) {

            $logoPath = Storage::disk('local')->path("public/" . session('tenant')->nome . "/configuracoes/logo/logo.jpg");
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($logoPath)) ?? '';
        } else {
            $logo = '';
        }
        return $logo;
    }

    function tirarAcentos($string)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(Ç)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N C c"), $string);
    }

    function sum($carry, $item)
    {
        $carry += $item['valorParcela'];
        return $carry;
    }

    function getCodigoMinicipio()
    {
        $uf = session('config')->estado;
        switch ($uf) {
            case 'AC':
                return 12;
                break;
            case 'AL':
                return 27;
                break;
            case 'AM':
                return 13;
                break;
            case 'AP':
                return 16;
                break;
            case 'BA':
                return 29;
                break;
            case 'CE':
                return 23;
                break;
            case 'DF':
                return 53;
                break;
            case 'ES':
                return 32;
                break;
            case 'GO':
                return 52;
                break;
            case 'MA':
                return 21;
                break;
            case 'MG':
                return 31;
                break;
            case 'MS':
                return 50;
                break;
            case 'MT':
                return 51;
                break;
            case 'PA':
                return 15;
                break;
            case 'PB':
                return 25;
                break;
            case 'PE':
                return 26;
                break;
            case 'PI':
                return 22;
                break;
            case 'PR':
                return 41;
                break;
            case 'RJ':
                return 33;
                break;
            case 'RN':
                return 24;
                break;
            case 'RO':
                return 11;
                break;
            case 'RR':
                return 14;
                break;
            case 'RS':
                return 43;
                break;
            case 'SC':
                return 42;
                break;
            case 'SE':
                return 28;
                break;
            case 'SP':
                return 35;
                break;
            case 'TO':
                return 17;
                break;
            default:
                return 35;
                break;
        }
    }
}
