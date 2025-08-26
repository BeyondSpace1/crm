<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CRM RBAC System') ?></title>
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.4/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Intro.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="public/assets/css/custom.css" rel="stylesheet">
    
    <style>
        /* Particles.js container */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        
        /* Three.js canvas */
        #three-canvas {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: -1;
        }
        
        /* Navbar styling */
        .navbar-brand {
            font-weight: bold;
        }
        
        /* Sidebar styling */
        .sidebar {
            min-height: calc(100vh - 56px);
            background: rgba(38, 75, 112, 0.95);
            backdrop-filter: blur(10px);
        }
        
        /* Main content area */
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Card styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .cardTitle{
            color: #657845;
        }
        
        /* Status badges */
        .status-active { color: #28a745; }
        .status-deleted { color: #dc3545; }
        
        /* Role badges */
        .role-admin { background-color: #dc3545; }
        .role-editor { background-color: #ffc107; color: #000; }
        .role-viewer { background-color: #6c757d; }
    </style>
</head>
<body>
    <!-- Particles.js background -->
    <div id="particles-js"></div>
    
    <!-- Three.js metallic sphere -->
    <div id="three-canvas"></div>
    
    <?php if (!isset($hideNavbar) || !$hideNavbar): ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php" data-intro="Welcome to CRM RBAC System! This is your main navigation." data-step="1">
                <i class="bi bi-building"></i> CRM RBAC
            </a>
            
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span class="navbar-text me-3">
                        Welcome, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                        <span class="badge role-<?= $_SESSION['user_role'] ?>"><?= ucfirst($_SESSION['user_role']) ?></span>
                    </span>
                    <a class="btn btn-outline-light btn-sm" href="index.php?action=logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Layout -->
    <div class="container-fluid">
        <div class="row">
            <?php if (!isset($hideNavbar) || !$hideNavbar): ?>
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3" data-intro="This is your navigation sidebar. Here you can access all the main features." data-step="2">
                    <div class="list-group list-group-flush">
                        <a href="index.php?action=contacts.list" 
                           class="list-group-item list-group-item-action <?= (($_GET['action'] ?? 'contacts.list') === 'contacts.list') ? 'active' : '' ?>"
                           data-intro="View and manage all your contacts here." data-step="3">
                            <i class="bi bi-people"></i> Contacts
                        </a>
                        
                        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor'])): ?>
                        <a href="index.php?action=contacts.create" 
                           class="list-group-item list-group-item-action <?= (($_GET['action'] ?? '') === 'contacts.create') ? 'active' : '' ?>"
                           data-intro="Create new contacts quickly and easily." data-step="4">
                            <i class="bi bi-person-plus"></i> Create Contact
                        </a>
                        
                        <a href="index.php?action=contacts.importCsvForm" 
                           class="list-group-item list-group-item-action <?= (($_GET['action'] ?? '') === 'contacts.importCsvForm') ? 'active' : '' ?>"
                           data-intro="Import multiple contacts from CSV files." data-step="5">
                            <i class="bi bi-upload"></i> Import CSV
                        </a>
                        <?php endif; ?>
                        
                        <a href="index.php?action=contacts.exportCsv<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= isset($_GET['show_deleted']) ? '&show_deleted=1' : '' ?>" 
                           class="list-group-item list-group-item-action"
                           data-intro="Export your contacts to CSV format." data-step="6">
                            <i class="bi bi-download"></i> Export CSV
                        </a>
                        
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <hr>
                        <a href="index.php?action=audit.list" 
                           class="list-group-item list-group-item-action <?= (($_GET['action'] ?? '') === 'audit.list') ? 'active' : '' ?>"
                           data-intro="View audit logs to track all system changes (Admin only)." data-step="7">
                            <i class="bi bi-clock-history"></i> Audit Logs
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
            <?php else: ?>
            <div class="col-12">
            <?php endif; ?>
                <div class="main-content p-4 m-3">
                    <!-- Flash Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <!-- Page Content -->
                    <?php if (isset($content) && file_exists($content)): ?>
                        <?php include $content; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h1>403 - Access Denied</h1>
                            <p class="lead">You don't have permission to access this resource.</p>
                            <a href="index.php?action=contacts.list" class="btn btn-primary">Go to Contacts</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    
    <!-- Three.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.179.1/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.179.1/three.core.js" type="module" integrity="sha512-tceOgcXarANESHddVF0eWYonVnAdITzIO46TQnePQnSjj4nK/rKJzuzJ7w6GaoCZEwCeI1oYuvdJH4AM7jrc3A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.4/dist/sweetalert2.min.js"></script>
    
    <!-- Intro.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="public/assets/js/particles-config.js"></script>
    <script src="public/assets/js/three-sphere.js"></script>
    <script src="public/assets/js/intro-tour.js"></script>
    
    <script>
        // Initialize components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize particles
            if (typeof particlesJS !== 'undefined') {
                initParticles();
            }
            
            // Initialize Three.js sphere
            if (typeof THREE !== 'undefined') {
                initThreeSphere();
            }
            
            // Initialize intro tour for first-time users
            if (typeof introJs !== 'undefined' && !localStorage.getItem('intro_completed')) {
                setTimeout(initIntroTour, 1000);
            }
            
            // SweetAlert2 confirmations for delete actions
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will soft delete the contact. You can restore it later.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
            
            // Auto-hide alerts after 5 seconds
            document.querySelectorAll('.alert').forEach(alert => {
                if (!alert.querySelector('.btn-close')) {
                    setTimeout(() => {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>