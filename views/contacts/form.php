<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'person-plus' ?>"></i>
        <?= $isEdit ? 'Edit Contact' : 'Create Contact' ?>
    </h1>
    
    <a href="index.php?action=contacts.list" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Contacts
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-exclamation-triangle"></i> Please correct the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?action=<?= $isEdit ? 'contacts.update' : 'contacts.create' ?>">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($contact->getId()) ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person"></i> Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($contact->getName() ?? $_POST['name'] ?? '') ?>"
                                   required 
                                   autofocus>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($contact->getEmail() ?? $_POST['email'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone"></i> Phone
                            </label>
                            <input type="tel" 
                                   class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($contact->getPhone() ?? $_POST['phone'] ?? '') ?>"
                                   placeholder="+1-555-0123">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Format: 7-20 characters, digits, spaces, +, - allowed</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">
                                <i class="bi bi-building"></i> Company
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="company" 
                                   name="company" 
                                   value="<?= htmlspecialchars($contact->getCompany() ?? $_POST['company'] ?? '') ?>"
                                   placeholder="Company Name">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="tags" class="form-label">
                            <i class="bi bi-tags"></i> Tags
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="tags" 
                               name="tags" 
                               value="<?= htmlspecialchars($contact->getTagsAsString() ?? $_POST['tags'] ?? '') ?>"
                               placeholder="customer, vip, lead">
                        <div class="form-text">Separate multiple tags with commas</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-<?= $isEdit ? 'check-circle' : 'plus-circle' ?>"></i>
                                <?= $isEdit ? 'Update Contact' : 'Create Contact' ?>
                            </button>
                            <a href="index.php?action=contacts.list" class="btn btn-outline-secondary btn-lg ms-2">
                                Cancel
                            </a>
                        </div>
                        
                        <?php if ($isEdit && $contact->getId()): ?>
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    Created: <?= date('M j, Y g:i A', strtotime($contact->getCreatedAt())) ?><br>
                                    Updated: <?= date('M j, Y g:i A', strtotime($contact->getUpdatedAt())) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title cardTitle" style="color:#658975;">
                    <i class="bi bi-info-circle"></i> Form Guidelines
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        <strong>Name:</strong> At least 2 characters required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        <strong>Email:</strong> Must be valid and unique
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        <strong>Phone:</strong> Optional, 7-20 characters
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        <strong>Company:</strong> Optional field
                    </li>
                    <li>
                        <i class="bi bi-check text-success"></i>
                        <strong>Tags:</strong> Comma-separated, auto-cleaned
                    </li>
                </ul>
            </div>
        </div>
        
        <?php if ($isEdit): ?>
            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h6 class="card-title cardTitle">
                        <i class="bi bi-exclamation-triangle"></i> Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Deleting this contact will soft delete it. You can restore it later from the deleted contacts view.
                    </p>
                    
                    <form method="POST" action="index.php?action=contacts.delete" class="d-inline">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($contact->getId()) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-delete">
                            <i class="bi bi-trash"></i> Delete Contact
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-format phone number
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                // Remove any non-digit, space, +, - characters
                let value = e.target.value.replace(/[^\d\s\+\-]/g, '');
                e.target.value = value;
            });
        }
        
        // Tags input enhancement
        const tagsInput = document.getElementById('tags');
        if (tagsInput) {
            tagsInput.addEventListener('blur', function(e) {
                // Clean up tags: trim whitespace, remove duplicates
                const tags = e.target.value.split(',')
                    .map(tag => tag.trim())
                    .filter(tag => tag.length > 0)
                    .filter((tag, index, arr) => arr.indexOf(tag) === index);
                
                e.target.value = tags.join(', ');
            });
        }
        
        // Form validation feedback
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const nameInput = document.getElementById('name');
                const emailInput = document.getElementById('email');
                
                let hasErrors = false;
                
                // Clear previous validation states
                document.querySelectorAll('.is-invalid').forEach(input => {
                    input.classList.remove('is-invalid');
                });
                
                // Validate name
                if (nameInput.value.trim().length < 2) {
                    nameInput.classList.add('is-invalid');
                    hasErrors = true;
                }
                
                // Validate email
                if (!emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    emailInput.classList.add('is-invalid');
                    hasErrors = true;
                }
                
                if (hasErrors) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please correct the highlighted fields.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
</script>