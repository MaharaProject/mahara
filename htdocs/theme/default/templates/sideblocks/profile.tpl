    <h3><a style="color:white" href="{$WWWROOT}user/view.php?id={$data.id}">{$data.id|display_name|escape}</a></h3>
    <div class="fr"><a href="{$WWWROOT}user/view.php?id={$data.id}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=50&amp;id={$data.id}" alt=""></a></div>
    <ul>
        {if $data.unreadnotifications}
        <li>
        <a href="{$WWWROOT}account/activity/">
            <span class="unreadmessagescontainer"><span class="unreadmessagecount">{$data.unreadnotifications}</span> <span class="unreadmessages">{$data.unreadnotificationsmessage}</span></span>
        </a>
        </li>
        {/if}
        {if $data.invitedgroups}
        <li>
            <a href="{$WWWROOT}group/mygroups.php?filter=invite">
                <span id="invitedgroupscount">{$data.invitedgroups}</span>
                <span id="invitedgroupsmessage">{$data.invitedgroupsmessage}</span>
            </a>
        </li>
        {/if}
        {if $data.pendingfriends}
        <li>
            <a href="{$WWWROOT}user/">
                <span id="pendingfriendscount">{$data.pendingfriends}</span>
                <span id="pendingfriendsmessage">{$data.pendingfriendsmessage}</span>
            </a>
        </li>
        {/if}
        {if $data.views}
        <li>
            <a href="{$WWWROOT}view/">{str tag="views"}:</a>
            <ul>
            {foreach from=$data.views item=view}
                <li><a href="{$WWWROOT}view/view.php?id={$view->id}">{$view->title|escape}</a></li>
            {/foreach}
            </ul>
        </li>
        {/if}
        {if $data.artefacts}
        <li>
            <a href="{$WWWROOT}artefact/file/">{str tag="artefacts"}:</a>
            <ul>
            {foreach from=$data.artefacts item=artefact}
                {if $artefact->artefacttype == 'blog'}
                    <li><a href="{$WWWROOT}artefact/blog/view/?id={$artefact->id}">{$artefact->title|escape}</a></li>
                {elseif $artefact->artefacttype == 'file' || $artefact->artefacttype == 'image'}
                    <li><a href="{$WWWROOT}artefact/file/download.php?file={$artefact->id}">{$artefact->title|escape}</a></li>
                {elseif $artefact->artefacttype == 'folder'}
                    <li><a href="{$WWWROOT}artefact/file/?folder={$artefact->id}">{$artefact->title|escape}</a></li>
                {/if}
            {/foreach}
            </ul>
        </li>
        {/if}
    </ul>
    <div class="center">
    <a href="{$WWWROOT}?logout">{str tag="logout"}</a>
    | <a href="{$WWWROOT}account/">{str tag="settings"}</a>
    </div>
