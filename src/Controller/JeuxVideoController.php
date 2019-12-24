<?php
// src/Controller/JeuxVideoController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoJeux;

class JeuxVideoController extends AbstractController
{
    /**
     * fonction pour afficher la liste des jeux vidéo
     * @param $db
     * @param $refJeuModif  positionné si demande de modification
     * @param $refJeuNotif  positionné si mise à jour dans la vue
     * @param $notification  pour notifier la mise à jour dans la vue
     */
    private function afficherJeuxVideo(PdoJeux $db, string $refJeuModif, string $refJeuNotif, string $notification ) {
        $tbJeuxVideo = $db->getLesJeuxVideo();
        $tbGenres  = $db->getLesGenres();
        $tbPlateformes  = $db->getLesPlateformes();
        $tbPegis  = $db->getLesPegis();
        $tbMarques  = $db->getLesMarques();
        return $this->render('lesJeuxVideo.html.twig', array(
            'menuActif' => 'Jeux',
            'tbJeuxVideo' => $tbJeuxVideo,
            'tbGenres' => $tbGenres,
            'tbPlateformes' => $tbPlateformes,
            'tbMarques' => $tbMarques,
            'tbPegis' => $tbPegis,
            'refJeuModif' => $refJeuModif,
            'refJeuNotif' => $refJeuNotif,
            'notification' => $notification
        ));
    }

    /**
     * @Route("/jeuxVideo", name="jeuxVideo_afficher")
     */
    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherJeuxVideo($db, '-1', '-1', 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * @Route("/jeuxVideo/ajouter", name="jeuxVideo_ajouter")
     */
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        if (!empty($request->request->get('txtRefJeu')) && !empty($request->request->get('lstPlateformes')) && !empty($request->request->get('lstPegis')) && !empty($request->request->get('lstGenres')) && !empty($request->request->get('lstMarques')) && !empty($request->request->get('txtNom')) && !empty($request->request->get('txtPrix')) && !empty($request->request->get('txtDateParution'))) {
            $refJeuNotif = $db->ajouterJeuVideo($request->request->get('txtRefJeu'), $request->request->get('lstPlateformes'), $request->request->get('lstPegis'), $request->request->get('lstGenres'), $request->request->get('lstMarques'), $request->request->get('txtNom'), $request->request->get('txtPrix'), $request->request->get('txtDateParution'));
            $notification = 'Ajouté';
        }
        return $this->afficherJeuxVideo($db, '-1',  $refJeuNotif, $notification);
    }

    /**
     * @Route("/jeuxVideo/demanderModifier", name="jeuxVideo_demanderModifier")
     */
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherJeuxVideo($db, $request->request->get('txtRefJeu'),  '-1', 'rien');
    }

    /**
     * @Route("/jeuxVideo/validerModifier", name="jeuxVideo_validerModifier")
     */
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->modifierJeuVideo($request->request->get('txtRefJeu'), $request->request->get('lstPlateformes'), $request->request->get('lstPegis'), $request->request->get('lstGenres'), $request->request->get('lstMarques'), $request->request->get('txtNom'), $request->request->get('txtPrix'), $request->request->get('txtDateParution'));
        return $this->afficherJeuxVideo($db, '-1',  $request->request->get('txtRefJeu'), 'Modifié');
    }

    /**
     * @Route("/jeuxVideo/supprimer", name="jeuxVideo_supprimer")
     */
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $db->supprimerJeuVideo($request->request->get('txtRefJeu'));
        $this->addFlash(
            'success', 'Le jeu vidéo a été supprimé'
        );

        return $this->afficherJeuxVideo($db, '-1',  '-1', 'rien');
    }
}
