<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'booking_system';
$username = 'root';
$password = '';

$statusMessage = '';
$bookingStatus = '';
$bookingId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['bookingId'])) {
        $bookingId = $_POST['bookingId'];

        try {
            // Create a PDO connection
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query to fetch booking status
            $stmt = $conn->prepare("SELECT status, table_number FROM bookings WHERE booking_id = :bookingId");
            $stmt->bindParam(':bookingId', $bookingId);
            $stmt->execute();

            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking) {
                $bookingStatus = ucfirst($booking['status']);
                $tableNumber = $booking['table_number'] ? $booking['table_number'] : 'Not Assigned';
                $statusMessage = "Your booking status is: <strong>$bookingStatus</strong><br>Table Number: <strong>$tableNumber</strong>";
            } else {
                $statusMessage = "No booking found with this ID.";
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
    <title>Booking System</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .form-section {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="submit"], #cancelBookingBtn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover, #cancelBookingBtn:hover {
            background-color: #2980b9;
        }
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.7); 
            padding-top: 60px; 
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto; 
            padding: 20px;
            border: 1px solid #ddd;
            width: 80%; 
            max-width: 500px; 
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #2c3e50;
            text-decoration: none;
            cursor: pointer;
        }
        .booking-info {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #ecf0f1;
            border: 1px solid #3498db;
            color: #2c3e50;
        }
    </style>
</head>
<body>

<h1>Booking System</h1>

<!-- Check Booking Status Section -->
<div class="form-section">
    <form method="post" id="bookingForm">
        <h2>Check Booking Status</h2>
        <input type="text" name="bookingId" placeholder="Enter Booking ID" required>
        <input type="submit" value="Check Status">
    </form>
</div>

<!-- Modal for Booking Status -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="booking-info">
            <p id="modalBookingId"><strong>Booking ID:</strong> <?php echo htmlspecialchars($bookingId); ?></p>
            <p id="modalMessage"><?php echo $statusMessage; ?></p>
            <button id="cancelBookingBtn" style="margin-top: 10px;">Cancel Booking</button>
        </div>
    </div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById("statusModal");
    var closeBtn = document.getElementsByClassName("close")[0];
    var cancelBookingBtn = document.getElementById("cancelBookingBtn");

    // Listen for form submission for checking booking status
    document.getElementById('bookingForm').onsubmit = function(event) {
        event.preventDefault(); // Prevent form submission
        var formData = new FormData(this);
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Parse response to update modal content
            var parser = new DOMParser();
            var doc = parser.parseFromString(data, 'text/html');
            document.getElementById("modalBookingId").innerHTML = "<strong>Booking ID:</strong> " + doc.querySelector('input[name="bookingId"]').value;
            document.getElementById("modalMessage").innerHTML = doc.querySelector('#modalMessage').innerHTML; // Get message
            modal.style.display = "block"; // Show the modal
        });
    };

    // When the user clicks on <span> (x), close the modal
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    // Close the modal if the user clicks anywhere outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Redirect to cancel_booking.php when the Cancel Booking button is clicked
    cancelBookingBtn.onclick = function() {
        var bookingId = document.querySelector('input[name="bookingId"]').value;
        window.location.href = 'cancel_booking.php?bookingId=' + bookingId;
    }
</script>

</body>
</html>
