<div class="panel panel-default">
        <h3 class="panel-heading profile-block">
            <a href="{$sbdata.url}" class="username">{$sbdata.myname}</a>
            <a href="{$sbdata.profileiconurl}" title="{str tag="editprofileicon" section="artefact.file"}" class="user-icon">
                <img src="{profile_icon_url user=$sbdata.id maxheight=60 maxwidth=60}" alt="{str tag="editprofileicon" section="artefact.file"}">
            </a>
        </h3>
    <div class="list-group">
        {if $sbdata.mnetloggedinfrom}
            <div class="sidebar-content">
                <p>{$sbdata.mnetloggedinfrom|clean_html|safe}</p>
            </div>
        {/if}
        {if $sbdata.invitedgroups}
            <div id="invitedgroups" class="list-group-item list-group-item-warning list-group-item-profile-notification">
                <a href="{$WWWROOT}group/mygroups.php?filter=invite">
                    <div>
                        <span id="invitedgroupscount" class="label label-warning">{$sbdata.invitedgroups}</span>
                    </div>
                    <span id="invitedgroupsmessage">{$sbdata.invitedgroupsmessage}</span>
                </a>
            </div>
        {/if}
        {if $sbdata.pendingfriends}
            <div id="pendingfriends" class="list-group-item list-group-item-warning list-group-item-profile-notification">
                <a href="{$WWWROOT}user/myfriends.php?filter=pending">
                    <div>
                        <span id="pendingfriendscount" class="label label-warning">{$sbdata.pendingfriends}</span>
                    </div>
                    <span id="pendingfriendsmessage">{$sbdata.pendingfriendsmessage}</span>
                </a>
            </div>
        {/if}
        {if $sbdata.groups}
            <div id="groups" class="list-group-item">
                <div class="list-group-item-heading sidebar-item-heading"><a href="{$WWWROOT}group/mygroups.php">{str tag="mygroups"}:</a></div>
                <ul class="list-nested list-group-item-text list-unstyled list-group-item-link">
        {foreach from=$sbdata.groups item=group}
                    <li><a href="{$group->homeurl}" class="bordered">{$group->name} {if $group->role == 'admin'}<small>({str tag=Admin section=group})</small>{/if}</a></li>
        {/foreach}
               </ul>
                <span class="text-small text-midtone">{$sbdata.grouplimitstr}</span>
            </div>
        {/if}
        {if $sbdata.views}
            <div id="views" class="list-group-item">
                <div class="list-group-item-heading sidebar-item-heading"><a href="{$WWWROOT}view/">{str tag="views"}:</a></div>
                <ul class="list-nested list-group-item-text list-unstyled list-group-item-link">
        {foreach from=$sbdata.views item=view}
                    <li><a href="{$view->fullurl}">{$view->title}</a></li>
        {/foreach}
                </ul>
            </div>
        {/if}
        {if $sbdata.artefacts}
            <div class="artefacts list-group-item">
                <div class="list-group-item-heading sidebar-item-heading">{str tag="Artefacts"}:</div>
                <ul class="list-nested list-group-item-text list-unstyled list-group-item-link">
        {foreach from=$sbdata.artefacts item=artefact}
        {if $artefact->artefacttype == 'blog'}
                    <li><a href="{$WWWROOT}artefact/blog/view/index.php?id={$artefact->id}">{$artefact->title}</a></li>
        {elseif $artefact->artefacttype == 'blogpost'}
                    <li><a href="{$WWWROOT}artefact/blog/view/index.php?id={$artefact->blogid}">{$artefact->title}</a></li>
        {elseif $artefact->artefacttype == 'file' || $artefact->artefacttype == 'image' || $artefact->artefacttype == 'profileicon' || $artefact->artefacttype == 'archive'}
                    <li><a href="{$WWWROOT}artefact/file/download.php?file={$artefact->id}">{$artefact->title}</a></li>
        {elseif $artefact->artefacttype == 'folder'}
                    <li><a href="{$WWWROOT}artefact/file/index.php?folder={$artefact->id}">{$artefact->title}</a></li>
        {/if}
        {/foreach}
                </ul>
            </div>
        {/if}
    </div>
    {if $sbdata.peer}
        <div id="sbdatapeer"><a href="{$sbdata.peer.wwwroot}" class="panel-body">{$sbdata.peer.name}</a></div>
    {/if}
    {if $USERMASQUERADING}
        <div id="changeuser" class="panel-footer">
            <a href="{$becomeyoulink}">
                <span class="icon icon-lg left icon-undo" role="presentation" aria-hidden="true"></span>
                <span>{$becomeyouagain}</span>
            </a>
        </div>
    {/if}
</div>
