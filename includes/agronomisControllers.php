<?php

    class agronomisControllers{
        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/Connection.php';

            $db = new Connection;

            $this->con = $db->connect();
        }

        public function createAgronomis($email, $password, $nama, $area, $alamat){
            if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("insert into agronomis (email, password, nama, area, alamat) values (?,?,?,?,?)");
                $stmt->bind_param("sssss",$email,$password,$nama,$area,$alamat);
                if($stmt->execute()){
                    return USER_CREATED;
                } else {
                    return USER_FIALURE;
                }
            }
            return USER_EXISTS;
        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("select id from agronomis where email = ? ");
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

        
    }