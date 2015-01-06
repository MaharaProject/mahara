<div class="sidebar-header">
    <h3>{str tag="onlineusers" args=$sbdata.count}</h3>
    <p id="lastminutes">({str tag="lastminutes" args=$sbdata.lastminutes})</p>
</div>
    <div class="sidebar-content">
        <ul class="cr">
{foreach from=$sbdata.users item=user}
            <li><a href="{profile_url($user)}"><img src="{$user->profileiconurl}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="profile-icon-container"> {$user|display_name}</a>{if $user->loggedinfrom} ({$user->loggedinfrom}){/if}</li>
{/foreach}
        </ul>
    <p id="allonline"><a href="{$WWWROOT}user/online.php">{str tag="allonline"}</a></p>
    </div>
