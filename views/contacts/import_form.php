<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-upload"></i> Import Contacts
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
                        <h6><i class="bi bi-exclamation-triangle"></i> Import Error:</h6>
                        <?php foreach ($errors as $field => $error): ?>
                            <div><?= $error ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?action=contacts.importCsvDryRun" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    
                    <div class="mb-4">
                        <label for="csv_file" class="form-label">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Select CSV File <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control <?= isset($errors['file']) ? 'is-invalid' : '' ?>" 
                               id="csv_file" 
                               name="csv_file" 
                               accept=".csv,text/csv" 
                               required>
                        <?php if (isset($errors['file'])): ?>
                            <div class="invalid-feedback"><?= $errors['file'] ?></div>
                        <?php endif; ?>
                        <div class="form-text">
                            Maximum file size: 2MB. Only CSV files are allowed.
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Import Process:</h6>
                        <ol class="mb-0">
                            <li><strong>Dry Run:</strong> We'll first validate your CSV and show you a preview</li>
                            <li><strong>Review:</strong> Check the preview and fix any errors</li>
                            <li><strong>Commit:</strong> Confirm to actually import the contacts</li>
                        </ol>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Preview Import (Dry Run)
                        </button>
                        
                        <a href="index.php?action=contacts.list" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- CSV Format Guide -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title cardTitle">
                    <i class="bi bi-question-circle"></i> CSV Format Requirements
                </h6>
            </div>
            <div class="card-body">
                <p>Your CSV file must have these columns in the first row:</p>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>name</code></td>
                                <td><span class="badge bg-danger">Yes</span></td>
                            </tr>
                            <tr>
                                <td><code>email</code></td>
                                <td><span class="badge bg-danger">Yes</span></td>
                            </tr>
                            <tr>
                                <td><code>phone</code></td>
                                <td><span class="badge bg-secondary">No</span></td>
                            </tr>
                            <tr>
                                <td><code>company</code></td>
                                <td><span class="badge bg-secondary">No</span></td>
                            </tr>
                            <tr>
                                <td><code>tags</code></td>
                                <td><span class="badge bg-secondary">No</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <h6>Example CSV:</h6>
                <pre class="bg-light p-2 small"><code>name,email,phone,company,tags
John Doe,john@example.com,+1-555-0123,Acme Corp,"customer,vip"
Jane Smith,jane@example.com,,Tech Solutions,lead</code></pre>
            </div>
        </div>
        
        <!-- Import Rules -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title cardTitle">
                    <i class="bi bi-gear"></i> Import Rules
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        Existing contacts (same email) will be <strong>updated</strong>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        New contacts will be <strong>created</strong>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        Invalid rows will be <strong>skipped</strong>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check text-success"></i>
                        Tags are comma-separated
                    </li>
                    <li>
                        <i class="bi bi-check text-success"></i>
                        All changes are logged in audit trail
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Sample CSV Download -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title cardTitle">
                    <i class="bi bi-download"></i> Download Sample
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Download a sample CSV file to use as a template for your import.
                </p>
                
                <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="downloadSampleCsv()">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Download Sample CSV
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadSampleCsv() {
        const csvContent = `name,email,phone,company,tags
John Doe,john@example.com,+1-555-0123,Acme Corp,"customer,vip"
Jane Smith,jane@example.com,+1-555-0124,Tech Solutions,"lead,potential"
Bob Johnson,bob@example.com,,Global Industries,partner
Alice Brown,alice@example.com,+1-555-0126,,"customer,new"`;
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'contacts_sample.csv';
        a.click();
        URL.revokeObjectURL(url);
        
        Swal.fire({
            title: 'Downloaded!',
            text: 'Sample CSV file downloaded successfully',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }
    
    // File validation
    document.getElementById('csv_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Check file type
        if (!file.type.includes('csv') && !file.name.endsWith('.csv')) {
            Swal.fire({
                title: 'Invalid File Type',
                text: 'Please select a CSV file.',
                icon: 'error'
            });
            e.target.value = '';
            return;
        }
        
        // Check file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                title: 'File Too Large',
                text: 'File size must be less than 2MB.',
                icon: 'error'
            });
            e.target.value = '';
            return;
        }
        
        // Show file info
        const fileInfo = document.createElement('div');
        fileInfo.className = 'mt-2 text-muted small';
        fileInfo.innerHTML = `
            <i class="bi bi-file-earmark-check text-success"></i>
            Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)
        `;
        
        // Remove existing file info
        const existingInfo = e.target.parentNode.querySelector('.text-muted.small');
        if (existingInfo && existingInfo.innerHTML.includes('Selected:')) {
            existingInfo.remove();
        }
        
        e.target.parentNode.appendChild(fileInfo);
    });
</script>

<style>
    .cardTitle{
        color: #657845;
    }
</style>