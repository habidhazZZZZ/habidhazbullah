<?php
class Subject {
    // Koneksi Database dan Nama Tabel
    private $conn;
    private $table_name = "subjects";

    // Properti Objek
    public $id;
    public $name;
    public $description;
    public $teacher_id;

    // Konstruktor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Membuat mata pelajaran baru
    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, description=:description, teacher_id=:teacher_id";

        $stmt = $this->conn->prepare($query);

        // Sanitasi data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->teacher_id = htmlspecialchars(strip_tags($this->teacher_id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":teacher_id", $this->teacher_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Mendapatkan semua mata pelajaran berdasarkan ID guru
    function getByTeacher() {
        $query = "SELECT id, name, description FROM " . $this->table_name . " WHERE teacher_id = ? ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);

        // Bind teacher_id
        $stmt->bindParam(1, $this->teacher_id);

        $stmt->execute();

        return $stmt;
    }

    // Mendapatkan semua mata pelajaran
    function getAll() {
        $query = "SELECT s.id, s.name, s.description, u.username as teacher_name
                  FROM " . $this->table_name . " s
                  JOIN users u ON s.teacher_id = u.id
                  ORDER BY s.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>
