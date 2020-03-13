<?php

namespace tests;
require '..' . DIRECTORY_SEPARATOR . 'autoload.php';

use app\ConfigDefault;
use app\Pet;
use PHPUnit\Framework\TestCase;

class PetTest extends TestCase
{
    public $db;
    public $config;

    public function setup() : void
    {

    }
    
    public function teardown() : void
    {

    }
    
    public function testFetchAndGetters()
    {
        $db = $this->createMock('app\DataStore');
        $config = new ConfigDefault();
        $dummy = array(0 => array(
            'pet_id' => 1,
            'pet_name' => 'John',
            'pet_type_id' => 2,
            'user_id' => 1,
            'fatal_state' => null,
            'pet_type_name' => 'Dog',
            'feed_coefficient' => 2,
            'stroke_coefficient' => 10
        ));

        $db->expects($this->once())
            ->method('query')
            ->with($this->stringContains('pet_type.pet_type_name, pet_type.feed_coefficient, pet_type.stroke_coefficient'))
            ->will($this->returnValue($dummy));

        $pet = new Pet(1, $config, $db);
        $this->assertEquals(1, $pet->getId());
        $this->assertEquals('John', $pet->getName());
        $this->assertEquals('Dog', $pet->getPetTypeName());
        $this->assertEquals(1, $pet->getUserId());

    }

    /*
    public function testDeRegister()
    {
        
    }

    public function testStatus()
    {
        
    }

    public function testCreatePet()
    {
        
        
    }

    public function testGetId()
    {

    }

    public function testFeed()
    {

    }

    public function testGetPetTypeName()
    {

    }

    public function testStroke()
    {

    }

    public function testGetName()
    {

    }

    public function testLatestEvent()
    {

    }

    public function testGetUserId()
    {

    }

    public function test__construct()
    {

    }
    */
}
