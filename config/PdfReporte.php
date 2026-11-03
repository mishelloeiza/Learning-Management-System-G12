<?php

class PdfReporte
{
    private array $paginas = [];
    private string $contenido = '';
    private float $y = 0;
    private int $pagina = 0;
    private array $encabezadosActuales = [];
    private array $anchosActuales = [];
    private const ANCHO = 1190.55;
    private const ALTO = 841.89;
    private const MARGEN = 38;

    public function __construct(private string $rol)
    {
        $this->nuevaPagina();
    }

    private function textoPdf(string $texto): string
    {
        $texto = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $texto);
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $texto === false ? '' : $texto);
    }

    private function rectangulo(float $x, float $y, float $ancho, float $alto, string $color): void
    {
        $this->contenido .= "$color rg " . sprintf('%.2F %.2F %.2F %.2F re f', $x, self::ALTO - $y - $alto, $ancho, $alto) . "\n";
    }

    private function texto(float $x, float $y, string $texto, float $tamano = 9, bool $negrita = false, string $color = '0.16 0.20 0.28'): void
    {
        $fuente = $negrita ? 'F2' : 'F1';
        $this->contenido .= "$color rg BT /$fuente " . sprintf('%.2F', $tamano) . ' Tf 1 0 0 1 ' . sprintf('%.2F %.2F', $x, self::ALTO - $y) . ' Tm (' . $this->textoPdf($texto) . ") Tj ET\n";
    }

    private function nuevaPagina(): void
    {
        if ($this->pagina > 0) {
            $this->paginas[] = $this->contenido;
        }
        $this->pagina++;
        $this->contenido = '';
        $this->rectangulo(0, 0, self::ANCHO, 58, '0.09 0.18 0.31');
        $this->texto(self::MARGEN, 27, 'CAMPUS VIRTUAL G12', 15, true, '1 1 1');
        $this->texto(self::MARGEN, 44, 'Reporte general - ' . $this->rol, 10, false, '0.85 0.90 0.97');
        $this->texto(self::ANCHO - 245, 34, date('d/m/Y H:i'), 10, false, '1 1 1');
        $this->y = 80;
    }

    private function asegurarEspacio(float $alto): void
    {
        if ($this->y + $alto > self::ALTO - 52) {
            $this->nuevaPagina();
        }
    }

    private function cortar(string $valor, int $maxCaracteres): array
    {
        $valor = trim(preg_replace('/\s+/u', ' ', $valor) ?? $valor);
        if ($valor === '') {
            return ['—'];
        }
        $lineas = [];
        $linea = '';
        foreach (preg_split('/\s+/u', $valor) ?: [] as $palabra) {
            $trozos = preg_split('//u', $palabra, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            while (count($trozos) > $maxCaracteres) {
                if ($linea !== '') {
                    $lineas[] = $linea;
                    $linea = '';
                }
                $lineas[] = implode('', array_splice($trozos, 0, $maxCaracteres));
            }
            $palabra = implode('', $trozos);
            if ($palabra === '') {
                continue;
            }
            $candidata = $linea === '' ? $palabra : $linea . ' ' . $palabra;
            if (count(preg_split('//u', $candidata, -1, PREG_SPLIT_NO_EMPTY) ?: []) > $maxCaracteres && $linea !== '') {
                $lineas[] = $linea;
                $linea = $palabra;
            } else {
                $linea = $candidata;
            }
        }
        if ($linea !== '') {
            $lineas[] = $linea;
        }
        return $lineas ?: ['—'];
    }

    private function anchos(array $encabezados, array $filas): array
    {
        $pesos = [];
        foreach ($encabezados as $indice => $encabezado) {
            $largo = min(34, max(10, count(preg_split('//u', (string) $encabezado, -1, PREG_SPLIT_NO_EMPTY) ?: [])));
            foreach (array_slice($filas, 0, 100) as $fila) {
                $largo = max($largo, min(34, count(preg_split('//u', (string) ($fila[$indice] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [])));
            }
            $pesos[] = $largo;
        }
        $disponible = self::ANCHO - 2 * self::MARGEN;
        $suma = array_sum($pesos);
        return array_map(static fn ($peso) => $disponible * $peso / $suma, $pesos);
    }

    private function fila(array $valores, array $anchos, bool $encabezado, bool $alterno): void
    {
        $lineas = [];
        foreach ($valores as $indice => $valor) {
            $lineas[] = $this->cortar((string) ($valor ?? ''), max(5, (int) floor(($anchos[$indice] - 12) / ($encabezado ? 4.2 : 3.95))));
        }
        $alto = max(23, 10 * max(array_map('count', $lineas)) + 10);
        if ($this->y + $alto > self::ALTO - 52) {
            $this->nuevaPagina();
            if (!$encabezado && $this->encabezadosActuales) {
                $this->fila($this->encabezadosActuales, $this->anchosActuales, true, false);
            }
        }
        if ($encabezado) {
            $this->rectangulo(self::MARGEN, $this->y, array_sum($anchos), $alto, '0.19 0.32 0.48');
        } elseif ($alterno) {
            $this->rectangulo(self::MARGEN, $this->y, array_sum($anchos), $alto, '0.94 0.96 0.98');
        }
        $x = self::MARGEN;
        foreach ($lineas as $indice => $contenido) {
            foreach ($contenido as $numero => $linea) {
                $this->texto($x + 6, $this->y + 14 + 10 * $numero, $linea, $encabezado ? 8 : 7.5, $encabezado, $encabezado ? '1 1 1' : '0.17 0.21 0.27');
            }
            $x += $anchos[$indice];
        }
        $this->y += $alto;
    }

    public function agregarSeccion(string $titulo, array $encabezados, array $filas): void
    {
        $this->asegurarEspacio(75);
        $this->rectangulo(self::MARGEN, $this->y, 5, 24, '0.12 0.42 0.69');
        $this->texto(self::MARGEN + 13, $this->y + 17, $titulo, 13, true);
        $this->texto(self::ANCHO - 195, $this->y + 17, 'Registros: ' . count($filas), 9);
        $this->y += 33;
        if (!$filas) {
            $this->texto(self::MARGEN + 8, $this->y + 12, 'No hay registros para esta sección.', 9);
            $this->y += 33;
            return;
        }
        $anchos = $this->anchos($encabezados, $filas);
        $this->encabezadosActuales = $encabezados;
        $this->anchosActuales = $anchos;
        $this->fila($encabezados, $anchos, true, false);
        foreach ($filas as $indice => $fila) {
            $this->fila($fila, $anchos, false, $indice % 2 === 1);
        }
        $this->encabezadosActuales = [];
        $this->anchosActuales = [];
        $this->y += 22;
    }

    public function descargar(string $nombre): void
    {
        $this->paginas[] = $this->contenido;
        $objetos = [];
        $agregar = static function (string $objeto) use (&$objetos): int {
            $objetos[] = $objeto;
            return count($objetos);
        };
        $catalogo = $agregar('');
        $paginasId = $agregar('');
        $regular = $agregar('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>');
        $negrita = $agregar('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>');
        $ids = [];
        foreach ($this->paginas as $numero => $contenido) {
            $contenido .= '0.55 0.60 0.65 rg BT /F1 8 Tf 1 0 0 1 ' . self::MARGEN . ' 25 Tm (Campus Virtual G12) Tj ET' . "\n";
            $contenido .= '0.55 0.60 0.65 rg BT /F1 8 Tf 1 0 0 1 ' . (self::ANCHO - 115) . ' 25 Tm (Pagina ' . ($numero + 1) . ' de ' . count($this->paginas) . ') Tj ET' . "\n";
            $flujo = $agregar('<< /Length ' . strlen($contenido) . " >>\nstream\n" . $contenido . "endstream");
            $ids[] = $agregar('<< /Type /Page /Parent ' . $paginasId . ' 0 R /MediaBox [0 0 ' . self::ANCHO . ' ' . self::ALTO . '] /Resources << /Font << /F1 ' . $regular . ' 0 R /F2 ' . $negrita . ' 0 R >> >> /Contents ' . $flujo . ' 0 R >>');
        }
        $objetos[$catalogo - 1] = '<< /Type /Catalog /Pages ' . $paginasId . ' 0 R >>';
        $objetos[$paginasId - 1] = '<< /Type /Pages /Kids [' . implode(' ', array_map(static fn ($id) => $id . ' 0 R', $ids)) . '] /Count ' . count($ids) . ' >>';
        $pdf = "%PDF-1.4\n";
        $posiciones = [0];
        foreach ($objetos as $indice => $objeto) {
            $posiciones[] = strlen($pdf);
            $pdf .= ($indice + 1) . " 0 obj\n" . $objeto . "\nendobj\n";
        }
        $inicioXref = strlen($pdf);
        $pdf .= 'xref' . "\n0 " . (count($objetos) + 1) . "\n0000000000 65535 f \n";
        foreach (array_slice($posiciones, 1) as $posicion) {
            $pdf .= sprintf('%010d 00000 n ', $posicion) . "\n";
        }
        $pdf .= 'trailer << /Size ' . (count($objetos) + 1) . ' /Root ' . $catalogo . " 0 R >>\nstartxref\n" . $inicioXref . "\n%%EOF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nombre . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: no-store');
        echo $pdf;
    }
}
