{if $artefacttype == 'image' || $artefacttype == 'profileicon'}
<h2 class="title">
    {str tag=Preview section=artefact.file}
</h2>
<div class="flexbox">
    <div class="filedata-icon modal-segment-heading">
        <a href="{$downloadpath}">
            <img src="{$downloadpath}&maxwidth=400&maxheight=180" alt="{$alttext|clean_html|safe}">
        </a>
    </div>
{/if}
<table class="filedata table-sm modal-segment-heading">
    <tr>
        <th>{str tag=Type section=artefact.file}:</th>
        <td>{$filetype}</td>
    </tr>
    {if $description}
    <tr>
        <th>{if $artefacttype == 'image'}{str tag=caption section=artefact.file}{else}{str tag=Description section=artefact.file}{/if}:</th>
        <td>{$description|clean_html|safe}</td>
    </tr>
    {/if}
    {if $alttext}
    <tr>
        <th>{str tag=alttext section=pieforms}:</th>
        <td>{$alttext|clean_html|safe}</td>
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
        <th class="visually-hidden">{str tag=Download section=artefact.file}:</th>
        <td>
            <a class="btn btn-secondary btn-sm" href="{$downloadpath}">{str tag=Download section=artefact.file}</a>
        </td>
    </tr>
</table>
{if $artefacttype == 'image' || $artefacttype == 'profileicon'}
</div>
{/if}
