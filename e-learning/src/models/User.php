<?php
class User {
    // Koneksi Database dan Nama Tabel
    private $conn;
    private $table_name = "users";

    // Properti Objek User
    public $id;
    public $username;
    public $password;
    public $role_id;
    public $created_at;

    // Konstruktor dengan $db sebagai koneksi database
    public function __construct($db) {
        $this->conn = $db;
    }

    // Cek apakah username sudah ada
    function isUsernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        // Sanitasi username
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(':username', $this->username);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    // Buat user baru
    function create() {
        // Query untuk memasukkan record baru
        $query = "INSERT INTO " . $this->table_name . " SET username=:username, password=:password, role_id=:role_id";

        // Persiapkan query
        $stmt = $this->conn->prepare($query);

        // Sanitasi data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role_id = htmlspecialchars(strip_tags($this->role_id));
        // Password sudah di-hash di register_process.php, jadi tidak perlu sanitasi HTML

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role_id", $this->role_id);

        // Eksekusi query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Dapatkan user berdasarkan username
    function getByUsername() {
        $query = "SELECT id, username, password, role_id FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        return $stmt;
    }
}
?>
