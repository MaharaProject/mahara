{if $results}
<div class="table-responsive">
    <table class="table fullwidth">
        <thead>
            <tr>
                <th>{str tag=collectiontitle section=collection}</th>
                <th>{str tag=viewname section=view}</th>
                <th>{str tag=Owner section=view}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$results item=row}
            <tr>
                <td>
                {if $row.collid}
                    <h2 class="title"><a class="collectionlink" href="{$WWWROOT}view/view.php?id={$row.id}">{$row.name}</a></h2>
                {/if}
                </td>
                <td>
                    <h2 class="title"><a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id}">{$row.title}</a></h2>
                </td>
                {if $row.institution}
                <td class="owner text-small">
                    {$row.sharedby}
                </td>
                {elseif $row.group}
                <td class="owner text-small">
                    <a class="grouplink" href="{$row.groupdata->homeurl}">{$row.sharedby}</a>
                </td>
                {elseif $row.owner}
                <td class="ownericon text-small">
                    <a class="userlink" href="{profile_url($row.user, true, true)}">
                        <img src="{profile_icon_url user=$row.user maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$row.user|display_default_name}" class="profile-icon-container">
                            {$row.sharedby}
                    </a>
                </td>
                {else}
                <td class="owner text-small">-</td>
                {/if}
                <td class="action-list-copy text-right">
                    <div class="btn-group">
                    {$row.form|safe}
                    </div>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{else}
    <div class="no-results">
        {str tag="nocopyableviewsfound" section=view}
    </div>
{/if}
