<?php
// Database connection parameters
$host = 'localhost'; // or your host
$dbname = 'booking_system';
$username = 'root';
$password = '';

$message = '';

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Prepare and bind parameters
        $stmt = $conn->prepare("INSERT INTO bookings (booking_id, name, email, phone, booking_datetime, guests, children) 
            VALUES (:booking_id, :name, :email, :phone, :booking_datetime, :guests, :children)");

        // Get data from the form
        $bookingId = $_POST['bookingId'];
        $name = $_POST['myname1'];
        $email = $_POST['myemail'];
        $phone = $_POST['myphone'];
        $bookingDatetime = $_POST['myage'];
        $guests = $_POST['mygender'];
        $children = $_POST['myname2'];

        // Bind parameters
        $stmt->bindParam(':booking_id', $bookingId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':booking_datetime', $bookingDatetime);
        $stmt->bindParam(':guests', $guests);
        $stmt->bindParam(':children', $children);

        // Execute the statement
        $stmt->execute();
        $message = "Booking successful!";
    }

    // Fetch the booking details to display after successful booking
    $bookingDetails = [
        'booking_id' => isset($bookingId) ? $bookingId : '',
        'name' => isset($name) ? $name : '',
        'email' => isset($email) ? $email : '',
        'phone' => isset($phone) ? $phone : '',
        'booking_datetime' => isset($bookingDatetime) ? $bookingDatetime : '',
        'guests' => isset($guests) ? $guests : '',
        'children' => isset($children) ? $children : '',
    ];
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        .booking-details p {
            margin: 10px 0;
        }

        .cancel-button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .cancel-button:hover {
            background-color: #c0392b;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: green;
        }
    </style>
    <script>
        function cancelBooking() {
            // Redirect to check_status.php
            window.location.href = "cancel_booking.php";
        }
    </script>
</head>
<body>

<div class="container">
    <h1>Booking Details</h1>
    <div class="message"><?php echo $message; ?></div>
    <div id="bookingDetails" class="booking-details">
        <p><strong>Booking ID:</strong> <span><?php echo htmlspecialchars($bookingDetails['booking_id']); ?></span></p>
        <p><strong>Name:</strong> <span><?php echo htmlspecialchars($bookingDetails['name']); ?></span></p>
        <p><strong>Email:</strong> <span><?php echo htmlspecialchars($bookingDetails['email']); ?></span></p>
        <p><strong>Phone:</strong> <span><?php echo htmlspecialchars($bookingDetails['phone']); ?></span></p>
        <p><strong>Booking Date & Time:</strong> <span><?php echo htmlspecialchars($bookingDetails['booking_datetime']); ?></span></p>
        <p><strong>Guests:</strong> <span><?php echo htmlspecialchars($bookingDetails['guests']); ?></span></p>
        <p><strong>Children:</strong> <span><?php echo htmlspecialchars($bookingDetails['children']); ?></span></p>
    </div>
    <button onclick="cancelBooking()" class="cancel-button">Cancel Booking</button>
</div>

</body>
</html>
