<?php
session_start();
include("functions/functions.php");
include("includes/db.php");
global $con;

// Initialize an empty errors array
$errors = [];

// Initialize variables to preserve form data
$c_name = $c_email = $c_pass = $c_passport = $c_country = $c_city = $c_contact = $c_address = '';

if (isset($_POST['register'])) {
    $ip = getIp();
    $c_name = $_POST['c_name'];
    $c_email = $_POST['c_email'];
    $c_pass = $_POST['c_pass'];
    $c_passport = $_POST['c_passport'];
    $c_image = $_FILES['c_image']['name'];
    $c_image_tmp = $_FILES['c_image']['tmp_name'];
    $c_country = $_POST['c_country'];
    $c_city = $_POST['c_city'];
    $c_contact = $_POST['c_contact'];
    $c_address = $_POST['c_address'];

    // Validation rules
    if (!preg_match("/^[a-zA-Z ]+$/", $c_name)) {
        $errors[] = "Name should contain only letters and spaces.";
    }

    if (!filter_var($c_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (!preg_match("/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/", $c_pass)) {
        $errors[] = "Password must be at least 8 characters long, include a number, an uppercase letter, and a special character.";
    }

    

    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = pathinfo($c_image, PATHINFO_EXTENSION);
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        $errors[] = "Only JPG, JPEG, and PNG files are allowed.";
    }
    if ($_FILES['c_image']['size'] > 2000000) {
        $errors[] = "Image size should be less than 2MB.";
    }

    if (!preg_match("/^(98|97)[0-9]{8}$/", $c_contact)) {
        $errors[] = "Invalid Nepali contact number.";
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        move_uploaded_file($c_image_tmp, "customer/customer_images/$c_image");

        $insert_c = "INSERT INTO customers (customer_ip, customer_name, customer_email, customer_pass, c_passport, customer_country, customer_city, customer_contact, customer_address, customer_image) 
        VALUES ('$ip', '$c_name', '$c_email', '$c_pass', '$c_passport', '$c_country', '$c_city', '$c_contact', '$c_address', '$c_image')";

        $run_c = mysqli_query($con, $insert_c);

        $sel_cart = "SELECT * FROM cart WHERE ip_add='$ip'";
        $run_cart = mysqli_query($con, $sel_cart);
        $check_cart = mysqli_num_rows($run_cart);

        if ($check_cart == 0) {
            $_SESSION['customer_email'] = $c_email;
            echo "<script>alert('Account has been created successfully. Thanks!')</script>";
            echo "<script>window.open('customer/my_account.php','_self')</script>";
        } else {
            $_SESSION['customer_email'] = $c_email;
            echo "<script>alert('Account has been created successfully. Thanks!')</script>";
            echo "<script>window.open('checkout.php','_self')</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ExploreNepal : Register</title>
    <link rel="stylesheet" href="styles/style.css" media="all">
</head>

<body>
    <div class="main_wrapper">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/navbar.php'; ?>
        <div class="content_wrapper">
            <?php include "includes/left-sidebar.php"; ?>
            <div id="content_area">
                <?php cart(); ?>
                <div id="shopping_cart">
                    <span style="float: right;font-size: 18px;padding: 5px;line-height: 40px;">Welcome Guest! <b
                            style="color: yellow;">Shopping Cart-</b> Total Items: <?php total_items(); ?> Total Price: <?php total_price(); ?> <a
                            href="cart.php" style="color: yellow;">Go to Cart</a></b></span>
                </div>
                <form action="customer_register.php" method="post" enctype="multipart/form-data">
                    <table align="center" width="750" style="margin-top: 20px;">
                        <tr align="center">
                            <td colspan="6">
                                <h2 style="margin-bottom: 15px; font-family: Cambria;">Create an Account</h2>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">
                                <?php
                                // Display errors above the form
                                if (!empty($errors)) {
                                    echo '<div style="color:red; font-size:14px; margin-bottom:15px;">';
                                    foreach ($errors as $error) {
                                        echo $error . "<br>";
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Your Name:</td>
                            <td><input type="text" name="c_name" value="<?php echo htmlspecialchars($c_name); ?>" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Email:</td>
                            <td><input type="email" name="c_email" value="<?php echo htmlspecialchars($c_email); ?>" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Password:</td>
                            <td><input type="password" name="c_pass" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Passport ID:</td>
                            <td><input type="text" name="c_passport" value="<?php echo htmlspecialchars($c_passport); ?>" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Image:</td>
                            <td><input type="file" name="c_image" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Country:</td>
                            <td>
                                <select name="c_country" required>
                                    <option value="">Select a country</option>
                                    <option value="Nepal" <?php echo ($c_country == 'Nepal') ? 'selected' : ''; ?>>Nepal</option>
                                    <!-- Add other options -->
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Your City:</td>
                            <td><input type="text" name="c_city" value="<?php echo htmlspecialchars($c_city); ?>" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Contact:</td>
                            <td><input type="text" name="c_contact" value="<?php echo htmlspecialchars($c_contact); ?>" required></td>
                        </tr>
                        <tr>
                            <td align="right">Your Address:</td>
                            <td><input type="text" name="c_address" value="<?php echo htmlspecialchars($c_address); ?>" required></td>
                        </tr>
                        <tr align="center">
                            <td colspan="6"><input type="submit" name="register" value="Create Account"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <?php include "includes/footer.php"; ?>
    </div>
</body>

</html>
