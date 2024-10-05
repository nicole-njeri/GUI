<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require "load.php"; 


if (!isset($_SESSION['userId'])) {
   
    header("Location: login.php"); 
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_password'])) {
    
    $userId = $_SESSION['userId']; 
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    
    $errors = [];

    
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    
    if (empty($errors)) {
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
       
        if ($ObjDb !== null && isset($ObjDb->connection)) {
            
            $stmt = $ObjDb->connection->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute()) {
                
                header("Location: login.php");
                exit();
            } else {
                $errors['database'] = "Error updating the password.";
            }
        } else {
            $errors['database'] = "Database connection is not available.";
        }
    }
}


$ObjLayouts->heading();
$ObjMenus->main_menu();
?>

<div class="container mt-5">
    <h2>Set Password</h2>
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) {
                echo "<p>$error</p>";
            } ?>
        </div>
    <?php } ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password:</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="set_password" class="btn btn-primary">Set Password</button>
    </form>
</div>

<?php
$ObjLayouts->footer(); 
