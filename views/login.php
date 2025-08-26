<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-building display-4 text-primary"></i>
                    <h2 class="card-title cardTitle mt-3">CRM RBAC Login</h2>
                    <p class="text-muted">Sign in to your account</p>
                </div>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                    <?php foreach ($errors as $field => $error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <form method="POST" action="index.php?action=login">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               required 
                               autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" 
                               name="password" 
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <small class="text-muted">
                        <strong>Demo Credentials:</strong><br>
                        Admin: admin@example.com / Admin@123<br>
                        Editor: editor@example.com / Admin@123<br>
                        Viewer: viewer@example.com / Admin@123
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                CRM RBAC System v1.0.0<br>
                Secure • Modern • Reliable
            </small>
        </div>
    </div>
</div>

<style>
    /* Login page specific styling */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .main-content {
        background: transparent;
        box-shadow: none;
    }
    
    .card {
        border: none;
        backdrop-filter: blur(15px);
        background: rgba(255, 255, 255, 0.9);
    }

    .cardTitle{
        color: #657845;
    }
    
    .form-control{
        color: #6f1ad0ff;
    
    }
    .form-control:focus{
        background: #31a0b1ff;
    }
</style>