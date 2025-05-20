<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    include "DB_connection.php";

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_plan'])) {
            $plan_id = $_POST['plan_id'];
            $price = $_POST['price'];
            $description = $_POST['description'];
            $note_limit = $_POST['note_limit'] ?: null;
            $private_note_limit = $_POST['private_note_limit'] ?: null;

            try {
                $stmt = $conn->prepare("UPDATE plans SET price = ?, description = ?, note_limit = ?, private_note_limit = ? WHERE id = ?");
                $stmt->execute([$price, $description, $note_limit, $private_note_limit, $plan_id]);
                $success = "Plan updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating plan: " . $e->getMessage();
            }
        }
    }

    // Fetch all plans
    $stmt = $conn->query("SELECT * FROM plans ORDER BY price ASC");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Plans | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .breadcrumb-item a {
            text-decoration: none;
            color: #0d6efd;
        }
        
        .plan-card {
            transition: all 0.2s ease;
            border-radius: 12px;
            background: #ffffff;
        }
        
        .plan-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
            transform: none; /* Remove bounce effect */
        }
        
        .card-header {
            background: linear-gradient(145deg, #f8f9ff, #ffffff);
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.5rem;
            background: linear-gradient(45deg, #0d6efd, #0099ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            transform: translateY(-1px);
        }

        .form-label {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            background: linear-gradient(45deg, #0d6efd, #0099ff);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.2);
            background: linear-gradient(45deg, #0099ff, #0d6efd);
        }

        .unlimited-badge {
            position: absolute;
            top: 12px; /* Adjust position to be fully visible */
            right: 12px;
            background: linear-gradient(45deg, #0d6efd, #0099ff);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .alert-success {
            background: linear-gradient(145deg, #d1e7dd, #e8f6f0);
            color: #0f5132;
            box-shadow: 0 4px 15px rgba(15, 81, 50, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            line-height: 1.6;
            resize: vertical;
        }

        /* Card inner spacing */
        .card-body {
            padding: 2rem;
        }

        /* Input group styling */
        .input-group-text {
            border-radius: 12px 0 0 12px;
            border: 2px solid #e9ecef;
            border-right: none;
            background-color: #f8f9fa;
        }

        /* Add subtle animation to form inputs */
        .form-control, .btn-primary {
            will-change: transform;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Make price input stand out */
        input[name="price"] {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0d6efd;
            background-color: #f8f9ff;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main style="margin-left: 250px; padding-top: 60px;">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Manage Plans</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Plans</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fa fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($plans as $plan): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm plan-card position-relative">
                            <?php if ($plan['is_unlimited']): ?>
                                <span class="unlimited-badge">Unlimited Plan</span>
                            <?php endif; ?>
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="fa fa-certificate me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($plan['name']); ?>
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                    
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fa fa-money me-2 text-primary"></i>Price (₹)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" name="price" class="form-control" 
                                                   value="<?php echo $plan['price']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3" required><?php 
                                            echo htmlspecialchars($plan['description']); 
                                        ?></textarea>
                                    </div>

                                    <?php if (!$plan['is_unlimited']): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Note Limit</label>
                                            <input type="number" name="note_limit" class="form-control" 
                                                   value="<?php echo $plan['note_limit']; ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Private Note Limit</label>
                                            <input type="number" name="private_note_limit" class="form-control" 
                                                   value="<?php echo $plan['private_note_limit']; ?>">
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-grid">
                                        <button type="submit" name="update_plan" class="btn btn-primary">
                                            <i class="fa fa-save me-2"></i>Update Plan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
} else {
    header("Location: login.php");
    exit;
}
?>
