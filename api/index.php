<?php

use app\DataStore;

require '..' . DIRECTORY_SEPARATOR . 'autoload.php';

$config = new app\ConfigDefault();
$db = DataStore::getInstance();

//Basic user validation
$user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
$user = new app\User($user_id, $db);
$user_id = $user->getId();
if (!$user_id && (!isset($_GET['action']) || $_GET['action'] !== 'register-user')) {
    echo (new view\Json('Please define a user in the request'))->out(401);
    exit;
}

//Basic pet validation
$pet_id = isset($_GET['pet']) ? intval($_GET['pet']) : 0;
$pet = new app\Pet($pet_id, $config, $db);
if ($pet->getId() > 0 && $pet->getUserId() !== $user_id) {
    echo (new view\Json('Hey, that\'s not your pet!'))->out(403);
    exit;
}

//Handle the request
try {
    $response = (new api\Router())::response($_SERVER['REQUEST_METHOD'], $_GET, $_POST, $user, $pet);
} catch(Exception $e) {
    $response = (new view\Json($e->getMessage()))->out(500);
}
echo $response;
exit;

?>