<?php
include_once("AccessBDD.php");

/**
 * Contrôleur : reçoit et traite les demandes du point d'entrée
 */
class Controle{
	
    private $accessBDD;

    /**
     * Constructeur : récupération de l'instance d'accès à la BDD
     */
    public function __construct(){
        try{
            $this->accessBDD = new AccessBDD();
        }catch(Exception $e){
            $this->reponse(500, "erreur serveur");
            die();
        }
    }

    /**
     * réponse renvoyée (affichée) au client au format json
     * @param int $code code standard HTTP
     * @param string $message message correspondant au code
     * @param array $result résultat de la demande 
     */
    private function reponse($code, $message, $result=""){
        $retour = array(
            'code' => $code,
            'message' => $message,
            'result' => $result
        );
        echo json_encode($retour, JSON_UNESCAPED_UNICODE);
    }

    /**
     * requete arrivée en GET (select)
     * @param string $table nom de la table
     * @param type $champs nom et valeur des champs de recherche
     */
    public function get($table, $champs){
        $result = null;
        if ($champs==""){
            $result = $this->accessBDD->selectAll($table);
        }else{
            $result = $this->accessBDD->select($table, $champs);
        }
        if (gettype($result) != "array" && ($result == false || $result == null)){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK", $result);
        }
    }

    /**
     * requete arrivée en DELETE
     * @param string $table nom de la table
     * @param array $champs nom et valeur des champs
     */
    public function delete($table, $champs){
        $result = null;
        if ($table == "livre"){
            $result = $this->accessBDD->deleteLivre($champs);	
        }elseif ($table == "dvd"){
            $result = $this->accessBDD->deleteDvd($champs);
        }elseif ($table == "revue"){
            $result = $this->accessBDD->deleteRevue($champs);
        }elseif ($table == "commandedocument"){
            $result = $this->accessBDD->deleteCommande($champs);
        }elseif ($table == "abonnement"){
            $result = $this->accessBDD->deleteAbonnement($champs);
        }else{
            $result = $this->accessBDD->delete($table, $champs);
        }
        if ($result == null || $result == false ){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }

    /**
     * requete arrivée en POST (insert)
     * @param string $table nom de la table
     * @param array $champs nom et valeur des champs
     */
    public function post($table, $champs){
        $result = null;
        if ($table == "livre"){
            $result = $this->accessBDD->insertLivre($champs);	
        }elseif ($table == "dvd"){
            $result = $this->accessBDD->insertDvd($champs);
        }elseif ($table == "revue"){
            $result = $this->accessBDD->insertRevue($champs);
        }elseif ($table == "commandedocument"){
            $result = $this->accessBDD->insertCommande($champs);
        }elseif ($table == "abonnement"){
            $result = $this->accessBDD->insertAbonnement($champs);
        }else{
            $result = $this->accessBDD->insertOne($table, $champs);	
        }
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }


    /**
     * requete arrivée en PUT (update)
     * @param string $table nom de la table
     * @param string $id valeur de l'id
     * @param array $champs nom et valeur des champs
     */
    public function put($table, $id, $champs){
        $result = null;
        if ($table == "livre"){
            $result = $this->accessBDD->updateLivre($id, $champs);	
        }elseif ($table == "dvd"){
            $result = $this->accessBDD->updateDvd($id, $champs);
        }elseif ($table == "revue"){
            $result = $this->accessBDD->updateRevue($id, $champs);
        }elseif ($table == "commandedocument"){
            $result = $this->accessBDD->updateCommande($id, $champs);
        }elseif ($table == "abonnement"){
            $result = $this->accessBDD->updateAbonnement($id, $champs);
        }elseif ($table == "exemplaire"){
            $result = $this->accessBDD->updateOne($table, $id, $champs, $champs["Numero"]);
        }else{
            $result = $this->accessBDD->updateOne($table, $id, $champs);
        }	
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }

	
    /**
     * login et/ou pwd incorrects
     */
    public function unauthorized(){
        $this->reponse(401, "authentification incorrecte");
    }
}