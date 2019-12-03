<?php

/**
 *  AGORA
 * 	©  Logma, 2019
 * @package default
 * @author MD
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 * 
 * Classe d'accès aux données. 
 * Utilise les services de la classe PDO
 * pour l'application AGORA
 * Les attributs sont tous statiques,
 * $monPdo de type PDO 
 * $monPdoJeux qui contiendra l'unique instance de la classe
 */
class PdoJeux {

    private static $monPdo;
    private static $monPdoJeux = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct() {
		// A) >>>>>>>>>>>>>>>   Connexion au serveur et à la base
		try {   
			// encodage
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''); 
			// Crée une instance (un objet) PDO qui représente une connexion à la base
			PdoJeux::$monPdo = new PDO($_ENV['AGORA_DSN'],$_ENV['AGORA_DB_USER'],$_ENV['AGORA_DB_PWD'], $options);
					//PdoJeux::$monPdo = new PDO(DSN,DB_USER,DB_PWD, $options);
			// configure l'attribut ATTR_ERRMODE pour définir le mode de rapport d'erreurs 
			// PDO::ERRMODE_EXCEPTION: émet une exception 
			PdoJeux::$monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// configure l'attribut ATTR_DEFAULT_FETCH_MODE pour définir le mode de récupération par défaut 
			// PDO::FETCH_OBJ: retourne un objet anonyme avec les noms de propriétés 
			//     qui correspondent aux noms des colonnes retournés dans le jeu de résultats
			PdoJeux::$monPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		}
		catch (PDOException $e)	{	// $e est un objet de la classe PDOException, il expose la description du problème
			die('<section id="main-content"><section class="wrapper"><div class = "erreur">Erreur de connexion à la base de données !<p>'
				.$e->getmessage().'</p></div></section></section>');
		}
    }
	
    /**
     * Destructeur, supprime l'instance de PDO  
     */
    public function _destruct() {
        PdoJeux::$monPdo = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoJeux = PdoJeux::getPdoJeux();
     * 
     * @return l'unique objet de la classe PdoJeux
     */
    public static function getPdoJeux() {
        if (PdoJeux::$monPdoJeux == null) {
            PdoJeux::$monPdoJeux = new PdoJeux();
        }
        return PdoJeux::$monPdoJeux;
    }

	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES GENRES
	//
	//==============================================================================
	
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return le tableau d'objets  (Genre)
     */
    public function getLesGenres() {
  		$requete =  "SELECT idGenre as identifiant, libGenre as libelle, idGerant as idGerant, 
					(SELECT CONCAT(prenomPersonne, ' ', nomPersonne) FROM personne WHERE idPersonne = idGerant) as gerantGenre, 
					(SELECT COUNT(*) FROM jeu_video WHERE idGenre = identifiant) as nbJeuxGenre 
					FROM genre 
					ORDER BY libGenre";
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbGenres  = $resultat->fetchAll();	
			return $tbGenres;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute un nouveau genre avec le libellé donné en paramètre
	 * 
	 * @param $libGenre : le libelle du genre à ajouter
	 * @param $idGerant : l'identifiant du gérant du genre à ajouter
	 * @return l'identifiant du genre crée
	 */
    public function ajouterGenre($libGenre, $idGerant) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO genre "
                    . "(idGenre, libGenre, idGerant) "
                    . "VALUES (0, :unLibGenre, :unGerantGenre) ");
            $requete_prepare->bindParam(':unLibGenre', $libGenre, PDO::PARAM_STR);
			$requete_prepare->bindParam(':unGerantGenre', $idGerant, PDO::PARAM_INT);
            $requete_prepare->execute();
			// récupérer l'identifiant crée
			return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	 /**
     * Modifie le libellé du genre donné en paramètre
     * 
     * @param $idGenre : l'identifiant du genre à modifier  
     * @param $libGenre : le libellé modifié
     */
    public function modifierGenre($idGenre, $libGenre, $idGerant) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE genre "
                    . "SET libGenre = :unLibGenre, idGerant = :unGerantGenre "
                    . "WHERE genre.idGenre = :unIdGenre");
            $requete_prepare->bindParam(':unIdGenre', $idGenre, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibGenre', $libGenre, PDO::PARAM_STR);
			$requete_prepare->bindParam(':unGerantGenre', $idGerant, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	/**
     * Supprime le genre donné en paramètre
     * 
     * @param $idGenre :l'identifiant du genre à supprimer 
     */
    public function supprimerGenre($idGenre) {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM genre "
                    . "WHERE genre.idGenre = :unIdGenre");
            $requete_prepare->bindParam(':unIdGenre', $idGenre, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES MARQUES
	//
	//==============================================================================
	
    /**
     * Retourne toutes les marques sous forme d'un tableau d'objets 
     * 
     * @return le tableau d'objets  (Marque)
     */
    public function getLesMarques() {
  		$requete =  'SELECT idMarque as identifiant, nomMarque as libelle 
						FROM marque 
						ORDER BY nomMarque';
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbMarques  = $resultat->fetchAll();	
			return $tbMarques;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute une nouvelle marque avec le libellé donné en paramètre
	 * 
	 * @param $libMarque : le libelle de la marque à ajouter
	 * @return l'identifiant de la marque créee
	 */
    public function ajouterMarque($libMarque) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO marque "
                    . "(idMarque, nomMarque) "
                    . "VALUES (0, :unLibMarque) ");
            $requete_prepare->bindParam(':unLibMarque', $libMarque, PDO::PARAM_STR);
            $requete_prepare->execute();
			// récupérer l'identifiant crée
			return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	 /**
     * Modifie le libellé de la marque donné en paramètre
     * 
     * @param $idMarque : l'identifiant de la marque à modifier  
     * @param $libMarque : le libellé modifié
     */
    public function modifierMarque($idMarque, $libMarque) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE marque "
                    . "SET nomMarque = :unLibMarque "
                    . "WHERE marque.idMarque = :unIdMarque");
            $requete_prepare->bindParam(':unIdMarque', $idMarque, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibMarque', $libMarque, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	/**
     * Supprime la marque donnée en paramètre
     * 
     * @param $idMarque : l'identifiant de la marque à supprimer 
     */
    public function supprimerMarque($idMarque) {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM marque "
                    . "WHERE marque.idMarque = :unIdMarque");
            $requete_prepare->bindParam(':unIdMarque', $idMarque, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES PLATEFORMES
	//
	//==============================================================================
	
    /**
     * Retourne toutes les plateformes sous forme d'un tableau d'objets 
     * 
     * @return le tableau d'objets  (Plateforme)
     */
    public function getLesPlateformes() {
  		$requete =  'SELECT idPlateforme as identifiant, libPlateforme as libelle, nbPlateformesDispo as nombreDispo 
						FROM plateforme 
						ORDER BY libPlateforme';
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbPlateformes  = $resultat->fetchAll();	
			return $tbPlateformes;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute une nouvelle plateforme avec le libellé et le nombre de plateformes disponibles donné en paramètre
	 * 
	 * @param $libPlateforme : le libelle de la plateforme à ajouter
     * @param $nbPlateformesDispo : le nombre disponible de la plateforme à ajouter
	 * @return l'identifiant de la plateforme créee
	 */
    public function ajouterPlateforme($libPlateforme, $nbPlateformesDispo) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO plateforme "
                    . "(idPlateforme, libPlateforme, nbPlateformesDispo) "
                    . "VALUES (0, :unLibPlateforme, :unNbPlateformesDispo) ");
            $requete_prepare->bindParam(':unLibPlateforme', $libPlateforme, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unNbPlateformesDispo', $nbPlateformesDispo, PDO::PARAM_INT);
            $requete_prepare->execute();
			// récupérer l'identifiant crée
			return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	 /**
     * Modifie le libellé et le nombre disponible de la plateforme donné en paramètre
     * 
     * @param $idPlateforme : l'identifiant de la plateforme à modifier  
     * @param $libPlateforme : le libellé modifié
     * @param $nbPlateformesDispo : le nombre de plateformes disponibles modifié
     */
    public function modifierPlateforme($idPlateforme, $libPlateforme, $nbPlateformesDispo) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE plateforme "
                    . "SET libPlateforme = :unLibPlateforme, nbPlateformesDispo = :unNbPlateformesDispo "
                    . "WHERE plateforme.idPlateforme = :unIdPlateforme");
            $requete_prepare->bindParam(':unIdPlateforme', $idPlateforme, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibPlateforme', $libPlateforme, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unNbPlateformesDispo', $nbPlateformesDispo, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	/**
     * Supprime la plateforme donnée en paramètre
     * 
     * @param $idPlateforme : l'identifiant de la plateforme à supprimer 
     */
    public function supprimerPlateforme($idPlateforme) {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM plateforme "
                    . "WHERE plateforme.idPlateforme = :unIdPlateforme");
            $requete_prepare->bindParam(':unIdPlateforme', $idPlateforme, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES PEGIS
	//
	//==============================================================================
	
    /**
     * Retourne tous les pegis sous forme d'un tableau d'objets 
     * 
     * @return le tableau d'objets  (Pegi)
     */
    public function getLesPegis() {
  		$requete =  'SELECT idPegi as identifiant, ageLimite as libelle, descPegi as description 
						FROM pegi 
						ORDER BY idPegi';
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbPegis  = $resultat->fetchAll();	
			return $tbPegis;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute un nouveau pegi avec l'âge limite et la description donnés en paramètre
	 * 
	 * @param $ageLimite : le libelle du pegi à ajouter
	 * @param $descPegi : la description du pegi à ajouter
	 * @return l'identifiant du pegi crée
	 */
    public function ajouterPegi($ageLimite, $descPegi) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO pegi "
                    . "(idPegi, ageLimite, descPegi) "
                    . "VALUES (0, :unAgeLimite, :uneDescPegi) ");
            $requete_prepare->bindParam(':unAgeLimite', $ageLimite, PDO::PARAM_STR);
			$requete_prepare->bindParam(':uneDescPegi', $descPegi, PDO::PARAM_STR);
            $requete_prepare->execute();
			// récupérer l'identifiant crée
			return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	 /**
     * Modifie l'âge limite et/ou la description du pegi donné en paramètre
     * 
     * @param $idPegi : l'identifiant du pegi à modifier  
     * @param $ageLimite : l'âge limite du pegi modifié
	 * @param $descPegi : la description du pegi modifié
     */
    public function modifierPegi($idPegi, $ageLimite, $descPegi) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE pegi "
                    . "SET ageLimite = :unAgeLimite, descPegi = :uneDescPegi "
                    . "WHERE pegi.idPegi = :unIdPegi");
            $requete_prepare->bindParam(':unIdPegi', $idPegi, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unAgeLimite', $ageLimite, PDO::PARAM_INT);
			$requete_prepare->bindParam(':uneDescPegi', $descPegi, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	/**
     * Supprime le pegi donné en paramètre
     * 
     * @param $idPegi :l'identifiant du pegi à supprimer 
     */
    public function supprimerPegi($idPegi) {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM pegi "
                    . "WHERE pegi.idPegi = :unIdPegi");
            $requete_prepare->bindParam(':unIdPegi', $idPegi, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	
	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES JEUX VIDEOS
	//
	//==============================================================================
	
    /**
     * Retourne tous les jeux vidéos sous forme d'un tableau d'objets 
     * 
     * @return le tableau d'objets  (JeuVideo)
     */
    public function getLesJeuxVideo() {
  		$requete =  'SELECT J.refJeu AS reference, PA.idPlateforme AS idPlateforme, PA.libPlateforme AS libPlateforme, 
					PE.idPegi AS idPegi, PE.ageLimite AS libPegi, G.idGenre AS idGenre, G.libGenre AS libGenre, 
					M.idMarque AS idMarque, M.nomMarque AS libMarque, J.nom AS nom, J.prix AS prix, J.dateParution AS dateP
						FROM jeu_video AS J 
						NATURAL JOIN plateforme AS PA 
						NATURAL JOIN pegi AS PE
						NATURAL JOIN genre AS G
						NATURAL JOIN marque AS M
						ORDER BY nom';
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbJeuxVideo  = $resultat->fetchAll();	
			return $tbJeuxVideo;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute un nouveau jeu vidéo avec la référence, l'id plateforme, l'id pegi, l'id genre, l'id marque,
	 *	le nom, le prix et la date de parution donnés en paramètre
	 * 
	 * @param $refJeu : la référence du jeu vidéo à ajouter
	 * @param $idPlateforme : la plateforme du jeu vidéo à ajouter
	 * @param $idPegi : le pegi du jeu vidéo à ajouter
	 * @param $idGenre : le genre du jeu vidéo à ajouter
	 * @param $idMarque : la marque du jeu vidéo à ajouter
	 * @param $nom : le nom du jeu vidéo à ajouter
	 * @param $prix : le prix du jeu vidéo à ajouter
	 * @param $dateParution la date de parution : du jeu vidéo à ajouter
	 * @return la référence du jeu vidéo donnée
	 */
    public function ajouterJeuVideo($refJeu, $idPlateforme, $idPegi, $idGenre, $idMarque, $nom, $prix, $dateParution) {
        try {	
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO jeu_video "
                    . "(refJeu, idPlateforme, idPegi, idGenre, idMarque, nom, prix, dateParution) "
                    . "VALUES (:uneRefJeu, :unePlateforme, :unPegi, :unGenre, :uneMarque, :unNom,
						:unPrix, :uneDateParution) ");
			$requete_prepare->bindParam(':uneRefJeu', $refJeu, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unePlateforme', $idPlateforme, PDO::PARAM_INT);
			$requete_prepare->bindParam(':unPegi', $idPegi, PDO::PARAM_INT);
			$requete_prepare->bindParam(':unGenre', $idGenre, PDO::PARAM_INT);
			$requete_prepare->bindParam(':uneMarque', $idMarque, PDO::PARAM_INT);
			$requete_prepare->bindParam(':unNom', $nom, PDO::PARAM_STR);
			$requete_prepare->bindParam(':unPrix', $prix);
			$requete_prepare->bindParam(':uneDateParution', $dateParution, PDO::PARAM_STR);
            $requete_prepare->execute();
			// récupérer la référence donnée
			return $refJeu ; 
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	 /**
     * Modifie la référence, l'id plateforme, l'id pegi, l'id genre, l'id marque, le nom, le prix 
	 *		et la date de parution du jeu vidéo donné en paramètre
     * 
     * @param $refJeu : la référence du jeu vidéo à modifier  
     * @param $idPlateforme : la plateforme du jeu vidéo modifié
	 * @param $idPegi : le pegi du jeu vidéo modifié
	 * @param $idGenre : le genre du jeu vidéo modifié
	 * @param $idMarque : la marque du jeu vidéo modifié
	 * @param $nom : le nom du jeu vidéo modifié
	 * @param $prix : le prix du jeu vidéo modifié
	 * @param $dateParution la date de parution : du jeu vidéo modifié
     */
    public function modifierJeuVideo($refJeu, $idPlateforme, $idPegi, $idGenre, $idMarque, $nom, $prix, $dateParution) {
        try {			
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE jeu_video "
                    . "SET refJeu = :uneRefJeu, idPlateforme = :unePlateforme, idPegi = :unPegi, 
					idGenre = :unGenre, idMarque = :uneMarque, nom = :unNom, prix = :unPrix, 
					dateParution = :uneDateParution "
                    . "WHERE jeu_video.refJeu = :uneRefJeu");
            $requete_prepare->bindParam(':uneRefJeu', $refJeu, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unePlateforme', $idPlateforme, PDO::PARAM_INT);
			$requete_prepare->bindParam(':unPegi', $idPegi, PDO::PARAM_INT);
			$requete_prepare->bindParam(':unGenre', $idGenre, PDO::PARAM_INT);
			$requete_prepare->bindParam(':uneMarque', $idMarque, PDO::PARAM_INT);
			$requete_prepare->bindParam(':unNom', $nom, PDO::PARAM_STR);
			$requete_prepare->bindParam(':unPrix', $prix);
			$requete_prepare->bindParam(':uneDateParution', $dateParution, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	
	
	/**
     * Supprime le jeu vidéo donné en paramètre
     * 
     * @param $refJeu :la référence du jeu vidéo à supprimer 
     */
    public function supprimerJeuVideo($refJeu) {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM jeu_video "
                    . "WHERE jeu_video.refJeu = :uneRefJeu");
            $requete_prepare->bindParam(':uneRefJeu', $refJeu, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function getLesJeux_LA(){
        $requete = "SELECT refJeu as identifiant, nom as libelle
                    FROM jeu_video
                    ORDER BY nom";
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbJeux  = $resultat->fetchAll();
            return $tbJeux;
        }
        catch (PDOException $e)	{
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }
	
	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES PERSONNES
	//
	//==============================================================================
    /**
    * Retourne l'identifiant, le nom et le prénom de la personne correspondant
     *  au login et mdp
     * @param $loginPersonne : l'identifiant de la personne à retournant
     *
     * @return l'objet ou null si cette personne n'existe pas avec ce mot de passe
     */
    public function getUnePersonne($loginPersonne, $mdpPersonne)
    {
        try {
            // préparer la requête
            $requete_prepare = PdoJeux::$monPdo->prepare('SELECT idPersonne, prenomPersonne, nomPersonne, mdpPersonne, selPersonne 
                    FROM personne 
                    WHERE loginPersonne = :unLoginPersonne');
            // associer les valeurs aux paramètres
            $requete_prepare->bindParam(':unLoginPersonne', $loginPersonne, PDO::PARAM_STR);
            // exécuter la requête
            $requete_prepare->execute();

            // récupérer l'objet
            if ($utilisateur = $requete_prepare->fetch()) {
                // vérifier le mot de passe
                // le mot de passe transmis par le formulaire est le hash du mot de passe saisi
                // le mot de passe enregistré dans la base doit correspondre au hash du (hash transmis concaténé au sel)
                // hash('sha512', $chaine) : fonction de hachage PHP
                if (hash('sha512', $mdpPersonne.$utilisateur->selPersonne) == $utilisateur->mdpPersonne) {
                    return $utilisateur;
                }
            }
            return null;
        }
        catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                . $e->getmessage() . '</p></div>');
        }
    }

    /**
     * Retourne toutes les personnes sous forme d'un tableau d'objets 
     * 
     * @return le tableau d'objets  (Personne)
     */
    public function getLesPersonnes_LA() {
  		$requete =  "SELECT idPersonne as identifiant, CONCAT(prenomPersonne, ' ',nomPersonne) as libelle 
						FROM personne 
						ORDER BY nomPersonne";
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbPersonnes_LA  = $resultat->fetchAll();	
			return $tbPersonnes_LA;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

    /**
     * Retourne toutes les personnes sous forme d'un tableau d'objets
     *
     * @return le tableau d'objets  (Personne)
     */
    public function getLesPersonnes() {
        $requete =  'SELECT idPersonne as identifiant, nomPersonne as nom, prenomPersonne as prenom, mailPersonne as mail,
                        telPersonne as tel, ruePersonne as rue, villePersonne as ville, CPPersonne as CP 
						FROM personne 
						ORDER BY idPersonne';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbPersonnes  = $resultat->fetchAll();
            return $tbPersonnes;
        }
        catch (PDOException $e)	{
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }


    /**
     * Ajoute une nouvelle personne avec le nom, le prenom, le mail, le tel, la rue, la ville et le code postal donnés en paramètre
     *
     * @param $nomPersonne : le nom de la personne à ajouter
     * @param $prenomPersonne : le prenom de la personne à ajouter
     * @param $mailPersonne : le mail de la personne à ajouter
     * @param $telPersonne : le téléphone de la personne à ajouter
     * @param $ruePersonne : la rue de la personne à ajouter
     * @param $villePersonne : la ville de la personne à ajouter
     * @param $CPPersonne : le code postal de la personne à ajouter
     * @return l'identifiant de la personne créee
     */
    public function ajouterPersonne($nomPersonne, $prenomPersonne, $mailPersonne, $telPersonne, $ruePersonne,
                                    $villePersonne, $CPPersonne) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO personne "
                . "(idPersonne, nomPersonne, prenomPersonne, mailPersonne, telPersonne, ruePersonne, villePersonne, CPPersonne) "
                . "VALUES (6, :unNomPersonne, :unPrenomPersonne, :unMailPersonne, :unTelPersonne, :uneRuePersonne,
                :uneVillePersonne, :unCPPersonne) ");
            $requete_prepare->bindParam(':unNomPersonne', $nomPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unPrenomPersonne', $prenomPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unMailPersonne', $mailPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unTelPersonne', $telPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneRuePersonne', $ruePersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneVillePersonne', $villePersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unCPPersonne', $CPPersonne, PDO::PARAM_STR);
            $requete_prepare->execute();
            // récupérer l'identifiant crée
            return PdoJeux::$monPdo->lastInsertId();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Modifie le nom, le prenom, le mail, le tel, la rue, la ville et/ou le code postal de la personne donnée en paramètre
     *
     * @param $idPersonne : l'identifiant de la personne à modifier
     * @param $nomPersonne : le nom de la personne modifiée
     * @param $prenomPersonne : le prénom de la personne modifiée
     * @param $mailPersonne : le mail de la personne modifiée
     * @param $telPersonne : le téléphone de la personne modifiée
     * @param $ruePersonne : la rue de la personne à ajouter
     * @param $villePersonne : la ville de la personne modifiée
     * @param $CPPersonne : le code postal de la personne modifiée
     */
    public function modifierPersonne($idPersonne, $nomPersonne, $prenomPersonne, $mailPersonne, $telPersonne, $ruePersonne, $villePersonne, $CPPersonne) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE personne "
                . "SET nomPersonne = :unNomPersonne, prenomPersonne = :unPrenomPersonne, mailPersonne = :unMailPersonne,
                telPersonne = :unTelPersonne, ruePersonne = :uneRuePersonne, villePersonne = :uneVillePersonne, 
                CPPersonne = :unCPPersonne "
                . "WHERE personne.idPersonne = :unIdPersonne");
            $requete_prepare->bindParam(':unIdPersonne', $idPersonne, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unNomPersonne', $nomPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unPrenomPersonne', $prenomPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unMailPersonne', $mailPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unTelPersonne', $telPersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneRuePersonne', $ruePersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneVillePersonne', $villePersonne, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unCPPersonne', $CPPersonne, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Supprime la personne donnée en paramètre
     *
     * @param $idPersonne :l'identifiant de la personne à supprimer
     */
    public function supprimerPersonne($idPersonne) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM personne "
                . "WHERE personne.idPersonne = :unIdPersonne");
            $requete_prepare->bindParam(':unIdPersonne', $idPersonne, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES EQUIPEMENTS
    //
    //==============================================================================

    /**
     * Retourne tous les equipements sous forme d'un tableau d'objets
     *
     * @return le tableau d'objets  (Equipement)
     */
    public function getLesEquipements() {
        $requete =  'SELECT refEquipement as identifiant, libEquipement as libelle 
						FROM equipement 
						ORDER BY libEquipement';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbEquipements  = $resultat->fetchAll();
            return $tbEquipements;
        }
        catch (PDOException $e)	{
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }


    /**
     * Ajoute un nouvel equipement avec le libellé donné en paramètre
     * @param $idEquipement : l'identifiant de l'equipement à ajouter
     * @param $libEquipement : le libelle de l'equipement à ajouter
     * @return l'identifiant de l'equipement crée
     */
    public function ajouterEquipement($idEquipement, $libEquipement) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO equipement "
                . "(refEquipement, libEquipement) "
                . "VALUES (:unIdEquipement, :unLibEquipement) ");
            $requete_prepare->bindParam(':unIdEquipement', $idEquipement, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unLibEquipement', $libEquipement, PDO::PARAM_STR);
            $requete_prepare->execute();
            // récupérer l'identifiant crée
            return $idEquipement;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Modifie le libellé de l'equipement donné en paramètre
     *
     * @param $idEquipement : l'identifiant de l'equipement à modifier
     * @param $libEquipement : le libellé modifié
     */
    public function modifierEquipement($idEquipement, $libEquipement) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE equipement "
                . "SET refEquipement = :unIdEquipement, libEquipement = :unLibEquipement "
                . "WHERE equipement.refEquipement = :unIdEquipement");
            $requete_prepare->bindParam(':unIdEquipement', $idEquipement, PDO::PARAM_STR);
            $requete_prepare->bindParam(':unLibEquipement', $libEquipement, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Supprime l'equipement donné en paramètre
     *
     * @param $idEquipement : l'identifiant de l'equipement à supprimer
     */
    public function supprimerEquipement($idEquipement) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM equipement "
                . "WHERE equipement.refEquipement = :unIdEquipement");
            $requete_prepare->bindParam(':unIdEquipement', $idEquipement, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES FORMATS
    //
    //==============================================================================

    /**
     * Retourne tous les formats sous forme d'un tableau d'objets
     *
     * @return le tableau d'objets  (Format)
     */
    public function getLesFormats() {
        $requete =  'SELECT idFormat as identifiant, nomFormat as libelle, descFormat as description 
						FROM format
						ORDER BY nomFormat';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbFormats  = $resultat->fetchAll();
            return $tbFormats;
        }
        catch (PDOException $e)	{
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }


    /**
     * Ajoute un nouveau format avec le nom et la description donnés en paramètre
     *
     * @param $libFormat : le libelle du format à ajouter
     * @param $descFormat : la description du format à ajouter
     * @return l'identifiant du format crée
     */
    public function ajouterFormat($libFormat, $descFormat) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO format "
                . "(idFormat, nomFormat, descFormat) "
                . "VALUES (0, :unLibFormat, :uneDescFormat) ");
            $requete_prepare->bindParam(':unLibFormat', $libFormat, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneDescFormat', $descFormat, PDO::PARAM_STR);
            $requete_prepare->execute();
            // récupérer l'identifiant crée
            return PdoJeux::$monPdo->lastInsertId();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Modifie le libelle et/ou la description du format donné en paramètre
     *
     * @param $idFormat : l'identifiant du format à modifier
     * @param $libFormat : le libelle du format modifié
     * @param $descFormat : la description du format modifié
     */
    public function modifierFormat($idFormat, $libFormat, $descFormat) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE format "
                . "SET nomFormat = :unLibFormat, descFormat = :uneDescFormat "
                . "WHERE format.idFormat = :unIdFormat");
            $requete_prepare->bindParam(':unIdFormat', $idFormat, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibFormat', $libFormat, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneDescFormat', $descFormat, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Supprime le format donné en paramètre
     *
     * @param $idFormat :l'identifiant du format à supprimer
     */
    public function supprimerFormat($idFormat) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM format "
                . "WHERE format.idFormat = :unIdFormat");
            $requete_prepare->bindParam(':unIdFormat', $idFormat, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }



    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES CATEGORIES
    //
    //==============================================================================

    /**
     * Retourne toutes les categories sous forme d'un tableau d'objets
     *
     * @return le tableau d'objets  (Categorie)
     */
    public function getLesCategories() {
        $requete =  'SELECT idCategorie as identifiant, libCategorie as libelle 
						FROM categorie 
						ORDER BY libCategorie';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbCategories  = $resultat->fetchAll();
            return $tbCategories;
        }
        catch (PDOException $e)	{
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }


    /**
     * Ajoute une nouvelle categorie avec le libellé donné en paramètre
     *
     * @param $libCategorie : le libelle de la categorie à ajouter
     * @return l'identifiant de la categorie créee
     */
    public function ajouterCategorie($libCategorie) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO categorie "
                . "(idCategorie, libCategorie) "
                . "VALUES (0, :unLibCategorie) ");
            $requete_prepare->bindParam(':unLibCategorie', $libCategorie, PDO::PARAM_STR);
            $requete_prepare->execute();
            // récupérer l'identifiant crée
            return PdoJeux::$monPdo->lastInsertId();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Modifie le libellé de la categorie donné en paramètre
     *
     * @param $idCategorie : l'identifiant de la categorie à modifier
     * @param $libCategorie : le libellé modifié
     */
    public function modifierCategorie($idCategorie, $libCategorie) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE categorie "
                . "SET libCategorie = :unLibCategorie "
                . "WHERE categorie.idCategorie = :unIdCategorie");
            $requete_prepare->bindParam(':unIdCategorie', $idCategorie, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibCategorie', $libCategorie, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Supprime la categorie donnée en paramètre
     *
     * @param $idCategorie : l'identifiant de la categorie à supprimer
     */
    public function supprimerCategorie($idCategorie) {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM categorie "
                . "WHERE categorie.idCategorie = :unIdCategorie");
            $requete_prepare->bindParam(':unIdCategorie', $idCategorie, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES TOURNOIS
    //
    //==============================================================================

    public function getLesTournois(){
        $requete = "SELECT anneeTournoi, numTournoi, nomTournoi, jeu_video.nom
                    FROM tournoi 
                    NATURAL JOIN jeu_video";
        try {
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbTournois = $resultat->fetchAll();
            return $tbTournois;
        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    public function getLeTournoi($annee, $numTournoi){
        try {
            // Requete tournoi
            $requeteTournoi = PdoJeux::$monPdo->prepare('SELECT T.anneeTournoi, T.refJeu, T.idJuge, T.idFormat, T.numTournoi, T.nomTournoi, JV.nom as nomJeu, PL.libPlateforme, F.nomFormat, gain, 
                                                        concat(P.nomPersonne," ",P.prenomPersonne) AS juge, T.nbParticipants
                                                        FROM tournoi AS T
                                                        NATURAL JOIN jeu_video AS JV
                                                        JOIN plateforme AS PL on JV.idPlateforme = PL.idPlateforme
                                                        NATURAL JOIN format AS F
                                                        JOIN personne AS P on T.idJuge = P.idPersonne
                                                        WHERE T.anneeTournoi = :annee and numTournoi = :numTournoi');
            $requeteTournoi->bindParam(':annee', $annee, PDO::PARAM_INT);
            $requeteTournoi->bindParam(':numTournoi', $numTournoi, PDO::PARAM_INT);
            $requeteTournoi->execute();
            $tbTournoi = $requeteTournoi->fetchAll();

            // Requete equipement
            $requeteEquipements = PdoJeux::$monPdo->prepare("SELECT E.libEquipement, refEquipement
                                                                FROM equipementtournoi as ET
                                                                NATURAL JOIN equipement as E
                                                                WHERE anneeTournoi = :annee and numTournoi = :numTournoi");
            $requeteEquipements->bindParam(':annee', $annee, PDO::PARAM_INT);
            $requeteEquipements->bindParam(':numTournoi', $numTournoi, PDO::PARAM_INT);
            $requeteEquipements->execute();
            $tbEquipements = $requeteEquipements->fetchAll();

            // Requete journées
            $requeteJournees = PdoJeux::$monPdo->prepare('SELECT dateJournee, heureDebut, heureFin
                                                        FROM journee
                                                        WHERE anneeTournoi = :annee and numTournoi = :numTournoi');
            $requeteJournees->bindParam(':annee', $annee, PDO::PARAM_INT);
            $requeteJournees->bindParam(':numTournoi', $numTournoi, PDO::PARAM_INT);
            $requeteJournees->execute();
            $tbJournees = $requeteJournees->fetchAll();

            // Requete animateurs
            $requeteAnimateurs = PdoJeux::$monPdo->prepare('SELECT concat(nomPersonne, " ", prenomPersonne) as animateur, idPersonne
                                                            FROM animateurtournoi
                                                            NATURAL JOIN personne 
                                                            WHERE anneeTournoi = :annee and numTournoi = :numTournoi');
            $requeteAnimateurs->bindParam(':annee', $annee, PDO::PARAM_INT);
            $requeteAnimateurs->bindParam(':numTournoi', $numTournoi, PDO::PARAM_INT);
            $requeteAnimateurs->execute();
            $tbAnimateurs = $requeteAnimateurs->fetchAll();

            // Création de l'objet
            $objTournoi = (Object)[
                "Tournoi" => $tbTournoi,
                "Equipements" => $tbEquipements,
                "Journees" => $tbJournees,
                "Animateurs" => $tbAnimateurs,
            ];
            return $objTournoi;
        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    public function ajouterTournoi($objTournoi){
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare('INSERT INTO Tournoi (anneeTournoi, numTournoi, nomTournoi, nbParticipants, gain, refJeu, idFormat, idJuge)
                                                        VALUES ( :annee, :numTournoi, :nomTournoi, :nbParticipants, :gain, :refJeu, :idFormat, :idJuge)');
            $requete_prepare->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':nomTournoi', $objTournoi->NomTournoi, Pdo::PARAM_STR);
            $requete_prepare->bindParam(':nbParticipants', $objTournoi->NbParticipants, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':gain', $objTournoi->Gain, Pdo::PARAM_STR);
            $requete_prepare->bindParam(':refJeu', $objTournoi->Jeu, Pdo::PARAM_STR);
            $requete_prepare->bindParam(':idFormat', $objTournoi->Format, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':idJuge', $objTournoi->Juge, Pdo::PARAM_INT);
            $requete_prepare->execute();

            foreach ($objTournoi->Animateurs as $animateur) {
                $requeteAnimateur = PdoJeux::$monPdo->prepare('INSERT INTO animateurtournoi (anneeTournoi, numTournoi, idPersonne) VALUES
                                                                (:annee, :numTournoi, :idPersonne)');
                $requeteAnimateur->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
                $requeteAnimateur->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
                $requeteAnimateur->bindParam(':idPersonne', $animateur, Pdo::PARAM_INT);
                $requeteAnimateur->execute();
            }

            foreach ($objTournoi->Equipements as $equipement) {
                $requeteEquipement = PdoJeux::$monPdo->prepare('INSERT INTO equipementtournoi (anneeTournoi, numTournoi, refEquipement) VALUES
                                                                (:annee, :numTournoi, :refEquipement)');
                $requeteEquipement->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
                $requeteEquipement->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
                $requeteEquipement->bindParam(':refEquipement', $equipement, Pdo::PARAM_STR);
                $requeteEquipement->execute();
            }

            foreach ($objTournoi->Journees as $journee) {
                $requeteJournee = PdoJeux::$monPdo->prepare('INSERT INTO journee (anneeTournoi, numTournoi, dateJournee, heureDebut, heureFin) VALUES
                                                                (:annee, :numTournoi, :dateJ, :heureD, :heureF)');
                $requeteJournee->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
                $requeteJournee->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
                $requeteJournee->bindParam(':dateJ', $journee->dateJ);
                $requeteJournee->bindParam(':heureD', $journee->heureD);
                $requeteJournee->bindParam(':heureF', $journee->heureF);
                $requeteJournee->execute();
            }

        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    public function modifierTournoi($objTournoi){
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare('UPDATE tournoi
                                                            SET nomTournoi = :nomTournoi, nbParticipants = :nbParticipants, gain = :gain, refJeu = :refJeu, idFormat = :idFormat, idJuge = :idJuge
                                                            WHERE anneeTournoi = :annee and numTournoi = :numTournoi');
            $requete_prepare->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':nomTournoi', $objTournoi->NomTournoi, Pdo::PARAM_STR);
            $requete_prepare->bindParam(':nbParticipants', $objTournoi->NbParticipants, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':gain', $objTournoi->Gain, Pdo::PARAM_STR);
            $requete_prepare->bindParam(':refJeu', $objTournoi->Jeu, Pdo::PARAM_STR);
            $requete_prepare->bindParam(':idFormat', $objTournoi->Format, Pdo::PARAM_INT);
            $requete_prepare->bindParam(':idJuge', $objTournoi->Juge, Pdo::PARAM_INT);
            $requete_prepare->execute();

            // Suppression des animateurs déjà enregistrés
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM animateurtournoi 
                                                            WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $objTournoi->Annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $objTournoi->Numero, PDO::PARAM_INT);
            $requete_prepare->execute();

            // Création des animateurs 
            foreach ($objTournoi->Animateurs as $animateur) {
                $requeteAnimateur = PdoJeux::$monPdo->prepare('INSERT INTO animateurtournoi (anneeTournoi, numTournoi, idPersonne) VALUES
                                                                (:annee, :numTournoi, :idPersonne)');
                $requeteAnimateur->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
                $requeteAnimateur->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
                $requeteAnimateur->bindParam(':idPersonne', $animateur, Pdo::PARAM_INT);
                $requeteAnimateur->execute();
            }

            // Suppression des équipements déjà enregistrés
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM equipementtournoi 
                                                            WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $objTournoi->Annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $objTournoi->Numero, PDO::PARAM_INT);
            $requete_prepare->execute();

            // Création des équipements 
            foreach ($objTournoi->Equipements as $equipement) {
                $requeteEquipement = PdoJeux::$monPdo->prepare('INSERT INTO equipementtournoi (anneeTournoi, numTournoi, refEquipement) VALUES
                                                                (:annee, :numTournoi, :refEquipement)');
                $requeteEquipement->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
                $requeteEquipement->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
                $requeteEquipement->bindParam(':refEquipement', $equipement, Pdo::PARAM_STR);
                $requeteEquipement->execute();
            }

            // Suppression des journées déjà enregistrées
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM journee 
                                                                WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $objTournoi->Annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $objTournoi->Numero, PDO::PARAM_INT);
            $requete_prepare->execute();

            // Création des journées
            foreach ($objTournoi->Journees as $journee) {
                $requeteJournee = PdoJeux::$monPdo->prepare('INSERT INTO journee (anneeTournoi, numTournoi, dateJournee, heureDebut, heureFin) VALUES
                                                                    (:annee, :numTournoi, :dateJ, :heureD, :heureF)');
                $requeteJournee->bindParam(':annee', $objTournoi->Annee, Pdo::PARAM_INT);
                $requeteJournee->bindParam(':numTournoi', $objTournoi->Numero, Pdo::PARAM_INT);
                $requeteJournee->bindParam(':dateJ', $journee->dateJ);
                $requeteJournee->bindParam(':heureD', $journee->heureD);
                $requeteJournee->bindParam(':heureF', $journee->heureF);
                $requeteJournee->execute();
            }
        } catch (PDOException $e) {
             die('<div class = "erreur">Erreur dans la requête !<p>'
                 .$e->getmessage().'</p></div>');
        }
    }

    public function supprimerTournoi($annee, $num){
        try{
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM equipementtournoi 
                                                            WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $num, PDO::PARAM_INT);
            $requete_prepare->execute();

            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM animateurtournoi 
                                                            WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $num, PDO::PARAM_INT);
            $requete_prepare->execute();

            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM journee 
                                                            WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $num, PDO::PARAM_INT);
            $requete_prepare->execute();

            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM tournoi 
                                                            WHERE anneeTournoi = :anneeTournoi and numTournoi = :numTournoi");
            $requete_prepare->bindParam(':anneeTournoi', $annee, PDO::PARAM_INT);
            $requete_prepare->bindParam(':numTournoi', $num, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
?>