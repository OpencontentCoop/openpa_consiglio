{if $node.data_map.file.content.contentobject_attribute_id}
    {def $file = $node.data_map.file}
    <a href={concat("content/download/", $file.contentobject_id, "/", $file.id, "/file/", $file.content.original_filename)|ezurl}>
        <i class="fa fa-download"></i> {$file.content.original_filename}
    </a>
    {$file.content.filesize|si(byte)}
    {undef $file}
{/if}
