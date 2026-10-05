<?php

declare(strict_types=1);

require_once __DIR__ . '/pdf.php';
require_once __DIR__ . '/lib/autoload.php';

const BANCO_CODIGO = '999';
const BANCO_NOME = 'Banco Demonstração S.A. (fictício)';
const BANCO_AGENCIA = '1234';
const BANCO_CONTA = '005678901';
const CARTEIRA = '175';
const CEDENTE_NOME = 'Cordeiro Energia Solar Ltda';
const CEDENTE_CNPJ = '00.000.000/0001-00';
const CEDENTE_ENDERECO = 'Av. Ceará, 1000 — Distrito Industrial — Montes Claros/MG';
const PIX_CHAVE = '123e4567-e89b-12d3-a456-426614174000';
const PIX_NOME = 'CORDEIRO ENERGIA SOLAR';
const PIX_CIDADE = 'MONTES CLAROS';

// Identidade visual do site (tailwind.config.mjs + Layout.astro)
const COR_ESCURA = [0.039, 0.039, 0.039];   // #0a0a0a
const COR_MARCA = [0.996, 0.361, 0.090];    // #FE5C17
const COR_MARCA_ESC = [0.878, 0.267, 0.0];  // #E04400 (hover)
const COR_AMBAR = [1.0, 0.757, 0.027];      // #FFC107 (acento de gradiente)
const COR_TITULO = [0.067, 0.067, 0.067];   // #111
const COR_CORPO = [0.216, 0.255, 0.318];    // #374151
const COR_MUDO = [0.42, 0.447, 0.502];      // #6b7280
const LOGO_ARQUIVO = __DIR__ . '/../logo.png';

function clientesFicticios(): array
{
    return [
        '12345678909' => [
            'nome' => 'Maria da Silva Santos',
            'documento' => '123.456.789-09',
            'endereco' => 'Rua das Acácias, 123 — Centro',
            'cidade' => 'Montes Claros/MG',
            'contrato' => '2026001',
            'descricao' => 'Fatura de energia solar — Outubro/2026',
            'valor' => 189.90,
            'vencimento' => '2026-10-10',
            'emissao' => '2026-09-30',
        ],
        '52998224725' => [
            'nome' => 'João Pereira Oliveira',
            'documento' => '529.982.247-25',
            'endereco' => 'Av. dos Ipês, 456 — São José',
            'cidade' => 'Juiz de Fora/MG',
            'contrato' => '2026002',
            'descricao' => 'Fatura de energia solar — Outubro/2026',
            'valor' => 459.00,
            'vencimento' => '2026-10-15',
            'emissao' => '2026-09-30',
        ],
    ];
}

function clientePorDocumento(string $somenteDigitos): ?array
{
    return clientesFicticios()[$somenteDigitos] ?? null;
}

function dvMod10(string $numero): string
{
    $soma = 0;
    $peso = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $soma += ((int) $numero[$i]) * $peso;
        $peso = $peso === 2 ? 1 : 2;
    }
    $resto = $soma % 10;
    return (string) ($resto === 0 ? 0 : 10 - $resto);
}

function dvMod11Geral(string $numero): string
{
    $soma = 0;
    $peso = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $soma += ((int) $numero[$i]) * $peso;
        $peso = $peso === 9 ? 2 : $peso + 1;
    }
    $dv = 11 - ($soma % 11);
    return (string) ($dv >= 10 ? 0 : $dv);
}

