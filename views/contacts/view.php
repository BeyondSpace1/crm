<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-person-circle"></i>
        <?= htmlspecialchars($contact['name']) ?>
    </h1>
    
    <div class="btn-group">
        <a href="index.php?action=contacts.list" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Contacts
        </a>
        
        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor']) && !$contact['deleted_at']): ?>
            <a href="index.php?action=contacts.update&id=<?= $contact['id'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Contact
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title cardTitle">
                    <i class="bi bi-info-circle"></i> Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Name</label>
                        <div class="fw-bold"><?= htmlspecialchars($contact['name']) ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email</label>
                        <div>
                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="text-decoration-none">
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($contact['email']) ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone</label>
                        <div>
                            <?php if (!empty($contact['phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" class="text-decoration-none">
                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($contact['phone']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Company</label>
                        <div>
                            <?php if (!empty($contact['company'])): ?>
                                <i class="bi bi-building"></i> <?= htmlspecialchars($contact['company']) ?>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Tags</label>
                    <div>
                        <?php 
                            $tags = $contact['tags'];
                            if (is_string($tags)) {
                                $tagsArray = json_decode($tags, true);
                                $tags = is_array($tagsArray) ? $tagsArray : [];
                            }
                        ?>
                        <?php if (!empty($tags)): ?>
                            <?php foreach ($tags as $tag): ?>
                                <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">No tags</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <?php if ($contact['deleted_at']): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-trash"></i> Deleted on <?= date('M j, Y g:i A', strtotime($contact['deleted_at'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Active
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">ID</label>
                        <div class="font-monospace">#<?= $contact['id'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <?php if (!$contact['deleted_at']): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title cardTitle">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="btn-group flex-wrap">
                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-envelope"></i> Send Email
                        </a>
                        
                        <?php if (!empty($contact['phone'])): ?>
                            <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" 
                               class="btn btn-outline-success">
                                <i class="bi bi-telephone"></i> Call
                            </a>
                        <?php endif; ?>
                        
                        <button type="button" 
                                class="btn btn-outline-info"
                                onclick="copyToClipboard('<?= htmlspecialchars($contact['email']) ?>')">
                            <i class="bi bi-clipboard"></i> Copy Email
                        </button>
                        
                        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor'])): ?>
                            <form method="POST" action="index.php?action=contacts.delete" class="d-inline">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-delete">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <!-- Metadata -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title cardTitle">
                    <i class="bi bi-clock"></i> Timeline
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Contact Created</h6>
                            <small class="text-muted">
                                <?= date('M j, Y g:i A', strtotime($contact['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($contact['updated_at'] !== $contact['created_at']): ?>
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Updated</h6>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($contact['updated_at'])) ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['deleted_at']): ?>
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Contact Deleted</h6>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($contact['deleted_at'])) ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title cardTitle">
                    <i class="bi bi-download"></i> Export Options
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" 
                            class="btn btn-outline-primary btn-sm"
                            onclick="exportContact('vcard')">
                        <i class="bi bi-person-vcard"></i> Export as vCard
                    </button>
                    
                    <button type="button" 
                            class="btn btn-outline-success btn-sm"
                            onclick="exportContact('json')">
                        <i class="bi bi-file-earmark-code"></i> Export as JSON
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 20px;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .cardTitle{
        color: #657845;
    }    
</style>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                title: 'Copied!',
                text: 'Email address copied to clipboard',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            Swal.fire({
                title: 'Error',
                text: 'Failed to copy to clipboard',
                icon: 'error'
            });
        });
    }
    
    function exportContact(format) {
        const contact = <?= json_encode($contact) ?>;
        
        if (format === 'vcard') {
            const vcard = `BEGIN:VCARD
VERSION:3.0
FN:${contact.name}
EMAIL:${contact.email}
${contact.phone ? `TEL:${contact.phone}` : ''}
${contact.company ? `ORG:${contact.company}` : ''}
END:VCARD`;
            
            const blob = new Blob([vcard], { type: 'text/vcard' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${contact.name.replace(/[^a-zA-Z0-9]/g, '_')}.vcf`;
            a.click();
            URL.revokeObjectURL(url);
            
        } else if (format === 'json') {
            const json = JSON.stringify(contact, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `contact_${contact.id}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
        
        Swal.fire({
            title: 'Exported!',
            text: `Contact exported as ${format.toUpperCase()}`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }
</script>