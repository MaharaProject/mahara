<div class="card">
    <h2 class="card-header">
        {str tag="peopleonline" args=$sbdata.count}
        <br>
        <span id="lastminutes" class="text-small text-midtone">({str tag="lastminutes" args=$sbdata.lastminutes})</span>
    </h2>
    <ul class="list-group">
        {foreach from=$sbdata.users item=user}
            <li class="list-unstyled list-group-item-link list-group-item">
                <a href="{profile_url($user)}" class="online-user">
                    <span class="user-icon user-icon-20">
                        <img src="{$user->profileiconurl}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="profile-icon-container">
                    </span>
                    <span class="user-name text-small">
                        {$user|display_name}
                        {if $user->loggedinfrom} ({$user->loggedinfrom}){/if}
                    </span>
                </a>
            </li>
        {/foreach}
    </ul>
    <a href="{$WWWROOT}user/online.php" class="online-users card-footer text-small" id="allonline">
        {str tag="allpeopleonline"}
        <span class="icon icon-arrow-circle-right float-right" role="presentation" aria-hidden="true"></span>
    </a>
</div>
