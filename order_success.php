<?php
ini_set('display_errors', 0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$order_id = $_GET['order_id'] ?? null;
$code = $_GET['code'] ?? null;
$qr_url = $_GET['qr'] ?? null;

if (!$order_id) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Commercial Pool Equipment</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <?php include('include/style.php') ?>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    
    <?php include('include/header.php'); ?>

    <!-- Main Content -->
    <div class="flex-grow container mx-auto px-4 py-12 max-w-3xl">
        <div class="bg-white rounded-lg shadow-sm p-8 md:p-12 text-center">
            
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-8">
                <i class="fas fa-check text-green-600 text-4xl"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Order Confirmed!</h1>
            <p class="text-gray-600 mb-8 max-w-lg mx-auto">
                Thank you for your purchase. Your order #<?php echo htmlspecialchars($order_id); ?> has been placed successfully.
                We've sent a confirmation email with all the details.
            </p>

            <?php if ($code && $qr_url): ?>
            <!-- Click & Collect Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-8 mb-8 max-w-lg mx-auto">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <i class="fas fa-store text-blue-600 text-xl"></i>
                    <h2 class="text-xl font-bold text-gray-900">Click & Collect Instructions</h2>
                </div>
                
                <p class="text-gray-700 mb-6 text-sm">
                    Please present this code or QR code when you visit our store to collect your items.
                    <span class="block mt-1 font-medium text-red-600">Expires in 72 hours</span>
                </p>

                <div class="bg-white rounded-lg p-6 shadow-sm inline-block">
                    <!-- 6 Digit Code -->
                    <div class="mb-6">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Collection Code</p>
                        <div class="text-4xl font-mono font-bold text-gray-900 tracking-widest bg-gray-100 py-3 px-6 rounded-lg">
                            <?php echo htmlspecialchars($code); ?>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Scan at Counter</p>
                        <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="Collection QR Code" class="w-40 h-40 mx-auto">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="index.php" class="bg-gray-900 hover:bg-gray-800 text-white font-semibold py-3 px-8 rounded-lg transition">
                    Return to Home
                </a>
                <a href="#" class="bg-white border border-gray-300 text-gray-700 font-semibold py-3 px-8 rounded-lg hover:bg-gray-50 transition">
                    View Order Details
                </a>
            </div>

        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>
</body>
</html>
