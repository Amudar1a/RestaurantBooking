<?php

class Table {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM tables ORDER BY table_number");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tables WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO tables (table_number, capacity, location, status)
                VALUES (:table_number, :capacity, :location, :status)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':table_number' => $data['table_number'],
            ':capacity' => $data['capacity'],
            ':location' => $data['location'] ?? '',
            ':status' => $data['status'] ?? 'available'
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE tables SET ";
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql .= implode(', ', $fields) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tables WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getAvailable($date, $time, $guests) {
        $sql = "SELECT t.* FROM tables t
                WHERE t.capacity >= :guests
                AND t.status = 'available'
                AND t.id NOT IN (
                    SELECT table_id FROM reservations
                    WHERE reservation_date = :date
                    AND reservation_time = :time
                    AND status IN ('pending', 'confirmed')
                )
                ORDER BY t.capacity ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':guests' => $guests,
            ':date' => $date,
            ':time' => $time
        ]);
        return $stmt->fetchAll();
    }
}
