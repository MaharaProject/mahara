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
                    <h3 class="title"><a class="collectionlink" href="{$WWWROOT}view/view.php?id={$row.id}">{$row.name}</a></h3>
                {/if}
                </td>
                <td>
                    <h3 class="title"><a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id}">{$row.title}</a></h3>
                </td>
                {if $row.institution}
                <td class="owner">
                    {$row.sharedby}
                </td>
                {elseif $row.group}
                <td class="owner">
                    <a class="grouplink" href="{$row.groupdata->homeurl}">{$row.sharedby}</a>
                </td>
                {elseif $row.owner}
                <td class="ownericon">
                    <a class="userlink" href="{profile_url($row.user, true, true)}">
                        <img src="{profile_icon_url user=$row.user maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$row.user|display_default_name}" class="profile-icon-container">
                            {$row.sharedby}
                    </a>
                </td>
                {else}
                <td class="owner">-</td>
                {/if}
                <td class="action-list-copy">
                    <div class="btn-group btn-group-top">
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
