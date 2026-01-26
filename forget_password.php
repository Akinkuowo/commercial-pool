<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Commercial Pool Equipment and Supplies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-input:focus {
            border-color: #022658;
            box-shadow: 0 0 0 3px rgba(2, 38, 88, 0.1);
        }
        .btn-primary {
            background-color: #022658;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #011a3d;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Reset Your Password</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>
            
            <form class="mt-8 space-y-6" action="process_forgot_password.php" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                           placeholder="your.email@example.com">
                </div>
                
                <div>
                    <button type="submit" 
                            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg hover:bg-[#011a3d] transition duration-300">
                        Send Reset Link
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="login.php" class="text-sm text-[#022658] hover:text-[#011a3d] hover:underline">
                        Back to Sign In
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>