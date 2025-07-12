<?php
session_start();

// Check if a file was uploaded
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
    $fileName = $_FILES['profile_photo']['name'];
    $fileSize = $_FILES['profile_photo']['size'];
    $fileType = $_FILES['profile_photo']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Allowed file extensions
    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Create a unique name for the file
        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $uploadFileDir = __DIR__ . '/uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Save the filename in session
            $_SESSION['profile_photo'] = $newFileName;
        } else {
            // Optionally, set an error message in session
            $_SESSION['profile_photo_error'] = 'There was an error moving the uploaded file.';
        }
    } else {
        $_SESSION['profile_photo_error'] = 'Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.';
    }
} else {
    $_SESSION['profile_photo_error'] = 'No file uploaded or upload error.';
}

// Redirect back to driver_dashboard.php
header('Location: driver_dashboard.php');
exit;
