{include file="header.tpl"}

{if $admin}
<div id="forumbtn" class="btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}interaction/edit.php?group={$groupid}&amp;plugin=forum" class="btn btn-default newforum">
        <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag="newforum" section=interaction.forum}
    </a>
</div>
{/if}

<div class="view-container">
    {if $forums}
    <div id="view-forum" class="table-responsive">
        <table id="forums-list" class="table fullwidth table-padded">
            <thead>
                <tr>
                    <th>
                        {str tag="name" section="interaction.forum"}
                    </th>
                    <th class="text-center">
                        {str tag="Topics" section="interaction.forum"}
                    </th>
                    <th class="subscribeth">
                        <span class="accessible-hidden sr-only">
                            {str tag=Subscribe section=interaction.forum}
                        </span>
                    </th>
                    <th class="control-buttons">
                        <span class="accessible-hidden sr-only">
                            {str tag=edit}
                        </span>
                    </th>
                </tr>
            </thead>
        <tbody>
        {foreach from=$forums item=forum}

            <tr>
                <td>
                    <h3 class="title">
                        <a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id}">
                            {$forum->title}
                        </a>

                        {if $publicgroup}
                        <a href="{$forum->feedlink}">
                           <span class="icon-rss icon icon-sm right mahara-rss-icon" role="presentation" aria-hidden="true"></span>
                        </a>
                        {/if}
                    </h3>
                    <div class="detail text-small">
                        {$forum->description|str_shorten_html:1000:true|safe}
                    </div>

                    {if $forum->moderators}
                    <div class="text-small">
                        <strong>
                            {str tag="Moderators" section="interaction.forum"}:
                        </strong>

                        {foreach from=$forum->moderators item=mod}
                        <a href="{profile_url($mod)}">
                            <img src="{profile_icon_url user=$mod maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$mod|display_default_name}">
                        </a>

                        <a href="{profile_url($mod)}" class="moderator">
                            {$mod|display_name:null:true}
                        </a>
                        {/foreach}
                    </div>
                    {/if}
                </td>

                <td class="text-center">
                    {$forum->topiccount}
                </td>

                <td class="subscribetd">
                    {if $forum->subscribe}
                    {$forum->subscribe|safe}
                    {/if}
                </td>

                <td class="right control-buttons">
                    <div class="btn-group">
                        <a href="{$WWWROOT}interaction/edit.php?id={$forum->id}&amp;returnto=index" class="btn btn-default btn-sm" title="{str tag=edit}">
                            <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">{str tag=editspecific arg1=$forum->title}</span>
                        </a>

                        <a href="{$WWWROOT}interaction/delete.php?id={$forum->id}&amp;returnto=index" class="btn btn-default btn-sm" title="{str tag=delete}">
                            <span class="text-danger icon icon-trash icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">{str tag=deletespecific arg1=$forum->title}</span>
                        </a>
                    </div>
                </td>
            </tr>
        {/foreach}
        <tbody>
        </table>
    </div>
    {else}
        <p class="no-results">{str tag=noforums section=interaction.forum}</p>
    {/if}
    <div class="forummods">
        <p class="text-small text-inline">
            {str tag="groupadminlist" section="interaction.forum"}
        </p>
        {foreach from=$groupadmins item=groupadmin}
            <a href="{profile_url($groupadmin)}" class="label label-default">
                <img src="{profile_icon_url user=$groupadmin maxheight=20 maxwidth=20}" alt="{str tag=profileimagetext arg1=$groupadmin|display_default_name}" class="user-icon-alt"> {$groupadmin|display_name}
            </a>
        {/foreach}
    </div>
</div>
{include file="footer.tpl"}
