<?php

namespace Tests\Support;

trait LibraryPdf
{
    private function pdf(string $text = 'Hello library'): string
    {
        $stream = "BT /F1 12 Tf 50 750 Td ($text) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream).' >>
stream
'.$stream.'
endstream',
        ];
        $pdf = '%PDF-1.4
';
        $offsets = [];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1).' 0 obj
'.$object.'
endobj
';
        }
        $xref = strlen($pdf);
        $pdf .= 'xref
0 6
0000000000 65535 f 
';
        foreach ($offsets as $offset) {
            $pdf .= sprintf('%010d 00000 n 
', $offset);
        }

        return $pdf.'trailer
<< /Size 6 /Root 1 0 R >>
startxref
'.$xref.'
%%EOF';
    }
}
