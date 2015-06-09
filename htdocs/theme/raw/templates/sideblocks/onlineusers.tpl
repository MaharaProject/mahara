<div class="panel panel-default">
    <h3 class="panel-heading">
        {str tag="onlineusers" args=$sbdata.count}
        <span id="lastminutes" class="pull-right metadata">({str tag="lastminutes" args=$sbdata.lastminutes})</span>
    </h3>
    <ul class="list-group">
        {foreach from=$sbdata.users item=user}
            <li class="list-group-item list-unstyled list-group-item-link">
                <a href="{profile_url($user)}" class="">
                    <span class="user-icon">
                        <img src="{$user->profileiconurl}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="profile-icon-container">
                    </span>
                    {$user|display_name}
                </a>{if $user->loggedinfrom} ({$user->loggedinfrom}){/if}
            </li>
        {/foreach}
    </ul>
    <a href="{$WWWROOT}user/online.php" class="online-users panel-footer" id="allonline">
        {str tag="allonline"}
        <span class="icon icon-arrow-circle-right mls  pull-right"></span>
    </a>
</div>


