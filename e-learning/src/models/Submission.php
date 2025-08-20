<?php
class Submission {
    // Koneksi Database dan Nama Tabel
    private $conn;
    private $table_name = "submissions";

    // Properti Objek
    public $id;
    public $assignment_id;
    public $student_id;
    public $file_path;
    public $submitted_at;
    public $grade;

    // Konstruktor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Membuat submission baru
    function create() {
        // Cek dulu apakah siswa sudah pernah submit untuk tugas ini
        if ($this->hasSubmitted()) {
            return false; // Mengembalikan false jika sudah pernah submit
        }

        $query = "INSERT INTO " . $this->table_name . " SET assignment_id=:assignment_id, student_id=:student_id, file_path=:file_path";

        $stmt = $this->conn->prepare($query);

        // Sanitasi data
        $this->assignment_id = htmlspecialchars(strip_tags($this->assignment_id));
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));

        // Bind values
        $stmt->bindParam(":assignment_id", $this->assignment_id);
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":file_path", $this->file_path);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Cek apakah siswa sudah pernah submit tugas yang sama
    function hasSubmitted() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE assignment_id = :assignment_id AND student_id = :student_id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":assignment_id", $this->assignment_id);
        $stmt->bindParam(":student_id", $this->student_id);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
?>
