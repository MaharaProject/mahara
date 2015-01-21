<div class="sidebar-header panel-heading">
    <h3 class="pull-left">{str tag="onlineusers" args=$sbdata.count}</h3>
    <p id="lastminutes" class="pull-right">({str tag="lastminutes" args=$sbdata.lastminutes})</p>
</div>
    <!-- <div class="sidebar-content panel-body"></div> -->
        <ul class="list-group">
{foreach from=$sbdata.users item=user}
            <li class="list-group-item">
                <a href="{profile_url($user)}">
                    <img src="{$user->profileiconurl}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="profile-icon-container"> {$user|display_name}
                </a>{if $user->loggedinfrom} ({$user->loggedinfrom}){/if}
            </li>
{/foreach}
            <li id="allonline" class="list-group-item online-users">
                <a href="{$WWWROOT}user/online.php">{str tag="allonline"}</a>
            </li>
        </ul>
    

