<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'booking_system';
$username = 'root';
$password = '';

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all bookings
    $stmt = $conn->prepare("SELECT * FROM bookings");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count status
    $pendingCount = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));
    $acceptedCount = count(array_filter($bookings, fn($b) => $b['status'] === 'accepted'));
    $declinedCount = count(array_filter($bookings, fn($b) => $b['status'] === 'declined'));

    // Update booking action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $tableNumber = $_POST['table_number'];
            $status = $_POST['status'];
            
            $stmt = $conn->prepare("UPDATE bookings SET status = :status, table_number = :table_number WHERE id = :id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':table_number', $tableNumber);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 200px;
            background-color: #dc3545;
            color: white;
            padding: 20px;
            min-height: 100vh;
        }
        .sidebar h2 {
            margin: 0 0 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background-color: #c82333;
        }
        .main {
            flex: 1;
            padding: 20px;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            background-color: #dc3545;
            padding: 10px 20px;
            color: white;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1 1 calc(33% - 20px);
            min-width: 300px;
            position: relative;
        }
        .chart-container {
            flex: 1 1 100%;
            margin-top: 20px;
        }
        h2 {
            color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        th {
            background-color: #dc3545;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e2e6ea;
        }
        .button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .accept { background-color: #28a745; color: white; }
        .edit { background-color: #007bff; color: white; }
        .button:hover { opacity: 0.8; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Dashboard</h2>
    <a href="#">Home</a>
    <a href="#">Manage Bookings</a>
    <a href="#">Reports</a>
    <a href="#">Settings</a>
    <a href="#">Logout</a>
</div>

<div class="main">
    <div class="navbar">
        <h1>Booking Management</h1>
    </div>

    <div class="container">
        <div class="card">
            <h2>Pending Bookings</h2>
            <p><?php echo $pendingCount; ?></p>
            <button class="button accept" onclick="openModal('pending')">View</button>
        </div>
        <div class="card">
            <h2>Accepted Bookings</h2>
            <p><?php echo $acceptedCount; ?></p>
            <button class="button accept" onclick="openModal('accepted')">View</button>
        </div>
        <div class="card">
            <h2>Declined Bookings</h2>
            <p><?php echo $declinedCount; ?></p>
            <button class="button decline" onclick="openModal('declined')">View</button>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="bookingChart"></canvas>
    </div>

    <h2>All Bookings</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Booking ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Booking Date & Time</th>
            <th>Guests</th>
            <th>Children</th>
            <th>Status</th>
            <th>Table Number</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?php echo htmlspecialchars($booking['id']); ?></td>
            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
            <td><?php echo htmlspecialchars($booking['name']); ?></td>
            <td><?php echo htmlspecialchars($booking['email']); ?></td>
            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
            <td><?php echo htmlspecialchars($booking['booking_datetime']); ?></td>
            <td><?php echo htmlspecialchars($booking['guests']); ?></td>
            <td><?php echo htmlspecialchars($booking['children']); ?></td>
            <td><?php echo htmlspecialchars($booking['status']); ?></td>
            <td><?php echo htmlspecialchars($booking['table_number']) ?: 'Not Assigned'; ?></td>
            <td>
                <button class="button edit" onclick="openEditModal(<?php echo htmlspecialchars($booking['id']); ?>, '<?php echo htmlspecialchars($booking['table_number']); ?>', '<?php echo htmlspecialchars($booking['status']); ?>')">Edit</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Booking</h2>
        <form id="editForm" method="post">
            <input type="hidden" name="id" id="editBookingId">
            <label for="editTableNumber">Table Number:</label>
            <input type="text" name="table_number" id="editTableNumber" required>
            <label for="editStatus">Status:</label>
            <select name="status" id="editStatus" required>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="declined">Declined</option>
            </select>
            <input type="hidden" name="action" value="update">
            <button type="submit" class="button">Update Booking</button>
        </form>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeBookingModal()">&times;</span>
        <h2>Booking Details</h2>
        <table id="bookingDetailsTable">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Booking Date & Time</th>
                    <th>Guests</th>
                    <th>Children</th>
                    <th>Status</th>
                    <th>Table Number</th>
                </tr>
            </thead>
            <tbody>
                <!-- Booking details will be populated here -->
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('bookingChart').getContext('2d');
const bookingChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Accepted', 'Declined'],
        datasets: [{
            label: 'Booking Status',
            data: [<?php echo $pendingCount; ?>, <?php echo $acceptedCount; ?>, <?php echo $declinedCount; ?>],
            backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Open and close modal functions
function openModal(status) {
    fetch('fetch_bookings.php?status=' + status)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#bookingDetailsTable tbody');
            tableBody.innerHTML = ''; // Clear existing data

            data.forEach(booking => {
                const row = `
                    <tr>
                        <td>${booking.booking_id}</td>
                        <td>${booking.name}</td>
                        <td>${booking.email}</td>
                        <td>${booking.phone}</td>
                        <td>${booking.booking_datetime}</td>
                        <td>${booking.guests}</td>
                        <td>${booking.children}</td>
                        <td>${booking.status}</td>
                        <td>${booking.table_number || 'Not Assigned'}</td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });

            document.getElementById("bookingModal").style.display = "block";
        })
        .catch(error => console.error('Error fetching bookings:', error));
}

function closeBookingModal() {
    document.getElementById("bookingModal").style.display = "none";
}

function openEditModal(id, tableNumber, status) {
    document.getElementById("editBookingId").value = id;
    document.getElementById("editTableNumber").value = tableNumber;
    document.getElementById("editStatus").value = status;
    document.getElementById("editModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    if (event.target === document.getElementById("editModal")) {
        closeEditModal();
    }
}
</script>

</body>
</html>
