<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $error = '';
    
    if (!$username || !$password) {
        $error = 'Username and password required';
    } else {
        try {
            $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
            if (!$conn) {
                $error = 'Database connection failed';
            } else {
                $result = pg_query_params($conn, 
                    'SELECT user_id, username, password_hash, role, first_name FROM users WHERE username = $1',
                    [$username]
                );
                
                if ($row = pg_fetch_assoc($result)) {
                    if (password_verify($password, $row['password_hash'])) {
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['role'] = $row['role'];
                        $_SESSION['first_name'] = $row['first_name'];
                        header('Location: index.php?action=dashboard');
                        exit;
                    } else {
                        $error = 'Invalid password';
                    }
                } else {
                    $error = 'User not found';
                }
                pg_close($conn);
            }
        } catch (Exception $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">📚 UniTrack</h2>
                        <h5 class="text-center mb-4 text-muted">Login</h5>
                        
                        <?php if (isset($error) && $error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <hr>
                        <p class="text-muted small text-center"><strong>Demo:</strong><br>admin / password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               required minlength="4" placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="demo-info">
                    <strong>Demo Credentials:</strong><br>
                    Username: <code>admin123</code><br>
                    Password: <code>password</code>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
