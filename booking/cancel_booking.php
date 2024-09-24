<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'booking_system';
$username = 'root';
$password = '';

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['cancelBookingId'])) {
        $cancelBookingId = $_POST['cancelBookingId'];

        try {
            // Create a PDO connection
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if the booking ID exists
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_id = :bookingId");
            $checkStmt->bindParam(':bookingId', $cancelBookingId);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                // Proceed to cancel the booking
                $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = :bookingId");
                $stmt->bindParam(':bookingId', $cancelBookingId);
                if ($stmt->execute()) {
                    $statusMessage = "Booking ID <strong>$cancelBookingId</strong> has been successfully canceled.";
                } else {
                    $statusMessage = "Failed to cancel the booking. Please try again.";
                }
            } else {
                // Booking ID does not exist
                $statusMessage = "Booking ID <strong>$cancelBookingId</strong> does not exist.";
            }
        } catch (PDOException $e) {
            $statusMessage = "Error: " . $e->getMessage();
        }

        // Close the connection
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }
        .form-section {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #c82333;
        }
        .booking-info {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #e9f7f9;
            border: 1px solid #007bff;
            color: #343a40;
            text-align: center;
        }
    </style>
</head>
<body>

<h1>Cancel Booking</h1>

<div class="form-section">
    <form method="post">
        <input type="text" name="cancelBookingId" placeholder="Enter Booking ID" required>
        <input type="submit" class="cancel-button" value="Cancel Booking">
    </form>
</div>

<?php if ($statusMessage): ?>
    <div class="booking-info">
        <p><?php echo $statusMessage; ?></p>
    </div>
<?php endif; ?>

</body>
</html>
