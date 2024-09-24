<?php
header("Content-Type: application/json");
require 'db.php';
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$request = explode('/', trim($uri[0], '/'));
$resource = array_shift($request);
$id = array_shift($request);

switch ($resource) {
    case 'users':
        handleUsers($method, $id);
        break;
    case 'pets':
        handlePets($method, $id);
        break;
    case 'posts':
        handlePosts($method, $id);
        break;
    case 'comments':
        handleComments($method, $id);
        break;
    case 'likes':
        handleLikes($method, $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Resource not found"]);
        break;
}

function handleUsers($method, $id) {
    global $conn;
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Users");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("INSERT INTO Users (username, password, email, full_name, profile_picture) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $data['username'], $data['password'], $data['email'], $data['full_name'], $data['profile_picture']);
            $stmt->execute();
            echo json_encode(["user_id" => $stmt->insert_id]);
            break;
        case 'PUT':
            if ($id) {
                $data = json_decode(file_get_contents("php://input"), true);
                $stmt = $conn->prepare("UPDATE Users SET username=?, password=?, email=?, full_name=?, profile_picture=? WHERE user_id=?");
                $stmt->bind_param("sssssi", $data['username'], $data['password'], $data['email'], $data['full_name'], $data['profile_picture'], $id);
                $stmt->execute();
                echo json_encode(["message" => "User updated"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "User ID required"]);
            }
            break;
        case 'DELETE':
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM Users WHERE user_id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                echo json_encode(["message" => "User deleted"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "User ID required"]);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}

function handlePets($method, $id) {
    global $conn;
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM Pets WHERE pet_id = ?");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Pets");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("INSERT INTO Pets (user_id, name, species, breed, age, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssis", $data['user_id'], $data['name'], $data['species'], $data['breed'], $data['age'], $data['profile_picture']);
            $stmt->execute();
            echo json_encode(["pet_id" => $stmt->insert_id]);
            break;
        case 'PUT':
            if ($id) {
                $data = json_decode(file_get_contents("php://input"), true);
                $stmt = $conn->prepare("UPDATE Pets SET user_id=?, name=?, species=?, breed=?, age=?, profile_picture=? WHERE pet_id=?");
                $stmt->bind_param("isssisi", $data['user_id'], $data['name'], $data['species'], $data['breed'], $data['age'], $data['profile_picture'], $id);
                $stmt->execute();
                echo json_encode(["message" => "Pet updated"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Pet ID required"]);
            }
            break;
        case 'DELETE':
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM Pets WHERE pet_id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                echo json_encode(["message" => "Pet deleted"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Pet ID required"]);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}

function handlePosts($method, $id) {
    global $conn;
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM Posts WHERE post_id = ?");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Posts");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("INSERT INTO Posts (user_id, pet_id, content, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $data['user_id'], $data['pet_id'], $data['content'], $data['image']);
            $stmt->execute();
            echo json_encode(["post_id" => $stmt->insert_id]);
            break;
        case 'PUT':
            if ($id) {
                $data = json_decode(file_get_contents("php://input"), true);
                $stmt = $conn->prepare("UPDATE Posts SET user_id=?, pet_id=?, content=?, image=? WHERE post_id=?");
                $stmt->bind_param("iissi", $data['user_id'], $data['pet_id'], $data['content'], $data['image'], $id);
                $stmt->execute();
                echo json_encode(["message" => "Post updated"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Post ID required"]);
            }
            break;
        case 'DELETE':
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM Posts WHERE post_id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                echo json_encode(["message" => "Post deleted"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Post ID required"]);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}

function handleComments($method, $id) {
    global $conn;
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM Comments WHERE comment_id = ?");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Comments");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("INSERT INTO Comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $data['post_id'], $data['user_id'], $data['content']);
            $stmt->execute();
            echo json_encode(["comment_id" => $stmt->insert_id]);
            break;
        case 'PUT':
            if ($id) {
                $data = json_decode(file_get_contents("php://input"), true);
                $stmt = $conn->prepare("UPDATE Comments SET post_id=?, user_id=?, content=? WHERE comment_id=?");
                $stmt->bind_param("iisi", $data['post_id'], $data['user_id'], $data['content'], $id);
                $stmt->execute();
                echo json_encode(["message" => "Comment updated"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Comment ID required"]);
            }
            break;
        case 'DELETE':
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM Comments WHERE comment_id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                echo json_encode(["message" => "Comment deleted"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Comment ID required"]);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}

function handleLikes($method, $id) {
    global $conn;
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM Likes WHERE like_id = ?");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Likes");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("INSERT INTO Likes (post_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $data['post_id'], $data['user_id']);
            $stmt->execute();
            echo json_encode(["like_id" => $stmt->insert_id]);
            break;
        case 'DELETE':
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM Likes WHERE like_id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                echo json_encode(["message" => "Like deleted"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Like ID required"]);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}
?>