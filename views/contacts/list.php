<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> Contacts</h1>
    
    <div class="btn-group">
        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor'])): ?>
            <a href="index.php?action=contacts.create" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> New Contact
            </a>
        <?php endif; ?>
        
        <a href="index.php?action=contacts.exportCsv<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= isset($_GET['show_deleted']) ? '&show_deleted=1' : '' ?>" 
           class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="action" value="contacts.list">
            
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                       placeholder="Search by name, email, or company...">
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="index.php?action=contacts.list" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Clear
                </a>
            </div>
            
            <div class="col-md-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="show_deleted" 
                           name="show_deleted" 
                           value="1" 
                           <?= isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1' ? 'checked' : '' ?>
                           onchange="this.form.submit()">
                    <label class="form-check-label" for="show_deleted">
                        Show Deleted Contacts
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Contacts Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($contacts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No contacts found</h3>
                <p class="text-muted">
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        Try adjusting your search criteria.
                    <?php else: ?>
                        Start by creating your first contact.
                    <?php endif; ?>
                </p>
                <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor'])): ?>
                    <a href="index.php?action=contacts.create" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Create First Contact
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <?php 
                                $tags = $contact['tags'];
                                if (is_string($tags)) {
                                    $tagsArray = json_decode($tags, true);
                                    $tags = is_array($tagsArray) ? $tagsArray : [];
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($contact['name']) ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                        <?= htmlspecialchars($contact['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($contact['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                                            <?= htmlspecialchars($contact['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($contact['company'] ?? '') ?: '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <?php if (!empty($tags)): ?>
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($contact['deleted_at']): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-trash"></i> Deleted
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($contact['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?action=contacts.view&id=<?= $contact['id'] ?>" 
                                           class="btn btn-outline-info"
                                           title="View Contact">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor'])): ?>
                                            <?php if (!$contact['deleted_at']): ?>
                                                <a href="index.php?action=contacts.update&id=<?= $contact['id'] ?>" 
                                                   class="btn btn-outline-warning"
                                                   title="Edit Contact">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <form method="POST" action="index.php?action=contacts.delete" class="d-inline">
                                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger btn-delete"
                                                            title="Delete Contact">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Deleted</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing <?= number_format($pagination['per_page'] * ($pagination['current_page'] - 1) + 1) ?> 
                        to <?= number_format(min($pagination['per_page'] * $pagination['current_page'], $pagination['total_items'])) ?> 
                        of <?= number_format($pagination['total_items']) ?> contacts
                    </div>
                    
                    <nav aria-label="Contacts pagination">
                        <ul class="pagination mb-0">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->buildPaginationUrl($pagination['prev_page']) ?>">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-left"></i> Previous</span>
                                </li>
                            <?php endif; ?>
                            
                            <?php 
                                $start = max(1, $pagination['current_page'] - 2);
                                $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $this->buildPaginationUrl($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->buildPaginationUrl($pagination['next_page']) ?>">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper function for pagination URLs
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'index.php?' . http_build_query($params);
}
?>

<script>
    // Fix pagination URL building
    document.querySelectorAll('.page-link').forEach(link => {
        if (link.href.includes('buildPaginationUrl')) {
            const page = link.textContent.trim();
            if (!isNaN(page)) {
                const url = new URL(window.location);
                url.searchParams.set('page', page);
                link.href = url.toString();
            }
        }
    });
    
    // Fix pagination for Previous/Next buttons
    document.querySelectorAll('.page-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes('$this->buildPaginationUrl')) {
            let page = 1;
            if (link.textContent.includes('Previous')) {
                page = Math.max(1, <?= $pagination['current_page'] ?> - 1);
            } else if (link.textContent.includes('Next')) {
                page = Math.min(<?= $pagination['total_pages'] ?>, <?= $pagination['current_page'] ?> + 1);
            }
            
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            link.href = url.toString();
        }
    });
</script>