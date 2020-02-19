<?php

namespace app;

use app\DataStore;
use app\Pet;

class User 
{
    /**
     * Database connection object
     * 
     * @var DataStore
     */
    private $db;
    
    /**
     * User Id
     * 
     * @var int
     */
    private $id;
    
    /**
     * User Name
     * 
     * @var string
     */
    private $name;
    
    /**
     * User's pets
     * 
     * @var array
     */
    private $pets;
    
    /**
     * Constructor
     *
     * @param  int  $id
     * @return void
     */
    function __construct(int $id = 0)
    {
        $this->db = DataStore::getInstance();
        if ($id > 0) {
            $user = $this->fetch($id);
            if ($user) {
                $this->id = $user[0]['user_id'];
                $this->name = $user[0]['name'];
            }
        }
    }
    
    /**
     * get user id
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get user name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Fetch user data
     *
     * @param  int  $id
     * @return array
     */
    public function fetch(int $id)
    {
        return $this->db->select(
            ['user_id','name'],
            'user',
            ['user_id' => $id]
        );
    }
    
    /**
     * Create a new user
     *
     * @param  string  $name
     * @return int
     */
    public function createUser(string $name)
    {
        $record = [
            'name' => preg_replace('/[^a-zA-Z0-9- ]/','', $name)
        ];
        return $this->db->insert($record, 'user');
    }
    
    /**
     * Fetch all pets
     * 
     * @return array
     */
    public function fetchPets() {
        return $this->db->query('
            SELECT 
                pet.pet_id, pet.pet_name, 
                pet_type.pet_type_name
            FROM
                pet
                INNER JOIN pet_type ON 
                    pet_type.pet_type_id = pet.pet_type_id 
            WHERE
                pet.user_id = ' . intval($this->id) . '
        ');
    }
}

?>