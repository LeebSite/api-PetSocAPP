<?php
header("Content-Type: application/json");
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Mengambil data posts
        $sql = "SELECT * FROM Posts";
        $result = $conn->query($sql);
        $posts = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        echo json_encode($posts, JSON_PRETTY_PRINT);
        break;

    case 'POST':
        // Menambahkan post baru
        if (isset($_POST['user_id']) && isset($_POST['pet_id']) && isset($_POST['content'])) {
            $user_id = $_POST['user_id'];
            $pet_id = $_POST['pet_id'];
            $content = $_POST['content'];
            $image = null;

            // Mengunggah file
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = 'uploads/' . basename($_FILES['image']['name']);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to upload image"], JSON_PRETTY_PRINT);
                    break;
                }
            }

            $sql = "INSERT INTO Posts (user_id, pet_id, content, image) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiss", $user_id, $pet_id, $content, $image);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Post added successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to add post"], JSON_PRETTY_PRINT);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
        }
        break;

    case 'PUT':
        // Memperbarui data post
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['post_id']) && isset($data['user_id']) && isset($data['pet_id']) && isset($data['content'])) {
            $post_id = $data['post_id'];
            $user_id = $data['user_id'];
            $pet_id = $data['pet_id'];
            $content = $data['content'];
            $image = isset($data['image']) ? $data['image'] : null;

            $sql = "UPDATE Posts SET user_id=?, pet_id=?, content=?, image=? WHERE post_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissi", $user_id, $pet_id, $content, $image, $post_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Post updated successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update post"], JSON_PRETTY_PRINT);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
        }
        break;

    case 'DELETE':
        // Menghapus post
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['post_id'])) {
            $post_id = $data['post_id'];
            $sql = "DELETE FROM Posts WHERE post_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $post_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Post deleted successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to delete post"], JSON_PRETTY_PRINT);
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
