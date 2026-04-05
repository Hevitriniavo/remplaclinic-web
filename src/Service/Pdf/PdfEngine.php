<?php
namespace App\Service\Pdf;

use Mpdf\Mpdf;

class PdfEngine
{

    public function __construct(
        private string $documentFolder
    )
    {}

    public function getMPDF(): Mpdf
    {
        return new Mpdf([
            'tempDir' => $this->documentFolder,
        ]);
    }
}