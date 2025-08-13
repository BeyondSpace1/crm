<?php
declare(strict_types=1);
?>
<form id="importContactsForm" enctype="multipart/form-data">
    <label for="csvFile">Select CSV file:</label><br>
    <input type="file" name="csvFile" id="csvFile" accept=".csv" required><br><br>
    <button type="submit">Import</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('importContactsForm');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fileInput = document.getElementById('csvFile');
        if(!fileInput.files.length){
            Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Please select a CSV file to import.' });
            return;
        }

        const formData = new FormData(form);

        try {
            const res = await fetch('../classes/Controllers/ContactController.php?action=importCSV', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if(data.success){
                let html = `<p>Imported: ${data.success.join(', ')}</p>`;
                if(data.failed && data.failed.length) html += `<p>Failed: ${data.failed.join(', ')}</p>`;
                
                Swal.fire({ icon:'success', title:'Import Results', html: html });
                if(typeof fetchContacts === 'function') fetchContacts(); // refresh list
                setTimeout(() => Swal.close(), 2500);
            } else {
                Swal.fire({ icon:'error', title:'Import Failed', text: data.message || 'Something went wrong' });
            }
        } catch (err) {
            Swal.fire({ icon:'error', title:'Error', text: err.message });
        }
    });
});
</script>
