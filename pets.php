<?php
header("Content-Type: application/json");
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Mengambil data pets
        $sql = "SELECT * FROM Pets";
        $result = $conn->query($sql);
        $pets = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pets[] = $row;
            }
        }
        echo json_encode($pets, JSON_PRETTY_PRINT);
        break;

    case 'POST':
        // Menambahkan pet baru
        if (isset($_POST['user_id']) && isset($_POST['name']) && isset($_POST['species'])) {
            $user_id = $_POST['user_id'];
            $name = $_POST['name'];
            $species = $_POST['species'];
            $breed = isset($_POST['breed']) ? $_POST['breed'] : null;
            $age = isset($_POST['age']) ? $_POST['age'] : null;
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

            $sql = "INSERT INTO Pets (user_id, name, species, breed, age, profile_picture) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssis", $user_id, $name, $species, $breed, $age, $profile_picture);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Pet added successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to add pet"], JSON_PRETTY_PRINT);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
        }
        break;

        case 'PUT':
            // Memperbarui data hewan peliharaan
            $data = json_decode(file_get_contents("php://input"), true);
        
            if (isset($data['pet_id']) && isset($data['user_id']) && isset($data['name']) && isset($data['species'])) {
                $pet_id = $data['pet_id'];
                $user_id = $data['user_id'];
                $name = $data['name'];
                $species = $data['species'];
                $breed = isset($data['breed']) ? $data['breed'] : null;
                $age = isset($data['age']) ? $data['age'] : null;
                $profile_picture = isset($data['profile_picture']) ? $data['profile_picture'] : null;
        
                $sql = "UPDATE Pets SET user_id=?, name=?, species=?, breed=?, age=?, profile_picture=? WHERE pet_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssisi", $user_id, $name, $species, $breed, $age, $profile_picture, $pet_id);
        
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Pet updated successfully"], JSON_PRETTY_PRINT);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to update pet"], JSON_PRETTY_PRINT);
                }
                $stmt->close();
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Bad Request: Missing required fields"], JSON_PRETTY_PRINT);
            }
            break;
        

    case 'DELETE':
        // Menghapus pet
        if (isset($_POST['pet_id'])) {
            $pet_id = $_POST['pet_id'];
            $sql = "DELETE FROM Pets WHERE pet_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pet_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Pet deleted successfully"], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to delete pet"], JSON_PRETTY_PRINT);
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
