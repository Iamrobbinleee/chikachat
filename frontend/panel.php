<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/semantic.min.css" rel="stylesheet">
    <title>Chat Panel</title>
</head>
<body>
<?php
    session_start();
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    if (!isset($_SESSION['user_id'])) {
        session_unset();
        session_destroy();
        header('Location: /index.html');
        exit;
    }
?>
    <div class="ui basic segment">
        <div>
            <h2>Hello there, <?php echo $_SESSION['username']; ?>!</h2>
        </div>

        <div>
            <a href="logout.php">Signout</a>
        </div>

         <div class="ui grid basic segment">
            <div class="eight wide column">
                <div class="ui card">
                    <div class="content">
                        <div class="header">Private Chats</div>
                    </div>
                    <div class="content">
                        <h4 class="ui sub header">Users</h4>
                        <div class="ui relaxed divided list" id="private-list">
                            <div class="item">Loading...</div>
                        </div>
                    </div>
                    <!-- <div class="extra content">
                        <button class="ui button">Join Project</button>
                    </div> -->
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui card">
                    <div class="content">
                        <div class="header">Group Chats</div>
                    </div>
                    <div class="content">
                        <h4 class="ui sub header">Activity</h4>
                        <div class="ui small feed">
                            <div class="event">
                                <div class="content">
                                <div class="summary">
                                    <a>Elliot Fu</a> added <a>Jenny Hess</a> to the project
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="extra content">
                        <button class="ui button">Join Project</button>
                    </div> -->
                </div>
            </div>
         </div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/semantic.min.js"></script>
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


async function loadUsers() {
    try {
        const res = await fetch(API_URL + "get_users", {
            method: "GET",
            credentials: "include"
        });
        const data = await res.json();

        if (data.status === "success") {
            const list = document.getElementById("private-list");
            list.innerHTML = "";

            data.users.forEach(user => {
                const item = document.createElement("div");
                item.className = "item";
                item.innerHTML = `
                    <i class="user icon"></i>
                    <div class="content">
                        <a class="header">${user.username || user.email}</a>
                        <div class="description">Start chat</div>
                    </div>
                `;
                item.onclick = () => {
                    // window.location.href = `chat.php?user_id=${user.id}&username=${encodeURIComponent(user.username)}`;
                    alert(user.username);
                };
                list.appendChild(item);
            });
        }
    } catch (err) {
        console.error("Error loading users:", err);
    }
}

document.addEventListener("DOMContentLoaded", loadUsers);
</script>
</body>
</html>