    <div class="sidebar-header"><h3><a href="{$WWWROOT}user/view.php?id={$sbdata.id}">{$sbdata.myname}</a></h3></div>
    <div class="sidebar-content"><div id="user-profileicon"><a href="{$WWWROOT}user/view.php?id={$sbdata.id}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxwidth=50&amp;maxheight=50&amp;id={$sbdata.id}&amp;earlyexpiry=1" alt=""></a></div>
{if $sbdata.mnetloggedinfrom}        <p>{$sbdata.mnetloggedinfrom|clean_html|safe}</p>
{/if}
        <ul>
{if $sbdata.unreadnotifications}
            <li id="unreadmessages"><a class="btn-message" href="{$WWWROOT}account/activity/">
                <span class="unreadmessagescontainer"><span class="unreadmessagecount">{$sbdata.unreadnotifications}</span> 
                <span class="unreadmessages">{$sbdata.unreadnotificationsmessage}</span></span>
            </a></li>
{/if}
{if $sbdata.invitedgroups}
            <li id="invitedgroups"><a href="{$WWWROOT}group/mygroups.php?filter=invite">
                <span id="invitedgroupscount">{$sbdata.invitedgroups}</span>
                <span id="invitedgroupsmessage">{$sbdata.invitedgroupsmessage}</span>
            </a></li>
{/if}
{if $sbdata.pendingfriends}
            <li id="pendingfriends"><a href="{$WWWROOT}user/myfriends.php?filter=pending" class="btn-friend">
                <span id="pendingfriendscount">{$sbdata.pendingfriends}</span>
                <span id="pendingfriendsmessage">{$sbdata.pendingfriendsmessage}</span>
            </a></li>
{/if}
{if $sbdata.groups}
            <li id="groups"><label><a href="{$WWWROOT}group/mygroups.php">{str tag="mygroups"}:</a></label>
                <ul>
{foreach from=$sbdata.groups item=group}
                    <li><a href="{$WWWROOT}group/view.php?id={$group->id}">{$group->name}</a>{if $group->role == 'admin'} ({str tag=Admin section=group}){/if}</li>
{/foreach}
                </ul></li>
{/if}
{if $sbdata.views}
            <li id="views"><label><a href="{$WWWROOT}view/">{str tag="views"}:</a></label>
                <ul>
{foreach from=$sbdata.views item=view}
                    <li><a href="{$WWWROOT}view/view.php?id={$view->id}">{$view->title}</a></li>
{/foreach}
                </ul>
            </li>
{/if}
{if $sbdata.artefacts}
            <li class="artefacts">
                <label>{str tag="Artefacts"}:</label>
                <ul>
{foreach from=$sbdata.artefacts item=artefact}
{if $artefact->artefacttype == 'blog'}
                    <li><a href="{$WWWROOT}artefact/blog/view/?id={$artefact->id}">{$artefact->title}</a></li>
{elseif $artefact->artefacttype == 'file' || $artefact->artefacttype == 'image' || $artefact->artefacttype == 'archive'}
                    <li><a href="{$WWWROOT}artefact/file/download.php?file={$artefact->id}">{$artefact->title}</a></li>
{elseif $artefact->artefacttype == 'folder'}
                    <li><a href="{$WWWROOT}artefact/file/?folder={$artefact->id}">{$artefact->title}</a></li>
{/if}
{/foreach}
                </ul>
            </li>
{/if}
        </ul>
{if $sbdata.peer}                <div class="center"><a href="{$sbdata.peer.wwwroot}">{$sbdata.peer.name}</a></div>
{/if}
{if $USERMASQUERADING}        <div id="changeuser">{$becomeyouagain|safe}</div>
{/if}
        <div class="cb"></div>
    </div>
