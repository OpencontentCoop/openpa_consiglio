Le notifichiamo i seguenti cambiamenti:
<ul>
{foreach $items as $item}
    <li><strong>{$item.subject}:</strong> {$item.body}</li>
{/foreach}
</ul>