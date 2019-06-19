<table class="filedata table-sm modal-segment-heading">
    <tr>
        <th>{str tag=createdin section=artefact.blog}:</th>
        <td>{$parentblogtitle}</td>
    </tr>
    {if $postedby}
    <tr>
        <th>{str tag=postedby section=artefact.blog}:</th>
        <td>{$postedby}</td>
    </tr>
    {/if}
    <tr>
        <th>{str tag=postedon section=artefact.blog}:</th>
        <td>{$postedon}</td>
    </tr>
    {if $lastmodifieddate}
    <tr>
        <th>{str tag=updatedon section=artefact.blog}:</th>
        <td>{$lastmodifieddate}</td>
    </tr>
    {/if}
    {if $artefacttags}
    <tr>
        <th>{str tag=tags section=mahara}:</th>
        <td>{list_tags owner=$owner tags=$artefacttags view=$view}</td>
    </tr>
    {/if}
    {if $license}
    <tr>
        <th>{str tag=License section=artefact.file}:</th>
        <td>{$license|safe}</td>
    </tr>
    {/if}
</table>
