<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Check if login is with username or email
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'active'";
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Plain text password comparison (as per requirement)
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username/email or password';
        }
    } else {
        $error = 'Invalid username/email or password';
    }
    
    mysqli_stmt_close($stmt);
}
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Tk Construction Chemicals</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .login-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            position: relative;
        }

        .company-info h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .company-info h2 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            font-weight: 300;
            opacity: 0.9;
        }

        .features {
            margin-top: 40px;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .feature-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .login-form h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .login-form p {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #d32f2f;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background: #b71c1c;
        }

        .error-message {
            background: #ffebee;
            color: #d32f2f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .logo-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            font-size: 10rem;
            font-weight: bold;
            color: white;
            pointer-events: none;
            user-select: none;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                margin: 10px;
            }
            
            .login-left, .login-right {
                padding: 30px;
            }
            
            .company-info h1 {
                font-size: 2rem;
            }
            
            .company-info h2 {
                font-size: 1.4rem;
            }
            
            .login-form h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
	
        <div class="login-left">
            <div class="company-info">
                <h1>Tk Construction Chemicals</h1>
                <h2>Office Management System</h2>
                <p>Professional management system for construction chemicals, flooring, waterproofing, and hardware products.</p>
            </div>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">📦</div>
                    <div>
                        <h3>Inventory Management</h3>
                        <p>Track stock levels in real-time</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon">💰</div>
                    <div>
                        <h3>POS System</h3>
                        <p>Streamlined sales and invoicing</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🏗️</div>
                    <div>
                        <h3>Project Tracking</h3>
                        <p>Monitor construction projects</p>
                    </div>
                </div>
            </div>
            
            <div class="logo-watermark">
                TK
            </div>
        </div>
        
        <div class="login-right">
            <form class="login-form" method="POST" action="">
                <h2>Login to System</h2>
                <p>Enter your credentials to access the system</p>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter username or email"
                           required
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter password"
                           required>
                </div>
                
                <button type="submit" class="btn-login">Login to System</button>
                
                <div style="text-align: center; margin-top: 20px; color: #666;">
                    <small>Default Admin: admin / TKethi</small>
                </div>
            </form>
        </div>
    </div>
</body>
</html>