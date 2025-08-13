<div id="auditListContainer">
    <ul class="list" id="auditList"></ul>
</div>
<script>
fetch('/controllers/AuditController.php?action=list')
.then(r=>r.json())
.then(data=>{
    const ul = document.getElementById('auditList');
    data.forEach(log=>{
        const li = document.createElement('li');
        li.innerHTML = `${log.ts} | ${log.actor_email} | ${log.action} | ${log.entity} | ${log.entity_id} | ${log.changes}`;
        ul.appendChild(li);
    });
    new List('auditList', { valueNames: ['ts','actor_email','action','entity','entity_id','changes'] });
});
</script>
