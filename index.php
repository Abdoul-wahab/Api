<?php

/*
 * On indique que les chemins des fichiers qu'on inclut
 * seront relatifs au répertoire Api.
 */
set_include_path("./Api");
require_once 'vendor/autoload.php';
require_once("StorageMySQL.php");
require_once('config.php');
header('Content-Type: application/json');

$storage = new StorageMySQL();
/*La ligne suivante est à commenter après prémière exéécution du code
* elle perment de réinitialiser la bd */

//$storage->initDB();

/* Analyse de l'URL */
$action = key_exists('action', $_GET)? $_GET['action']: null;
$id = key_exists('id', $_GET)? $_GET['id']: null;


if(key_exists('date', $_POST) || key_exists('severity', $_POST) || key_exists('description', $_POST)){

    $data = array();

    $data['date'] = key_exists('date', $_POST)? $_POST['date']: '';
    $data['severity'] = key_exists('severity', $_POST)? $_POST['severity']: '';
    $data['description'] = key_exists('description', $_POST)? $_POST['description']: '';

}


try{
    switch ($action) {
        case "list":
            $storage->readAll();
            break;
        case "populate":
            $storage->generate();
            break;

        case "delete":
            if($id === null)
                $storage->apiResult(false, "identifiant inconnu ");
            else
                $storage->delete($id);
            break;
        case "read":
            if($id === null)
                $storage->apiResult(false, "identifiant inconnu ");
            else
                $storage->read($id);
            break;

        case "update":
            if($id === null)
                $storage->apiResult(false, "identifiant inconnu ");
            else
                if(isset($data))
                    $storage->update($id, $data);
                else $storage->apiResult(false, "Pas de données envoyées par POST");
            break;
        case "create":
            if(isset($data))
                $storage->create($data);
            else $storage->apiResult(false, "Pas de données envoyées par POST");
            break;
        default:
            $storage->apiResult(false, "Action inconnue ");
            break;
    }
}
catch (Exception $e) {
    /* Si on arrive ici, il s'est passé quelque chose d'imprévu
       * (par exemple un problème de base de données) */
    $storage->apiResult(false, "Une Erreur s'est produite ");
}


