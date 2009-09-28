    <h3><a href="{$WWWROOT}user/view.php?id={$data.id}">{$data.myname|escape}</a> <span class="s" id="profile-sideblock-username">(<a href="{$WWWROOT}user/view.php?id={$data.id}">{$data.username|escape}</a>)</span></h3>
    <div class="sideblock-content">
        <div id="user-profileicon"><a href="{$WWWROOT}user/view.php?id={$data.id}"><img src="{$WWWROOT}thumb.php?type=profileiconbyid&amp;maxwidth=50&amp;maxheight=50&amp;id={$data.profileicon}" alt="" width="50" height="50"></a></div>
{if $data.mnetloggedinfrom}        <p>{$data.mnetloggedinfrom}</p>
{/if}
        <ul>
{if $data.unreadnotifications}
            <li id="unreadmessages"><a class="btn-msg" href="{$WWWROOT}account/activity/">
                <span class="unreadmessagescontainer"><span class="unreadmessagecount">{$data.unreadnotifications}</span> <span class="unreadmessages">{$data.unreadnotificationsmessage}</span></span>
            </a></li>
{/if}
{if $data.invitedgroups}
            <li id="invitedgroups"><a href="{$WWWROOT}group/mygroups.php?filter=invite">
                <span id="invitedgroupscount">{$data.invitedgroups}</span>
                <span id="invitedgroupsmessage">{$data.invitedgroupsmessage}</span>
            </a></li>
{/if}
{if $data.pendingfriends}
            <li id="pendingfriends"><a href="{$WWWROOT}user/myfriends.php?filter=pending">
                <span id="pendingfriendscount">{$data.pendingfriends}</span>
                <span id="pendingfriendsmessage">{$data.pendingfriendsmessage}</span>
            </a></li>
{/if}
{if $data.groups}
            <li id="groups"><a href="{$WWWROOT}group/mygroups.php">{str tag="groups"}:</a>
                <ul>
{foreach from=$data.groups item=group}
                    <li><a href="{$WWWROOT}group/view.php?id={$group->id}">{$group->name|escape}</a>{if $group->role == 'admin'} ({str tag=Admin section=group}){/if}</li>
{/foreach}
                </ul></li>
{/if}
{if $data.views}
            <li id="views"><a href="{$WWWROOT}view/">{str tag="views"}:</a>
                <ul>
{foreach from=$data.views item=view}
                    <li><a href="{$WWWROOT}view/view.php?id={$view->id}">{$view->title|escape}</a></li>
{/foreach}
                </ul>
            </li>
{/if}
{if $data.artefacts}
            <li>
                {str tag="Artefacts"}:
                <ul>
{foreach from=$data.artefacts item=artefact}
{if $artefact->artefacttype == 'blog'}
                    <li><a href="{$WWWROOT}artefact/blog/view/?id={$artefact->id}">{$artefact->title|escape}</a></li>
{elseif $artefact->artefacttype == 'file' || $artefact->artefacttype == 'image' || $artefact->artefacttype == 'archive'}
                    <li><a href="{$WWWROOT}artefact/file/download.php?file={$artefact->id}">{$artefact->title|escape}</a></li>
{elseif $artefact->artefacttype == 'folder'}
                    <li><a href="{$WWWROOT}artefact/file/?folder={$artefact->id}">{$artefact->title|escape}</a></li>
{/if}
{/foreach}
                </ul>
            </li>
{/if}
        </ul>
        <div class="controls center">
            <a href="{$WWWROOT}?logout" class="btn-link" id="btn-logout">{str tag="logout"}</a>&nbsp;
            <a href="{$WWWROOT}account/" class="btn-link">{str tag="settings"}</a>
        </div>
{if $USERMASQUERADING}        <div id="changeuser">{$becomeyouagain}</div>
{/if}
        <div class="cb"></div>
    </div>
