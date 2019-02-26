{if $rows}
<div class="text-right">
    <a class="btn btn-secondary" href="{$WWWROOT}artefact/internal/socialprofile.php">
        <span class="icon icon-lg icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag=newsocialprofile section=artefact.internal}
    </a>
</div>
<div class="table-responsive">
<table id="socialprofilelist" class="tablerenderer fullwidth table">
    <thead>
        <tr>
            <th class="icons"></th>
            <th>{str tag='service' section='artefact.internal'}</th>
            <th>{str tag='profileurl' section='artefact.internal'}</th>
            {if $controls}<th class="control-buttons">
                <span class="accessible-hidden sr-only">{str tag=edit}</span>
            </th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="social-info">
            <td class="text-center">
                <img src="{$row->icon}" alt="{$row->description}">
            </td>
            <td>
                <span>{$row->description}</span>
            </td>
            <td>
                {if $row->link}
                <a href="{$row->link}" title="{$row->link}" class="socialprofile">
                {/if}
                {$row->title}
                {if $row->link}
                </a>{/if}
            </td>
            {if $controls}
            <td class="control-buttons">
                <div class="btn-group">
                    <a href="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}" title="{str tag='edit'}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag='edit'}</span>
                    </a>
                    {if $candelete}
                    <a href="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}&delete=1" title="{str tag='delete'}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag='delete'}</span>
                    </a>
                    {/if}
                </div>
            </td>
            {/if}
        </tr>
        {/foreach}
    </tbody>
</table>
</div>
{else}
<p class="no-results">
    <a href="{$WWWROOT}artefact/internal/socialprofile.php">
        <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag=newsocialprofile section=artefact.internal}
    </a>
</p>
{/if}
{$pagination.html|safe}
