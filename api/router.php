<?php

namespace api;

class Router
{
    public static function response($request_method, $get, $post, \app\User $user, \app\Pet $pet)
    {
        if (!isset($get['action'])) {
            throw new \Exception('Action undefined');
        }
        //GETs
        if ($request_method === 'GET') {
            switch ($get['action']) {
                case 'listpets':
                    echo (new \view\Json($user->fetchPets()))->out();
                    break;
                case 'status':
                    echo (new \view\Json($pet->status()))->out();
                    break;
            }
            exit;
        }
        //POSTs
        if ($request_method === 'POST' && $user->getId() > 0) {
            switch ($get['action']) {
                case 'feed':
                    echo (new \view\Json($pet->feed()))->out();
                    break;
                case 'stroke':
                    echo (new \view\Json($pet->stroke()))->out();
                    break;
                case 'register-pet':
                    echo (new \view\Json($pet->createPet($user->getId(), $post['pet_name'], $post['pet_type_id'])))->out();
                    break;
                case 'register-user':
                    echo (new \view\Json($user->createUser($post['name'])))->out();
                    break;
            }
            exit;
        }
        //DELETEs
        if ($request_method === 'DELETE') {
            switch ($get['action']) {
                case 'deregister':
                    echo (new \view\Json($pet->deRegister()))->out();
                    break;
            }
            exit;
        }
        //Anything left over
        throw new \Exception('Action or request not recognised');
    }
}

?>