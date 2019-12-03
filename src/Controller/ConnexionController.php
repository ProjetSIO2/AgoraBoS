<?php
// src/Controller/ConnexionController.php
namespace App\Controller;

require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use PdoJeux;

class ConnexionController extends AbstractController
{
    /**
     * @Route("/connexion/valider", name="connexion_valider")
     */
    public function validerConnexion(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $utilisateur = $db->getUnePersonne($request->request->get('txtLogin'), $request->request->get('hdMdp')); // $db->getUnePersonne($_POST['txtLogin'], $_POST['hdMdp'])
        // si l'utilisateur n'existe pas
        if (!$utilisateur) {
            // positionner le message d'erreur 
            $this->addFlash(
                'danger', 'Login ou mot de passe incorrect !'
            );
            return $this->render('connexion.html.twig');
        } else {
            // créer trois variables de session pour id utilisateur, nom et prénom
            $session->set('idUtilisateur',$utilisateur->idPersonne);	// $_SESSION['idUtilisateur'] = $utilisateur->idPersonne;
            $session->set('nomUtilisateur',$utilisateur->nomPersonne); // $_SESSION['nomUtilisateur'] = $utilisateur->nomPersonne;
            $session->set('prenomUtilisateur',$utilisateur->prenomPersonne); // $_SESSION['prenomUtilisateur'] = $utilisateur->prenomPersonne;
            // redirection du navigateur vers la page d'accueil
            return $this->redirectToRoute('accueil'); // header('Location: index.php');
        }
    }

    /**
     * @Route("/deconnexion", name="deconnexion")
     */
    public function deconnexion(SessionInterface $session)
    {
        // supprimer la session
        $session->clear(); // session_destroy();
        $session->invalidate(); // session_destroy();
        // redirection vers l'accueil
        return $this->redirectToRoute('accueil'); // header('Location: index.php');
    }
}

