<?php
session_start();
require_once "config.php";

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Initialize variables
$service_name = $service_description = $service_price = $service_image = "";
$service_name_err = $service_description_err = $service_price_err = $service_image_err = $service_err = $service_success = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["create_service"])) {
        // Validate service name
        if (empty(trim($_POST["service_name"]))) {
            $service_name_err = "Please enter a service name.";
        } else {
            $service_name = trim($_POST["service_name"]);
        }

        // Validate service description
        if (empty(trim($_POST["service_description"]))) {
            $service_description_err = "Please enter a service description.";
        } else {
            $service_description = trim($_POST["service_description"]);
        }

        // Validate service price
        if (empty(trim($_POST["service_price"]))) {
            $service_price_err = "Please enter a service price.";
        } else {
            $service_price = trim($_POST["service_price"]);
        }

        // Check if an image was uploaded
        if ($_FILES["service_image"]["error"] == 0) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["service_image"]["name"]);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Check if file is a valid image
            $check = getimagesize($_FILES["service_image"]["tmp_name"]);
            if ($check !== false) {
                // Check file size
                if ($_FILES["service_image"]["size"] > 5000000) { // 5MB
                    $service_image_err = "Sorry, your file is too large.";
                } else {
                    // Allow certain file formats
                    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                        $service_image_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    } else {
                        // Upload the image
                        if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $targetFile)) {
                            $service_image = $targetFile;
                        } else {
                            $service_image_err = "Sorry, there was an error uploading your file.";
                        }
                    }
                }
            } else {
                $service_image_err = "File is not an image.";
            }
        }

        // Check input errors before inserting in database
        if (empty($service_name_err) && empty($service_description_err) && empty($service_price_err) && empty($service_image_err)) {
            // Prepare an insert statement
            $sql = "INSERT INTO services (service_name, service_description, service_price, service_image, user_id) VALUES (?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ssdsi", $param_service_name, $param_service_description, $param_service_price, $param_service_image, $param_user_id);

                // Set parameters
                $param_service_name = $service_name;
                $param_service_description = $service_description;
                $param_service_price = $service_price;
                $param_service_image = $service_image;
                $param_user_id = $_SESSION["user_id"];

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    $service_success = "Service created successfully.";
                } else {
                    $service_err = "Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        }
    }

    // Other actions like edit and delete go here...
}

// Fetch all services for the logged-in user
$sql = "SELECT * FROM services WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $param_user_id);
    $param_user_id = $_SESSION["user_id"];

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $services = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $services = [];
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>
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
        <h2>Services</h2>
        <?php
        if (!empty($service_success)) {
            echo '<div class="alert alert-success">' . $service_success . '</div>';
        }
        if (!empty($service_err)) {
            echo '<div class="alert alert-danger">' . $service_err . '</div>';
        }
        ?>
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#createServiceModal">Create Service</button>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service["service_name"]); ?></td>
                        <td><?php echo htmlspecialchars($service["service_description"]); ?></td>
                        <td><?php echo htmlspecialchars($service["service_price"]); ?></td>
                        <td><img src="<?php echo htmlspecialchars($service["service_image"]); ?>" alt="Service Image" style="max-width: 100px;"></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-service-btn" data-toggle="modal" data-target="#editServiceModal" data-service-id="<?php echo $service["service_id"]; ?>" data-service-name="<?php echo htmlspecialchars($service["service_name"]); ?>" data-service-description="<?php echo htmlspecialchars($service["service_description"]); ?>" data-service-price="<?php echo htmlspecialchars($service["service_price"]); ?>">Edit</button>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;">
                                <input type="hidden" name="service_id" value="<?php echo $service["service_id"]; ?>">
                                <button type="submit" name="delete_service" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Create Service Modal -->
    <div class="modal fade" id="createServiceModal" tabindex="-1" role="dialog" aria-labelledby="createServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createServiceModalLabel">Create Service</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Service Name</label>
                            <input type="text" name="service_name" class="form-control" value="<?php echo htmlspecialchars($service_name); ?>">
                            <span class="text-danger"><?php echo $service_name_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Service Description</label>
                            <textarea name="service_description" class="form-control"><?php echo htmlspecialchars($service_description); ?></textarea>
                            <span class="text-danger"><?php echo $service_description_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Service Price</label>
                            <input type="text" name="service_price" class="form-control" value="<?php echo htmlspecialchars($service_price); ?>">
                            <span class="text-danger"><?php echo $service_price_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Service Image</label>
                            <input type="file" name="service_image" class="form-control-file">
                            <span class="text-danger"><?php echo $service_image_err; ?></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="create_service" class="btn btn-primary">Create Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="service_id" id="editServiceId">
                        <div class="form-group">
                            <label>Service Name</label>
                            <input type="text" name="service_name" id="editServiceName" class="form-control" value="<?php echo htmlspecialchars($service_name); ?>">
                            <span class="text-danger"><?php echo $service_name_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Service Description</label>
                            <textarea name="service_description" id="editServiceDescription" class="form-control"><?php echo htmlspecialchars($service_description); ?></textarea>
                            <span class="text-danger"><?php echo $service_description_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Service Price</label>
                            <input type="text" name="service_price" id="editServicePrice" class="form-control" value="<?php echo htmlspecialchars($service_price); ?>">
                            <span class="text-danger"><?php echo $service_price_err; ?></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="edit_service" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editServiceModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var serviceId = button.data('service-id');
            var serviceName = button.data('service-name');
            var serviceDescription = button.data('service-description');
            var servicePrice = button.data('service-price');

            var modal = $(this);
            modal.find('#editServiceId').val(serviceId);
            modal.find('#editServiceName').val(serviceName);
            modal.find('#editServiceDescription').val(serviceDescription);
            modal.find('#editServicePrice').val(servicePrice);
        });
    </script>
     <footer>
        <div class="footer">
            <div class="container">
                <p>&copy; 2024 Home Repair Services. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
