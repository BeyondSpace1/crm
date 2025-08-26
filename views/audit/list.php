<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-clock-history"></i> Audit Logs
        <small class="text-muted">(Admin Only)</small>
    </h1>
    
    <div class="btn-group">
        <a href="index.php?action=audit.exportCsv<?= !empty($_GET) ? '&' . http_build_query(array_intersect_key($_GET, array_flip(['filter_action', 'filter_entity', 'filter_actor', 'date_from', 'date_to']))) : '' ?>" 
           class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        
        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
            <i class="bi bi-arrow-clockwise"></i> Clear Filters
        </button>
    </div>
</div>

<!-- Audit Summary -->
<?php if (!empty($auditSummary)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title cardTitle">
                <i class="bi bi-bar-chart"></i> Activity Summary
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach (array_slice($auditSummary, 0, 6) as $summary): ?>
                    <div class="col-md-2 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-1"><?= $summary['count'] ?></div>
                            <div class="small text-muted">
                                <?= ucfirst($summary['action']) ?> <?= ucfirst($summary['entity']) ?>
                            </div>
                            <div class="small text-muted">
                                <?= date('M j', strtotime($summary['last_activity'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="action" value="audit.list">
            
            <div class="col-md-2">
                <label for="filter_action" class="form-label">Action</label>
                <select class="form-select" id="filter_action" name="filter_action">
                    <option value="">All Actions</option>
                    <option value="create" <?= ($_GET['filter_action'] ?? '') === 'create' ? 'selected' : '' ?>>Create</option>
                    <option value="update" <?= ($_GET['filter_action'] ?? '') === 'update' ? 'selected' : '' ?>>Update</option>
                    <option value="delete" <?= ($_GET['filter_action'] ?? '') === 'delete' ? 'selected' : '' ?>>Delete</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="filter_entity" class="form-label">Entity</label>
                <select class="form-select" id="filter_entity" name="filter_entity">
                    <option value="">All Entities</option>
                    <option value="contact" <?= ($_GET['filter_entity'] ?? '') === 'contact' ? 'selected' : '' ?>>Contact</option>
                    <option value="user" <?= ($_GET['filter_entity'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="filter_actor" class="form-label">Actor</label>
                <input type="text" 
                       class="form-control" 
                       id="filter_actor" 
                       name="filter_actor" 
                       value="<?= htmlspecialchars($_GET['filter_actor'] ?? '') ?>"
                       placeholder="Email...">
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" 
                       class="form-control" 
                       id="date_from" 
                       name="date_from" 
                       value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" 
                       class="form-control" 
                       id="date_to" 
                       name="date_to" 
                       value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($auditLogs)): ?>
            <div class="text-center py-5">
                <i class="bi bi-clock-history display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No audit logs found</h3>
                <p class="text-muted">
                    <?php if (!empty($_GET) && array_intersect_key($_GET, array_flip(['filter_action', 'filter_entity', 'filter_actor', 'date_from', 'date_to']))): ?>
                        Try adjusting your filter criteria.
                    <?php else: ?>
                        Audit logs will appear here as users perform actions.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Timestamp</th>
                            <th>Actor</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Entity ID</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= date('M j, Y', strtotime($log['ts'])) ?></div>
                                    <small class="text-muted"><?= date('g:i:s A', strtotime($log['ts'])) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($log['actor_email']) ?></div>
                                    <small class="text-muted">ID: <?= $log['actor_id'] ?></small>
                                </td>
                                <td>
                                    <?php
                                        $actionClass = '';
                                        $actionIcon = '';
                                        switch ($log['action']) {
                                            case 'create':
                                                $actionClass = 'success';
                                                $actionIcon = 'plus-circle';
                                                break;
                                            case 'update':
                                                $actionClass = 'warning';
                                                $actionIcon = 'pencil';
                                                break;
                                            case 'delete':
                                                $actionClass = 'danger';
                                                $actionIcon = 'trash';
                                                break;
                                            default:
                                                $actionClass = 'secondary';
                                                $actionIcon = 'question';
                                        }
                                    ?>
                                    <span class="badge bg-<?= $actionClass ?>">
                                        <i class="bi bi-<?= $actionIcon ?>"></i> <?= ucfirst($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bi bi-<?= $log['entity'] === 'contact' ? 'person' : 'gear' ?>"></i>
                                        <?= ucfirst($log['entity']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['entity'] === 'contact'): ?>
                                        <a href="index.php?action=contacts.view&id=<?= $log['entity_id'] ?>" 
                                           class="text-decoration-none">
                                            #<?= $log['entity_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        #<?= $log['entity_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['changes'])): ?>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm"
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#changes-<?= $log['id'] ?>">
                                            <i class="bi bi-eye"></i> View Changes
                                        </button>
                                        <div class="collapse mt-2" id="changes-<?= $log['id'] ?>">
                                            <div class="card card-body bg-light small">
                                                <?php
                                                    $changes = json_decode($log['changes'], true);
                                                    if (is_array($changes)):
                                                ?>
                                                    <?php if (isset($changes['created'])): ?>
                                                        <strong>Created:</strong>
                                                        <pre class="small mb-2"><?= htmlspecialchars(json_encode($changes['created'], JSON_PRETTY_PRINT)) ?></pre>
                                                    <?php elseif (isset($changes['deleted'])): ?>
                                                        <strong>Deleted:</strong>
                                                        <pre class="small mb-2"><?= htmlspecialchars(json_encode($changes['deleted'], JSON_PRETTY_PRINT)) ?></pre>
                                                    <?php else: ?>
                                                        <strong>Changes:</strong>
                                                        <ul class="mb-0">
                                                            <?php foreach ($changes as $field => $change): ?>
                                                                <?php if (is_array($change) && isset($change['old'], $change['new'])): ?>
                                                                    <li>
                                                                        <strong><?= htmlspecialchars($field) ?>:</strong>
                                                                        <span class="text-danger"><?= htmlspecialchars($change['old']) ?></span>
                                                                        →
                                                                        <span class="text-success"><?= htmlspecialchars($change['new']) ?></span>
                                                                    </li>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <pre class="small mb-0"><?= htmlspecialchars($log['changes']) ?></pre>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
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
                        of <?= number_format($pagination['total_items']) ?> logs
                    </div>
                    
                    <nav aria-label="Audit logs pagination">
                        <ul class="pagination mb-0">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= buildAuditPaginationUrl($pagination['prev_page']) ?>">
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
                                    <a class="page-link" href="<?= buildAuditPaginationUrl($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= buildAuditPaginationUrl($pagination['next_page']) ?>">
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
function buildAuditPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'index.php?' . http_build_query($params);
}
?>

<script>
    function clearFilters() {
        window.location.href = 'index.php?action=audit.list';
    }
    
    // Auto-submit form on date changes
    document.getElementById('date_from').addEventListener('change', function() {
        if (this.value && document.getElementById('date_to').value) {
            this.closest('form').submit();
        }
    });
    
    document.getElementById('date_to').addEventListener('change', function() {
        if (this.value && document.getElementById('date_from').value) {
            this.closest('form').submit();
        }
    });
    
    // Fix pagination URL building
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.page-link').forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.includes('buildAuditPaginationUrl')) {
                const text = link.textContent.trim();
                let page = 1;
                
                if (!isNaN(text)) {
                    page = parseInt(text);
                } else if (text.includes('Previous')) {
                    page = Math.max(1, <?= $pagination['current_page'] ?> - 1);
                } else if (text.includes('Next')) {
                    page = Math.min(<?= $pagination['total_pages'] ?>, <?= $pagination['current_page'] ?> + 1);
                }
                
                const url = new URL(window.location);
                url.searchParams.set('page', page);
                link.href = url.toString();
            }
        });
    });
</script>
<style>
    .cardTitle{
        color: #657845;
    }
</style>