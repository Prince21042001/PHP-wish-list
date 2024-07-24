<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user']['id'];

// Handle form submission to add a new wishlist item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'];
    $item_description = $_POST['item_description'];
    $price = $_POST['price'];

    $sql = "INSERT INTO wishlists (user_id, item_name, item_description, price) VALUES (?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("isss", $user_id, $item_name, $item_description, $price);

    if ($stmt->execute()) {
        $success_message = "Item added to your wishlist successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Handle form submission to update a wishlist item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_description = $_POST['item_description'];
    $price = $_POST['price'];

    $sql = "UPDATE wishlists SET item_name = ?, item_description = ?, price = ? WHERE id = ? AND user_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sssii", $item_name, $item_description, $price, $item_id, $user_id);

    if ($stmt->execute()) {
        $success_message = "Item updated successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Handle request to delete a wishlist item
if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];

    $sql = "DELETE FROM wishlists WHERE id = ? AND user_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ii", $item_id, $user_id);

    if ($stmt->execute()) {
        $success_message = "Item deleted successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Retrieve the wishlist items for the logged-in user
$sql = "SELECT * FROM wishlists WHERE user_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wish List Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .dashboard-container {
            position: relative;
            max-width: 1000px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dashboard-container h1 {
            font-size: 2em;
            margin-bottom: 20px;
        }

        .btn-custom {
            border-radius: 20px;
            font-size: 1.2em;
        }

        .wishlist-table th,
        .wishlist-table td {
            text-align: left;
            padding: 10px;
        }

        .navbar {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="overlay"></div>
    <div class="dashboard-container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Wish List Management</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item btn-btn-dark">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <h1 class="text-primary">Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</h1>
        <h2>Your Wishlist</h2>
        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <?php if ($result->num_rows > 0) : ?>
            <table class="table wishlist-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['item_description']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                            <td>
                                <!-- Edit button to trigger the modal -->
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#updateModal<?php echo $row['id']; ?>">Edit</button>
                                <!-- Delete button -->
                                <a href="dashboard.php?delete_item=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel<?php echo $row['id']; ?>">Update Item</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="dashboard.php" method="POST">
                                            <input type="hidden" name="update_item" value="1">
                                            <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                                            <div class="form-group">
                                                <label for="item_name<?php echo $row['id']; ?>">Item Name</label>
                                                <input type="text" class="form-control" id="item_name<?php echo $row['id']; ?>" name="item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="item_description<?php echo $row['id']; ?>">Description</label>
                                                <input type="text" class="form-control" id="item_description<?php echo $row['id']; ?>" name="item_description" value="<?php echo htmlspecialchars($row['item_description']); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="price<?php echo $row['id']; ?>">Price</label>
                                                <input type="number" step="0.01" class="form-control" id="price<?php echo $row['id']; ?>" name="price" value="<?php echo htmlspecialchars($row['price']); ?>">
                                            </div>
                                            <button type="submit" class="btn btn-success">Update Item</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>You have no items in your wishlist.</p>
        <?php endif; ?>

        <h2>Add a New Item</h2>
        <form action="dashboard.php" method="POST">
            <input type="hidden" name="add_item" value="1">
            <div class="form-group">
                <label for="new_item_name">Item Name</label>
                <input type="text" class="form-control" id="new_item_name" name="item_name" placeholder="Item Name" required>
            </div>
            <div class="form-group">
                <label for="new_item_description">Description</label>
                <input type="text" class="form-control" id="new_item_description" name="item_description" placeholder="Item Description">
            </div>
            <div class="form-group">
                <label for="new_price">Price</label>
                <input type="number" step="0.01" class="form-control" id="new_price" name="price" placeholder="Price">
            </div>
            <button type="submit" class="btn btn-success btn-custom">Add Item</button>
        </form>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>