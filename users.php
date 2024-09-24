<?php
header("Content-Type: application/json");
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Mengambil data users
        $sql = "SELECT * FROM Users";
        $result = $conn->query($sql);
        $users = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        echo json_encode($users, JSON_PRETTY_PRINT);
        break;

    case 'POST':
        // Menambahkan user baru
        if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $email = $_POST['email'];
            $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : null;
            $profile_picture = null;

            // Mengunggah file
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to upload profile picture"], JSON_PRETTY_PRINT);
                    break;
                }
            }

            $sql = "INSERT INTO Users (username, password, email, full_name, profile_picture) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $password, $email, $full_name, $profile_picture);

            if ($stmt->execute()) {
                echo json_encode(["message" => "User added successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to add user"], JSON_PRETTY_PRINT);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
        }
        break;

    case 'PUT':
        // Memperbarui data user
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['user_id']) && isset($data['username']) && isset($data['email'])) {
            $user_id = $data['user_id'];
            $username = $data['username'];
            $email = $data['email'];
            $full_name = isset($data['full_name']) ? $data['full_name'] : null;
            $profile_picture = isset($data['profile_picture']) ? $data['profile_picture'] : null;

            if (isset($data['password']) && !empty($data['password'])) {
                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $sql = "UPDATE Users SET username=?, password=?, email=?, full_name=?, profile_picture=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $username, $password, $email, $full_name, $profile_picture, $user_id);
            } else {
                $sql = "UPDATE Users SET username=?, email=?, full_name=?, profile_picture=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $username, $email, $full_name, $profile_picture, $user_id);
            }

            if ($stmt->execute()) {
                echo json_encode(["message" => "User updated successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update user"], JSON_PRETTY_PRINT);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
        }
        break;

    case 'DELETE':
        // Menghapus user
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
            $sql = "DELETE FROM Users WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "User deleted successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to delete user"], JSON_PRETTY_PRINT);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"], JSON_PRETTY_PRINT);
        break;
}

$conn->close();
?>
