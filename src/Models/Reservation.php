<?php

class Reservation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = []) {
        $sql = "SELECT r.*, t.table_number, t.capacity, t.location
                FROM reservations r
                JOIN tables t ON r.table_id = t.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND r.reservation_date = :date";
            $params[':date'] = $filters['date'];
        }

        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT r.*, t.table_number, t.capacity, t.location
                                     FROM reservations r
                                     JOIN tables t ON r.table_id = t.id
                                     WHERE r.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO reservations (table_id, customer_name, customer_email, customer_phone,
                guest_count, reservation_date, reservation_time, special_requests, status)
                VALUES (:table_id, :customer_name, :customer_email, :customer_phone,
                :guest_count, :reservation_date, :reservation_time, :special_requests, :status)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':table_id' => $data['table_id'],
            ':customer_name' => $data['customer_name'],
            ':customer_email' => $data['customer_email'],
            ':customer_phone' => $data['customer_phone'],
            ':guest_count' => $data['guest_count'],
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'],
            ':special_requests' => $data['special_requests'] ?? '',
            ':status' => $data['status'] ?? 'pending'
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE reservations SET ";
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
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function checkAvailability($table_id, $date, $time, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM reservations
                WHERE table_id = :table_id
                AND reservation_date = :date
                AND reservation_time = :time
                AND status IN ('pending', 'confirmed')";

        $params = [
            ':table_id' => $table_id,
            ':date' => $date,
            ':time' => $time
        ];

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    public function getUpcoming($limit = 10) {
        $sql = "SELECT r.*, t.table_number
                FROM reservations r
                JOIN tables t ON r.table_id = t.id
                WHERE r.reservation_date >= CURDATE()
                AND r.status IN ('pending', 'confirmed')
                ORDER BY r.reservation_date ASC, r.reservation_time ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStats() {
        $stats = [];

        // Total reservations
        $stmt = $this->db->query("SELECT COUNT(*) FROM reservations");
        $stats['total'] = $stmt->fetchColumn();

        // Today's reservations
        $stmt = $this->db->query("SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE()");
        $stats['today'] = $stmt->fetchColumn();

        // Pending reservations
        $stmt = $this->db->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'");
        $stats['pending'] = $stmt->fetchColumn();

        // This month
        $stmt = $this->db->query("SELECT COUNT(*) FROM reservations
                                  WHERE YEAR(reservation_date) = YEAR(CURDATE())
                                  AND MONTH(reservation_date) = MONTH(CURDATE())");
        $stats['month'] = $stmt->fetchColumn();

        return $stats;
    }
}
