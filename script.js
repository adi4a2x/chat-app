// Connect to the WebSocket server
const socket = new WebSocket("ws://localhost:8080");

// When the WebSocket connection is open, send a welcome message
socket.addEventListener('open', () => {
    console.log("Connected to the WebSocket server!");
});

// When a message is received, display it in the chat
socket.addEventListener('message', (event) => {
    const messageContainer = document.getElementById("chatContainer");
    const messageElement = document.createElement("div");
    messageElement.textContent = event.data; // Display the incoming message
    messageContainer.appendChild(messageElement);
    messageContainer.scrollTop = messageContainer.scrollHeight; // Auto-scroll to the bottom
});

// Send message when the user clicks the send button
document.getElementById("sendButton").addEventListener("click", () => {
    const messageInput = document.getElementById("messageInput");
    const messageText = messageInput.value.trim();
    if (messageText !== "") {
        socket.send(messageText); // Send the message to the WebSocket server
        messageInput.value = ""; // Clear the input box
    }
});

// Send message when the user presses Enter key
document.getElementById("messageInput").addEventListener("keypress", (event) => {
    if (event.key === "Enter") {
        document.getElementById("sendButton").click();
    }
});
