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
    if (!isset($_SESSION['user_id'])) {
        session_unset();
        session_destroy();
        header('Location: /index.html');
        exit;
    }
?>
<div class="ui basic segment">
    <div class="ui secondary pointing menu">
        <a class="active item" data-tab="private-chats">Private Chats</a>
        <a class="item" data-tab="group-chats">Group Chats</a>
        <div class="right menu">
            <a class="ui item" onclick="logout()">Logout</a>
        </div>
    </div>

    <div class="ui active tab segment" data-tab="private-chats">
        <div class="ui grid">
            <div class="four wide column">
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
                </div>
            </div>

            <!-- Chat box -->
            <div class="twelve wide column">
                <div class="ui card" style="width: 100%;">
                    <div class="content">
                        <div class="header">Name of the person</div>
                    </div>
                    <div class="content">
                        <div class="ui relaxed divided list" id="private-messages">
                            <div class="item">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/semantic.min.js"></script>
<script>
    const API_URL = "http://localhost:8000/index.php?action=";
    $('.menu .item').tab();

    function logout() {    
        window.location.href = API_URL + "logout";
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