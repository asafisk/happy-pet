<?php

namespace app;

use app\DataStore;

class Pet
{
    /**
     * Database connection object
     * 
     * @var DataStore
     */
    private $db;
    
    /**
     * Pet id
     * 
     * @var int
     */
    private $id;
    
    /**
     * Pet name
     * 
     * @var string
     */
    private $name;
    
    /**
     * Pet type id
     * 
     * @var int
     */
    private $type;
    
    /**
     * Pet owner
     * 
     * @var int
     */
    private $user_id;
    
    /**
     * Game over reason
     * 
     * @var string
     */
    private $fatal_state;
    
    /**
     * Pet type name
     * 
     * @var string
     */
    private $type_name;
    
    /**
     * Pet type feed frequency modifier
     * 
     * @var int
     */
    private $feed_coef = 1;
    
    /**
     * Pet type stroke frequency modifier
     * 
     * @var int
     */
    private $stroke_coef = 1;
    
    /**
     * Standard date format for database operations
     * 
     * @var string
     */
    private $date_format = 'Y-m-d H:i:s';
    
    /**
     * Game config
     * 
     * @var Config
     */
    protected $config;
    
    /**
     * Constructor
     *
     * @param  int  $pet_id
     * @param  Config  $config
     * @return void
     */
    function __construct(int $pet_id, Config $config)
    {
        $this->db = DataStore::getInstance();
        if ($pet_id > 0) {
            $pet = $this->fetch($pet_id);
            if ($pet) {
                $this->id = $pet[0]['pet_id'];
                $this->name = $pet[0]['pet_name'];
                $this->type = $pet[0]['pet_type_id'];
                $this->user_id = $pet[0]['user_id'];
                $this->fatal_state = $pet[0]['fatal_state'];
                $this->type_name = $pet[0]['pet_type_name'];
                if ($pet[0]['feed_coefficient'] > 0) {
                    $this->feed_coef = $pet[0]['feed_coefficient'];
                }
                if ($pet[0]['stroke_coefficient'] > 0) {
                    $this->stroke_coef = $pet[0]['stroke_coefficient'];
                }
            }
        }
        $this->config = $config;
    }
    
    /**
     * Get the Pet Id
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get the Pet Name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get the Pet Type Name
     * 
     * @return string
     */
    public function getPetTypeName()
    {
        return $this->pet_type_name;
    }
    
    /**
     * Get the pet owners User Id
     * 
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * Get Fatal (game over) state
     * 
     * @return string
     */
    protected function getFatalState()
    {
        return $this->fatal_state;
    }
    
    /**
     * Feed the pet
     * 
     * @return int
     */
    public function feed()
    {
        return $this->logEvent('feed');
    }
    
    /**
     * Stroke the pet
     * 
     * @return int
     */
    public function stroke()
    {
        return $this->logEvent('stroke');
    }
    
    /**
     * Log an event
     * 
     * @param  string  $type
     * @return int
     */
    private function logEvent(string $type)
    {
        $event_datetime = (new \DateTime())->format($this->date_format);
        $record = [
            'pet_id' => $this->id,
            'event_type' => $type,
            'event_datetime' => $event_datetime
        ];
        return $this->db->insert($record, 'pet_event');
    }
    
    /**
     * Get the overall status of a pet
     * 
     * @return object
     */
    public function status()
    {
        //Check for fatality
        if ($this->fatal_state == 'feed') {
            return $this->config::hungryStatusLabel()[5];
        }
        if ($this->fatal_state == 'stroke') {
            return $this->config::happyStatusLabel()[5];
        }
        //Get the current hungry and happy status
        $feed_status = $this->state(
            'feed', 
            $this->config::overfeed(),
            $this->feed_coef,
            $this->config::hungryStatusLabel(),
            $this->config::feedScale()
        );
        $stroke_status = $this->state(
            'stroke', 
            $this->config::overstroke(),
            $this->stroke_coef,
            $this->config::happyStatusLabel(),
            $this->config::strokeScale()
        );
        $return = new \stdClass();
        $return->feed_status = $feed_status;
        $return->stroke_status = $stroke_status;
        return $return;
    }
    
    /**
     * Get the state for a given metric type
     * 
     * @param  string  $type
     * @param  array  $over_config
     * @param  float  $coefficient
     * @param  array  $label_config
     * @param  array  $scale
     * @return string
     */
    protected function state(
        string $type,
        array $over_config,
        float $coefficient,
        array $label_config,
        array $scale
    ) {
        //Check for over feeding
        $state = $this->excess($type, $over_config, $coefficient);
        if ($state !== false) {
            return $label_config[$state];
        }
        //Check on scale
        return $this->checkScale(
            $this->latestEvent($type), 
            $label_config,
            $scale,
            $coefficient,
            $type
        );
    }
    
