<?php

    class usersControllers{
        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/Connection.php';

            $db = new Connection;

            $this->con = $db->connect();
        }

        public function createUser($email, $password, $nama, $area, $alamat){
            if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("insert into users (email, password, nama, area, alamat) values (?,?,?,?,?)");
                $stmt->bind_param("sssss",$email,$password,$nama,$area,$alamat);
                if($stmt->execute()){
                    return USER_CREATED;
                } else {
                    return USER_FIALURE;
                }
            }
            return USER_EXISTS;
        }

        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email);
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                } else {
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            } else {
                return USER_NOT_FOUND;
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("select password from users where email = ? ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($password);
            $stmt->fetch();
            return $password;
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("select id, email, nama, area, alamat from users;");
            $stmt->execute();
            $stmt->bind_result($id, $email, $nama, $area, $alamat);
            $users = array();
            while ($stmt->fetch()){
                $user = array();
                $user['id'] = $id;
                $user['email'] = $email;
                $user['nama'] = $nama;
                $user['area'] = $area;
                $user['alamat'] = $alamat;
                array_push($users, $user);
            }
            return $users;
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("select id, email, nama, area, alamat from users where email = ? ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id, $email, $nama, $area, $alamat);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['email'] = $email;
            $user['nama'] = $nama;
            $user['area'] = $area;
            $user['alamat'] = $alamat;
            return $user;
        }

        public function updateUser($email, $nama, $area, $alamat, $id){
            $stmt = $this->con->prepare("update users set email = ?, nama = ?, area = ?, alamat = ? where id = ?");
            $stmt->bind_param("sssss", $email, $nama, $area, $alamat, $id);
            if($stmt->execute())
                return true;
            return false;
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);

            if(password_verify($currentpassword, $hashed_password)){

                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("update users set password = ? where email = ?");
                $stmt->bind_param("ss", $hash_password, $email);

                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_DO_NOT_CHANGED;

            } else {
                return PASSWORD_DO_NOT_MATCH;
            }
        }

        public function deleteUser($id){
            $stmt = $this->con->prepare("delete from users where id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true;
            return false;

        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("select id from users where email = ? ");
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

        
    }