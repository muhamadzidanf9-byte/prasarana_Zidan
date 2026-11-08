<?php

class Koneksi
{
    private $host = "sql207.infinityfree.com";
    private $username = "if0_43212518";
    private $password = "zidansapras";
    private $database = "if0_43212518_pengaduan_sekolah";

    protected $conn;

    public function __construct()
    {
        try {

            $this->conn = new PDO(
                "mysql:host=" . $this->host .
                ";dbname=" . $this->database .
                ";charset=utf8",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch (PDOException $e) {

            die("Koneksi database gagal: " . $e->getMessage());

        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
?>