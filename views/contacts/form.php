<?php
declare(strict_types=1);

$contactId = $contact->id ?? null;
$name      = $contact->name ?? '';
$email     = $contact->email ?? '';
$phone     = $contact->phone ?? '';
$company   = $contact->company ?? '';
$tags      = $contact->tags ?? '[]';
$tagsDisplay = is_string($tags) ? implode(',', json_decode($tags, true) ?? []) : '';
$action = $contactId ? 'update' : 'create';
?>

<form id="contactForm" data-action="<?= $action ?>" data-id="<?= $contactId ?>" action="javascript:void(0)">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>"><br>

    <label>Company:</label><br>
    <input type="text" name="company" value="<?= htmlspecialchars($company) ?>"><br>

    <label>Tags (comma separated):</label><br>
    <input type="text" id="tagsInput" name="tags" value="<?= htmlspecialchars($tagsDisplay) ?>"><br><br>

    <button type="submit"><?= $contactId ? 'Update' : 'Create' ?></button>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let csrfToken = '';

async function fetchCsrf() {
    try {
        const res = await fetch('classes/Router.php?action=get-csrf', { credentials: 'same-origin' });
        const data = await res.json();
        csrfToken = data.csrf_token || '';
    } catch(err) {
        console.error('Failed to fetch CSRF token:', err);
        Swal.fire('Error', 'Could not fetch CSRF token', 'error');
    }
}
fetchCsrf();

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    const tagsInput = document.getElementById('tagsInput');

    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // STOP native submission

        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        // Convert tags to JSON array
        let tagsArray = tagsInput.value.split(',').map(t => t.trim()).filter(t => t.length > 0);
        formData.set('tags', JSON.stringify(tagsArray));

        const action = form.dataset.action; // 'create' or 'update'
        const id = form.dataset.id;
        if (id) formData.append('id', id);

        try {
            const res = await fetch(`classes/Router.php?action=contacts.${action}`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            let data;
            try { data = await res.json(); } 
            catch(err){
                const text = await res.text();
                Swal.fire({icon:'error', title:'Server Error', html:`<pre>${text}</pre>`});
                return;
            }

            if(data.success){
                Swal.fire({icon:'success', title:`Contact ${action==='create'?'created':'updated'}!`, timer:1500, showConfirmButton:false});
                if(typeof fetchContacts==='function') fetchContacts();
            } else {
                Swal.fire({icon:'error', title:'Error', text:data.message || 'Something went wrong'});
            }

        } catch(err){
            Swal.fire({icon:'error', title:'Network Error', text:err.message});
        }
    });
});
</script>
