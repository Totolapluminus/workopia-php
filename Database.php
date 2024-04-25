<?php

class Database{
    public $conn;

    /**
     * Constructor fot database class
     * 
     * @param array $config 
     */
    public function __construct($config){
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try{
            $this->conn = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: {$e->getMessage()}");
        }
    }

    /**
     * Execute an sql statement
     *
     * @param string $sql
     * @return obj
     */
    public function query($sql, $params = []){
        try{
            $stmt = $this->conn->prepare($sql);

            //Bind named params
            foreach($params as $param => $value){
                $stmt->bindValue(':' . $param, $value);       //????????????????
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed to execute: {$e->getMessage()}");
        }
    }
}