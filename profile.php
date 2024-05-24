<?php
session_start();
require_once "config.php";

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Initialize variables with session values if they exist, otherwise set to empty string
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "";
$email = isset($_SESSION["email"]) ? $_SESSION["email"] : "";
$phone = isset($_SESSION["phone"]) ? $_SESSION["phone"] : "";

// Define variables to store errors
$username_err = $email_err = $phone_err = $password_err = $confirm_password_err = "";
$update_success = $update_error = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate password
    if (!empty(trim($_POST["password"]))) {
        if (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "Password did not match.";
            }
        }
    }

    // Check input errors before updating the database
    if (empty($username_err) && empty($email_err) && empty($phone_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare an update statement
        if (empty($password)) {
            $sql = "UPDATE users SET username = ?, email = ?, phone = ? WHERE user_id = ?";
        } else {
            $sql = "UPDATE users SET username = ?, email = ?, phone = ?, password = ? WHERE user_id = ?";
        }

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            if (empty($password)) {
                $stmt->bind_param("sssi", $param_username, $param_email, $param_phone, $param_user_id);
            } else {
                $stmt->bind_param("ssssi", $param_username, $param_email, $param_phone, $param_password, $param_user_id);
                $param_password = password_hash($password, PASSWORD_DEFAULT);
            }

            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_phone = $phone;
            $param_user_id = $_SESSION["user_id"];

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                $_SESSION["phone"] = $phone;
                $update_success = "Profile updated successfully.";
            } else {
                $update_error = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .center-form {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
    <section class="container mt-5">
        <div class="center-form">
            <div class="jumbotron">
                <h2>Update Profile</h2>
                <?php
                if (!empty($update_success)) {
                    echo '<div class="alert alert-success">' . $update_success . '</div>';
                }
                if (!empty($update_error)) {
                    echo '<div class="alert alert-danger">' . $update_error . '</div>';
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
                        <span class="text-danger"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                        <span class="text-danger"><?php echo $email_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                        <span class="text-danger"><?php echo $phone_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control">
                        <span class="text-danger"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control">
                        <span class="text-danger"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
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
