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
    private $config;

    private $tools;

    public function __construct($config)
    {
        // Ensure that there is an active session and configuration
        $tenant = session('tenant');
        $activeConfig = session('config');

        if (!$tenant || !$activeConfig) {
            throw new \Exception('Sessão ou configuração ativa não encontrada');
        }

        // Build the certificate file path based on the active tenant's name and configuration
        $filePath = 'public/' . $tenant->nome . '/configuracoes/' . $activeConfig->id . '/certificadoDigital/certificado-digital.x-pkcs12';

        logger("filepath", [$filePath]);

        // Check if the certificate file exists
        if (Storage::disk('local')->exists($filePath)) {
            $path = Storage::disk('local')->path($filePath);
            $certificadoDigital = file_get_contents($path);
        } else {
            throw new \Exception('Certificado digital não encontrado');
        }

        // Set the configuration
        $this->config = $config;

        try {
            // Initialize the Tools object with the configuration and certificate
            $this->tools = new Tools(
                json_encode($config),
                Certificate::readPfx($certificadoDigital, $activeConfig->senhaCertificadoDigital)
            );
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        // Initialize the SoapCurl object
        $this->soap = new SoapCurl(Certificate::readPfx($certificadoDigital, $activeConfig->senhaCertificadoDigital));
        $this->soap->timeout(600); // 10 minutos de timeout
        $this->soap->httpVersion('1.1'); // Set HTTP protocol version to 1.1

        // Inject the SoapCurl instance into Tools
        $this->tools->loadSoapClass($this->soap);
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
        if (array_key_exists("refNFe", $dados) && $dados['refNFe']) {
            $nfeRef = new stdClass();
            $nfeRef->refNFe = $dados['refNFe'];
            $nfe->tagrefNFe($nfeRef);
        }

        $nfeRef = new stdClass();
        // $nfeRef->refNFe = $dados['refNFe'];
        // $nfeRef->refNFe = "35221109136351000107550010000028741885337470";
        // $nfe->tagrefNFe($nfeRef);



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

        //====================TAG PRODUTO===================
        // Armazena o total dos produtos para calculo correto do ICMS
        $valorProdutosReal = 0.0;
        $totalIPI = 0.00;
        $totalICMS = 0.00;
        $totalProdutosCobrados = 0.00;
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

            // Não conta o cfop 5902 para calculo de ICSM
            if ($dados['produtos'][$i]['cfop'] != '5902') {
                $valorProdutosReal += $dados['produtos'][$i]['total'];
            }

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

            $valorIPI = 0.0;
            if (session('config')->crt != 1 && !in_array($dados['produtos'][$i]['cfop'], ['5902', '6912', '6910', '5124', '5901', '5916', '5949'])) {
                $aliquotaIPI = 9.75;
                $valorIPI = $dados['produtos'][$i]['total'] * ($aliquotaIPI / 100);
            }


            if (session('config')->crt != 1) {
                //====================TAG ICMS REGIME NORMAL===================
                if (in_array($dados['produtos'][$i]['cfop'], ['5902', '5102', '6102', '5124', '5901', '5916', '5556', '5949'])) {
                    $icms = new stdClass();
                    $icms->item = $i + 1; //item da NFe
                    $icms->orig = 0; // Origem da mercadoria (0 = Nacional, 1 = Estrangeira, etc.)
                    $icms->CST = '50'; // Código da Situação Tributária do ICMS (50 = Isenta ou não tributada e com cobrança do ICMS por substituição tributária)
                    $icms->modBC = 3; // Modalidade de determinação da BC (0 = Valor da Operação)
                    // $icms->vBC = $dados['produtos'][$i]['total']; // Base de Cálculo do ICMS
                    // $icms->pICMS = $aliquota; // Alíquota do ICMS (%)
                    // $icms->vICMS = $icms->vBC * ($icms->pICMS / 100); // Valor do ICMS
                } else {
                    $icms = new stdClass();
                    $icms->item = $i + 1; //item da NFe
                    $icms->orig = 0; // Origem da mercadoria (0 = Nacional, 1 = Estrangeira, etc.)
                    $icms->CST = '00'; // Código da Situação Tributária do ICMS (00 = Tributado integralmente)
                    $icms->modBC = 3; // Modalidade de determinação da BC (0 = Valor da Operação)
                    $icms->vBC = $dados['produtos'][$i]['total'];                 // COMENTAR SE FOR PARA USO E CONSUMO
                    // $icms->vBC = $dados['produtos'][$i]['total'] + $valorIPI; // DESCOMENTAR SE FOR PARA USO E CONSUMO
                    $icms->pICMS = strtolower($favorecido->estado) == "sp" ? $aliquota : $this->getAliquotaByEstado($favorecido->estado); // Alíquota do ICMS (%)
                    $icms->vICMS = $icms->vBC * ($icms->pICMS / 100); // Valor do ICMS
                    $totalICMS += $icms->vICMS;
                    $totalProdutosCobrados += $dados['produtos'][$i]['total'];                 // COMENTAR SE FOR PARA USO E CONSUMO
                    // $totalProdutosCobrados += $dados['produtos'][$i]['total'] + $valorIPI; // DESCOMENTAR SE FOR PARA USO E CONSUMO
                }

                // Adiciona ao XML
                $nfe->tagICMS($icms);
            } else {
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
                    if (strlen($favorecido['cpfCnpj']) == 14) {
                        if ($favorecido['inscricaoEstadual']) {
                            $icms->CSOSN = '101';
                        } else {
                            $icms->CSOSN = '400';
                        }
                    } else {
                        $icms->CSOSN = '102';
                    }
                    $icms->pCredSN = $aliquota;
                    $icms->vCredICMSSN = $valorProdutosReal * ($aliquota / 100);
                } else if (
                    $dados['produtos'][$i]['cfop'] == '5902' ||
                    $dados['produtos'][$i]['cfop'] == '6912' ||
                    $dados['produtos'][$i]['cfop'] == '6910'
                ) {
                    $icms->CSOSN = '400';
                    $icms->pCredSN = $aliquota;
                    $icms->vCredICMSSN = $valorProdutosReal * ($aliquota / 100);
                } else {
                    $icms->CSOSN = '900';
                    $icms->pCredSN = $aliquota;
                    $icms->vCredICMSSN = $valorProdutosReal * ($aliquota / 100);
                }
                if (session('config')->crt != 1) {
                    $icms->CSOSN = null;
                }
                //$icms->modBCST = null;
                //$icms->pMVAST = null;
                //$icms->pRedBCST = null;
                //$icms->vBCST = null;
                //$icms->pICMSST = null;
                //$icms->vICMSST = null;
                //$icms->vBCFCPST = null; //incluso no layout 4.00
                //$icms->pFCPST = null; //incluso no layout 4.00
                //$icms->vFCPST = null; //incluso no layout 4.00
                //$icms->vBCSTRet = null;
                //$icms->pST = null;
                //$icms->vICMSSTRet = null;
                //$icms->vBCFCPSTRet = null; //incluso no layout 4.00
                //$icms->pFCPSTRet = null; //incluso no layout 4.00
                //$icms->vFCPSTRet = null; //incluso no layout 4.00
                //$icms->modBC = null;
                //$icms->vBC = null;
                //$icms->pRedBC = null;
                //$icms->pICMS = null;
                //$icms->vICMS = 480.21; // change COMENTAR A LINHA OU NULL
                //$icms->pRedBCEfet = null;
                //$icms->vBCEfet = null;
                //$icms->pICMSEfet = null;
                //$icms->vICMSEfet = null;
                //$icms->vICMSSubstituto = null;

                $nfe->tagICMSSN($icms);
            }

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

            if (session('config')->crt != 1) {
                logger("GERANDO IPI");
                logger($i, $dados['produtos'][$i]);
                logger($i, [$dados['produtos'][$i]['cfop']]);
                if (!in_array($dados['produtos'][$i]['cfop'], ['5902', '6912', '6910', '5124', '5901', '5916', '5556', '5949'])) {
                    $aliquotaIPI = 9.75;
                    //====================TAG IPI===================
                    $ipi = new stdClass();
                    $ipi->item =  $i + 1; //item da NFe
                    $ipi->clEnq = null;
                    $ipi->CNPJProd = null;
                    $ipi->cSelo = null;
                    $ipi->qSelo = null;
                    $ipi->cEnq = '999'; // Usar 113 para Saída com Suspensão
                    $ipi->CST = 50; // CST 55 - Saída com Suspensão
                    $ipi->vBC = $dados['produtos'][$i]['total'];
                    $ipi->pIPI = $aliquotaIPI;
                    $ipi->vIPI = $ipi->vBC * ($aliquotaIPI / 100);
                    $ipi->qUnid = null;
                    $ipi->vUnid = null;

                    $nfe->tagIPI($ipi);
                    $totalIPI += $ipi->vIPI;
                }
            }
        }

        //====================TAG ICMSTOTAL===================
        $icmsTotal = new stdClass();
        if (session('config')->crt == 3) {
            $icmsTotal->vBC = $totalProdutosCobrados;
            $icmsTotal->vICMS = $totalICMS; //change 480.21
        } else {
            $icmsTotal->vBC = 0.00;
            $icmsTotal->vICMS = 0.00;
        }
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
        $icmsTotal->vIPI = $totalIPI; //change 133.39
        $icmsTotal->vIPIDevol = 0.00; //incluso no layout 4.00
        $icmsTotal->vPIS = 0.00;
        $icmsTotal->vCOFINS = 0.00;
        $icmsTotal->vOutro = 0.00; // change to 0.00
        $icmsTotal->vNF = $dados['totalFinal'] + $icmsTotal->vIPI; // total produtos + frete
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

        if ($dados['parcelasManual'] == 1) {
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
            }
        } else {
            if (count($dados['parcelas']) >= 1) {
                // Utiliza o valor de vNF já calculado (vNF = totalFinal + vIPI)
                $vNF = $icmsTotal->vNF;

                //====================TAG FATURA===================
                $fat = new stdClass();
                $fat->nFat = $ide->nNF;
                // Define o valor original da fatura igual a vNF
                $fat->vOrig = number_format($vNF, 2, '.', '');
                // Se houver desconto já aplicado em totalFinal, certifique-se de que este valor esteja refletido em vNF.
                // Aqui, estamos considerando que o desconto já está embutido em totalFinal, então setamos vDesc = 0.
                $fat->vDesc = '0.00';
                $fat->vLiq = number_format($vNF, 2, '.', '');
                $nfe->tagfat($fat);

                //====================TAG DUPLICATA===================
                $numParcelas = count($dados['parcelas']);
                // Calcula o valor de cada parcela (arredondado para 2 casas decimais)
                $valorParcela = floor(($vNF / $numParcelas) * 100) / 100;
                $somaParcelas = $valorParcela * $numParcelas;
                // Calcula a diferença para ajustar a última parcela
                $diferenca = round($vNF - $somaParcelas, 2);

                for ($i = 0; $i < $numParcelas; $i++) {
                    $dup = new stdClass();
                    $dup->nDup = str_pad($i + 1, 3, "0", STR_PAD_LEFT);
                    $date = DateTime::createFromFormat('d/m/Y', $dados['parcelas'][$i]['dataVencimento']);
                    $dup->dVenc = $date->format('Y-m-d');
                    // Se for a última parcela, adiciona a diferença de arredondamento
                    if ($i == $numParcelas - 1) {
                        $dup->vDup = number_format($valorParcela + $diferenca, 2, '.', '');
                    } else {
                        $dup->vDup = number_format($valorParcela, 2, '.', '');
                    }
                    $nfe->tagdup($dup);
                }
            }
        }



        //====================TAG PAGAMENTO===================
        $pag = new stdClass();
        //$std->vTroco = null; //incluso no layout 4.00, obrigatório informar para NFCe (65)

        $nfe->tagpag($pag);

        //====================TAG DETALHE PAGAMENTO===================
        $totalFinalFormaPag = 0;
        if ($dados['parcelasManual'] == 1) {
            if (count($dados['parcelas']) >= 1) {
                $tipoFormaPag = '01';
                $totalFinalFormaPag = $dados['totalFinal'];
            } else {
                $tipoFormaPag = '90';
                $totalFinalFormaPag = 0;
            }
        } else {
            if (count($dados['parcelas']) >= 1) {
                $tipoFormaPag = '01';
                // Usa o vNF para garantir que o total do pagamento seja igual ao valor da NF-e
                $totalFinalFormaPag = $icmsTotal->vNF;
            } else {
                $tipoFormaPag = '90';
                $totalFinalFormaPag = 0;
            }
        }
        $detPag = new stdClass();
        $detPag->tPag = $tipoFormaPag;
        $detPag->vPag = number_format($totalFinalFormaPag, 2, '.', '');
        //$detPag->CNPJ = '12345678901234';
        //$detPag->tBand = '01';
        //$detPag->cAut = '3333333';
        //$detPag->tpIntegra = 1; //incluso na NT 2015/002
        //$detPag->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo

        $nfe->tagdetPag($detPag);

        //====================INFO ADICIONAL===================
        $stdInfo = new stdClass();
        // Verifica se vCredICMSSN é nulo e utiliza outro valor apropriado
        $vCredICMSSN = $icms->vCredICMSSN ?? $icms->vICMS;

        // Define a informação adicional de acordo com a nova lógica
        if (array_key_exists("infAdFisco", $dados)) {
            if (session('config')->crt != 1) {
                $stdInfo->infAdFisco = $dados['infAdFisco'] . " --- DOCUMENTO EMITIDO POR EMPRESA REGIME NORMAL. ";
            } else {
                $stdInfo->infAdFisco = $dados['infAdFisco'] .
                    " --- DOCUMENTO EMITIDO POR EMPRESA SIMPLES NACIONAL. " .
                    "NAO GERA DIREITO A CREDITO FISCAL DE IPI. " .
                    "PERMITE O APROVEITAMENTO DO CREDITO DE ICMS NO VALOR DE R$ " .
                    number_format($vCredICMSSN, 2, ',', '.') .
                    ", CORRESPONDENTE A ALIQUOTA DE " .
                    number_format($aliquota, 2, ',', '.') . "%.";
            }
        } else {
            if (session('config')->crt != 1) {
                $stdInfo->infAdFisco = " --- DOCUMENTO EMITIDO POR EMPRESA REGIME NORMAL. ";
            } else {
                $stdInfo->infAdFisco =
                    " --- DOCUMENTO EMITIDO POR EMPRESA SIMPLES NACIONAL. " .
                    "NAO GERA DIREITO A CREDITO FISCAL DE IPI. " .
                    "PERMITE O APROVEITAMENTO DO CREDITO DE ICMS NO VALOR DE R$ " .
                    number_format($vCredICMSSN, 2, ',', '.') .
                    ", CORRESPONDENTE A ALIQUOTA DE " .
                    number_format($aliquota, 2, ',', '.') . "%.";
            }
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
            Log::info(['$std' => $std]);

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

    function getAliquotaByEstado($estadoFavorecido)
    {
        $uf = strtoupper($estadoFavorecido);
        switch ($uf) {
            case 'AC':
                return 7;
                break;
            case 'AL':
                return 7;
                break;
            case 'AM':
                return 7;
                break;
            case 'AP':
                return 7;
                break;
            case 'BA':
                return 7;
                break;
            case 'CE':
                return 7;
                break;
            case 'DF':
                return 7;
                break;
            case 'ES':
                return 7;
                break;
            case 'GO':
                return 7;
                break;
            case 'MA':
                return 7;
                break;
            case 'MG':
                return 12;
                break;
            case 'MS':
                return 7;
                break;
            case 'MT':
                return 7;
                break;
            case 'PA':
                return 7;
                break;
            case 'PB':
                return 7;
                break;
            case 'PE':
                return 7;
                break;
            case 'PI':
                return 7;
                break;
            case 'PR':
                return 12;
                break;
            case 'RJ':
                return 12;
                break;
            case 'RN':
                return 7;
                break;
            case 'RO':
                return 7;
                break;
            case 'RR':
                return 7;
                break;
            case 'RS':
                return 12;
                break;
            case 'SC':
                return 12;
                break;
            case 'SE':
                return 7;
                break;
            case 'SP':
                return 18;
                break;
            case 'TO':
                return 7;
                break;
            default:
                return 7;
                break;
        }
    }
}
