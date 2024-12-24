<?php
include("includes/db.php");

if (isset($_POST['insert_post'])) {
    // Getting and escaping the text data from the fields
    $package_cat = mysqli_real_escape_string($con, $_POST['package_cat']);
    $package_type = mysqli_real_escape_string($con, $_POST['package_type']);
    $package_title = mysqli_real_escape_string($con, $_POST['package_title']);
    $package_price = mysqli_real_escape_string($con, $_POST['package_price']);
    $package_desc = mysqli_real_escape_string($con, $_POST['package_desc']);
    $package_keywords = mysqli_real_escape_string($con, $_POST['package_keywords']);

    // Getting and processing the image
    
    $package_image = $_FILES['package_image']['name'];
    $package_image_tmp = $_FILES['package_image']['tmp_name'];
    move_uploaded_file($package_image_tmp, "package_images/$package_image");

    // Calculate priority score using a greedy algorithm
    $popularity_score = 0;

    // Step 1: Add score for category popularity
    // Counts the number of existing packages in the selected category.
// Adds 5 points for each existing package in that category.

    $cat_query = "SELECT COUNT(*) as count FROM packages WHERE package_cat = '$package_cat'";
    $cat_result = mysqli_query($con, $cat_query);
    $cat_row = mysqli_fetch_assoc($cat_result);
    $popularity_score += (int)$cat_row['count'] * 5; // Weight: 5 per existing package in the category

    // Step 2: Add score for type popularity
//     Adds 3 points for each existing package of the same type.
// Formula: popularity_score += count * 3.
    $type_query = "SELECT COUNT(*) as count FROM packages WHERE package_type = '$package_type'";
    $type_result = mysqli_query($con, $type_query);
    $type_row = mysqli_fetch_assoc($type_result);
    $popularity_score += (int)$type_row['count'] * 3; // Weight: 3 per existing package of the type

    // Step 3: Deduct points for higher prices (prefer cheaper packages)
//     Deducts points based on the package price to prioritize more affordable packages.
// Formula: popularity_score -= price / 10.
    $popularity_score -= (int)$package_price / 10; // Deduct 1 point for every $10 in price

    // Step 4: Add points for keyword relevance
    // Adds 2 points for each keyword found in other packages.
    $keywords = explode(",", $package_keywords); // Assume keywords are comma-separated
    foreach ($keywords as $keyword) {
        $keyword = trim($keyword);
        $keyword_query = "SELECT COUNT(*) as count FROM packages WHERE package_keywords LIKE '%$keyword%'";
        $keyword_result = mysqli_query($con, $keyword_query);
        $keyword_row = mysqli_fetch_assoc($keyword_result);
        $popularity_score += (int)$keyword_row['count'] * 2; // Weight: 2 per existing package matching the keyword
    }

    // SQL query to insert data
    $insert_package = "
        INSERT INTO packages (package_cat, package_type, package_title, package_price, package_desc, package_image, package_keywords, priority_score) 
        VALUES ('$package_cat', '$package_type', '$package_title', '$package_price', '$package_desc', '$package_image', '$package_keywords', '$popularity_score')
    ";

    $insert_pack = mysqli_query($con, $insert_package);

    // Check for successful insertion or show error
    if ($insert_pack) {
        echo "<script>alert('Package has been inserted with priority score: $popularity_score')</script>";
        echo "<script>window.open('index.php?insert_package', '_self')</script>";
    } else {
        echo "Error: " . mysqli_error($con); // Debugging output
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Inserting Package</title>
    
    <script>tinymce.init({selector: 'textarea'});</script>
</head>

<body bgcolor="skyblue">
    <form action="insert_package.php" method="post" enctype="multipart/form-data">
        <table align="center" width="795" border=2px bgcolor="ABB3C8">
            <tr align="center">
                <td colspan="7"><h2 style="font-family: Cambria;margin-top: 20px; margin-bottom: 15px;">Insert New
                                                                                                        package
                                                                                                        Here</h2>
                </td>
            </tr>
            <tr>
                <td align="right"><b>Package Title:</b></td>
                <td><input type="text" name="package_title" size="60"></td>
            </tr>
            <tr>
                <td align="right"><b>Package Category:</b></td>
                <td>
                    <select name="package_cat">
                        <option>Select a Category</option>
                        <?php $get_cats = "select * from categories";

                        $run_cats = mysqli_query($con, $get_cats);

                        while ($row_cats = mysqli_fetch_array($run_cats)) {
                            $cat_id = $row_cats['cat_id'];
                            $cat_title = $row_cats['cat_title'];

                            echo "<option value='$cat_id'>$cat_title</option>";
                        } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right"><b>Package Type:</b></td>
                <td>
                    <select name="package_type">
                        <option>Select a type</option>
                        <?php $get_types = "select * from types";

                        $run_types = mysqli_query($con, $get_types);

                        while ($row_types = mysqli_fetch_array($run_types)) {
                            $type_id = $row_types['type_id'];
                            $type_title = $row_types['type_title'];

                            echo "<option value='$type_id'>$type_title</option>";
                        } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right"><b>Package Image:</b></td>
                <td><input type="file" name="package_image"></td>
            </tr>
            <tr>
                <td align="right"><b>Package Price:</b></td>
                <td><input type="text" name="package_price"></td>
            </tr>
            <tr>
                <td align="right"><b>Package Description:</b></td>
                <td><textarea name="package_desc" cols="20" rows="10"></textarea></td>
            </tr>
            <tr>
                <td align="right"><b>Package Keywords:</b></td>
                <td><input type="text" name="package_keywords" size="70"></textarea></td>
            </tr>
            <tr align="center">
                <td colspan="7"><input style="margin-top: 10px; margin-bottom: 15px;" type="submit"
                                       name="insert_post" value="Insert Package"></td>
            </tr>
        </table>
    </form>
</body>
</html>