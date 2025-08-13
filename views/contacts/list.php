<?php
// views/contacts/list.php
require_once __DIR__ . '/../../repositories/ContactRepository.php';
session_start();
$user = $_SESSION['user'];
$repo = new ContactRepository();

$trash = isset($_GET['trash']) && $_GET['trash'] == 1;
// Use getAll instead of getContacts
$contacts = $repo->getAll($trash);
?>
<div id="contacts">
    <input class="search" placeholder="Search contacts" />
    <ul class="list">
        <?php foreach ($contacts as $c): ?>
        <li>
            <span class="name"><?= htmlspecialchars($c->name) ?></span>
            <span><?= htmlspecialchars($c->email) ?></span>
            <?php if ($user['role'] !== 'viewer'): ?>
                <button onclick="editContact(<?= $c->id ?>)">Edit</button>
            <?php endif; ?>
            <button onclick="viewContact(<?= $c->id ?>)">View</button>
            <?php if ($user['role'] === 'admin'): ?>
                <button onclick="deleteContact(<?= $c->id ?>)">Delete</button>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
var contactList = new List('contacts', { valueNames: ['name'] });

function viewContact(id) {
    fetch('/contacts/view.php?id=' + id)
        .then(res => res.text())
        .then(html => document.getElementById('content-area').innerHTML = html);
}

function editContact(id) {
    fetch('/contacts/form.php?id=' + id)
        .then(res => res.text())
        .then(html => document.getElementById('content-area').innerHTML = html);
}

function deleteContact(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will move the contact to trash!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/contacts/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            }).then(() => loadContacts());
        }
    });
}
</script>
