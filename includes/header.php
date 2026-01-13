<header>
    <div class="talland-logo">
        <img src="images\Placeholder Talland logo.png" alt="Talland Logo">
    </div>
    <?php include("zoek.php"); ?>
    <div class="account-buttons">
        <?php
        if (isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
            echo '<a class="admin-btn" href="adminpanel.php">Admin</a>';
        }
        ?>
        <a class="account-btn" href="loginform.php">
            <svg class="account-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.33 0-8 2.17-8 5v1h16v-1c0-2.83-3.67-5-8-5z" />
            </svg>
        </a>
    </div>
</header>