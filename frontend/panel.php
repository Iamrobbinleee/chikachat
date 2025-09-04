<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Panel</title>
</head>
<body>
<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        session_unset();
        session_destroy();
        header('Location: /index.html');
        exit;
    }
?>
    <div>
        <div>
            <h2>Hello there, <?php echo $_SESSION['username']; ?>!</h2>
        </div>

        <div>
            <a href="logout.php">Signout</a>
        </div>

        <!-- List of Private Chats -->
        <div>

        </div>
    </div>

<script>
    const API_URL = "http://localhost:8000/index.php?action=";

    async function logout() {

      try {
        const status = true;
        const res = await fetch(API_URL + "logout", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ status })
        });
      } catch (err) {
        console.error(err);
        // alert("Error connecting to server.");
      }
    }
</script>
</body>
</html>