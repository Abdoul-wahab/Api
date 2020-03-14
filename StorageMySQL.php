<?php

class StorageMySQL
{
    private $sql = "
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(255) DEFAULT NULL,
  `severity` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `tickets`(`id`, `date`, `severity`, `description`) VALUES (NULL,'daltyut', 'ogûebvqj','description de mon ticket');
--
-- Dumping data for table `tickets`
--
LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
    ";

    private $db;
    private $init;
    public $responseAPI= array();

    /**
     * StorageMySQL constructor.
     */
    public function __construct()
    {
        try{
            $this->db = new PDO('mysql:host='.HOSTNAME.';port='.DATABASEPORT.';dbname='.DATABASE.';charset=utf8', USERDATABASE, PSWDDATABASE);
            //$this->apiResult(true, "Connexion à MySQL réussie !");

        }
        catch (Exception $e){
            die('Erreur : ' . $e->getMessage());
        }
    }

    //création de la table
    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }


    // Lecture de l'élément dont le id est passé en paramètre
    public function read($id)
    {

        if($this->isValid($id)) {
            $stmt = $this->db->prepare('SELECT * FROM tickets WHERE id = :id');
            $stmt->execute(array('id' => $id));
            $result = $stmt->fetch();
            $response['id'] =$result['id'];
            $response['date'] =$result['date'];
            $response['severity'] =$result['severity'];
            $response['description'] =$result['description'];

            $this->apiResult(true, $response);
        }else{
            $this->apiResult(false,"Identifiant n'existe pas ");
        }

    }

    //Lire Tout les élements de la bd
    public function readAll()
    {
        $stmt= $this->db->prepare('SELECT * FROM tickets');
        if($stmt->execute()){
            $result = $stmt->fetchAll();
            $response = array();
            foreach ($result  as $key => $item){
                $response[$key]['id'] =$item['id'];
                $response[$key]['date'] =$item['date'];
                $response[$key]['severity'] =$item['severity'];
                $response[$key]['description'] =$item['description'];
            }
            $this->apiResult(true,$response);
        }
    }

    //Enregistrement en bd de l'élément passé en paramètre
    public function create($data)
    {
        // TODO: Implement create() method.
        $stmt= $this->db->prepare('INSERT INTO tickets (date, severity, description) VALUES (:name, :number, :description)');
        if($stmt->execute(array('date'=> $this->noScript($data['date']), 'severity'=> $this->noScript($data['severity']), 'description'=> $this->noScript($data['description']))))
            $this->apiResult(true,"Bien Envoiyés !");
        else $this->apiResult(false,"Erreur lors de l'ajout du ticket !");
    }

    // modification des infos de l'élément passé en paramètre avec son id
    public function update($id, $data)
    {
        // TODO: Implement update() method.
        if($this->isValid($id)) {
            $stmt = $this->db->prepare('UPDATE tickets SET date= :date, severity= :severity, description= :description WHERE id= :id');
            if ($stmt->execute(array('date' => $this->noScript($data['date']), 'severity' => $this->noScript($data['severity']), 'description' => $this->noScript($data['description']), 'id' => $id)))
                $this->apiResult(true, "Mise à jour éffectuée !");
            else $this->apiResult(false, 'Erreur lors de la mise à jour du ticket !');
        }else{
            $this->apiResult(false,"Identifiant n'existe pas ");
        }
    }

    //suppression de la bd de l'élemnt dont le id es passé en paramètre
    public function delete($id)
    {
        // TODO: Implement delete() method.
        if($this->isValid($id)){
            $stmt= $this->db->prepare('DELETE FROM tickets WHERE id= :id');
            if($stmt->execute(array('id'=> $id)))
                $this->apiResult(true,"Suppression éffectuée !");
            else $this->apiResult(false,"Identifiant inconnu ");
        }else{
            $this->apiResult(false,"Identifiant n'existe pas ");
        }
    }

    //Generation de 100 modules au avec du contenu
    public function generate(){
        $faker = Faker\Factory::create();
        for($i=0; $i<99; $i++) {
            $stmt = $this->db->prepare('INSERT INTO tickets(date, severity, description) VALUES (:date, :severity, :description)');
            $stmt->execute(array('date'=> $this->noScript($faker->uuid), 'severity'=> $this->noScript($faker->numberBetween(1000,9000)), 'description'=> $this->noScript($faker->paragraph(4,true))));
        }
        $this->apiResult(true,"Bien éffectué !");
    }

    //fonction de Vérification des informations(pour éviter d'enrégistrer des données dangéreux: faille xss)
    public function noScript($value){
        $value= htmlentities($value);
        return $value;
    }
    /*creation d'une tables initilisée avec un prémier élement la dase de données*/
    public function initDB(){
        if(!$this->init){
            $this->db->query($this->getSql())->execute();
            $this->init = true;
        }
    }

    public function isValid($id){
        $stmt= $this->db->prepare('SELECT * FROM tickets');
        $stmt->execute();
        $result = $stmt->fetchAll();
        //var_dump($result);
        foreach($result as $key => $item){
            if($id === $key){
                return true;
            }
        }
        return false;
    }

    /*Parssinig vers du json*/
    public function apiResult($success, $response){
        $this->responseAPI['success'] = $success;
        $this->responseAPI['response'] = $response;
        //var_dump($this->responseAPI);
        echo json_encode($this->responseAPI);
    }

}
