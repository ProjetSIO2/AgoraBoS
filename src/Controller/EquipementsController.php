<?php
// src/Controller/EquipementsController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoJeux;

class EquipementsController extends AbstractController
{
    /**
     * fonction pour afficher la liste des equipements
     * @param $db
     * @param $idEquipementModif  positionné si demande de modification
     * @param $idEquipementNotif  positionné si mise à jour dans la vue
     * @param $notification  pour notifier la mise à jour dans la vue
     */
    private function afficherEquipements(PdoJeux $db, string $idEquipementModif, string $idEquipementNotif, string $notification) {
        $tbEquipements  = $db->getLesEquipements();
        return $this->render('lesEquipements.html.twig', array(
            'menuActif' => 'Jeux',
            'tbEquipements' => $tbEquipements,
            'idEquipementModif' => $idEquipementModif,
            'idEquipementNotif' => $idEquipementNotif,
            'notification' => $notification
        ));
    }

    /**
     * @Route("/equipements", name="equipements_afficher")
     */
    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherEquipements($db, '-1', '-1', 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * @Route("/equipements/ajouter", name="equipements_ajouter")
     */
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        if (!empty($request->request->get('txtIdEquipement')) && !empty($request->request->get('txtLibEquipement'))) {
            $idEquipementNotif = $db->ajouterEquipement($request->request->get('txtIdEquipement'), $request->request->get('txtLibEquipement'));
            $notification = 'Ajouté';
        }
        return $this->afficherEquipements($db, '-1',  $idEquipementNotif, $notification);
    }

    /**
     * @Route("/equipements/demanderModifier", name="equipements_demanderModifier")
     */
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherEquipements($db, $request->request->get('txtIdEquipement'),  '-1', 'rien');
    }

    /**
     * @Route("/equipements/validerModifier", name="equipements_validerModifier")
     */
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->modifierEquipement($request->request->get('txtIdEquipement'), $request->request->get('txtLibEquipement'));
        return $this->afficherEquipements($db, '-1',  $request->request->get('txtIdEquipement'), 'Modifié');
    }

    /**
     * @Route("/equipements/supprimer", name="equipements_supprimer")
     */
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->supprimerEquipement($request->request->get('txtIdEquipement'));
        $this->addFlash(
            'success', "L'équipement a été supprimé"
        );

        return $this->afficherEquipements($db, '-1',  '-1', 'rien');
    }
}
