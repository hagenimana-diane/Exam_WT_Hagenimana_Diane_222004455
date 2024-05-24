<?php
session_start();
require_once "config.php";

// Check if service ID is provided in the URL
if (isset($_GET['service_id'])) {
    // Fetch service details from the database based on the provided service ID
    $sql = "SELECT * FROM services WHERE service_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $_GET['service_id']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                // Service found, fetch its details
                $service = $result->fetch_assoc();
            } else {
                // Service not found, redirect to error page or display error message
                echo "Service not found.";
                exit;
            }
        } else {
            echo "Error fetching service details.";
            exit;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement.";
        exit;
    }
} else {
    echo "Service ID not provided.";
    exit;
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to login page
    header("location: login.php");
    exit;
}

// Initialize variables for booking form
$date = $additional_info = "";
$date_err = $additional_info_err = "";
$booking_success = $booking_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate date
    if (empty(trim($_POST["date"]))) {
        $date_err = "Please select a date for the service.";
    } else {
        $date = trim($_POST["date"]);
    }

    // Validate additional information
    $additional_info = trim($_POST["additional_info"]);

    // Check input errors before inserting in database
    if (empty($date_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO bookings (service_id, user_id, scheduled_date, additional_info) VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("iiss", $param_service_id, $param_user_id, $param_date, $param_additional_info);

            // Set parameters
            $param_service_id = $_GET['service_id'];
            $param_user_id = $_SESSION["user_id"];
            $param_date = date("Y-m-d H:i:s"); // Current date and time
            $param_additional_info = $additional_info;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $booking_success = "Service booked successfully. We will contact you shortly to confirm the booking.";
            } else {
                $booking_err = "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Details</title>
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
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
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
        <h2>Service Details</h2>
        <?php if (!empty($booking_success)) : ?>
            <div class="alert alert-success"><?php echo $booking_success; ?></div>
        <?php endif; ?>
        <?php if (!empty($booking_err)) : ?>
            <div class="alert alert-danger"><?php echo $booking_err; ?></div>
        <?php endif; ?>
        <div>
            <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($service['service_description']); ?></p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($service['service_price']); ?></p>
        </div>
        <hr>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bookServiceModal">Book Now</button>
    </section>

    <!-- Bootstrap JS libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookServiceModal" tabindex="-1" role="dialog" aria-labelledby="bookServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookServiceModalLabel">Book Service</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?service_id=<?php echo $_GET['service_id']; ?>" method="post">
                        <div class="form-group">
                            <label>Select Date:</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $date; ?>">
                            <span class="text-danger"><?php echo $date_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Additional Information:</label>
                            <textarea name="additional_info" class="form-control"><?php echo htmlspecialchars($additional_info); ?></textarea>
                            <span class="text-danger"><?php echo $additional_info_err; ?></span>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Book Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 <footer>
        <div class="footer">
            <div class="container">
                <p>&copy; 2024 Home Repair Services. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
