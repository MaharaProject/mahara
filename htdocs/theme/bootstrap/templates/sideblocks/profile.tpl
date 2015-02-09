    <div class="sidebar-header panel-heading profile-block">
      <h3 class="pull-left"><a href="{$sbdata.url}">{$sbdata.myname}</a></h3>
      <div id="user-profileicon" class="pull-right">
        <a href="{$sbdata.profileiconurl}" title="{str tag="editprofileicon" section="artefact.file"}"><img src="{profile_icon_url user=$sbdata.id maxheight=50 maxwidth=50}" alt="{str tag="editprofileicon" section="artefact.file"}"></a>
      </div>
    </div>
{if $sbdata.mnetloggedinfrom}
    <div class="sidebar-content panel-body">
<p>{$sbdata.mnetloggedinfrom|clean_html|safe}</p>
    </div>
{/if}
{if $sbdata.invitedgroups}
            <div id="invitedgroups" class="list-group-item"><a href="{$WWWROOT}group/mygroups.php?filter=invite" class="btn-group">
                <span id="invitedgroupsmessage">{$sbdata.invitedgroupsmessage}</span>
                <span id="invitedgroupscount">{$sbdata.invitedgroups}</span>
            </a></div>
{/if}
{if $sbdata.pendingfriends}
            <div id="pendingfriends" class="list-group-item"><a href="{$WWWROOT}user/myfriends.php?filter=pending" class="btn-friend">
                <span id="pendingfriendscount" class="badge pull-right">{$sbdata.pendingfriends}</span>
                <span id="pendingfriendsmessage">{$sbdata.pendingfriendsmessage}</span>
            </a></div>
{/if}
{if $sbdata.groups}
            <div id="groups" class="list-group-item">
                <div class="list-group-heading sidebar-item-heading pbs"><a href="{$WWWROOT}group/mygroups.php">{str tag="mygroups"}:</a></div>
                <ul class="list-group-item-text list-unstyled list-group-item-link">
{foreach from=$sbdata.groups item=group}
                    <li><a href="{group_homepage_url($group)}" class="bordered">{$group->name} {if $group->role == 'admin'}<small>({str tag=Admin section=group})</small>{/if}</a></li>
{/foreach}
                </ul>
                <span class="tiny">{$sbdata.grouplimitstr}</span>
            </div>
{/if}
{if $sbdata.views}
            <div id="views" class="list-group-item">
                <div class="list-group-heading sidebar-item-heading pbs"><a href="{$WWWROOT}view/">{str tag="views"}:</a></div>
                <ul class="list-group-item-text list-unstyled list-group-item-link">
{foreach from=$sbdata.views item=view}
                    <li><a href="{$view->fullurl}">{$view->title}</a></li>
{/foreach}
                </ul>
            </div>
{/if}
{if $sbdata.artefacts}
            <div class="artefacts list-group-item">
                <div class="list-group-heading sidebar-item-heading pbs">{str tag="Artefacts"}:</div>
                <ul class="list-group-item-text list-unstyled list-group-item-link">
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
       
{if $sbdata.peer}                <div id="sbdatapeer"><a href="{$sbdata.peer.wwwroot}">{$sbdata.peer.name}</a></div>
{/if}
{if $USERMASQUERADING}        <div id="changeuser">{$becomeyouagain|safe}</div>
{/if}

