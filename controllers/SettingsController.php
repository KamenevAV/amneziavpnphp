<?php

class SettingsController {
    private $pdo;
    private $translator;
    
    public function __construct() {
        $this->pdo = DB::conn();
        $this->translator = new Translator();
    }
    
    public function index() {
        $stats = $this->getTranslationStats();
        $users = $this->getAllUsers();
        $apiKey = $this->getApiKey('openrouter');
        
        $data = [
            'translation_stats' => $stats,
            'users' => $users,
            'openrouter_key' => $apiKey
        ];
        
        // Check for session messages
        if (isset($_SESSION['settings_success'])) {
            $data['success'] = $_SESSION['settings_success'];
            unset($_SESSION['settings_success']);
        }
        if (isset($_SESSION['settings_error'])) {
            $data['error'] = $_SESSION['settings_error'];
            unset($_SESSION['settings_error']);
        }
        
        View::render('settings.twig', $data);
    }
    
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /settings');
            exit;
        }
        
        $user = Auth::user();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['settings_error'] = 'All fields are required';
            header('Location: /settings#profile');
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['settings_error'] = 'New passwords do not match';
            header('Location: /settings#profile');
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['settings_error'] = 'Password must be at least 6 characters';
            header('Location: /settings#profile');
            exit;
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $_SESSION['settings_error'] = 'Current password is incorrect';
            header('Location: /settings#profile');
            exit;
        }
        
        // Update password
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $user['id']]);
        
        $_SESSION['settings_success'] = 'Password changed successfully';
        header('Location: /settings#profile');
        exit;
    }
    
    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /settings');
            exit;
        }
        
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['settings_error'] = 'All fields are required';
            header('Location: /settings#users');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['settings_error'] = 'Invalid email address';
            header('Location: /settings#users');
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['settings_error'] = 'Password must be at least 6 characters';
            header('Location: /settings#users');
            exit;
        }
        
        // Check if email already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['settings_error'] = 'Email already exists';
            header('Location: /settings#users');
            exit;
        }
        
        // Create user
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $passwordHash, $role]);
        
        $_SESSION['settings_success'] = 'User added successfully';
        header('Location: /settings#users');
        exit;
    }
    
    public function deleteUser($userId) {
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        
        if ($userId == $user['id']) {
            $_SESSION['settings_error'] = 'Cannot delete yourself';
            header('Location: /settings#users');
            exit;
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $_SESSION['settings_success'] = 'User deleted successfully';
        header('Location: /settings#users');
        exit;
    }
    
    private function getAllUsers() {
        $stmt = $this->pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    private function getApiKey($service) {
        $stmt = $this->pdo->prepare("SELECT api_key FROM api_keys WHERE service_name = ? AND is_active = 1");
        $stmt->execute([$service]);
        $result = $stmt->fetch();
        return $result ? $result['api_key'] : null;
    }
    
    public function saveApiKey() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /settings');
            exit;
        }
        
        $service = $_POST['service'] ?? '';
        $apiKey = trim($_POST['api_key'] ?? '');
        
        if (empty($service) || empty($apiKey)) {
            View::render('settings.twig', [
                'error' => $this->translator->translate('settings.error_empty_key'),
                'translation_stats' => $this->getTranslationStats()
            ]);
            return;
        }
        
        // Validate OpenRouter key format
        if ($service === 'openrouter' && !preg_match('/^sk-or-v1-[a-zA-Z0-9]{64,}$/', $apiKey)) {
            View::render('settings.twig', [
                'error' => $this->translator->translate('settings.error_invalid_key'),
                'translation_stats' => $this->getTranslationStats()
            ]);
            return;
        }
        
        // Test the API key
        if ($service === 'openrouter') {
            $testResult = $this->testOpenRouterKey($apiKey);
            if (!$testResult['success']) {
                View::render('settings.twig', [
                    'error' => $this->translator->translate('settings.error_key_test') . ': ' . $testResult['error'],
                    'translation_stats' => $this->getTranslationStats()
                ]);
                return;
            }
        }
        
        // Save the key
        $saved = $this->translator->saveApiKey($service, $apiKey);
        
        if ($saved) {
            View::render('settings.twig', [
                'success' => $this->translator->translate('settings.key_saved'),
                'translation_stats' => $this->getTranslationStats(),
                'openrouter_key' => '' // Don't show the saved key
            ]);
        } else {
            View::render('settings.twig', [
                'error' => $this->translator->translate('message.error'),
                'translation_stats' => $this->getTranslationStats()
            ]);
        }
    }
    
    private function testOpenRouterKey($apiKey) {
        // Test with a simple translation request
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $data = [
            'model' => 'google/gemini-2.0-flash-exp:free',
            'messages' => [
                ['role' => 'user', 'content' => 'Translate "test" to Spanish. Reply only with the translation.']
            ],
            'temperature' => 0.3,
            'max_tokens' => 10
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: https://amnez.ia',
            'X-Title: Amnezia VPN Panel'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return ['success' => true];
            }
        }
        
        $error = json_decode($response, true);
        return [
            'success' => false,
            'error' => $error['error']['message'] ?? 'Unknown error (HTTP ' . $httpCode . ')'
        ];
    }
    
    private function getTranslationStats() {
        // Get all languages
        $stmt = $this->pdo->query("SELECT * FROM languages ORDER BY code");
        $languages = $stmt->fetchAll();
        
        // Get total translation keys count
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT translation_key) as count FROM translations WHERE language_code = 'en'");
        $totalKeys = $stmt->fetch();
        $totalCount = $totalKeys['count'];
        
        $stats = [];
        foreach ($languages as $lang) {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) as count FROM translations WHERE language_code = ? AND translation_value IS NOT NULL AND translation_value != ''"
            );
            $stmt->execute([$lang['code']]);
            $translated = $stmt->fetch();
            
            $stats[] = [
                'code' => $lang['code'],
                'name' => $lang['name'],
                'native_name' => $lang['native_name'],
                'total_count' => $totalCount,
                'translated_count' => $translated['count']
            ];
        }
        
        return $stats;
    }
}
