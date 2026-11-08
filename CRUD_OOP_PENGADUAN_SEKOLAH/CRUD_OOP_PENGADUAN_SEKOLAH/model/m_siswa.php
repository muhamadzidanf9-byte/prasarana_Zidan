<?php

class Siswa
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Menampilkan semua siswa
    public function tampilSiswa()
    {
        $sql = "SELECT * FROM users
                WHERE role = 'siswa'
                ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menambah siswa
    public function tambahSiswa($nama, $username, $password, $kelas)
    {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO users
                (nama, username, password, role, kelas)
                VALUES
                (:nama, :username, :password, 'siswa', :kelas)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nama' => $nama,
            ':username' => $username,
            ':password' => $passwordHash,
            ':kelas' => $kelas
        ]);
    }

    // Mengambil data siswa berdasarkan ID
    public function getSiswa($id)
    {
        $sql = "SELECT * FROM users
                WHERE id = :id
                AND role = 'siswa'";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mengubah data siswa
    public function editSiswa(
        $id,
        $nama,
        $username,
        $password,
        $kelas
    ) {

        if ($password != "") {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "UPDATE users SET
                    nama = :nama,
                    username = :username,
                    password = :password,
                    kelas = :kelas
                    WHERE id = :id
                    AND role = 'siswa'";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':nama' => $nama,
                ':username' => $username,
                ':password' => $passwordHash,
                ':kelas' => $kelas,
                ':id' => $id
            ]);

        } else {

            $sql = "UPDATE users SET
                    nama = :nama,
                    username = :username,
                    kelas = :kelas
                    WHERE id = :id
                    AND role = 'siswa'";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':nama' => $nama,
                ':username' => $username,
                ':kelas' => $kelas,
                ':id' => $id
            ]);
        }
    }

    // Menghapus siswa
    public function hapusSiswa($id)
    {
        $sql = "DELETE FROM users
                WHERE id = :id
                AND role = 'siswa'";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
?>