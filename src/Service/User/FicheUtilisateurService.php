<?php
namespace App\Service\User;

use App\Entity\User;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Twig\Environment;

final class FicheUtilisateurService
{
    public function __construct(
        private readonly Environment $twig
    )
    {}

    public function generate(User $user): void
    {
        $ficheUtilisateur = $this->twig->render('admin/user/replacement/fiche-pdf.html.twig', [
            'user' => $user
        ]);

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($ficheUtilisateur);

        $mpdf->Output(sprintf('fiche-utilisateur-%d.pdf', $user->getId()), Destination::INLINE);
    }
}