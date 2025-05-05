
<?php
require_once 'DBController.php';
require_once 'SessionController.php';
require_once 'models/User.php';
require_once 'models/Badge.php';

class UserController {
    private $db;
    private $sessionController;
    
    public function __construct($db) {
        $this->db = $db;
        // Ensure the database connection is open
        if (!$this->db->connection) {
            $this->db->openConnection();
        }
        $this->sessionController = new SessionController();
    }
    
    public function register($username, $email, $password) {
        // Check if username already exists
        $username = $this->db->connection->real_escape_string($username);
        $email = $this->db->connection->real_escape_string($email);
        
        $query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $result = $this->db->select($query);
        
        if (!empty($result)) {
            return [
                'success' => false,
                'message' => 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل'
            ];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (username, email, password, reputation, created_at) 
                  VALUES ('$username', '$email', '$hashedPassword', 0, NOW())";
        $userId = $this->db->insert($query);
        
        if ($userId) {
            // Log user in
            $this->sessionController->login($userId, $username);
            
            return [
                'success' => true,
                'message' => 'تم التسجيل بنجاح!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء التسجيل'
            ];
        }
    }
    
    public function login($email, $password) {
        $email = $this->db->connection->real_escape_string($email);
        
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $this->db->select($query);
        
        if (empty($result)) {
            return [
                'success' => false,
                'message' => 'البريد الإلكتروني غير موجود'
            ];
        }
        
        $user = $result[0];
        
        if (password_verify($password, $user['password'])) {
            // Pass the correct is_admin value
            $isAdmin = isset($user['is_admin']) ? $user['is_admin'] : 0;
            $this->sessionController->login($user['id'], $user['username'], $isAdmin);
            
            return [
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح!',
                'isAdmin' => $isAdmin == 1
            ];
        } else {
            return [
                'success' => false,
                'message' => 'كلمة المرور غير صحيحة'
            ];
        }
    }
    
    public function getUserById($userId) {
        if (!$userId) {
            return null;
        }
        
        $userId = (int)$userId;
        $query = "SELECT * FROM users WHERE id = $userId";
        $result = $this->db->select($query);
        
        if (!empty($result)) {
            return $result[0];
        }
        
        return null;
    }
    
    public function getUserObject($userId) {
        $userData = $this->getUserById($userId);
        if ($userData) {
            return new User($userData);
        }
        return null;
    }
    
    public function updateProfile($userId, $data) {
        if (!$userId) {
            return false;
        }
        
        $userId = (int)$userId;
        $fields = [];
        
        foreach ($data as $key => $value) {
            if ($key != 'id') {
                $value = $this->db->connection->real_escape_string($value);
                $fields[] = "$key = '$value'";
            }
        }
        
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = $userId";
        $result = $this->db->update($query);
        
        return $result ? true : false;
    }
    
    public function getUserReputation($userId) {
        if (!$userId) {
            return 0;
        }
        
        $userId = (int)$userId;
        $query = "SELECT reputation FROM users WHERE id = $userId";
        $result = $this->db->select($query);
        
        if (!empty($result)) {
            return $result[0]['reputation'];
        }
        
        return 0;
    }
    
    public function getUserBadge($userId) {
        $reputation = $this->getUserReputation($userId);
        
        if ($reputation >= Badge::GOLD_THRESHOLD) {
            return [
                'type' => Badge::GOLD,
                'name' => 'ذهبي',
                'icon' => 'fas fa-medal text-warning'
            ];
        } else if ($reputation >= Badge::BRONZE_THRESHOLD) {
            return [
                'type' => Badge::BRONZE,
                'name' => 'برونزي',
                'icon' => 'fas fa-medal text-danger'
            ];
        } else if ($reputation >= Badge::SILVER_THRESHOLD) {
            return [
                'type' => Badge::SILVER,
                'name' => 'فضي',
                'icon' => 'fas fa-medal text-secondary'
            ];
        } else {
            return [
                'type' => 'beginner',
                'name' => 'مبتدئ',
                'icon' => 'fas fa-user text-info'
            ];
        }
    }
    
    public function canUserEditPosts($userId) {
        $user = $this->getUserObject($userId);
        if (!$user) {
            return false;
        }
        return $user->canEditPosts();
    }
}
?>
