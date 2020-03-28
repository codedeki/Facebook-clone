<?php 
include("includes/header.php");
include("includes/handlers/form_handlers/settings_handler.php");
?>

<div class="main_column column">
    <h4>Account Settings</h4>
    <?php
    echo "<img src='" . $user['profilePic'] . "' class='small_profile_pic'>";
    ?>
    <br>
    <a href="upload.php">Upload new profile picture</a> <br><br><br>

    <?php 
    $user_data_query = mysqli_query($con, "SELECT firstName, lastName, email FROM users WHERE username='$userLoggedIn'");
    $row = mysqli_fetch_array($user_data_query);

    $first_name = $row['firstName'];
    $last_name = $row['lastName'];
    $email= $row['email'];
    ?>

    <form action="settings.php" method="POST">
        </p>First Name: <input type="text" name="first_name" value="<?php echo $first_name; ?>" id="settings_input"></p>
        </p>Last Name: <input type="text" name="last_name" value="<?php echo $last_name; ?>" id="settings_input"></p>
        </p>Email: <input type="text" name="email" value="<?php echo $email; ?>" id="settings_input"></p>

        <?php echo $message; ?>

        <input type="submit" name="update_details" id="save_details" value="Update Details" class="info settings_submit"><br>
    </form>

    <h4>Change Password</h4>
    <form action="settings.php" method="POST">
        </p>Old Password: <input type="password" name="old_password" id="settings_input"></p>
        </p>New Password: <input type="password" name="new_password_1" id="settings_input"></p>
        </p>Confirm New Password: <input type="password" name="new_password_2" id="settings_input"></p>

        <?php echo $password_message; ?>

        <input type="submit" name="update_password" id="update_password" value="Update Password" class="info settings_submit">

    </form>

    <h4>Close Account</h4>
    <form action="settings.php" method="POST">
        <input type="submit" name="close_account" id="close_account" value="Close Account" class="danger settings_submit">
    </form>

</div>