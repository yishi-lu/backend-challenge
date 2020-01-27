<?php
    namespace App\Utility;
    use mysqli;

    require_once 'config.php'; 

    /**
     * Created by Yishi Lu.
     * User: Yishi Lu
     * Date: 2020/01/25
     */
    class Database{
    
        // specify your own database credentials
        private $host = DB_HOST;
        private $db_name = DB_NAME;
        private $username = USERNAME;
        private $password = PASSWORD;
        public $conn;
        
        /**
         * generate a db connection
         *
         * @param null
         * @return conn
         */
        public function getConnection(){
    
            $this->conn = null;
    
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            } 
    
            return $this->conn;
        }

        public function closeConnection(){

            $this->conn->close();

        }
    }
?>