    <div class="sidebar-header"><p id="lastminutes">({str tag="lastminutes" args=$sbdata.lastminutes})</p>
    <h3>{str tag="onlineusers" args=$sbdata.count}</h3></div>
    <div class="sidebar-content">
        <ul class="cr">
{foreach from=$sbdata.users item=user}
            <li><a href="{profile_url($user)}"><img src="{$user->profileiconurl}" alt="" class="profile-icon-container"> {$user|display_name}</a>{if $user->loggedinfrom} ({$user->loggedinfrom}){/if}</li>
{/foreach}
        </ul>
    <p id="allonline"><a href="{$WWWROOT}user/online.php">{str tag="allonline"}</a></p>
    </div>

