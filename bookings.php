<?php
session_start();
require_once "config.php";

// Check if action is provided in the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['booking_id'])) {
    $action = $_POST['action'];
    $booking_id = $_POST['booking_id'];

    // Update booking status in the database based on action
    if ($action === 'accept') {
        $status = 'Accepted';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } elseif ($action === 'delete') {
        // Prepare and execute SQL statement to delete booking
        $sql = "DELETE FROM bookings WHERE booking_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $booking_id);
            if ($stmt->execute()) {
                // Booking deleted successfully
                header("location: bookings.php");
                exit;
            } else {
                // Error deleting booking
                echo "Error deleting booking.";
                exit;
            }
            $stmt->close();
        } else {
            // Error preparing statement
            echo "Error preparing statement.";
            exit;
        }
    }
}

// Fetch booked services from the database
$sql = "SELECT bookings.booking_id, services.service_name, services.service_price FROM bookings 
        JOIN services ON bookings.service_id = services.service_id";
$result = $conn->query($sql);
$booked_services = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
     <header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">Home Repair Services</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="dashboard.php">Dashboard <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

    <section class="container mt-5">
        <h2>Booked Services</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booked_services as $booked_service): ?>
                        <tr>
                            <td><?php echo $booked_service['service_name']; ?></td>
                            <td><?php echo $booked_service['service_price']; ?></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="booking_id" value="<?php echo $booked_service['booking_id']; ?>">
                                    <button type="submit" class="btn btn-success" name="action" value="accept">Accept</button>
                                    <button type="submit" class="btn btn-danger" name="action" value="reject">Reject</button>
                                    <button type="submit" class="btn btn-secondary" name="action" value="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer>
        <div class="footer">
            <div class="container">
                <p>&copy; 2024 Home Repair Services. All rights reserved.</p>
            </div>
        </div>
    </footer>


</body>
</html>
