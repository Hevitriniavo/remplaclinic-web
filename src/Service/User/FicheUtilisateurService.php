<?php
namespace App\Service\User;

use App\Entity\User;
use App\Service\Pdf\PdfEngine;
use Mpdf\Output\Destination;
use Twig\Environment;

final class FicheUtilisateurService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly PdfEngine $pdf
    )
    {}

    public function generate(User $user): void
    {
        $ficheUtilisateur = $this->twig->render('admin/user/replacement/fiche-pdf.html.twig', [
            'user' => $user
        ]);

        $mpdf = $this->pdf->getMPDF();
        $mpdf->WriteHTML($ficheUtilisateur);

        $mpdf->Output(sprintf('fiche-utilisateur-%d.pdf', $user->getId()), Destination::INLINE);
    }
}