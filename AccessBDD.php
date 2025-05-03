<?php
include_once("ConnexionPDO.php");
/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {
	
    public $login="root";
    public $mdp="";
    public $bd="mediatek86";
    public $serveur="localhost";
    public $port="3306";	
    public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct(){
        try{
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table){
        if($this->conn != null){
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "maxcommande" :
                    return $this->selectMaxCommande();
                case "maxlivre" :
                    return $this->selectMaxLivre();
                case "maxdvd" :
                    return $this->selectMaxDvd();
                case "maxrevue" :
                    return $this->selectMaxRevue();
                case "genre" :
                case "public" :
                case "rayon" :
                    // select portant sur une table contenant juste id et libelle
                    return $this->selectTableSimple($table);
                default:
                    // select portant sur une table, sans condition
                    return $this->selectTable($table);
            }
        }else{
            return null;
        }
    }

    /**
     * récupération des lignes concernées
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de recherche
     * @return lignes répondant aux critères de recherches
     */	
    public function select($table, $champs){
        if($this->conn != null && $champs != null){
            switch($table){
                case "exemplaire" :
                    return $this->selectExemplairesRevue($champs['id']);
                case "commandedocument" :
                    return $this->selectCommandesDocument($champs['idLivreDvd']);
                case "abonnements" :
                    return $this->selectAbonnementsRevue($champs['idRevue']);
                case "utilisateur" :
                    return $this->selectUtilisateur($champs);
                default:                    
                    // cas d'un select sur une table avec recherche sur des champs
                    return $this->selectTableOnConditons($table, $champs);
            }
        }else{
                return null;
        }
    }

    /**
     * récupération de toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return lignes triées sur lebelle
     */
    public function selectTableSimple($table){
        $req = "select * from $table order by libelle;";		
        return $this->conn->query($req);	    
    }
    
    /**
     * récupération de toutes les lignes d'une table
     * @param string $table
     * @return toutes les lignes de la table
     */
    public function selectTable($table){
        $req = "select * from $table;";		
        return $this->conn->query($req);        
    }
    
    /**
     * récupération des lignes d'une table dont les champs concernés correspondent aux valeurs
     * @param type $table
     * @param type $champs
     * @return type
     */
    public function selectTableOnConditons($table, $champs){
        // construction de la requête
        $requete = "select * from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-3);
        return $this->conn->query($requete, $champs);
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres(){
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";		
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd(){
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";	
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues(){
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectExemplairesRevue($id){
        $param = array(
                "id" => $id
        );
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id = :id ";
        $req .= "order by e.dateAchat DESC";		
        return $this->conn->query($req, $param);
    }

    /**
     * récupération de tous les abonnements d'une revue
     *
     * @param [type] $idRevue
     * @return lignes de la requete
     */
    public function selectAbonnementsRevue($idRevue){
        $param = array(
            "idRevue" => $idRevue
        );
        $req = "Select a.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue ";
        $req .= "from abonnement a join commande c on a.id=c.id ";
        $req .= "where a.idRevue = :idRevue ";
        $req .= "order by c.dateCommande DESC";	
        return $this->conn->query($req, $param);
    }

    /**
     * récupération d'un utilisateur si les données correspondent
     *
     * @param [type] $champs
     * @return ligne de la requete
     */
    public function selectUtilisateur($champs)
    {
        $param = array(
            "mail" => $champs["mail"],
            "password" => $champs["password"]
        );
        $req = "Select u.id, u.nom, u.prenom, u.mail, u.idservice, s.libelle as service ";
        $req .= "from utilisateur u join service s on u.idservice=s.id ";
        $req .= "where u.mail = :mail ";
        $req .= "and u.password = :password ";
        $req .= "or u.nom = :mail ";
        $req .= "and u.password = :password";
        return $this->conn->query($req, $param);
    }

    /**
     * Retourne la plus grande id de la table commande
     *
     * @return lignes de la requete 
     */
    public function selectMaxCommande(){
        $req = "Select MAX(id) AS id FROM commande";
        return $this->conn->query($req);
    }

    /**
     * Retourne la plus grande id de la table livre
     *
     * @return lignes de la requete 
     */
    public function selectMaxLivre(){
        $req = "Select MAX(id) AS id FROM livre";
        return $this->conn->query($req);
    }

    /**
     * Retourne la plus grande id de la table dvd
     *
     * @return lignes de la requete 
     */
    public function selectMaxDvd(){
        $req = "Select MAX(id) AS id FROM dvd";
        return $this->conn->query($req);
    }

    /**
     * Retourne la plus grande id de la table revue
     *
     * @return lignes de la requete 
     */
    public function selectMaxRevue(){
        $req = "Select MAX(id) AS id FROM revue";
        return $this->conn->query($req);
    }

     /**
     * récupération de toutes les commandes d'une dvd_livre
     * @param string $idLivreDvd id du livre_dvd
     * @return lignes de la requete
     */
    public function selectCommandesDocument($idLivreDvd){
        $param = array(
                "idLivreDvd" => $idLivreDvd
        );
        $req = "Select cd.id, c.dateCommande, c.montant, cd.nbExemplaire, cd.idLivreDvd, ";
        $req .= "cd.idsuivi, s.etat ";
        $req .= "from commandedocument cd join commande c on cd.id=c.id ";
        $req .= "join suivi s on cd.idsuivi=s.id ";
        $req .= "where cd.idLivreDvd = :idLivreDvd ";
        $req .= "order by c.dateCommande DESC";	
        return $this->conn->query($req, $param);
    }

     /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */	
    public function delete($table, $champs){
        if($this->conn != null){
            // construction de la requête
            $requete = "delete from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

     /**
     * Suppresion de l'entitée composée livre dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function deleteLivre($champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsDvdLivre = [ "id" => $champs["Id"]];
        $champsLivre = [ "id" => $champs["Id"], "ISBN" => $champs["Isbn"],
                "auteur" => $champs["Auteur"], "collection" => $champs["Collection"]];
        $result = $this->delete("livre", $champsLivre);
        if ($result == null || $result == false){
            return null;
        }
        $result = $this->delete( "livres_dvd", $champsDvdLivre);
        if ($result == null || $result == false){
            return null;
        }
        return $this->delete("document", $champsDocument);
    }

     /**
     * Suppresion de l'entitée composée Dvd dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function deleteDvd($champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsDvdLivre = [ "id" => $champs["Id"]];
        $champsDvd = [ "id" => $champs["Id"], "synopsis" => $champs["Synopsis"],
                "realisateur" => $champs["Realisateur"], "duree" => $champs["Duree"]];
        $result = $this->delete("dvd", $champsDvd);
        if ($result == null || $result == false){
            return null;
        }
        $result = $this->delete( "livres_dvd", $champsDvdLivre);
        if ($result == null || $result == false){
            return null;
        }
        return $this->delete("document", $champsDocument);
    }

    /**
     * Suppresion de l'entitée composée commandeDocument dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function deleteCommande($champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsCommandeDocument = [ "id" => $champs["Id"], "nbExemplaire" => $champs["NbExemplaire"],
                "idLivreDvd" => $champs["IdLivreDvd"], "idsuivi" => $champs["IdSuivi"]];
        $result = $this->delete("commandedocument", $champsCommandeDocument);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->delete( "commande", $champsCommande);
    }

     /**
     * Suppresion de l'entitée composée revue dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function deleteRevue($champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsRevue = [ "id" => $champs["Id"], "periodicite" => $champs["Periodicite"],
                "delaiMiseADispo" => $champs["DelaiMiseADispo"]];
        $result = $this->delete("revue", $champsRevue);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->delete( "document", $champsDocument);
    }

    /**
     * Suppresion de l'entitée composée abonnement dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function deleteAbonnement($champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsAbonnement = [ "id" => $champs["Id"], "dateFinAbonnement" => $champs["DateFinAbonnement"],
                "idRevue" => $champs["IdRevue"]];
        $result = $this->delete("abonnement", $champsAbonnement);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->delete( "commande", $champsCommande);
    }

    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */	
    public function insertOne($table, $champs){
        if($this->conn != null && $champs != null){
            // construction de la requête
            $requete = "insert into $table (";
            foreach ($champs as $key => $value){
                $requete .= "$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ") values (";
            foreach ($champs as $key => $value){
                $requete .= ":$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ");";
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

    /**
     * Ajout de l'entitée composée livre dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertLivre($champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsDvdLivre = [ "id" => $champs["Id"]];
        $champsLivre = [ "id" => $champs["Id"], "ISBN" => $champs["Isbn"],
                "auteur" => $champs["Auteur"], "collection" => $champs["Collection"]];
        $result = $this->insertOne("document", $champsDocument);
        if ($result == null || $result == false){
            return null;
        }
        $result = $this->insertOne( "livres_dvd", $champsDvdLivre);
        if ($result == null || $result == false){
            return null;
        }
        return $this->insertOne("livre", $champsLivre);
    }

    /**
     * Ajout de l'entitée composée Dvd dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertDvd($champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsDvdLivre = [ "id" => $champs["Id"]];
        $champsDvd = [ "id" => $champs["Id"], "synopsis" => $champs["Synopsis"],
                "realisateur" => $champs["Realisateur"], "duree" => $champs["Duree"]];
        $result = $this->insertOne("document", $champsDocument);
        if ($result == null || $result == false){
            return null;
        }
        $result = $this->insertOne( "livres_dvd", $champsDvdLivre);
        if ($result == null || $result == false){
            return null;
        }
        return $this->insertOne("dvd", $champsDvd);
    }

     /**
     * Ajout de l'entitée composée commandeDocument dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertCommande($champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsCommandeDocument = [ "id" => $champs["Id"], "nbExemplaire" => $champs["NbExemplaire"],
                "idLivreDvd" => $champs["IdLivreDvd"], "idsuivi" => $champs["IdSuivi"]];
        $result = $this->insertOne("commande", $champsCommande);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->insertOne( "commandedocument", $champsCommandeDocument);
    }

     /**
     * Ajout de l'entitée composée revue dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertRevue($champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsRevue = [ "id" => $champs["Id"], "periodicite" => $champs["Periodicite"],
                "delaiMiseADispo" => $champs["DelaiMiseADispo"]];
        $result = $this->insertOne("document", $champsDocument);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->insertOne( "revue", $champsRevue);
    }

    /**
     * Ajout de l'entitée composée abonnement dans la bdd
     *
     * @param [type] $champs
     * @return void
     */
    public function insertAbonnement($champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsAbonnement = [ "id" => $champs["Id"], "dateFinAbonnement" => $champs["DateFinAbonnement"],
                "idRevue" => $champs["IdRevue"]];
        $result = $this->insertOne("commande", $champsCommande);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->insertOne( "abonnement", $champsAbonnement);         
    }

    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $param nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */
    public function updateOne($table, $id, $champs, $numero = null){
        if($this->conn != null && $champs != null){
            // construction de la requête
            $requete = "update $table set ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);				
            $champs["id"] = $id;
            $requete .= " where id=:id;";
            if($numero != null)
            {
                $requete = substr($requete, 0, strlen($requete)-1);				
                $champs["numero"] = $numero;
                $requete .= " and numero=:numero;";
            }				
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

     /**
     * Modification de l'entitée composée livre dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @param [type] $id de l'element
     * @return true si l'ajout a fonctionné
     */
    public function updateLivre($id, $champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsDvdLivre = [ "id" => $champs["Id"]];
        $champsLivre = [ "id" => $champs["Id"], "ISBN" => $champs["Isbn"],
                "auteur" => $champs["Auteur"], "collection" => $champs["Collection"]];
        $result = $this->updateOne("document", $id, $champsDocument);
        if ($result == null || $result == false){
            return null;
        }
        $result = $this->updateOne( "livres_dvd", $id, $champsDvdLivre);
        if ($result == null || $result == false){
            return null;
        }
        return $this->updateOne("livre", $id, $champsLivre);
    }

    /**
     * Modification de l'entitée composée Dvd dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @param [type] $id de l'element
     * @return true si l'ajout a fonctionné
     */
    public function updateDvd($id, $champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsDvdLivre = [ "id" => $champs["Id"]];
        $champsDvd = [ "id" => $champs["Id"], "synopsis" => $champs["Synopsis"],
            "realisateur" => $champs["Realisateur"], "duree" => $champs["Duree"]];
        $result = $this->updateOne("document", $id, $champsDocument);
        if ($result == null || $result == false){
            return null;
        }
        $result = $this->updateOne( "livres_dvd", $id, $champsDvdLivre);
        if ($result == null || $result == false){
            return null;
        }
        return $this->updateOne("dvd", $id, $champsDvd);
    }

    /**
     * Modification de l'entitée composée CommandeDocument dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @param [type] $id de l'element
     * @return true si l'ajout a fonctionné
     */
    public function updateCommande($id, $champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsCommandeDocument = [ "id" => $champs["Id"], "nbExemplaire" => $champs["NbExemplaire"],
                "idLivreDvd" => $champs["IdLivreDvd"], "idsuivi" => $champs["IdSuivi"]];
        $result = $this->updateOne("commande",$id, $champsCommande);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->updateOne( "commandedocument",$id, $champsCommandeDocument);
    }

     /**
     * Modification de l'entitée composée revue dans la bdd
     *
     * @param [type] $champs nom et valeur de chaque champs de la ligne
     * @param [type] $id de l'element
     * @return true si l'ajout a fonctionné
     */
    public function updateRevue($id, $champs)
    {
        $champsDocument = [ "id" => $champs["Id"], "titre" => $champs["Titre"],
            "image" => $champs["Image"] , "idRayon" => $champs["IdRayon"],
            "idPublic" => $champs["IdPublic"], "idGenre" => $champs["IdGenre"]];
        $champsRevue = [ "id" => $champs["Id"], "periodicite" => $champs["Periodicite"],
                "delaiMiseADispo" => $champs["DelaiMiseADispo"]];
        $result = $this->updateOne("document", $id, $champsDocument);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->updateOne( "revue",$id, $champsRevue);
    }

    /**
     * Modification de l'entitée composée abonnement dans la bdd
     *
     * @param [type] $id
     * @param [type] $champs
     * @return void
     */
    public function updateAbonnement($id, $champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsAbonnement = [ "id" => $champs["Id"], "dateFinAbonnement" => $champs["DateFinAbonnement"],
                "idRevue" => $champs["IdRevue"]];
        $result = $this->updateOne("commande", $id, $champsCommande);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->updateOne( "abonnement",$id, $champsAbonnement); #updateExemplaire
    }

     /**
     * Modification de l'entitée composée abonnement dans la bdd
     *
     * @param [type] $id
     * @param [type] $champs
     * @return void
     */
    public function updateExemplaire($id, $champs)
    {
        $champsCommande = [ "id" => $champs["Id"], "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]];
        $champsAbonnement = [ "id" => $champs["Id"], "dateFinAbonnement" => $champs["DateFinAbonnement"],
                "idRevue" => $champs["IdRevue"]];
        $result = $this->updateOne("commande", $id, $champsCommande);
        if ($result == null || $result == false){
            return null;
        }
        return  $this->updateOne( "abonnement",$id, $champsAbonnement);
    }

}