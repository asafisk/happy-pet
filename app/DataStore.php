<?php

namespace app;

class DataStore
{
    /**
     * Sqlite data filename
     */
    const DATA_FILE = 'pets.db';
    
    /**
     * Sqlite data directory
     */
    const DATA_DIR = 'data';
    
    /**
     * Data object 
     */
    private $pdo;
    
    /**
     * Singleton instance
     */
    private static $instance;
    
    /**
     * Constructor establishes a connection 
     */
    private function __construct()
    {
        try {
            $this->pdo = new \SQLite3(
                __DIR__ . DIRECTORY_SEPARATOR . self::DATA_DIR
                . DIRECTORY_SEPARATOR . self::DATA_FILE
            );
        } catch (\Exception $e) {
            echo 'SQLite Connection Failed: ' . $e->getMessage();
            return false;
        }
        //Check for no tables and initialise if needed /
        if ($this->tableCount() === 0) {
            $this->initializeDatabase();
        }
    }
    
    /**
     * Return a single(ton) instance of the data object connection
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DataStore();
        }
        return self::$instance;
    }

    /**
     * Check version
     */
    public function version()
    {
        $version = $this->pdo->querySingle('SELECT SQLITE_VERSION()');
        return $version;   
    }

    /**
     * Select records
     */
    public function select(array $fields, string $table, array $where = [])
    {
        $field_list = implode(', ', $fields);
        $where_binders = empty($where) ? '1' : $this->arrayToBind($where, ' AND ');
        $statement = $this->pdo->prepare('
            SELECT
                ' . $field_list . ' 
            FROM
                `' . $table . '`
            WHERE
                ' . $where_binders . '
        ');
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $statement->bindValue(':' . $k, $v);
            }
        }
        $result = $statement->execute();
        $result_array = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $result_array[] = $row;
        }
        return (array) $result_array;
    }
    
    /**
     * Insert a record
     */
    public function insert(array $record, string $table)
    {
        $fields = implode(', ', array_keys($record));
        $field_binders = ':' . implode(', :', array_keys($record));
        $statement = $this->pdo->prepare('
            INSERT INTO ' . $table . ' ( ' . $fields . ')
            VALUES ( ' . $field_binders . ')
        ');
        foreach ($record as $k => $v) {
            $statement->bindValue(':' . $k, $v);
        }
        $statement->execute();
        return $this->pdo->lastInsertRowid();
    }
    
    /**
     * Delete record(s)
     */
    public function delete(string $table, array $where)
    {
        $where_binders = empty($where) ? '1' : $this->arrayToBind($where, ' AND ');
        $statement = $this->pdo->prepare('
            DELETE FROM
                `' . $table . '`
            WHERE
                ' . $where_binders . '
        ');
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $statement->bindValue(':' . $k, $v);
            }
        }
        return $statement->execute();
    }
    
    /**
     * Update record(s)
     */
    public function update(array $values, string $table, array $where)
    {
        if (empty($values)) {
            return false;
        }
        $value_binders = $this->arrayToBind($values, ' = ');
        $where_binders = empty($where) ? '1' : $this->arrayToBind($where, ' AND ');
        $statement = $this->pdo->prepare('
            UPDATE
                `' . $table . '`
            SET
                ' . $value_binders . '
            WHERE
                ' . $where_binders . '
        ');
        foreach ($values as $k => $v) {
            $statement->bindValue(':' . $k, $v);
        }
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $statement->bindValue(':' . $k, $v);
            }
        }
        return $statement->execute();
    }
    
    /**
     * General query
     */
    public function query($query)
    {
        $result = $this->pdo->query($query);
        $result_array = [];
        if ($result) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $result_array[] = $row;
            }
        }
        return $result_array;
    }
    
    /**
     * Convert an array of field => value to a bindable string
     */
    protected function arrayToBind(array $array, string $glue) {
        $binders = [];
        foreach ($array as $k => $v) {
            $binders[] = $k . ' = :' . $k;
        }
        return implode($glue, $binders);
    }
    
    /**
     * Check table count
     */
    private function tableCount()
    {
        $result = $this->pdo->query('
            SELECT 
                name
            FROM 
                sqlite_master 
            WHERE 
                type = \'table\' 
                AND name NOT LIKE \'sqlite_%\'
        ');
        $tables = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tables[]['name'] = $row['name'];
        }
        return count($tables);
    }
    
    /**
     * Reset the database
     */
    public function resetDatabase()
    {
        $this->pdo->query('DROP TABLE IF EXISTS user');
        $this->pdo->query('DROP TABLE IF EXISTS pet');
        $this->pdo->query('DROP TABLE IF EXISTS pet_type');
        $this->pdo->query('DROP TABLE IF EXISTS pet_event');
        $this->pdo->query('DROP TABLE IF EXISTS hungry_level');
        $this->pdo->query('DROP TABLE IF EXISTS happy_level');
    }
    
    /**
     * Initialise the database
     */
    public function initializeDatabase()
    {
        //User table
        $this->pdo->query('
            CREATE TABLE `user` (
                user_id INTEGER PRIMARY KEY,
                name TEXT DEFAULT \'Anon\'
            )
        ');
        $this->pdo->query('
            INSERT INTO `user` (name) VALUES (\'Asa\');
        ');
        
        //Pet table and indexes
        $this->pdo->query('
            CREATE TABLE `pet` (
                pet_id INTEGER PRIMARY KEY,
                pet_name TEXT DEFAULT \'Fluffy\',
                pet_type_id INTEGER, 
                user_id INTEGER,
                fatal_state TEXT
            )
        ');
        $this->pdo->query('
            CREATE INDEX idx_pet_type_id 
            ON pet(pet_type_id);
        ');
        $this->pdo->query('
            CREATE INDEX idx_user_id 
            ON pet(user_id);
        ');
        
        //Pet type table
        $this->pdo->query('
            CREATE TABLE `pet_type` (
                pet_type_id INTEGER PRIMARY KEY,
                pet_type_name TEXT,
                feed_coefficient REAL, 
                stroke_coefficient REAL
            )
        ');
        $this->pdo->query('INSERT INTO pet_type (pet_type_id, pet_type_name, feed_coefficient, stroke_coefficient) VALUES (1,\'Dog\',\'2\',\'10\')');
        $this->pdo->query('INSERT INTO pet_type (pet_type_id, pet_type_name, feed_coefficient, stroke_coefficient) VALUES (2,\'Cat\',\'1\',\'20\')');
        $this->pdo->query('INSERT INTO pet_type (pet_type_id, pet_type_name, feed_coefficient, stroke_coefficient) VALUES (3,\'Bird\',\'10\',\'1\')');
        $this->pdo->query('INSERT INTO pet_type (pet_type_id, pet_type_name, feed_coefficient, stroke_coefficient) VALUES (4,\'Snake\',\'0.1\',\'0.1\')');
        $this->pdo->query('INSERT INTO pet_type (pet_type_id, pet_type_name, feed_coefficient, stroke_coefficient) VALUES (5,\'Spider\',\'0.03\',\'0\')');
        
        //Pet Event table and indexes
        $this->pdo->query('
            CREATE TABLE `pet_event` (
                pet_event_id INTEGER PRIMARY KEY,
                pet_id INTEGER,
                event_type TEXT, 
                event_datetime TEXT
            )
        ');
        $this->pdo->query('
            CREATE INDEX idx_pet_id 
            ON pet_event(pet_id);
        ');
        $this->pdo->query('
            CREATE INDEX idx_event_type 
            ON pet_event(event_type);
        ');
        $this->pdo->query('
            CREATE INDEX idx_event_datetime 
            ON pet_event(event_datetime);
        ');
    }
}