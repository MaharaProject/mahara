<br /><div class="rbuttons">
    <a class="btn" href="{$WWWROOT}artefact/internal/socialprofile.php">{str tag=newsocialprofile section=artefact.internal}</a>
</div>
<table id="socialprofilelist" class="tablerenderer fullwidth table">
    <thead>
        <tr>
            <th class="icons"></th>
            <th>{str tag='service' section='artefact.internal'}</th>
            <th>{str tag='profileurl' section='artefact.internal'}</th>
            {if $controls}<th class="btns2">
                <span class="accessible-hidden sr-only">{str tag=edit}</span>
            </th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r1'}">
            <td class="center"><img src="{$row->icon}" alt="{$row->description}"></td>
            <td><span>{$row->description}</span></td>
            <td>{if $row->link}<a href="{$row->link}" title="{$row->link}" target="_blank" class="socialprofile">{/if}{$row->title}{if $row->link}</a>{/if}</td>
            {if $controls}<td class="right buttonscell btns2">
                <a href="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}" title="{str tag='edit'}" class="btn btn-default btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span>
                    <span class="sr-only">{str tag='edit'}</span>
                </a>
                {if $candelete}
                <a href="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}&delete=1" title="{str tag='delete'}" class="btn btn-danger btn-xs">
                    <span class="glyphicon glyphicon-trash"></span>
                    <span class="sr-only">{str tag='delete'}</span>
                </a>
                {/if}
            </td>{/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{$pagination.html|safe}
