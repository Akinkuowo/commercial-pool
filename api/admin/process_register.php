<?php
// api/admin/process_register.php
session_start();

// Include database configuration
require_once '../../config.php';

// Get database connection
$conn = getDbConnection();

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
    header('Location: ../../admin/admin_register.php?error=registration_disabled');
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
        header('Location: ../../admin/admin_register.php?error=invitation_required');
        exit;
    }
    
    // Validate invitation token
    $stmt = $conn->prepare("SELECT * FROM admin_invitations WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("s", $invitation_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $invitation = $result->fetch_assoc();
    $stmt->close();
    
    if (!$invitation) {
        closeDbConnection($conn);
        header('Location: ../../admin/admin_register.php?error=invalid_token');
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
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        $stmt->close();
        closeDbConnection($conn);
        header('Location: ../../admin/admin_register.php?error=username_exists');
        exit;
    }
    $stmt->close();
}

// Email validation
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
} else {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        $stmt->close();
        closeDbConnection($conn);
        header('Location: ../../admin/admin_register.php?error=email_exists');
        exit;
    }
    $stmt->close();
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
    closeDbConnection($conn);
    header('Location: ../../admin/admin_register.php?error=passwords_mismatch');
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
    closeDbConnection($conn);
    header('Location: ../../admin/admin_register.php?error=validation&msg=' . urlencode(implode(', ', $errors)));
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
    $stmt = $conn->prepare("
        INSERT INTO admin_users (
            username, email, password, first_name, last_name, 
            role, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("sssssss", 
        $username, $email, $hashed_password, $first_name, $last_name, $role, $status
    );
    
    $stmt->execute();
    $admin_id = $conn->insert_id;
    $stmt->close();
    
    // Store additional information in a separate table (create this if needed)
    if (!empty($phone) || !empty($department) || !empty($bio)) {
        $stmt = $conn->prepare("
            INSERT INTO admin_profiles (
                admin_id, phone, department, bio, requested_role, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param("issss", 
            $admin_id, $phone, $department, $bio, $requested_role
        );
        
        $stmt->execute();
        $stmt->close();
    }
    
    // Mark invitation as used if applicable
    if ($require_invitation && isset($invitation)) {
        $stmt = $conn->prepare("UPDATE admin_invitations SET used = 1, used_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $admin_id, $invitation['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Log registration activity
    $stmt = $conn->prepare("
        INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
        VALUES (?, 'registration', ?, ?, ?)
    ");
    
    $description = $require_approval ? 
        "New admin account registered (pending approval)" : 
        "New admin account registered";
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt->bind_param("isss", 
        $admin_id, $description, $ip_address, $user_agent
    );
    
    $stmt->execute();
    $stmt->close();
    
    // Send notification email to user
    sendWelcomeEmail($email, $first_name, $username, $require_approval);
    
    // Send notification to existing admins about new registration
    if ($require_approval) {
        notifyAdminsOfNewRegistration($conn, $username, $email, $requested_role);
    }
    
    // Close database connection
    closeDbConnection($conn);
    
    // Redirect based on approval requirement
    if ($require_approval) {
        header('Location: ../../admin/admin_register.php?success=pending_approval');
    } else {
        header('Location: ../../admin/admin_login.php?registered=true');
    }
    exit;
    
} catch (Exception $e) {
    error_log("Admin registration error: " . $e->getMessage());
    closeDbConnection($conn);
    header('Location: ../../admin/admin_register.php?error=system');
    exit;
}

// Helper functions
function sendWelcomeEmail($email, $firstName, $username, $requireApproval) {
    // Implement email sending logic here
    // You can use PHPMailer or similar
    
    $subject = "Welcome to Commerial Pool Equipment Admin Portal";
    
    if ($requireApproval) {
        $message = "Hello $firstName,\n\n";
        $message .= "Thank you for registering an admin account.\n\n";
        $message .= "Username: $username\n\n";
        $message .= "Your account is currently pending approval. You will receive another email once an administrator reviews and approves your account.\n\n";
        $message .= "Best regards,\n";
        $message .= "Commerial Pool Equipment Leisure and Supplies";
    } else {
        $message = "Hello $firstName,\n\n";
        $message .= "Welcome to the Commerial Pool Equipment Admin Portal!\n\n";
        $message .= "Username: $username\n\n";
        $message .= "You can now log in at: " . $_SERVER['HTTP_HOST'] . "/admin/admin_login.php\n\n";
        $message .= "Best regards,\n";
        $message .= "Commerial Pool Equipment Leisure and Supplies";
    }
    
    // mail($email, $subject, $message);
    // Or use PHPMailer for better email handling
}

function notifyAdminsOfNewRegistration($conn, $username, $email, $requestedRole) {
    // Get all active admin users
    $stmt = $conn->prepare("SELECT email, first_name FROM admin_users WHERE role = 'admin' AND status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    $admins = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
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
        $message .= "Commerial Pool Equipment System";
        
        // mail($admin['email'], $subject, $message);
    }
}
?>