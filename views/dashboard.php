<?php
require_once __DIR__ . '/../classes/repositories/UserRepository.php';
$userRepo = new UserRepository();

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['user']['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
<style>
body { font-family: Arial; margin:0; padding:0; background:#f5f5f5; }
.navbar { position: fixed; top: 0; right: 0; width: 60px; height: 100%; background: #222; display: flex; flex-direction: column; align-items: center; padding-top: 20px; }
.navbar button { margin: 10px 0; color: white; background: none; border: none; font-size: 24px; cursor: pointer; }
.list-container { margin-right: 80px; padding: 20px; }
.contact-item { padding:5px 0; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center; }
</style>
</head>
<body>

<div class="navbar">
    <button id="btnCreate" title="Add Contact">+</button>
    <?php if(in_array($role,['admin','viewer'])): ?>
        <button id="btnTrash" title="Show Deleted">🗑</button>
    <?php endif; ?>
    <?php if($role==='admin'): ?>
        <button id="btnAudit" title="Audit Logs">📜</button>
    <?php endif; ?>
    <button id="btnLogout" title="Logout">🚪</button>
</div>

<div class="list-container">
    <input class="search" placeholder="Search contacts" />
    <button id="btnImport">Import CSV</button>
    <button id="btnExport">Export CSV</button>
    <ul class="list" id="contactsList"></ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const role = '<?= $role ?>';
    let csrfToken = '';

    // --- Fetch CSRF token
    async function fetchCsrfToken() {
        try {
            const res = await fetch('../Router.php?action=get-csrf', { credentials:'same-origin' });
            const data = await res.json().catch(()=>({csrf_token:''}));
            csrfToken = data.csrf_token || '';
        } catch(err) {
            console.error('CSRF token fetch failed', err);
        }
    }
    await fetchCsrfToken();

    // --- Fetch contacts
    async function fetchContacts(includeDeleted=false){
        try {
            let url = '../Router.php?action=contacts.list';
            if(includeDeleted) url += '&deleted=1';
            const res = await fetch(url, { credentials:'same-origin' });
            const data = await res.json().catch(()=>[]);
            const ul = document.getElementById('contactsList');
            ul.innerHTML = '';

            if(!Array.isArray(data) || data.length===0){
                ul.innerHTML = '<li>No contacts found.</li>';
                return;
            }

            data.forEach(c => {
                const li = document.createElement('li');
                li.classList.add('contact-item');
                li.dataset.id = c.id;
                li.innerHTML = `
                    <span class="name">${c.name}</span> |
                    <span class="email">${c.email}</span> |
                    <span class="company">${c.company}</span>
                    <span class="contact-actions">
                        <button class="view">👁</button>
                        ${role!=='viewer' && !includeDeleted ? `<button class="edit">✏️</button>` : ''}
                        ${role==='admin' && !includeDeleted ? `<button class="delete">🗑</button>` : ''}
                    </span>
                `;
                ul.appendChild(li);
            });

            new List('contactsList', { valueNames: ['name','email','company'] });

            document.querySelectorAll('.view').forEach(b => b.onclick = e => openForm(e.target.closest('li').dataset.id));
            document.querySelectorAll('.edit').forEach(b => b.onclick = e => openForm(e.target.closest('li').dataset.id));
            document.querySelectorAll('.delete').forEach(b => b.onclick = deleteContact);
        } catch(err){
            console.error('Fetch contacts failed', err);
        }
    }

    // --- Open form
    async function openForm(typeOrId=null){
        try {
            let url = '../Router.php?action=contacts.getForm';
            if(typeOrId && typeOrId!=='import') url += '&id=' + typeOrId;
            if(typeOrId==='import') url += '&id=import';

            const res = await fetch(url, { credentials:'same-origin' });
            const html = await res.text();

            Swal.fire({
                html: html,
                showCloseButton:true,
                focusConfirm:false,
                didOpen: () => {
                    const form = document.querySelector('#contactForm');
                    if(form){
                        form.addEventListener('submit', async e => {
                            e.preventDefault();
                            const fd = new FormData(form);
                            fd.append('csrf_token', csrfToken);
                            const action = form.dataset.action || 'contacts.create';

                            const resp = await fetch(`../Router.php?action=${action}`, {
                                method:'POST',
                                body: fd,
                                credentials:'same-origin'
                            });

                            const data = await resp.json().catch(()=>({success:false, message:'Invalid response'}));

                            if(data.success){
                                Swal.close();
                                fetchContacts();
                                Swal.fire({icon:'success', title:'Saved!'});
                            } else {
                                Swal.fire({icon:'error', title:'Failed', text: data.message || 'Error'});
                            }
                        });
                    }
                }
            });
        } catch(err){ console.error('openForm failed', err); }
    }

    // --- Delete contact
    async function deleteContact(e){
        const li = e.target.closest('li');
        const id = li.dataset.id;
        const result = await Swal.fire({title:'Delete contact?', showCancelButton:true, confirmButtonText:'Yes'});
        if(result.isConfirmed){
            const res = await fetch('../Router.php?action=contacts.delete', {
                method:'POST',
                body: new URLSearchParams({id, csrf_token: csrfToken}),
                credentials:'same-origin'
            });
            await res.json().catch(()=>{});
            fetchContacts();
        }
    }

    // --- Audit logs
    async function fetchAuditLogs(){
        try {
            const res = await fetch('../Router.php?action=audit.logs', { credentials:'same-origin' });
            const data = await res.json().catch(()=>[]);
            if(!Array.isArray(data) || !data.length){
                Swal.fire({icon:'info', title:'Audit Logs', text:'No logs found'});
                return;
            }
            let html = '<table style="width:100%;text-align:left;"><tr><th>TS</th><th>User</th><th>Action</th><th>Entity</th><th>ID</th></tr>';
            data.forEach(l=>{
                html += `<tr><td>${l.ts}</td><td>${l.actor_email}</td><td>${l.action}</td><td>${l.entity}</td><td>${l.entity_id}</td></tr>`;
            });
            html += '</table>';
            Swal.fire({title:'Audit Logs', html:html, width:'80%'});
        } catch(err){ console.error('Fetch audit logs failed', err); }
    }

    // --- Event listeners
    document.getElementById('btnCreate').onclick = () => openForm();
    document.getElementById('btnTrash')?.addEventListener('click', () => fetchContacts(true));
    document.getElementById('btnImport').addEventListener('click', () => openForm('import'));
    document.getElementById('btnExport').addEventListener('click', () => { window.location.href='../Router.php?action=contacts.exportCSV'; });
    document.getElementById('btnAudit')?.addEventListener('click', fetchAuditLogs);
    document.getElementById('btnLogout').addEventListener('click', async ()=>{
        const result = await Swal.fire({title:'Logout?', showCancelButton:true, confirmButtonText:'Yes'});
        if(result.isConfirmed){
            await fetch('../Router.php?action=logout',{method:'POST',credentials:'same-origin'});
            window.location.href='./';
        }
    });

    fetchContacts();
});
</script>
</body>
</html>
