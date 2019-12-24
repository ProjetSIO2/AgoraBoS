<?php
// src/Controller/PlateformesController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoJeux;

class PlateformesController extends AbstractController
{
    /**
     * fonction pour afficher la liste des plateformes
     * @param $db
     * @param $idPlateformeModif  positionné si demande de modification
     * @param $idPlateformeNotif  positionné si mise à jour dans la vue
     * @param $notification  pour notifier la mise à jour dans la vue
     */
    private function afficherPlateformes(PdoJeux $db, int $idPlateformeModif, int $idPlateformeNotif, string $notification ) {
        $tbPlateformes  = $db->getLesPlateformes();
        return $this->render('lesPlateformes.html.twig', array(
            'menuActif' => 'Jeux',
            'tbPlateformes' => $tbPlateformes,
            'idPlateformeModif' => $idPlateformeModif,
            'idPlateformeNotif' => $idPlateformeNotif,
            'notification' => $notification
        ));
    }

    /**
     * @Route("/plateformes", name="plateformes_afficher")
     */
    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherPlateformes($db, -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * @Route("/plateformes/ajouter", name="plateformes_ajouter")
     */
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        if (!empty($request->request->get('txtLibPlateforme')) && !empty($request->request->get('txtNbPlateformesDispo'))) {
            $idPlateformeNotif = $db->ajouterPlateforme($request->request->get('txtLibPlateforme'), $request->request->get('txtNbPlateformesDispo'));
            $notification = 'Ajouté';
        }
        return $this->afficherPlateformes($db, -1,  $idPlateformeNotif, $notification);
    }

    /**
     * @Route("/plateformes/demanderModifier", name="plateformes_demanderModifier")
     */
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherPlateformes($db, $request->request->get('txtIdPlateforme'),  -1, 'rien');
    }

    /**
     * @Route("/plateformes/validerModifier", name="plateformes_validerModifier")
     */
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->modifierPlateforme($request->request->get('txtIdPlateforme'), $request->request->get('txtLibPlateforme'), $request->request->get('txtNbPlateformesDispo'));
        return $this->afficherPlateformes($db, -1,  $request->request->get('txtIdPlateforme'), 'Modifié');
    }

    /**
     * @Route("/plateformes/supprimer", name="plateformes_supprimer")
     */
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->supprimerPlateforme($request->request->get('txtIdPlateforme'));
        $this->addFlash(
            'success', 'La plateforme a été supprimée'
        );

        return $this->afficherPlateformes($db, -1,  -1, 'rien');
    }
}
