const { MongoClient } = require("mongodb");
const { Server } = require("socket.io");

const io = new Server(3000, {
  cors: { origin: "*" }
});

const mongoUrl = "mongodb://localhost:27017";
const client = new MongoClient(mongoUrl);
let db;

async function init() {
  await client.connect();
  db = client.db("chikachat");
  console.log("MongoDB connected");
}
init();

io.on("connection", (socket) => {
  console.log("User connected:", socket.id);

  // Join private chat room
  socket.on("join", (userId) => {
    socket.join(userId);
    console.log(`User ${userId} joined`);
  });

  // Handle private message
  socket.on("private_message", async ({ senderId, receiverId, content }) => {
    const msg = {
      sender_id: senderId,
      receiver_id: receiverId,
      content,
      timestamp: new Date(),
      status: "sent"
    };

    await db.collection("messages").insertOne(msg);

    io.to(receiverId).emit("private_message", msg);
    io.to(senderId).emit("private_message", msg);
  });

  //Group Chats
    // Join group
    socket.on("join_group", (groupId) => {
    socket.join(groupId);
    console.log(`User joined group ${groupId}`);
    });

    // Send group message
    socket.on("group_message", async ({ senderId, groupId, content }) => {
    const msg = {
        sender_id: senderId,
        group_id: groupId,
        content,
        timestamp: new Date(),
        status: "sent"
    };

    await db.collection("messages").insertOne(msg);

    // Emit to all users in the group (except sender)
    io.to(groupId).emit("group_message", msg);
    });
});
