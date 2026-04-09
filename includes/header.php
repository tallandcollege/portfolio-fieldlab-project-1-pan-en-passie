<!-- header included on every page of the site -->
<header>
    <a href="index.php">
        <div class="talland-logo">
            <img src="images\Placeholder Talland logo.png" alt="Talland Logo">
        </div>
    </a>
    <!-- Searchbar include with all the logic behind it -->
    <?php include("zoek.php"); ?>
    <div class="account-buttons">
        <?php
        // shows the admin and docent buttons in the header if the users account has the corresponding role.
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
            echo '<a class="admin-btn" href="adminpanel.php">Admin</a>';
        }
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'docent') {
            echo '<a class="admin-btn" href="docentpanel.php">Docent</a>';
        }
        // Checks if the user is logged in and sets the account button link accordingly. If not logged in, it redirects to the login page.
        if (isset($_SESSION['user_id'])) {
            $profileHref = "profile.php?id=" . urlencode($_SESSION['user_id']);
        } else {
            $profileHref = "loginform.php";
        }
        ?>
        <!-- profile button -->
        <a class="account-btn" href="<?php echo $profileHref; ?>">
            <svg class="account-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.33 0-8 2.17-8 5v1h16v-1c0-2.83-3.67-5-8-5z" />
            </svg>
        </a>
    </div>
</header>
