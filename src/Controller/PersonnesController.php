<?php
// src/Controller/PersonnesController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoJeux;

class PersonnesController extends AbstractController
{
    /**
     * fonction pour afficher la liste des personnes
     * @param $db
     * @param $idPersonneModif  positionné si demande de modification
     * @param $idPersonneNotif  positionné si mise à jour dans la vue
     * @param $notification  pour notifier la mise à jour dans la vue
     */
    private function afficherPersonnes(PdoJeux $db, int $idPersonneModif, int $idPersonneNotif, string $notification ) {
        $tbPersonnes = $db->getLesPersonnes();
        return $this->render('lesPersonnes.html.twig', array(
            'menuActif' => 'Jeux',
            'tbPersonnes' => $tbPersonnes,
            'idPersonneModif' => $idPersonneModif,
            'idPersonneNotif' => $idPersonneNotif,
            'notification' => $notification
        ));
    }

    /**
     * @Route("/personnes", name="personnes_afficher")
     */
    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherPersonnes($db, -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * @Route("/personnes/ajouter", name="personnes_ajouter")
     */
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        if (!empty($request->request->get('txtNomPersonne')) && !empty($request->request->get('txtPrenomPersonne')) && !empty($request->request->get('txtMailPersonne')) && !empty($request->request->get('txtTelPersonne')) && !empty($request->request->get('txtRuePersonne')) && !empty($request->request->get('txtVillePersonne')) && !empty($request->request->get('txtCPPersonne'))) {
            $idPersonneNotif = $db->ajouterPersonne($request->request->get('txtNomPersonne'), $request->request->get('txtPrenomPersonne'), $request->request->get('txtMailPersonne'), $request->request->get('txtTelPersonne'), $request->request->get('txtRuePersonne'), $request->request->get('txtVillePersonne'), $request->request->get('txtCPPersonne'));
            $notification = 'Ajouté';
        }
        return $this->afficherPersonnes($db, -1,  $idPersonneNotif, $notification);
    }

    /**
     * @Route("/personnes/demanderModifier", name="personnes_demanderModifier")
     */
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherPersonnes($db, $request->request->get('txtIdPersonne'),  -1, 'rien');
    }

    /**
     * @Route("/personnes/validerModifier", name="personnes_validerModifier")
     */
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->modifierPersonne($request->request->get('txtIdPersonne'), $request->request->get('txtNomPersonne'), $request->request->get('txtPrenomPersonne'), $request->request->get('txtMailPersonne'), $request->request->get('txtTelPersonne'), $request->request->get('txtRuePersonne'), $request->request->get('txtVillePersonne'), $request->request->get('txtCPPersonne'));
        return $this->afficherPersonnes($db, -1,  $request->request->get('txtIdPersonne'), 'Modifié');
    }

    /**
     * @Route("/personnes/supprimer", name="personnes_supprimer")
     */
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->supprimerPersonne($request->request->get('txtIdPersonne'));
        $this->addFlash(
            'success', 'La personne a été supprimée'
        );

        return $this->afficherPersonnes($db, -1,  -1, 'rien');
    }
}
