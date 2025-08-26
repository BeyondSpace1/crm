<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-<?= $isDryRun ? 'search' : 'check-circle' ?>"></i>
        <?= $isDryRun ? 'Import Preview' : 'Import Results' ?>
    </h1>
    
    <a href="index.php?action=contacts.list" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Contacts
    </a>
</div>

<?php if ($isDryRun): ?>
    <?php $results = $_SESSION['dry_run_results']; ?>
    
    <!-- Dry Run Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-primary"><?= $results['summary']['total'] ?></div>
                    <div class="text-muted">Total Rows</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-success"><?= $results['summary']['valid'] ?></div>
                    <div class="text-muted">Valid Rows</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-warning"><?= $results['summary']['updates'] ?></div>
                    <div class="text-muted">Updates</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-info"><?= $results['summary']['new'] ?></div>
                    <div class="text-muted">New Contacts</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Review Preview Results</h5>
                    <p class="text-muted mb-0">
                        <?php if ($results['summary']['valid'] > 0): ?>
                            Ready to import <?= $results['summary']['valid'] ?> valid contacts.
                        <?php else: ?>
                            No valid contacts found. Please fix errors and try again.
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="btn-group">
                    <a href="index.php?action=contacts.importCsvForm" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Import
                    </a>
                    
                    <?php if ($results['summary']['valid'] > 0): ?>
                        <form method="POST" action="index.php?action=contacts.importCsvCommit" class="d-inline">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-success btn-commit-import">
                                <i class="bi bi-upload"></i> Commit Import
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php $results = $importResults; ?>
    
    <!-- Final Import Summary -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-primary"><?= $results['summary']['total'] ?></div>
                    <div class="text-muted">Total Rows</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-success"><?= $results['summary']['success'] ?></div>
                    <div class="text-muted">Successful</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-info"><?= $results['summary']['created'] ?></div>
                    <div class="text-muted">Created</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-warning"><?= $results['summary']['updated'] ?></div>
                    <div class="text-muted">Updated</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-danger"><?= $results['summary']['errors'] ?></div>
                    <div class="text-muted">Errors</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-secondary"><?= $results['summary']['skipped'] ?></div>
                    <div class="text-muted">Skipped</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Message -->
    <div class="alert alert-success">
        <h5><i class="bi bi-check-circle"></i> Import Complete!</h5>
        <p class="mb-0">
            Successfully processed <?= $results['summary']['total'] ?> rows. 
            <?= $results['summary']['created'] ?> contacts created, 
            <?= $results['summary']['updated'] ?> contacts updated.
        </p>
    </div>
<?php endif; ?>

<!-- Detailed Results -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title cardTitle">
            <i class="bi bi-list-ul"></i> Detailed Results
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($results['results'])): ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-3">No results to display</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Row</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['results'] as $result): ?>
                            <tr>
                                <td><?= $result['index'] ?></td>
                                <td><?= htmlspecialchars($result['data']['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($result['data']['email'] ?? '') ?></td>
                                <td>
                                    <?php
                                        $actionClass = '';
                                        $actionIcon = '';
                                        $actionText = '';
                                        
                                        switch ($result['action'] ?? $result['action']) {
                                            case 'create':
                                            case 'created':
                                                $actionClass = 'success';
                                                $actionIcon = 'plus-circle';
                                                $actionText = 'Create';
                                                break;
                                            case 'update':
                                            case 'updated':
                                                $actionClass = 'warning';
                                                $actionIcon = 'pencil';
                                                $actionText = 'Update';
                                                break;
                                            case 'error':
                                            case 'validation_error':
                                                $actionClass = 'danger';
                                                $actionIcon = 'exclamation-triangle';
                                                $actionText = 'Error';
                                                break;
                                            default:
                                                $actionClass = 'secondary';
                                                $actionIcon = 'question';
                                                $actionText = ucfirst($result['action'] ?? 'Unknown');
                                        }
                                    ?>
                                    <span class="badge bg-<?= $actionClass ?>">
                                        <i class="bi bi-<?= $actionIcon ?>"></i> <?= $actionText ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($result['success']) && $result['success']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check"></i> Success
                                        </span>
                                    <?php elseif (isset($result['valid']) && $result['valid']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check"></i> Valid
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x"></i> Error
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($result['errors'])): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm"
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#errors-<?= $result['index'] ?>">
                                            <i class="bi bi-exclamation-circle"></i> View Errors
                                        </button>
                                        <div class="collapse mt-2" id="errors-<?= $result['index'] ?>">
                                            <div class="alert alert-danger alert-sm mb-0">
                                                <ul class="mb-0 small">
                                                    <?php foreach ($result['errors'] as $field => $error): ?>
                                                        <li><strong><?= htmlspecialchars($field) ?>:</strong> <?= htmlspecialchars($error) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php elseif (isset($result['id'])): ?>
                                        <small class="text-muted">ID: <?= $result['id'] ?></small>
                                    <?php elseif (isset($result['existing_id'])): ?>
                                        <small class="text-muted">Existing ID: <?= $result['existing_id'] ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isDryRun && $results['summary']['valid'] > 0): ?>
    <script>
        document.querySelector('.btn-commit-import').addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Confirm Import',
                text: `This will import ${<?= $results['summary']['valid'] ?>} contacts. This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Import Now!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    form.submit();
                }
            });
        });
    </script>
<?php endif; ?>