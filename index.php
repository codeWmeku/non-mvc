<?php
session_start();
require_once 'Database.php';
require_once 'User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->connect();
    $userModel = new User($db);

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: index.php");
        exit;
    }

    $user = $userModel->authenticateUser($email, $password);

    if ($user === 'inactive') {
        $_SESSION['error'] = "Your account is inactive. Please contact support.";
        header("Location: index.php");
        exit;
    }

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header("Location: Admin.php");
        } elseif ($user['role'] === 'member') {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid credentials.";
        header("Location: index.php");
        exit;
    }
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
<link rel="stylesheet" href="./css/style.css">

<div class="container" id="container">
    <!-- Sign In Form -->
    <div class="form-container sign-in-container">
        <form action="?page=login" method="POST">
            <h1>Sign in</h1>
            <div class="social-container">
                <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <span>or use your account</span>
            <div class="infield">
                <input type="text" placeholder="Email" name="email" />
                <label></label>
            </div>
            <div class="infield">
                <input type="password" placeholder="Password" name="password" />
                <label></label>
            </div>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                <?php unset($_SESSION['error']);?>
            <?php endif; ?>
            <button type="submit" name="login">Sign In</button>
        </form>
    </div>

    <!-- Sign Up Form -->
    <div class="form-container sign-up-container">
        <form action="?page=register" method="POST">
            <h1>Sign Up</h1>
            <div class="social-container">
                <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <span>or use your email to register</span>
            <div class="infield">
                <input type="text" placeholder="Name" name="name" />
                <label></label>
            </div>
            <div class="infield">
                <input type="email" placeholder="Email" name="email" />
                <label></label>
            </div>
            <div class="infield">
                <input type="password" placeholder="Password" name="password" />
                <label></label>
            </div>
            <?php if (isset($_SESSION['error'])): ?>
                    <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                    <?php unset($_SESSION['error']);?>
                <?php endif; ?>
            <button type="submit" name="register">Sign Up</button>
        </form>
    </div>

    <div class="overlay-container" id="overlayCon">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Hello, Friend!</h1>
                <p>Register to Help others</p>
                <button id="signIn">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Welcome Back!</h1>
                <p>Continue Helping Others</p>
                <button id="signUp">Sign Up</button>
            </div>
        </div>
        <button id="overlayBtn"></button>
    </div>  
</div>

<script>
    const container = document.getElementById('container');
    const overlayBtn = document.getElementById('overlayBtn');

    overlayBtn.addEventListener('click', () => {
        container.classList.toggle('right-panel-active');
        overlayBtn.classList.remove('btnScaled');
        window.requestAnimationFrame(() => {
            overlayBtn.classList.add('btnScaled');
        });
    });
</script>