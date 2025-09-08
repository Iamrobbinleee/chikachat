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

  // === PRIVATE CHAT ===
  // Join private chat room (userId is usually your logged-in user’s id/username/email)
  socket.on("join", (userId) => {
    socket.join(userId);
    console.log(`User ${userId} joined private room`);
  });

  // Load previous conversation between two users
  socket.on("load_conversation", async ({ userA, userB }) => {
  const messages = await db.collection("messages")
    .find({
      $or: [
        { sender_id: userA, receiver_id: userB },
        { sender_id: userB, receiver_id: userA }
      ]
    })
    .sort({ timestamp: 1 })
    .toArray();
  
  socket.emit("conversation_history", { userA, userB, messages });
});

  // Handle sending private message
  socket.on("private_message", async ({ senderId, receiverId, content }) => {
    const msg = {
      sender_id: senderId,
      receiver_id: receiverId,
      content,
      timestamp: new Date(),
      status: "sent"
    };

    await db.collection("messages").insertOne(msg);

    // Send to both sender and receiver
    io.to(receiverId).emit("private_message", msg);
    io.to(senderId).emit("private_message", msg);
  });

  // === GROUP CHAT ===
  socket.on("join_group", (groupId) => {
    socket.join(groupId);
    console.log(`User joined group ${groupId}`);
  });

  // Load previous group conversation
  socket.on("load_group_conversation", async (groupId) => {
    const messages = await db.collection("messages")
      .find({ group_id: groupId })
      .sort({ timestamp: 1 })
      .toArray();

    socket.emit("group_conversation_history", { groupId, messages });
  });

  // Handle group message
  socket.on("group_message", async ({ senderId, groupId, content }) => {
    const msg = {
      sender_id: senderId,
      group_id: groupId,
      content,
      timestamp: new Date(),
      status: "sent"
    };

    await db.collection("messages").insertOne(msg);

    // Emit to everyone in the group
    io.to(groupId).emit("group_message", msg);
  });

  socket.on("disconnect", () => {
    console.log("User disconnected:", socket.id);
  });
});