function crc16Ccitt(string $dados): string
{
    $crc = 0xFFFF;
    for ($i = 0, $n = strlen($dados); $i < $n; $i++) {
        $crc ^= ord($dados[$i]) << 8;
        for ($bit = 0; $bit < 8; $bit++) {
            $crc = ($crc & 0x8000) !== 0
                ? (($crc << 1) ^ 0x1021) & 0xFFFF
                : ($crc << 1) & 0xFFFF;
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

function pixCampo(string $id, string $valor): string
{
    return $id . str_pad((string) strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
}

function pixPayload(array $cliente): string
{
    $conta = pixCampo('00', 'BR.GOV.BCB.PIX') . pixCampo('01', PIX_CHAVE);

    $payload = pixCampo('00', '01')
        . pixCampo('26', $conta)
        . pixCampo('52', '0000')
        . pixCampo('53', '986')
        . pixCampo('54', number_format((float) $cliente['valor'], 2, '.', ''))
        . pixCampo('58', 'BR')
        . pixCampo('59', PIX_NOME)
        . pixCampo('60', PIX_CIDADE)
        . pixCampo('62', pixCampo('05', (string) $cliente['contrato']));

    $final = $payload . '6304';

    return $final . crc16Ccitt($final);
}

function matrizQr(string $dados): array
{
    $opcoes = new \chillerlan\QRCode\QROptions;
    $opcoes->eccLevel = \chillerlan\QRCode\Common\EccLevel::M;
    $qr = new \chillerlan\QRCode\QRCode($opcoes);
    $qr->addSegment(new \chillerlan\QRCode\Data\Byte($dados));

    return $qr->getQRMatrix()->getMatrix(true);
}

function fatorVencimento(string $data): string
{
    $dias = (int) round((strtotime($data) - strtotime('2025-02-22')) / 86400);
    return str_pad((string) (1000 + $dias), 4, '0', STR_PAD_LEFT);
}

function linhaDigitavel(string $barras): string
{
    $bancoMoeda = substr($barras, 0, 4);
    $dvGeral = $barras[4];
    $fatorValor = substr($barras, 5, 14);
    $campoLivre = substr($barras, 19);
    $c1 = $bancoMoeda . substr($campoLivre, 0, 5);
    $c2 = substr($campoLivre, 5, 10);
    $c3 = substr($campoLivre, 15, 10);

    return sprintf(
        '%s.%s%s %s.%s%s %s.%s%s %s %s',
        substr($c1, 0, 5),
        substr($c1, 5),
        dvMod10($c1),
        substr($c2, 0, 5),
        substr($c2, 5),
        dvMod10($c2),
        substr($c3, 0, 5),
        substr($c3, 5),
        dvMod10($c3),
        $dvGeral,
        $fatorValor
    );
}

function montarBoleto(array $cliente): array
{
    $nossoNumero = str_pad($cliente['contrato'], 9, '0', STR_PAD_LEFT);
    $campoLivre = BANCO_AGENCIA . BANCO_CONTA . $nossoNumero . CARTEIRA;
    $fator = fatorVencimento($cliente['vencimento']);
    $valorCentavos = str_pad((string) (int) round($cliente['valor'] * 100), 10, '0', STR_PAD_LEFT);
    $semDv = BANCO_CODIGO . '9' . $fator . $valorCentavos . $campoLivre;
    $dvGeral = dvMod11Geral($semDv);
    $barras = BANCO_CODIGO . '9' . $dvGeral . $fator . $valorCentavos . $campoLivre;

    return [
        'cliente' => $cliente,
        'barras' => $barras,
        'linha_digitavel' => linhaDigitavel($barras),
        'fator' => $fator,
        'valor_centavos' => $valorCentavos,
        'nosso_numero' => $nossoNumero . '-' . dvMod10($nossoNumero),
        'campo_livre' => $campoLivre,
        'dv_geral' => $dvGeral,
        'agencia_codigo' => BANCO_AGENCIA . ' / ' . BANCO_CONTA . '-' . dvMod10(BANCO_CONTA),
        'pix' => pixPayload($cliente),
    ];
}

function elementosCodigoBarrasItf(string $digitos): array
{
    static $padrao = [
        '0' => '00110',
        '1' => '10001',
        '2' => '01001',
        '3' => '11000',
        '4' => '00101',
        '5' => '10100',
        '6' => '01100',
        '7' => '00011',
        '8' => '10010',
        '9' => '01010',
    ];

    if (strlen($digitos) % 2 === 1) {
        $digitos = '0' . $digitos;
    }

    $elementos = [];
    foreach ([true, false, true, false] as $barra) {
        $elementos[] = [$barra, 1.0];
    }
    for ($i = 0, $n = strlen($digitos); $i < $n; $i += 2) {
        $barrasDig = $padrao[$digitos[$i]];
        $espacosDig = $padrao[$digitos[$i + 1]];
        for ($j = 0; $j < 5; $j++) {
            $elementos[] = [true, $barrasDig[$j] === '1' ? 2.5 : 1.0];
            $elementos[] = [false, $espacosDig[$j] === '1' ? 2.5 : 1.0];
        }
    }
    $elementos[] = [true, 2.5];
    $elementos[] = [false, 1.0];
    $elementos[] = [true, 1.0];

    return $elementos;
}

function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function dataBr(string $data): string
{
    return date('d/m/Y', strtotime($data));
}

function gerarPdfBoleto(array $boleto): string
{
    $pdf = new MiniPdf();
    $pdf->addPage();
    desenharCabecalho($pdf);
    desenharDadosBoleto($pdf, $boleto);
    desenharCodigoBarras($pdf, $boleto);
    desenharPix($pdf, $boleto);
    desenharRodape($pdf);

    return $pdf->output();
}

function desenharCabecalho(MiniPdf $pdf): void
{
    $pdf->rect(0, 0, MiniPdf::WIDTH, 66, true, [1.0, 1.0, 1.0]);

    $pdf->image(46, 14.5, 165, 33, LOGO_ARQUIVO);

    $pdf->setFont('helv', 9);
    $pdf->setTextColor(COR_MUDO[0], COR_MUDO[1], COR_MUDO[2]);
    $pdf->text(40, 62, 'Energia solar — segunda via de boleto');

    $pdf->setFont('helvb', 13);
    $pdf->setTextColor(COR_MARCA[0], COR_MARCA[1], COR_MARCA[2]);
    $pdf->text(415, 30, '2ª VIA');
    $pdf->setFont('helv', 8);
    $pdf->setTextColor(COR_ESCURA[0], COR_ESCURA[1], COR_ESCURA[2]);
    $pdf->text(415, 46, 'BOLETO DE COBRANÇA');

    desenharFaixaGradiente($pdf, 66, 22);

    $pdf->setFont('helvb', 9);
    $pdf->setTextColor(COR_ESCURA[0], COR_ESCURA[1], COR_ESCURA[2]);
    $pdf->text(40, 80, 'DOCUMENTO FICTÍCIO — EXPERIMENTO TÉCNICO — NÃO PODE SER PAGO');
}

function desenharFaixaGradiente(MiniPdf $pdf, float $y, float $h): void
{
    // Assinatura do site: gradiente #FE5C17 → #FFC107 → #FE5C17 (barra fixa do Header.astro)
    $fatias = 48;
    $largura = MiniPdf::WIDTH / $fatias;
    for ($i = 0; $i < $fatias; $i++) {
        $t = $i / ($fatias - 1);
        $cor = [];
        for ($c = 0; $c < 3; $c++) {
            $cor[$c] = $t < 0.5
                ? COR_MARCA[$c] + (COR_AMBAR[$c] - COR_MARCA[$c]) * ($t * 2)
                : COR_AMBAR[$c] + (COR_MARCA[$c] - COR_AMBAR[$c]) * (($t - 0.5) * 2);
        }
        $pdf->rect($i * $largura, $y, $largura + 0.4, $h, true, $cor);
    }
}

function desenharCaixa(
    MiniPdf $pdf,
    float $x,
    float $y,
    float $w,
    float $h,
    string $rotulo,
    string $valor,
    float $valorSize = 10.0
): void {
    $pdf->rect($x, $y, $w, $h, false, [0.1, 0.1, 0.1], 0.7);
    $pdf->setFont('helvb', 6.5);
    $pdf->setTextColor(COR_MARCA_ESC[0], COR_MARCA_ESC[1], COR_MARCA_ESC[2]);
    $pdf->text($x + 5, $y + 10, mb_strtoupper($rotulo, 'UTF-8'));
    $pdf->setFont('helvb', $valorSize);
    $pdf->setTextColor(COR_TITULO[0], COR_TITULO[1], COR_TITULO[2]);
    $pdf->text($x + 5, $y + 26, $valor);
}

function desenharDadosBoleto(MiniPdf $pdf, array $boleto): void
{
    $cliente = $boleto['cliente'];
    $m = 40.0;
    $largura = MiniPdf::WIDTH - 2 * $m;
    $terco = ($largura - 16) / 3;
    $quarto = ($largura - 24) / 4;

    $pdf->rect($m, 104, 70, 36, false, [0.1, 0.1, 0.1], 0.9);
    $pdf->setFont('helvb', 16);
    $pdf->setTextColor(COR_TITULO[0], COR_TITULO[1], COR_TITULO[2]);
    $pdf->text($m + 10, 130, BANCO_CODIGO . '-9');

    $pdf->rect($m + 78, 104, $largura - 78, 36, false, [0.1, 0.1, 0.1], 0.9);
    $pdf->setFont('helvb', 11);
    $pdf->text($m + 86, 119, BANCO_NOME);
    $pdf->setFont('helv', 7.5);
    $pdf->setTextColor(COR_MUDO[0], COR_MUDO[1], COR_MUDO[2]);
    $pdf->text($m + 86, 133, 'Linha digitável e código de barras na parte inferior do documento');

    desenharCaixa($pdf, $m, 148, $largura, 48, 'Beneficiário', CEDENTE_NOME, 11);
    $pdf->setFont('helv', 8.5);
    $pdf->setTextColor(COR_CORPO[0], COR_CORPO[1], COR_CORPO[2]);
    $pdf->text($m + 5, 186, 'CNPJ ' . CEDENTE_CNPJ . '  ·  ' . CEDENTE_ENDERECO);

    desenharCaixa($pdf, $m, 202, $terco, 46, 'Agência / Código do beneficiário', $boleto['agencia_codigo'], 10);
    desenharCaixa($pdf, $m + $terco + 8, 202, $terco, 46, 'Nosso número', $boleto['nosso_numero'], 10);
    desenharCaixa($pdf, $m + 2 * ($terco + 8), 202, $terco, 46, 'Carteira', CARTEIRA, 10);

    desenharCaixa($pdf, $m, 256, $largura, 56, 'Pagador', $cliente['nome'], 11);
    $pdf->setFont('helv', 8.5);
    $pdf->setTextColor(COR_CORPO[0], COR_CORPO[1], COR_CORPO[2]);
    $pdf->text($m + 5, 294, $cliente['documento'] . '  ·  ' . $cliente['endereco'] . ' — ' . $cliente['cidade']);

    desenharCaixa($pdf, $m, 320, $quarto, 46, 'Vencimento', dataBr($cliente['vencimento']), 11);
    desenharCaixa($pdf, $m + $quarto + 8, 320, $quarto, 46, 'Valor total', moeda((float) $cliente['valor']), 11);
    desenharCaixa($pdf, $m + 2 * ($quarto + 8), 320, $quarto, 46, 'Data de emissão', dataBr($cliente['emissao']));
    desenharCaixa($pdf, $m + 3 * ($quarto + 8), 320, $quarto, 46, 'Contrato', (string) $cliente['contrato']);

    $pdf->rect($m, 374, $largura, 84, false, [0.1, 0.1, 0.1], 0.7);
    $pdf->setFont('helvb', 6.5);
    $pdf->setTextColor(COR_MARCA_ESC[0], COR_MARCA_ESC[1], COR_MARCA_ESC[2]);
    $pdf->text($m + 5, 384, 'INSTRUÇÕES');
    $pdf->setFont('helv', 9);
    $pdf->setTextColor(COR_CORPO[0], COR_CORPO[1], COR_CORPO[2]);
    $linhas = [
        $cliente['descricao'],
        'Referente ao contrato nº ' . $cliente['contrato'] . ' — geração de energia fotovoltaica.',
        'Pagamento aceito somente em agência bancária ou internet banking (documento fictício).',
        'Este boleto foi gerado como demonstração técnica e não representa cobrança real.',
    ];
    $y = 402.0;
    foreach ($linhas as $linha) {
        $pdf->text($m + 5, $y, $linha);
        $y += 16;
    }
}

function desenharCodigoBarras(MiniPdf $pdf, array $boleto): void
{
    $m = 40.0;
    $largura = MiniPdf::WIDTH - 2 * $m;

    $pdf->line($m, 478, MiniPdf::WIDTH - $m, 478, [0.1, 0.1, 0.1], 0.7);
    $pdf->setFont('cour', 12);
    $pdf->setTextColor(0, 0, 0);
    $pdf->text($m, 496, $boleto['linha_digitavel']);
    $pdf->line($m, 506, MiniPdf::WIDTH - $m, 506, [0.1, 0.1, 0.1], 0.7);

    $pdf->setFont('helv', 6.5);
    $pdf->setTextColor(COR_MUDO[0], COR_MUDO[1], COR_MUDO[2]);
    $pdf->text($m, 674, 'Código de barras do documento (ITF)');

    $elementos = elementosCodigoBarrasItf($boleto['barras']);
    $modulos = 0.0;
    foreach ($elementos as [, $larguraModulos]) {
        $modulos += $larguraModulos;
    }
    $modulo = 337.0 / $modulos;
    $altura = 40.0;
    $x = (MiniPdf::WIDTH - $modulos * $modulo) / 2;
    $y = 686.0;
    foreach ($elementos as [$barra, $larguraModulos]) {
        $w = $larguraModulos * $modulo;
        if ($barra) {
            $pdf->rect($x, $y, $w, $altura, true, [0, 0, 0]);
        }
        $x += $w;
    }

    $pdf->setFont('helv', 7);
    $pdf->setTextColor(COR_MUDO[0], COR_MUDO[1], COR_MUDO[2]);
    $pdf->text($m, 738, 'Barras centralizadas com silêncio mínimo de 10 módulos em cada extremidade.');
}

function desenharPix(MiniPdf $pdf, array $boleto): void
{
    $cliente = $boleto['cliente'];
    $m = 40.0;
    $largura = MiniPdf::WIDTH - 2 * $m;
    $topo = 516.0;

    $pdf->rect($m, $topo, $largura, 144, false, [0.1, 0.1, 0.1], 0.7);
    $pdf->setFont('helvb', 6.5);
    $pdf->setTextColor(COR_MARCA_ESC[0], COR_MARCA_ESC[1], COR_MARCA_ESC[2]);
    $pdf->text($m + 5, $topo + 10, 'PAGAMENTO VIA PIX — DEMONSTRAÇÃO (CHAVE FICTÍCIA)');

    $tamanho = 108.0;
    $x = $m + 12;
    $y = $topo + 20;
    $matriz = matrizQr($boleto['pix']);
    $n = count($matriz);
    $modulo = $tamanho / $n;
    for ($linha = 0; $linha < $n; $linha++) {
        $coluna = 0;
        while ($coluna < $n) {
            if (!$matriz[$linha][$coluna]) {
                $coluna++;
                continue;
            }
            $inicio = $coluna;
            while ($coluna < $n && $matriz[$linha][$coluna]) {
                $coluna++;
            }
            $pdf->rect(
                $x + $inicio * $modulo,
                $y + $linha * $modulo,
                ($coluna - $inicio) * $modulo,
                $modulo,
                true,
                [0.0, 0.0, 0.0]
            );
        }
    }

    $coluna = $m + 136.0;
    $pdf->setFont('helvb', 10);
    $pdf->setTextColor(COR_TITULO[0], COR_TITULO[1], COR_TITULO[2]);
    $pdf->text($coluna, $topo + 34, 'Pague com PIX');
    $pdf->setFont('helv', 8.5);
    $pdf->setTextColor(COR_CORPO[0], COR_CORPO[1], COR_CORPO[2]);
    $pdf->text($coluna, $topo + 50, 'Chave (fictícia): ' . PIX_CHAVE);
    $pdf->text($coluna, $topo + 66, 'Valor: ' . moeda((float) $cliente['valor']) . '  ·  Identificador: ' . $cliente['contrato']);
    $pdf->setFont('helvb', 8);
    $pdf->setTextColor(COR_TITULO[0], COR_TITULO[1], COR_TITULO[2]);
    $pdf->text($coluna, $topo + 84, 'PIX Copia e Cola:');
    $pdf->setFont('cour', 6.5);
    $pdf->setTextColor(COR_CORPO[0], COR_CORPO[1], COR_CORPO[2]);
    $linhaY = $topo + 97;
    foreach (str_split($boleto['pix'], 94) as $pedaco) {
        $pdf->text($coluna, $linhaY, $pedaco);
        $linhaY += 11;
    }
    $pdf->setFont('helv', 7.5);
    $pdf->setTextColor(COR_MUDO[0], COR_MUDO[1], COR_MUDO[2]);
    $pdf->text($coluna, $topo + 132, 'Escaneie com o app do seu banco ou use o código acima.');
}

function desenharRodape(MiniPdf $pdf): void
{
    $m = 40.0;
    $pdf->line($m, 780, MiniPdf::WIDTH - $m, 780, [0.7, 0.7, 0.7], 0.5);
    $pdf->setFont('helv', 7.5);
    $pdf->setTextColor(COR_MUDO[0], COR_MUDO[1], COR_MUDO[2]);
    $pdf->text($m, 794, 'Documento fictício gerado por script PHP para experimentação — não válido para pagamento.');
    $pdf->text($m, 806, 'Gerado em ' . date('d/m/Y H:i') . ' — sem conexão com banco de dados.');
    $pdf->setFont('helvb', 7.5);
    $pdf->setTextColor(0.75, 0.1, 0.1);
    $pdf->text($m, 822, 'NÃO PAGAR');
}
