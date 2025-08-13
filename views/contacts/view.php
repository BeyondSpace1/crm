<?php
$id = $_GET['id'] ?? null;
if(!$id) exit('No contact');
require_once __DIR__.'/../../repositories/ContactRepository.php';
$repo = new ContactRepository();
$c = $repo->getById($id);
if(!$c) exit('Contact not found');
?>
<div>
    <p><strong>Name:</strong> <?= $c->name ?></p>
    <p><strong>Email:</strong> <?= $c->email ?></p>
    <p><strong>Phone:</strong> <?= $c->phone ?></p>
    <p><strong>Company:</strong> <?= $c->company ?></p>
    <p><strong>Tag:</strong> <?= $c->tag ?></p>
</div>
