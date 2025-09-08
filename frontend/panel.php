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
    $currentUser = $_SESSION['user_id'];
?>
<div class="ui basic segment">
    <div class="ui secondary pointing menu">
        <a class="active item" data-tab="private-chats">Private Chats</a>
        <!-- <a class="item" data-tab="group-chats">Group Chats</a> -->
        <div class="right menu">
            <a class="ui item" onclick="logout()">Logout</a>
        </div>
    </div>

    <div class="ui active tab segment" data-tab="private-chats">
        <div class="ui grid">
            <!-- Sidebar -->
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
                        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="selected-username"></span>
                            <i class="close icon link" id="close-current-user" onclick="closeChat()" title="Close chat"></i>
                        </div>
                    </div>
                    <div class="ui comments content" style="height:400px; max-width: 100%; overflow-y:auto;" id="private-messages">
                        <h2 id="default-msg" style="text-align: center; margin-top: 50px;">WELCOME TO CHIKACHAT!</h2>
                    </div>
                    <div class="extra content" id="type-send">
                        <form class="ui reply form" onsubmit="handleSend(event)">
                            <div class="field">
                                <textarea id="message-input" style="max-height: 50px;" placeholder="Type a message..."></textarea>
                            </div>
                            <div style="text-align: right;">
                                <button id="sendMyMsg" class="ui blue labeled submit icon button">
                                    <i class="paper plane icon"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="ui tab segment" data-tab="group-chats">
        <div class="ui grid">
            <div class="four wide column">
                <div class="ui card">
                    <div class="content">
                        <div class="header">Group Chats</div>
                    </div>
                    <div class="content">
                        <h4 class="ui sub header">Users</h4>
                        <div class="ui relaxed divided list" id="group-list">
                            <div class="item">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/semantic.min.js"></script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script>
const API_URL = "http://localhost:8000/index.php?action=";
const socket = io("http://localhost:3000");
const loggedInUser = "<?php echo $currentUser; ?>";

$('.menu .item').tab();

let selectedUser = null;
let selectedUserId = null;

document.getElementById('default-msg').style.display = 'block';
document.getElementById('type-send').style.display = 'none';
document.getElementById('close-current-user').style.display = 'none';

// Join socket room for this user
socket.emit("join", loggedInUser);

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
                if (user.id === loggedInUser) return;
                const username = user.username || user.email;

                const item = document.createElement("div");
                item.className = "item";
                item.innerHTML = `
                    <i class="user icon"></i>
                    <div class="content">
                        <a class="header">${username}</a>
                        <div class="description">Start chat</div>
                    </div>
                `;
                item.onclick = () => {
                    selectUser(username, user.id);
                };
                list.appendChild(item);

                // if (!conversations[user.username]) {
                //     conversations[user.username] = [
                //         { from: user.username, text: "Hello 👋 This is your first message.", time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) },
                //     ];
                // }
            });
        }
    } catch (err) {
        console.error("Error loading users:", err);
    }
}

function selectUser(username, selUserId) {
    selectedUser = username;
    selectedUserId = selUserId;
    document.getElementById("selected-username").textContent = username;
    document.getElementById('type-send').style.display = 'block';
    document.getElementById('close-current-user').style.display = 'block';
    if(document.getElementById('default-msg')){
        document.getElementById('default-msg').style.display = 'none';
    }
        
    document.getElementById("message-input").value = "";
    toggleSendButton(false);

    // Load conversation from server
    socket.emit("load_conversation", { userA: loggedInUser, userB: selUserId });
    // document.getElementById('default-msg').style.display = 'none';
}

function renderConversation(messages) {
    const container = document.getElementById("private-messages");
    container.innerHTML = "";

    if (!messages || messages.length === 0) {
        container.innerHTML = `<h4 style="text-align:center;color:gray;">Start Chatting with ${selectedUser}</h4>`;
        return;
    }

    messages.forEach(msg => {
        const div = document.createElement("div");
        div.className = "comment";

        if (msg.sender_id === loggedInUser) {
            div.innerHTML = `
                <div class="content" style="text-align:right;">
                    <div class="metadata"><span>${formatTime(msg.timestamp)}</span></div>
                    <div class="text" style="background:#2185d0; color:white; display:inline-block; padding:10px; border-radius:12px;">
                        ${msg.content}
                    </div>
                    <a class="author">You</a> <i class="user icon"></i>
                </div>
            `;
        } else {
            div.innerHTML = `
                <div class="content" style="text-align:left;">
                    <i class="user icon"></i> <a class="author">${selectedUser}</a>
                    <div class="text" style="background:#f1f1f1; display:inline-block; padding:10px; border-radius:12px;">
                        ${msg.content}
                    </div>
                    <div class="metadata"><span>${formatTime(msg.timestamp)}</span></div>
                </div>
            `;
        }

        container.appendChild(div);
    });

    container.scrollTop = container.scrollHeight;
}

function handleSend(e) {
    e.preventDefault();
    if (!selectedUser) return;

    const input = document.getElementById("message-input");
    const text = input.value.trim();
    if (!text) return;

    socket.emit("private_message", {
        senderId: loggedInUser,
        receiverId: selectedUserId,
        content: text
    });

    input.value = "";
    toggleSendButton(false);
}

function closeChat(){
    document.getElementById("private-messages").innerHTML = `
        <h2 id="default-msg" style="text-align: center; margin-top: 50px;">
            WELCOME TO CHIKACHAT!
        </h2>
    `;
    document.getElementById('type-send').style.display = 'none';
    document.getElementById('close-current-user').style.display = 'none';
    document.getElementById("selected-username").textContent = '';
    selectedUser = null;
    selectedUserId = null;
}

// === SOCKET EVENTS ===
socket.on("conversation_history", ({ userA, userB, messages }) => {
  if (userB === selectedUserId || userA === selectedUserId) {
    renderConversation(messages);
  }
});


socket.on("private_message", (msg) => {
    if (msg.sender_id === selectedUserId || msg.receiver_id === selectedUserId) {
        socket.emit("load_conversation", { userA: loggedInUser, userB: selectedUserId });
    }
});

// === HELPERS ===
function toggleSendButton(enabled) {
    const btn = document.getElementById('sendMyMsg');
    if (enabled) {
        btn.style.pointerEvents = '';
        btn.style.backgroundColor = '';
    } else {
        btn.style.pointerEvents = 'none';
        btn.style.backgroundColor = 'gray';
    }
}

function formatTime(ts) {
    return new Date(ts).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

document.getElementById('message-input').addEventListener('input', () => {
    const text = document.getElementById("message-input").value;
    toggleSendButton(text.trim() !== "");
});

document.addEventListener("DOMContentLoaded", loadUsers);
</script>
</body>
</html>
