{if $artefacttype == 'image' || $artefacttype == 'profileicon'}
<h2 class="title">
    {str tag=Preview section=artefact.file}
</h2>
<div class="filedata-icon">
    <a href="{$downloadpath}">
        <img src="{$downloadpath}&maxwidth=400&maxheight=180" alt="">
    </a>
</div>
{/if}
<table class="filedata table-sm">
    <tr>
        <th>{str tag=Type section=artefact.file}:</th>
        <td>{$filetype}</td>
    </tr>
    {if $description}
    <tr>
        <th>{str tag=Description section=artefact.file}:</th>
        <td>{$description|safe|clean_html}</td>
    </tr>
    {/if}
    {if $tags}
    <tr>
        <th>{str tag=tags}:</th>
        <td>{list_tags owner=$owner tags=$tags view=$view}</td>
    </tr>
    {/if}
    {if $ownername}
    <tr>
        <th>{str tag=Owner section=artefact.file}:</th>
        <td>{$ownername}</td>
    </tr>
    {/if}
    {if $uploadedby}
    <tr>
        <th>{str tag=uploadedby section=artefact.file}:</th>
        <td>{$uploadedby}</td>
    </tr>
    {/if}
    <tr>
        <th>{str tag=Created section=artefact.file}:</th>
        <td>{$created}</td>
    </tr>
    <tr>
        <th>{str tag=lastmodified section=artefact.file}:</th>
        <td>{$modified}</td>
    </tr>
    <tr>
        <th>{str tag=Size section=artefact.file}:</th>
        <td>{$size}</td>
    </tr>
    {if $license!==false}
    <tr>
        <th>{str tag=License section=artefact.file}:</th>
        <td>{$license|safe}</td>
    </tr>
    {/if}
    <tr>
        <th class="sr-only">{str tag=Download section=artefact.file}:</th>
        <td>
            <a class="btn btn-secondary btn-sm" href="{$downloadpath}">{str tag=Download section=artefact.file}</a>
        </td>
    </tr>
</table>
