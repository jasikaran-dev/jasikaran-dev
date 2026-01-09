<?php
require_once 'includes/functions.php';
checkAuth();
require_once 'config/database.php';

$conn = getConnection();

// Get dashboard statistics
$stats = [
    'total_sales' => 0,
    'pending_projects' => 0,
    'low_stock' => 0,
    'pending_cheques' => 0
];

// Get today's sales
$sql = "SELECT SUM(net_amount) as total FROM sales WHERE DATE(created_at) = CURDATE()";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_sales'] = $row['total'] ?? 0;
}

// Get ongoing projects
$sql = "SELECT COUNT(*) as count FROM projects WHERE status = 'Ongoing'";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['pending_projects'] = $row['count'] ?? 0;
}

// Get low stock items
$sql = "SELECT COUNT(*) as count FROM products WHERE quantity <= min_stock_level";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['low_stock'] = $row['count'] ?? 0;
}

// Get pending cheques
$sql = "SELECT COUNT(*) as count FROM cheques WHERE status = 'Pending'";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['pending_cheques'] = $row['count'] ?? 0;
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Tk Construction Chemicals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #d32f2f;
            --primary-dark: #b71c1c;
            --secondary: #2c3e50;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #e0e0e0;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }

        body {
            background: #f5f7fa;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .logo-section {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid var(--border);
            background: var(--primary);
            color: white;
        }

        .logo-section h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .logo-section h2 {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: normal;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: #f8f9fa;
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .nav-item.active {
            background: #fff5f5;
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .nav-item i {
            width: 24px;
            margin-right: 10px;
            text-align: center;
        }

        .user-info {
            padding: 20px;
            border-top: 1px solid var(--border);
            background: #f8f9fa;
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: var(--secondary);
            margin: 0;
        }

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-btn {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: var(--secondary);
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .user-btn:hover {
            background: #f8f9fa;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.sales { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.projects { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.inventory { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.cheques { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .stat-info h3 {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-info .value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .stat-info .change {
            font-size: 0.9rem;
            color: var(--success);
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .module-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .module-header {
            background: var(--primary);
            color: white;
            padding: 20px;
        }

        .module-header h3 {
            margin: 0;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .module-header i {
            font-size: 1.5rem;
        }

        .module-body {
            padding: 20px;
        }

        .module-body p {
            color: var(--gray);
            margin: 0;
            line-height: 1.6;
        }

        .module-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-module {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-module:hover {
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
        }

        .module-count {
            background: var(--primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .recent-activity h2 {
            color: var(--secondary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1rem;
        }

        .activity-icon.sale { background: var(--success); }
        .activity-icon.project { background: var(--info); }
        .activity-icon.inventory { background: var(--warning); }

        .activity-info h4 {
            margin: 0 0 5px 0;
            color: var(--secondary);
        }

        .activity-info p {
            margin: 0;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .activity-time {
            margin-left: auto;
            color: var(--gray);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar .logo-section h1,
            .sidebar .logo-section h2,
            .sidebar .nav-item span,
            .sidebar .user-info .user-name {
                display: none;
            }
            
            .sidebar .nav-item {
                justify-content: center;
                padding: 15px;
            }
            
            .sidebar .nav-item i {
                margin: 0;
                font-size: 1.2rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header h1 {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-section">
                <h1>TK Construction</h1>
                <h2>Office System</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="modules/pos/index.php" class="nav-item">
                    <i class="fas fa-cash-register"></i>
                    <span>POS / Selling</span>
                </a>
                <a href="modules/inventory/index.php" class="nav-item">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="modules/projects/index.php" class="nav-item">
                    <i class="fas fa-project-diagram"></i>
                    <span>Projects</span>
                </a>
                <a href="modules/labour/index.php" class="nav-item">
                    <i class="fas fa-hard-hat"></i>
                    <span>Labour</span>
                </a>
                <a href="modules/cheques/index.php" class="nav-item">
                    <i class="fas fa-money-check"></i>
                    <span>Cheques</span>
                </a>
                <a href="modules/reports/index.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <?php if (isAdmin()): ?>
                <a href="modules/users/index.php" class="nav-item">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                </div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
                <div class="user-dropdown">
                    <button class="user-btn">
                        <i class="fas fa-user-circle" style="font-size: 1.2rem; margin-right: 8px;"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <i class="fas fa-chevron-down" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Today's Sales</h3>
                        <div class="value"><?php echo formatCurrency($stats['total_sales']); ?></div>
                        <div class="change">+12% from yesterday</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon projects">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Active Projects</h3>
                        <div class="value"><?php echo $stats['pending_projects']; ?></div>
                        <div class="change">3 completed this week</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon inventory">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Low Stock Items</h3>
                        <div class="value"><?php echo $stats['low_stock']; ?></div>
                        <div class="change">Needs attention</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon cheques">
                        <i class="fas fa-money-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pending Cheques</h3>
                        <div class="value"><?php echo $stats['pending_cheques']; ?></div>
                        <div class="change">2 cleared today</div>
                    </div>
                </div>
            </div>
            
            <!-- Module Cards -->
            <div class="modules-grid">
                <a href="modules/pos/index.php" class="module-card">
                    <div class="module-header">
                        <h3>
                            <span>POS / Selling</span>
                            <i class="fas fa-cash-register"></i>
                        </h3>
                    </div>
                    <div class="module-body">
                        <p>Create invoices, process sales, manage customer transactions and generate receipts.</p>
                    </div>
                    <div class="module-footer">
                        <span class="btn-module">Open POS</span>
                        <span class="module-count"><?php echo $stats['total_sales'] > 0 ? '+' : '0'; ?></span>
                    </div>
                </a>
                
                <a href="modules/inventory/index.php" class="module-card">
                    <div class="module-header" style="background: #2c3e50;">
                        <h3>
                            <span>Inventory Management</span>
                            <i class="fas fa-boxes"></i>
                        </h3>
                    </div>
                    <div class="module-body">
                        <p>Manage stock levels, track products, set reorder points and monitor inventory value.</p>
                    </div>
                    <div class="module-footer">
                        <span class="btn-module" style="background: #2c3e50;">View Stock</span>
                        <span class="module-count" style="background: #2c3e50;"><?php echo $stats['low_stock']; ?></span>
                    </div>
                </a>
                
                <a href="modules/projects/index.php" class="module-card">
                    <div class="module-header" style="background: #43a047;">
                        <h3>
                            <span>Project Management</span>
                            <i class="fas fa-project-diagram"></i>
                        </h3>
                    </div>
                    <div class="module-body">
                        <p>Track construction projects, monitor progress, manage budgets and client payments.</p>
                    </div>
                    <div class="module-footer">
                        <span class="btn-module" style="background: #43a047;">View Projects</span>
                        <span class="module-count" style="background: #43a047;"><?php echo $stats['pending_projects']; ?></span>
                    </div>
                </a>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon sale">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="activity-info">
                            <h4>New Sale Completed</h4>
                            <p>Invoice #INV-20231215-001 for Rs. 45,000.00</p>
                        </div>
                        <div class="activity-time">10 min ago</div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon project">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="activity-info">
                            <h4>Project Status Updated</h4>
                            <p>Colombo Tower Project marked as 75% complete</p>
                        </div>
                        <div class="activity-time">1 hour ago</div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon inventory">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="activity-info">
                            <h4>Low Stock Alert</h4>
                            <p>Premium Floor Tiles stock below minimum level</p>
                        </div>
                        <div class="activity-time">2 hours ago</div>
                    </li>
                </ul>
            </div>
        </main>
    </div>
    
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Handle module card clicks
            document.querySelectorAll('.module-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.classList.contains('btn-module')) {
                        e.stopPropagation();
                    }
                });
            });
            
            // Handle logout
            document.querySelector('.user-btn').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>
</html>N