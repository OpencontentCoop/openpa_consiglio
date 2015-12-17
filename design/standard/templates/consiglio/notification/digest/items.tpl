<div style="background: #eee; padding: 20px; border: 1ps solid #ccc">
<ul>
{foreach $items as $item}
    <li><strong>{$item.subject|wash()}:</strong> {$item.body|wash()}</li>
{/foreach}
</ul>
</div>