<?php

declare(strict_types=1);

final class MiniPdf
{
    public const WIDTH = 595.28;
    public const HEIGHT = 841.89;

    private string $pageContent = '';
    private array $pages = [];
    private array $images = [];

    private string $font = 'F1';

    private float $fontSize = 10.0;

    private string $textColor = '0 0 0';

    public function addPage(): void
    {
        $this->flushPage();
        $this->font = 'F1';
        $this->fontSize = 10.0;
        $this->textColor = '0 0 0';
    }

    public function setFont(string $family, float $size): void
    {
        static $fonts = ['helv' => 'F1', 'helvb' => 'F2', 'cour' => 'F3'];
        $key = strtolower($family);
        if (!isset($fonts[$key])) {
            throw new InvalidArgumentException('Fonte nao suportada: ' . $family);
        }
        $this->font = $fonts[$key];
        $this->fontSize = $size;
    }

    public function setTextColor(float $r, float $g, float $b): void
    {
        $this->textColor = sprintf('%.3F %.3F %.3F', $r, $g, $b);
    }

    public function text(float $x, float $yFromTop, string $s): void
    {
        $s = self::toWinAnsi($s);
        $y = self::HEIGHT - $yFromTop;
        $this->pageContent .= sprintf(
            "BT /%s %.2F Tf %s rg %.2F %.2F Td (%s) Tj ET\n",
            $this->font,
            $this->fontSize,
            $this->textColor,
            $x,
            $y,
            self::escape($s)
        );
    }

    public function rect(
        float $x,
        float $yFromTop,
        float $w,
        float $h,
        bool $filled = false,
        array $rgb = [0.0, 0.0, 0.0],
        float $lineWidth = 0.6
    ): void {
        $y = self::HEIGHT - $yFromTop - $h;
        $color = sprintf('%.3F %.3F %.3F', $rgb[0], $rgb[1], $rgb[2]);
        if ($filled) {
            $this->pageContent .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", $color, $x, $y, $w, $h);
            return;
        }
        $this->pageContent .= sprintf(
            "%s RG %.2F w %.2F %.2F %.2F %.2F re S\n",
            $color,
            $lineWidth,
            $x,
            $y,
            $w,
            $h
        );
    }

    public function line(
        float $x1,
        float $y1FromTop,
        float $x2,
        float $y2FromTop,
        array $rgb = [0.0, 0.0, 0.0],
        float $lineWidth = 0.6
    ): void {
        $color = sprintf('%.3F %.3F %.3F', $rgb[0], $rgb[1], $rgb[2]);
        $this->pageContent .= sprintf(
            "%s RG %.2F w %.2F %.2F m %.2F %.2F l S\n",
            $color,
            $lineWidth,
            $x1,
            self::HEIGHT - $y1FromTop,
            $x2,
            self::HEIGHT - $y2FromTop
        );
    }

    public function image(
        float $x,
        float $yFromTop,
        float $w,
        float $h,
        string $pngFile,
        array $bg = [1.0, 1.0, 1.0]
    ): void {
        $dados = @file_get_contents($pngFile);
        if ($dados === false) {
            throw new RuntimeException('Imagem PNG nao encontrada: ' . $pngFile);
        }
        [$iw, $ih, $rgb] = self::pngParaRgb($dados, $bg);
        $nome = 'Im' . (count($this->images) + 1);
        $this->images[] = ['w' => $iw, 'h' => $ih, 'rgb' => $rgb];
        $this->pageContent .= sprintf(
            "q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n",
            $w,
            $h,
            $x,
            self::HEIGHT - $yFromTop - $h,
            $nome
        );
    }

