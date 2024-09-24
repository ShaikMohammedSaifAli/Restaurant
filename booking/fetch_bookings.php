<?php
$host = 'localhost';
$dbname = 'booking_system';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE status = :status");
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($bookings);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
