<?php
session_start();
include 'config.php';

// Fetch all services
$sql = "SELECT * FROM services";
$result = $conn->query($sql);
$services = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Repair Services</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      padding-top: 56px;
    }

    /* Background Section */
    .background-section {
      position: relative;
      background-image: url('img/bg.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 100px 0;
    }

    .background-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5); /* Dark overlay */
    }

    /* Service Card Section */
    .service-card-section {
      padding: 50px 0;
    }

    .service-card {
      margin-bottom: 20px;
    }

    .service-card img {
      height: 200px; /* Adjust height as needed */
      object-fit: cover;
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Home Repair Services</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <?php if(isset($_SESSION["user_id"])): ?>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Logout</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="register.php">Sign Up</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Background Section -->
  <section class="background-section">
    <div class="background-overlay"></div>
    <div class="container">
      <div class="row">
        <div class="col text-center">
          <h1 class="display-4">Welcome to Home Repair Services!</h1>
          <p class="lead">Find the best service providers for all your home repair needs.</p>
          <a class="btn btn-primary btn-lg" href="register.php" role="button">Sign Up Now</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Service Card Section -->
  <section class="service-card-section">
    <div class="container">
      <div class="row">
        <div class="col text-center mb-4">
          <h2>Our Services</h2>
        </div>
      </div>
      <div class="row">
        <!-- Iterate through your services and create service cards -->
        <?php foreach ($services as $service): ?>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 service-card">
              <img class="card-img-top" src="<?php echo htmlspecialchars($service["service_image"]); ?>" alt="Service Image" style="max-width: 100%;">

              <div class="card-body">
                <h4 class="card-title"><?php echo htmlspecialchars($service["service_name"]); ?></h4>
                <p class="card-text"><?php echo htmlspecialchars($service["service_description"]); ?></p>
              </div>
              <div class="card-footer">
                <h5>$<?php echo number_format($service["service_price"], 2); ?></h5>
                <a href="service_details.php?service_id=<?php echo $service['service_id']; ?>" class="btn btn-primary">View Details</a>

              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-4 bg-dark text-white-50">
    <div class="container text-center">
      <small>&copy; 2024 Home Repair Services. All Rights Reserved.</small>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
