<?php

    class barangControllers{
        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/Connection.php';

            $db = new Connection;

            $this->con = $db->connect();
        }

        public function createBarang($id_barang, $nama_barang, $detail, $harga, $stok_barang){
            if(!$this->isBarangExists($nama_barang)){
                $stmt = $this->con->prepare("insert into barang (id_barang, nama_barang, detail, harga, stok_barang)
                                            values (?,?,?,?,?)");
                $stmt->bind_param("sssss", $id_barang, $nama_barang, $detail, $harga, $stok_barang);
                if($stmt->execute()){
                    return BARANG_CREATED;
                } else {
                    return BARANG_FAILURE;
                }
            }

            return BARANG_EXISTS;
        }
        
        public function getAllBarang(){
            $stmt = $this->con->prepare("select id_barang, nama_barang, detail, harga, stok_barang from barang");
            $stmt->execute();
            $stmt->bind_result($id_barang,$nama_barang,$detail,$harga,$stok_barang);
            $barangs = array();
            while ($stmt->fetch()){
                $barang = array();
                $barang['id_barang'] = $id_barang;
                $barang['nama_barang'] = $nama_barang;
                $barang['detail'] = $detail;
                $barang['harga'] = $harga;
                $barang['stok_barang'] = $stok_barang;
                array_push($barangs, $barang);
            }
            return $barangs;
        }

        public function deleteBarang($id_barang){
            $stmt = $this->con->prepare("delete from barang where id_barang = ?");
            $stmt->bind_param("s", $id_barang);
            if ($stmt->execute())
                return true;
            return false;
        }

        private function isBarangExists($nama_barang){
            $stmt = $this->con->prepare("select id_barang from barang where nama_barang = ?");
            $stmt->bind_param("s", $nama_barang);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

    }