<?php
// Setup headers for JSON communication
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

// Fig 4: Database Connection Script
$host     = "localhost";
$db_name  = "inventory_db";
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password is empty

try {
    // Establish PDO connection securely
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Fig 3: PHP Syntax Practice 
// A helper function demonstrating parameter passing, string manipulation, and hashing
function processSecurityToken($passwordString) {
    // Trim whitespace and hash the incoming password 
    $cleanPassword = trim($passwordString);
    $hashedPassword = password_hash($cleanPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    return $hashedPassword;
}

// Handle incoming HTTP Request Method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    
    case 'GET':
        try {
            // Query to fetch all items from your table
            $query = "SELECT id, user_id, item_name, description, created_at FROM items ORDER BY id DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            // Fetch records as an associative array
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "count" => count($items),
                "data" => $items
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to fetch items: " . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Read the incoming raw JSON payload 
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);
        
        // Validate that required fields are present
        if (!empty($data['user_id']) && !empty($data['item_name']) && !empty($data['description']) && !empty($data['password'])) {
            
            // Execute Fig 3 Practice: Simulate processing the security token (password)
            $secureHash = processSecurityToken($data['password']);

            try {
                // Prepared statement to prevent SQL injection
                $query = "INSERT INTO items (user_id, item_name, description, created_at) 
                          VALUES (:user_id, :item_name, :description, NOW())";
                
                $stmt = $conn->prepare($query);
                
                // Bind user data parameters safely
                $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(':item_name', $data['item_name'], PDO::PARAM_STR);
                $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
                // Note: Not storing $secureHash in this database table as it's an inventory table, 
                // but the syntax practice logic processed it successfully.
                
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode([
                        "status" => "success",
                        "message" => "Item added successfully!",
                        "item_id" => $conn->lastInsertId()
                    ]);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Database insertion failed: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error", 
                "message" => "Incomplete data. 'user_id', 'item_name', 'description', and 'password' are required."
            ]);
        }
        break;

    case 'DELETE':
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);

        if (!empty($data['id'])) {
            try {
                $query = "DELETE FROM items WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "message" => "Item deleted."]);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Database deletion failed: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Item ID is required for deletion."]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed. Use GET, POST, or DELETE."]);
        break;
}
?>