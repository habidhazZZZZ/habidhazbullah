<?php
class Assignment {
    // Koneksi Database dan Nama Tabel
    private $conn;
    private $table_name = "assignments";

    // Properti Objek
    public $id;
    public $subject_id;
    public $title;
    public $description;
    public $due_date;
    public $created_at;

    // Konstruktor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Membuat tugas baru
    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET subject_id=:subject_id, title=:title, description=:description, due_date=:due_date";

        $stmt = $this->conn->prepare($query);

        // Sanitasi data
        $this->subject_id = htmlspecialchars(strip_tags($this->subject_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));

        // Bind values
        $stmt->bindParam(":subject_id", $this->subject_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":due_date", $this->due_date);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Mendapatkan semua tugas yang masih aktif (belum lewat tenggat)
    function getAllActive() {
        $query = "SELECT a.id, a.title, a.due_date, s.name as subject_name
                  FROM " . $this->table_name . " a
                  JOIN subjects s ON a.subject_id = s.id
                  WHERE a.due_date > NOW()
                  ORDER BY a.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Mendapatkan detail tugas berdasarkan ID
    function getById() {
        $query = "SELECT a.id, a.title, a.description, a.due_date, s.name as subject_name
                  FROM " . $this->table_name . " a
                  JOIN subjects s ON a.subject_id = s.id
                  WHERE a.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->due_date = $row['due_date'];
            $this->subject_name = $row['subject_name']; // Menambahkan properti subject_name
            return true;
        }
        return false;
    }
}
?>
