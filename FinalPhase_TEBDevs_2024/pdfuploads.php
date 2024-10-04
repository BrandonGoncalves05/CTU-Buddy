<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "resource_uploads";
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
// Function to handle file upload
function handleUpload($conn) {
    if (isset($_FILES["pdfFile"]) && $_FILES["pdfFile"]["error"] == 0) {
        $allowed = array("pdf" => "application/pdf");
        $filename = $_FILES["pdfFile"]["name"];
        $filetype = $_FILES["pdfFile"]["type"];
        $filesize = $_FILES["pdfFile"]["size"];
   
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            return "Error: Please select a valid file format.";
        }
   
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            return "Error: File size is larger than the allowed limit.";
        }
   
        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            if (file_exists("uploads1/" . $filename)) {
                return $filename . " already exists.";
            } else {
                if (move_uploaded_file($_FILES["pdfFile"]["tmp_name"], "uploads1/" . $filename)) {
                    // Insert file information into database
                    $sql = "INSERT INTO pdfs (filename, filepath) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $filepath = "uploads1/" . $filename;
                    $stmt->bind_param("ss", $filename, $filepath);
                   
                    if ($stmt->execute()) {
                        return "The file " . $filename . " has been uploaded and stored in the database.";
                    } else {
                        return "Error: " . $sql . "<br>" . $conn->error;
                    }
                    $stmt->close();
                } else {
                    return "Error: There was a problem uploading your file. Please try again.";
                }
            }
        } else {
            return "Error: There was a problem uploading your file. Please try again.";
        }
    } else {
        return "Error: " . $_FILES["pdfFile"]["error"];
    }
}
 
// Function to list all PDFs
function listPDFs($conn) {
    $sql = "SELECT id, filename FROM pdfs";
    $result = $conn->query($sql);
   
    $pdfs = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $pdfs[] = $row;
        }
    }
    return $pdfs;
}
 
// Function to view a PDF
function viewPDF($id) {
    $sql = "SELECT filepath FROM pdfs WHERE id = ?";
    $stmt = $GLOBALS['conn']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
   
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filepath = $row['filepath'];
        if (file_exists($filepath)) {
            header('Content-Type: application/pdf');
            readfile($filepath);
            exit;
        } else {
            echo "File not found.";
        }
    } else {
        echo "PDF not found in database.";
    }
}
 
// Function to download a PDF
function downloadPDF($id) {
    $sql = "SELECT filename, filepath FROM pdfs WHERE id = ?";
    $stmt = $GLOBALS['conn']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
   
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filepath = $row['filepath'];
        $filename = $row['filename'];
        if (file_exists($filepath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($filepath);
            exit;
        } else {
            echo "File not found.";
        }
    } else {
        echo "PDF not found in database.";
    }
}
 
// Handle different actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["pdfFile"])) {
        echo handleUpload($conn);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'list':
                echo json_encode(listPDFs($conn));
                break;
            case 'view':
                if (isset($_GET['id'])) {
                    viewPDF($_GET['id']);
                }
                break;
            case 'download':
                if (isset($_GET['id'])) {
                    downloadPDF($_GET['id']);
                }
                break;
        }
    }
}
 
$conn->close();
?>
 