<?php
// 1. Setup headers for JSON communication
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

// 2. Database Configuration
$host     = "localhost";
$db_name  = "inventory_db";
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password is empty

try {
    // Establish PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// 3. Handle incoming HTTP Request Method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    
    case 'GET':
        try {
            // Query to fetch all items from your table
            $query = "SELECT id, user_id, item_name, description, created_at FROM items";
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
        // Read the incoming raw JSON payload from Postman
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);
        
        // Validate that required fields are present
        if (!empty($data['user_id']) && !empty($data['item_name']) && !empty($data['description'])) {
            try {
                // Prepared statement to prevent SQL injection
                $query = "INSERT INTO items (user_id, item_name, description, created_at) 
                          VALUES (:user_id, :item_name, :description, NOW())";
                
                $stmt = $conn->prepare($query);
                
                // Bind user data parameters safely
                $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(':item_name', $data['item_name'], PDO::PARAM_STR);
                $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
                
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
                "message" => "Incomplete data. 'user_id', 'item_name', and 'description' are required."
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed. Use GET or POST."]);
        break;
}
?>