    public function output(): string
    {
        $this->flushPage();
        if ($this->pages === []) {
            $this->pages[] = '';
        }

        $bodies = [];
        $bodies[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $bodies[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $bodies[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $bodies[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>';

        $kids = [];
        $n = 6;
        foreach ($this->pages as $content) {
            $pageObj = ++$n;
            $contentObj = ++$n;
            $kids[] = $pageObj . ' 0 R';
            $bodies[$contentObj] = '<< /Length ' . strlen($content) . " >>\nstream\n"
                . $content . 'endstream';
            $bodies[$pageObj] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources 6 0 R /Contents %d 0 R >>',
                self::WIDTH,
                self::HEIGHT,
                $contentObj
            );
        }

        $xobjects = [];
        foreach ($this->images as $indice => $imagem) {
            $num = ++$n;
            $nome = 'Im' . ($indice + 1);
            $xobjects[$nome] = $num;
            $compactado = gzcompress($imagem['rgb'], 9);
            if ($compactado === false) {
                throw new RuntimeException('Falha ao comprimir imagem do PDF.');
            }
            $bodies[$num] = sprintf(
                '<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB'
                . ' /BitsPerComponent 8 /Filter /FlateDecode /Interpolate true /Length %d >>' . "\nstream\n",
                $imagem['w'],
                $imagem['h'],
                strlen($compactado)
            ) . $compactado . 'endstream';
        }

        $recursos = '<< /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >>';
        if ($xobjects !== []) {
            $recursos .= ' /XObject <<';
            foreach ($xobjects as $nome => $num) {
                $recursos .= sprintf(' /%s %d 0 R', $nome, $num);
            }
            $recursos .= ' >>';
        }
        $recursos .= ' >>';
        $bodies[6] = $recursos;

        $bodies[2] = '<< /Type /Pages /Count ' . count($kids) . ' /Kids [' . implode(' ', $kids) . '] >>';

        ksort($bodies);

        $buf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($bodies as $num => $body) {
            $offsets[$num] = strlen($buf);
            $buf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefPos = strlen($buf);
        $size = count($bodies) + 1;
        $buf .= "xref\n0 " . $size . "\n";
        $buf .= "0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $buf .= sprintf("%010d 00000 n \n", $off);
        }
        $buf .= "trailer\n<< /Size " . $size . " /Root 1 0 R >>\n";
        $buf .= "startxref\n" . $xrefPos . "\n%%EOF\n";

        return $buf;
    }

    private function flushPage(): void
    {
        if ($this->pageContent === '') {
            return;
        }
        $this->pages[] = $this->pageContent;
        $this->pageContent = '';
    }

    private static function escape(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    /** @return array{0:int,1:int,2:string} largura, altura e RGB bruto */
    private static function pngParaRgb(string $png, array $bg): array
    {
        if (strncmp($png, "\x89PNG\r\n\x1a\n", 8) !== 0) {
            throw new RuntimeException('Arquivo nao e um PNG valido.');
        }
        $pos = 8;
        $tam = strlen($png);
        $iw = $ih = 0;
        $depth = $cor = $interlace = -1;
        $idat = '';
        while ($pos + 8 <= $tam) {
            $len = unpack('N', substr($png, $pos, 4))[1];
            $tipo = substr($png, $pos + 4, 4);
            $dados = substr($png, $pos + 8, $len);
            if ($tipo === 'IHDR') {
                $cab = unpack('Nw/Nh/Cdepth/Ccor/Ccomp/Cfilt/Cint', $dados);
                $iw = (int) $cab['w'];
                $ih = (int) $cab['h'];
                $depth = (int) $cab['depth'];
                $cor = (int) $cab['cor'];
                $interlace = (int) $cab['int'];
            } elseif ($tipo === 'IDAT') {
                $idat .= $dados;
            } elseif ($tipo === 'IEND') {
                break;
            }
            $pos += 8 + $len + 4;
        }
        if ($iw < 1 || $ih < 1 || $idat === '') {
            throw new RuntimeException('PNG incompleto ou corrompido.');
        }
        if ($depth !== 8 || ($cor !== 2 && $cor !== 6) || $interlace !== 0) {
            throw new RuntimeException(
                'Formato PNG nao suportado (esperado 8 bits, RGB/RGBA, sem entrelacamento).'
            );
        }
        $bruto = @gzuncompress($idat);
        if ($bruto === false) {
            throw new RuntimeException('Falha ao descompactar os dados do PNG.');
        }
        $bpp = $cor === 6 ? 4 : 3;
        $stride = $iw * $bpp;
        if (strlen($bruto) < ($stride + 1) * $ih) {
            throw new RuntimeException('Dados do PNG truncados.');
        }

        $r0 = (int) round($bg[0] * 255);
        $g0 = (int) round($bg[1] * 255);
        $b0 = (int) round($bg[2] * 255);

        $saida = '';
        $anterior = str_repeat("\x00", $stride);
        $p = 0;
        for ($y = 0; $y < $ih; $y++) {
            $filtro = ord($bruto[$p]);
            $linha = self::refiltrarLinha($filtro, substr($bruto, $p + 1, $stride), $anterior, $bpp);
            $p += $stride + 1;
            if ($cor === 6) {
                for ($i = 0; $i < $stride; $i += 4) {
                    $a = ord($linha[$i + 3]);
                    if ($a === 255) {
                        $saida .= $linha[$i] . $linha[$i + 1] . $linha[$i + 2];
                    } elseif ($a === 0) {
                        $saida .= chr($r0) . chr($g0) . chr($b0);
                    } else {
                        $saida .= chr(intdiv(ord($linha[$i]) * $a + $r0 * (255 - $a) + 127, 255))
                            . chr(intdiv(ord($linha[$i + 1]) * $a + $g0 * (255 - $a) + 127, 255))
                            . chr(intdiv(ord($linha[$i + 2]) * $a + $b0 * (255 - $a) + 127, 255));
                    }
                }
            } else {
                $saida .= $linha;
            }
            $anterior = $linha;
        }

        return [$iw, $ih, $saida];
    }

    private static function refiltrarLinha(int $filtro, string $linha, string $anterior, int $bpp): string
    {
        if ($filtro === 0) {
            return $linha;
        }
        if ($filtro < 1 || $filtro > 4) {
            throw new RuntimeException('Filtro de scanline PNG invalido: ' . $filtro);
        }
        $n = strlen($linha);
        for ($i = 0; $i < $n; $i++) {
            $x = ord($linha[$i]);
            $a = $i >= $bpp ? ord($linha[$i - $bpp]) : 0;
            $b = ord($anterior[$i]);
            $c = $i >= $bpp ? ord($anterior[$i - $bpp]) : 0;
            $v = match ($filtro) {
                1 => $x + $a,
                2 => $x + $b,
                3 => $x + (($a + $b) >> 1),
                default => $x + self::paeth($a, $b, $c),
            };
            $linha[$i] = chr($v & 0xFF);
        }
        return $linha;
    }

    private static function paeth(int $a, int $b, int $c): int
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);
        if ($pa <= $pb && $pa <= $pc) {
            return $a;
        }
        return $pb <= $pc ? $b : $c;
    }

    private static function toWinAnsi(string $s): string
    {
        if ($s === '' || !preg_match('/[\x80-\xFF]/', $s)) {
            return $s;
        }
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252', $s);
            if ($converted !== false) {
                return $converted;
            }
            $converted = @iconv('UTF-8', 'Windows-1252//IGNORE', $s);
            if ($converted !== false) {
                return $converted;
            }
        }
        $clean = preg_replace('/[^\x20-\x7E]/', '?', $s);
        return $clean === null || $clean === '' ? '?' : $clean;
    }
}
