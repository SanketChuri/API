<?php
// Include the database connection
require_once 'DataBaseConnection.php';

// Set headers for JSON response and CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Remove base path from the request URI
$basePath = '/~sgschuri/v2'; 
$path = str_replace($basePath, '', $requestUri);
$path = trim($path, '/');

// Split the path into segments
$request = explode('/', $path);

// Function to send JSON response
function sendResponse($status, $body = null) {
    http_response_code($status);
    if (!is_null($body)) {
        echo json_encode($body);
    }
    exit();
}

try {
    switch ($method) {
        case 'GET':
        // Handle GET requests
            if (count($request) == 1 && $request[0] == 'department') {
                // Get all departments
                $stmt = $pdo->query("SELECT * FROM department ORDER BY department_name");
                $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (empty($departments)) {
                    sendResponse(404, ["message" => "No departments found"]);
                } else {
                    sendResponse(200, $departments);
                }
            } elseif (count($request) == 2 && $request[0] == 'department') {
                // Get all academics of a specific department
                $departmentId = $request[1];
                $stmt = $pdo->prepare("SELECT * FROM academics WHERE department_id = ?");
                $stmt->execute([$departmentId]);
                $academics = $stmt->fetchAll();
                sendResponse(200, $academics);
            } elseif (count($request) == 4 && $request[0] == 'department' && $request[2] == 'academics') {
                // Get a specific academic of a specific department
                $departmentId = $request[1];
                $academicId = $request[3];
                $stmt = $pdo->prepare("SELECT * FROM academics WHERE department_id = ? AND academic_id = ?");
                $stmt->execute([$departmentId, $academicId]);
                $academic = $stmt->fetch();
                if ($academic) {
                    sendResponse(200, $academic);
                } else {
                    sendResponse(404, ["error" => "Academic not found"]);
                }
            }
            break;

        case 'POST':
        // Handle POST requests
            // Add an academic to a specific department
            if (count($request) < 2) {
                sendResponse(400, ["error" => "Department ID is required"]);
            }
        
            $departmentId = $request[1];
            
            // Decode as an associative array
            $data = json_decode(file_get_contents("php://input"), true);  
        
            
            //error_log('POST Data: ' . print_r($data, true));
        
            // Check if the required fields are present in the decoded data
            if (!isset($data['surname']) || !isset($data['givenName']) || !isset($data['title']) || !isset($data['email'])) {
                sendResponse(400, ["error" => "Missing required fields"]);
            }
            
            // Determine the next ID
            $idGeneratorstmt = $pdo->query("SELECT MAX(academic_id) AS max_id FROM academics");
            $idGeneratorrow = $idGeneratorstmt->fetch(PDO::FETCH_ASSOC);
            $newId = $idGeneratorrow['max_id'] ? $idGeneratorrow['max_id'] + 1 : 1;
        
            // Insert data into the database
            $stmt = $pdo->prepare("INSERT INTO academics (surname, givenName, title, email, department_id, academic_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['surname'], $data['givenName'], $data['title'], $data['email'], $departmentId, $newId]);
            
            // Prepare the SQL to increment the number_of_academics column
            $numberOfAcademicsstmt = $pdo->prepare("UPDATE department SET numberOfAcademics = numberOfAcademics + 1 WHERE department_id = ?");
            // Execute the statement with the department ID
            $numberOfAcademicsstmt->execute([$departmentId]);
        
            // Send response with the ID of the newly inserted record
            sendResponse(201, ["message" => "Academic added successfully", "id" => $newId]);
            
            
            break;

        case 'PUT':
        // Handle PUT requests
            // Update information for a specific academic of a specific department
            if (count($request) < 2) {
                sendResponse(400, ["error" => "Department ID and Academic ID are required"]);
            }
        
            $departmentId = $request[1];
            $academicId = $request[3];
            // Decode as an associative array
            $data = json_decode(file_get_contents("php://input"), true);  
        
            // Check if there are fields to update
            $fieldsToUpdate = [];
            $values = [];
        
            if (isset($data['surname'])) {
                $fieldsToUpdate[] = "surname = ?";
                $values[] = $data['surname'];
            }
            if (isset($data['givenName'])) {
                $fieldsToUpdate[] = "givenName = ?";
                $values[] = $data['givenName'];
            }
            if (isset($data['title'])) {
                $fieldsToUpdate[] = "title = ?";
                $values[] = $data['title'];
            }
            if (isset($data['email'])) {
                $fieldsToUpdate[] = "email = ?";
                $values[] = $data['email'];
            }
        
            if (empty($fieldsToUpdate)) {
                sendResponse(400, ["error" => "No fields to update"]);
            }
        
            // Add the academic_id and department_id to the values array
            $values[] = $academicId;
            $values[] = $departmentId;
        
            // Construct the SQL query dynamically
            $sql = "UPDATE academics SET " . implode(", ", $fieldsToUpdate) . " WHERE academic_id = ? AND department_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
        
            if ($stmt->rowCount() > 0) {
                sendResponse(200, ["message" => "Academic updated successfully"]);
            } else {
                sendResponse(404, ["error" => "Academic not found"]);
            }
            break;

        case 'DELETE':
        // Handle DELETE requests
            // Delete a specific academic from a specific department
            if (count($request) < 2) {
                sendResponse(400, ["error" => "Department ID and Academic ID are required"]);
            }

            $departmentId = $request[1];
            $academicId = $request[3];
            
            $stmt = $pdo->prepare("DELETE FROM academics WHERE department_id = ? AND academic_id = ?");
            $stmt->execute([$departmentId, $academicId]);

            if ($stmt->rowCount() > 0) {
                // Prepare the SQL to decrement the numberOfAcademics column
                $deletestmt = $pdo->prepare("UPDATE department SET numberOfAcademics = numberOfAcademics - 1 WHERE department_id = ?");
                $deletestmt->execute([$departmentId]);
                sendResponse(200, ["message" => "Academic deleted successfully"]);
            } else {
                sendResponse(404, ["error" => "Academic not found"]);
            }
            break;

        default:
            sendResponse(405, ["error" => "Method not allowed"]);
            break;
    }
} catch (Exception $e) {
    // Catch any other exceptions
    sendResponse(500, ["error" => "Server error: " . $e->getMessage()]);
}
?>
