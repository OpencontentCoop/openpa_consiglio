<div style="background: #eee; padding: 20px; border: 1ps solid #ccc">
Le notifichiamo i seguenti cambiamenti:
<ul>
{foreach $items as $item}
    <li><strong>{$item.subject|wash()}:</strong> {$item.body|wash()}</li>
{/foreach}
</ul>
</div>