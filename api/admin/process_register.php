<?php
// api/admin/process_register.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/admin_register.php?error=invalid');
    exit;
}

// Configuration
$allow_registration = true; // Set to false to disable registration
$require_approval = true;   // Set to true to require admin approval
$require_invitation = false; // Set to true to require invitation token
$default_role_for_new_users = 'editor'; // 'editor' or 'admin'

if (!$allow_registration) {
    header('Location: ../../admin_register.php?error=registration_disabled');
    exit;
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$department = trim($_POST['department'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$requested_role = $_POST['role'] ?? 'editor';
$terms = isset($_POST['terms']);
$data_handling = isset($_POST['data_handling']);
$code_of_conduct = isset($_POST['code_of_conduct']);
$invitation_token = $_POST['invitation_token'] ?? '';

// Validation
$errors = [];

// Check invitation token if required
if ($require_invitation) {
    if (empty($invitation_token)) {
        header('Location: ../../admin_register.php?error=invitation_required');
        exit;
    }
    
    // Validate invitation token
    $stmt = $pdo->prepare("SELECT * FROM admin_invitations WHERE token = :token AND used = 0 AND expires_at > NOW()");
    $stmt->execute(['token' => $invitation_token]);
    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invitation) {
        header('Location: ../../admin_register.php?error=invalid_token');
        exit;
    }
}

// Username validation
if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3 || strlen($username) > 20) {
    $errors[] = 'Username must be 3-20 characters';
} elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    $errors[] = 'Username can only contain letters and numbers';
} else {
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        header('Location: ../../admin_register.php?error=username_exists');
        exit;
    }
}

// Email validation
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
} else {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        header('Location: ../../admin_register.php?error=email_exists');
        exit;
    }
}

// Password validation
if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors[] = 'Password must contain at least one lowercase letter';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one number';
} elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
    $errors[] = 'Password must contain at least one special character';
}

// Confirm password
if ($password !== $confirm_password) {
    header('Location: ../../admin_register.php?error=passwords_mismatch');
    exit;
}

// Personal details validation
if (empty($first_name)) {
    $errors[] = 'First name is required';
}

if (empty($last_name)) {
    $errors[] = 'Last name is required';
}

// Terms validation
if (!$terms || !$data_handling || !$code_of_conduct) {
    $errors[] = 'You must accept all terms and conditions';
}

// If there are validation errors
if (!empty($errors)) {
    header('Location: ../../admin_register.php?error=validation&msg=' . urlencode(implode(', ', $errors)));
    exit;
}

try {
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Determine initial status
    $status = $require_approval ? 'inactive' : 'active';
    
    // Determine role (admins might need approval even if registration is open)
    $role = $default_role_for_new_users;
    if ($requested_role === 'admin' && $require_approval) {
        $role = 'editor'; // Start as editor, upgrade after approval
        $needs_role_approval = true;
    } else {
        $needs_role_approval = false;
    }
    
    // Insert new admin user
    $stmt = $pdo->prepare("
        INSERT INTO admin_users (
            username, email, password, first_name, last_name, 
            role, status, created_at
        ) VALUES (
            :username, :email, :password, :first_name, :last_name, 
            :role, :status, NOW()
        )
    ");
    
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashed_password,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => $role,
        'status' => $status
    ]);
    
    $admin_id = $pdo->lastInsertId();
    
    // Store additional information in a separate table (create this if needed)
    if (!empty($phone) || !empty($department) || !empty($bio)) {
        $stmt = $pdo->prepare("
            INSERT INTO admin_profiles (
                admin_id, phone, department, bio, requested_role, created_at
            ) VALUES (
                :admin_id, :phone, :department, :bio, :requested_role, NOW()
            )
        ");
        
        $stmt->execute([
            'admin_id' => $admin_id,
            'phone' => $phone,
            'department' => $department,
            'bio' => $bio,
            'requested_role' => $requested_role
        ]);
    }
    
    // Mark invitation as used if applicable
    if ($require_invitation && isset($invitation)) {
        $stmt = $pdo->prepare("UPDATE admin_invitations SET used = 1, used_by = :admin_id WHERE id = :id");
        $stmt->execute([
            'admin_id' => $admin_id,
            'id' => $invitation['id']
        ]);
    }
    
    // Log registration activity
    $stmt = $pdo->prepare("
        INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
        VALUES (:admin_id, 'registration', :description, :ip_address, :user_agent)
    ");
    
    $description = $require_approval ? 
        "New admin account registered (pending approval)" : 
        "New admin account registered";
    
    $stmt->execute([
        'admin_id' => $admin_id,
        'description' => $description,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
    
    // Send notification email to user
    sendWelcomeEmail($email, $first_name, $username, $require_approval);
    
    // Send notification to existing admins about new registration
    if ($require_approval) {
        notifyAdminsOfNewRegistration($pdo, $username, $email, $requested_role);
    }
    
    // Redirect based on approval requirement
    if ($require_approval) {
        header('Location: ../../admin_register.php?success=pending_approval');
    } else {
        header('Location: ../../admin_login.php?registered=true');
    }
    exit;
    
} catch (PDOException $e) {
    error_log("Admin registration error: " . $e->getMessage());
    header('Location: ../../admin_register.php?error=system');
    exit;
}

// Helper functions
function sendWelcomeEmail($email, $firstName, $username, $requireApproval) {
    // Implement email sending logic here
    // You can use PHPMailer or similar
    
    $subject = "Welcome to Jacksons Admin Portal";
    
    if ($requireApproval) {
        $message = "Hello $firstName,\n\n";
        $message .= "Thank you for registering an admin account.\n\n";
        $message .= "Username: $username\n\n";
        $message .= "Your account is currently pending approval. You will receive another email once an administrator reviews and approves your account.\n\n";
        $message .= "Best regards,\n";
        $message .= "Jacksons Leisure and Supplies";
    } else {
        $message = "Hello $firstName,\n\n";
        $message .= "Welcome to the Jacksons Admin Portal!\n\n";
        $message .= "Username: $username\n\n";
        $message .= "You can now log in at: " . $_SERVER['HTTP_HOST'] . "/admin_login.php\n\n";
        $message .= "Best regards,\n";
        $message .= "Jacksons Leisure and Supplies";
    }
    
    // mail($email, $subject, $message);
    // Or use PHPMailer for better email handling
}

function notifyAdminsOfNewRegistration($pdo, $username, $email, $requestedRole) {
    // Get all active admin users
    $stmt = $pdo->prepare("SELECT email, first_name FROM admin_users WHERE role = 'admin' AND status = 'active'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $subject = "New Admin Registration Pending Approval";
    $approvalLink = $_SERVER['HTTP_HOST'] . "/admin/approve_user.php";
    
    foreach ($admins as $admin) {
        $message = "Hello {$admin['first_name']},\n\n";
        $message .= "A new admin account registration requires approval:\n\n";
        $message .= "Username: $username\n";
        $message .= "Email: $email\n";
        $message .= "Requested Role: $requestedRole\n\n";
        $message .= "Please review and approve at: $approvalLink\n\n";
        $message .= "Best regards,\n";
        $message .= "Jacksons System";
        
        // mail($admin['email'], $subject, $message);
    }
}
?>