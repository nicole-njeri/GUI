<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require "load.php"; 


$errors = [];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    
    if (empty($errors)) {
        
        $stmt = $ObjDb->connection->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                
                $_SESSION['userId'] = $row['id'];
                
                header("Location: dashboard.php"); 
                exit();
            } else {
                $errors['login'] = "Invalid username or password.";
            }
        } else {
            $errors['login'] = "Invalid username or password.";
        }
    }
}


$ObjLayouts->heading();
$ObjMenus->main_menu();
?>

<div class="container mt-5">
    <h2>Login</h2>
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) {
                echo "<p>$error</p>";
            } ?>
        </div>
    <?php } ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Login</button>
    </form>
</div>

<?php
$ObjLayouts->footer(); 
?>
