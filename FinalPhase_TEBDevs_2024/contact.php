<?php
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "contact_us_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Subscription functionality
    if (!empty($_POST['email'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $query = "INSERT INTO subscribers (email) VALUES ('$email')";

        if ($conn->query($query) === TRUE) {
            echo "<p style='color:green;'>Thank you for subscribing, $email!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
        }
    }

    // File upload functionality
    if (isset($_FILES['file'])) {
        $fileName = $_FILES['file']['name'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileDestination = 'uploads/' . $fileName;

        // Create upload directory if not exists
        if (!is_dir('uploads')) {
            mkdir('uploads');
        }

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            echo "<p style='color:green;'>File uploaded successfully: $fileName</p>";
        } else {
            echo "<p style='color:red;'>Failed to upload file.</p>";
        }
    }

    // Question and comment functionality
    if (!empty($_POST['question'])) {
        $question = htmlspecialchars($_POST['question']);  // Sanitize input
        echo "<p style='color:blue;'>Your question/comment has been submitted: $question</p>";
    }
}
?>