    /**
     * Get metric state on a given scale and return the appropriate state label
     * 
     * @param  float  $hours_ago
     * @param  array  $status_label
     * @param  array  $scale
     * @param  float  $coefficient
     * @return string
     */
    protected function checkScale(
        float $hours_ago,
        array $status_label,
        array $scale,
        float $coefficient,
        string $type
    ) {
        foreach ($scale as $k => $v) {
            if (
                $hours_ago >= $scale[$k] / $coefficient
                && (
                    $k === max(array_keys($scale))
                    || $scale[$k + 1] / $coefficient > $hours_ago 
                )
            ) {
                if ($k === 5) {
                    $this->fatality($type);
                }
                return $status_label[$k];
            }
        }
        throw new \Exception('Unknown pet status code');
    }
    
    /**
     * Check for excessive actions and return the appropriate state label
     * 
     * @param  string  $type
     * @param  array  $checks
     * @param  float  $coefficient
     * @return string
     */
    private function excess(string $type, array $checks, float $coefficient)
    {
        foreach ($checks as $check) {
            $hours = $check['hours'] / $coefficient;
            $result = $this->db->query('
                SELECT
                    COUNT(pet_id) AS count 
                FROM
                    pet_event
                WHERE
                    pet_id = ' . intval($this->id) . '
                    AND event_type = \'' . $type . '\'
                    AND datetime(event_datetime) > datetime(\'' . $this->dateStringAgo($hours) . '\')
            ');
            if ($result[0]['count'] >= $check['limit']) {
                return $check['state'];
            }
        }
        return false;
    }
    
    /**
     * Get the latest event of a given type
     * 
     * @param  string  $type
     * @return string
     */
    public function latestEvent(string $type) {
        $latest = $this->db->query('
            SELECT
                MAX(datetime(event_datetime)) AS latest
            FROM
                pet_event
            WHERE
                pet_id = ' . intval($this->id) . '
                AND event_type = \'' . $type . '\'
        ')[0]['latest'];
        return (
            (new \DateTime())->getTimestamp() - (new \DateTime($latest))->getTimestamp()
        ) / 3600;
    }
    
    /**
     * Set a fatal flag for the pet
     * 
     * @param  string  $value
     * @return bool
     */
    protected function fatality(string $value)
    {
        return $this->db->update(['fatal_state' => $value], 'pet', ['pet_id' => $this->id]);
    }
    
    /**
     * Fetch the pet record
     * 
     * @param  int  $pet_id
     * @return string
     */
    protected function fetch(int $pet_id)
    {
        return $this->db->query('
            SELECT 
                pet.pet_id, pet.pet_name, pet.pet_type_id, pet.user_id, pet.fatal_state, 
                pet_type.pet_type_name, pet_type.feed_coefficient, pet_type.stroke_coefficient 
            FROM
                pet
                INNER JOIN pet_type ON pet_type.pet_type_id = pet.pet_type_id
            WHERE
                pet.pet_id = ' . $pet_id . '
        ');
    }
    
    /**
     * Convert hours into the datetime string for that many hours ago.
     * 
     * @param  int  $hours
     * @return string
     */
    private function dateStringAgo(int $hours)
    {
        $seconds = $hours * 3600;
        return (new \DateTime())
                    ->sub(new \DateInterval('PT' . $seconds . 'S'))
                    ->format($this->date_format);
    }
    
    /**
     * Register a new pet.
     * 
     * @param  string  $pet_name
     * @param  int  $pet_type_id
     * @return int
     */
    public function createPet(int $user_id, string $pet_name, int $pet_type_id)
    {
        $record = [
            'pet_name' => preg_replace('/[^a-zA-Z0-9- ]/','', $pet_name),
            'pet_type_id' => $pet_type_id,
            'user_id' => $user_id
        ];
        $pet_id = $this->db->insert($record, 'pet');
        //Insert feed and stroke events for normal status.
        if ($pet_id > 0) {
            $event_datetime = (new \DateTime())->format($this->date_format);
            $record = [
                'pet_id' => $pet_id,
                'event_type' => 'feed',
                'event_datetime' => $event_datetime
            ];
            $this->db->insert($record, 'pet_event');
            $record = [
                'pet_id' => $pet_id,
                'event_type' => 'stroke',
                'event_datetime' => $event_datetime
            ];
            $this->db->insert($record, 'pet_event');
        }
        return $pet_id;
    }
    
    /**
     * De-Register a pet.
     * 
     * @return bool
     */
    public function deRegister() {
        return $this->db->delete('pet', ['pet_id' => $this->id]);
    }
}

?>