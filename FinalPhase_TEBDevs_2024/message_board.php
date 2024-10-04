<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "message_board";
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
// Create messages table if not exists
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
 
if ($conn->query($sql) === FALSE) {
    die("Error creating messages table: " . $conn->error);
}
 
// Create comments table if not exists
$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id INT(6) UNSIGNED,
    content TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id)
)";
 
if ($conn->query($sql) === FALSE) {
    die("Error creating comments table: " . $conn->error);
}
 
function getMessages($conn) {
    $sql = "SELECT * FROM messages ORDER BY timestamp DESC";
    $result = $conn->query($sql);
    $messages = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
    return json_encode($messages);
}
 
function postMessage($conn, $content) {
    $stmt = $conn->prepare("INSERT INTO messages (content) VALUES (?)");
    $stmt->bind_param("s", $content);
    if ($stmt->execute()) {
        return json_encode(["success" => true]);
    } else {
        return json_encode(["success" => false, "error" => $stmt->error]);
    }
}
 
function getComments($conn, $messageId) {
    $stmt = $conn->prepare("SELECT * FROM comments WHERE message_id = ? ORDER BY timestamp ASC");
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
    }
    return json_encode($comments);
}
 
function postComment($conn, $messageId, $content) {
    $stmt = $conn->prepare("INSERT INTO comments (message_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $messageId, $content);
    if ($stmt->execute()) {
        return json_encode(["success" => true]);
    } else {
        return json_encode(["success" => false, "error" => $stmt->error]);
    }
}
 
// Handle requests
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if ($_GET['action'] == 'getMessages') {
        echo getMessages($conn);
    } elseif ($_GET['action'] == 'getComments' && isset($_GET['messageId'])) {
        echo getComments($conn, $_GET['messageId']);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['action'] == 'postMessage') {
        echo postMessage($conn, $_POST['content']);
    } elseif ($_POST['action'] == 'postComment') {
        echo postComment($conn, $_POST['messageId'], $_POST['content']);
    }
}
 
$conn->close();
?>