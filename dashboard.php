<?php
// Start session
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Define query to fetch all services
$sql = "SELECT * FROM services";
$result = $conn->query($sql);

// Initialize an empty array to store services
$services = [];

// Check if services exist
if ($result && $result->num_rows > 0) {
    // Fetch services and store them in an array
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
} else {
    // Handle case where no services are available
    // You can set a default message or take any other appropriate action
    $error_message = "No services available.";
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
            padding-top: 20px;
            background-color: #fff;
        }

        /* Navbar */
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
        }

        /* Welcome Title */
        .welcome-title {
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }

        /* Service Card Section */
        .service-card-section {
            padding: 50px 0;
        }

        .service-card {
            margin-bottom: 20px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .service-card img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .service-card .card-body {
            padding: 20px;
        }

        .service-card .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .service-card .card-text {
            font-size: 16px;
            color: #555;
        }

        .service-card .service-price {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
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

    <section class="container">
        <!-- Welcome Title -->
        <div class="welcome-title">
            Welcome to your Dashboard, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
        </div>

        <!-- Service Card Section -->
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 service-card">
                        <img class="card-img-top" src="<?php echo htmlspecialchars($service["service_image"]); ?>" alt="Service Image">

                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($service["service_name"]); ?></h4>
                            <p class="card-text"><?php echo htmlspecialchars($service["service_description"]); ?></p>
                            <p class="card-text service-price">Price: <?php echo htmlspecialchars($service["service_price"]); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <div class="footer">
            <div class="container">
                <p>&copy; 2024 Home Repair Services. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
