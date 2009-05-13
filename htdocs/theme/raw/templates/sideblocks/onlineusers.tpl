    <p id="lastminutes">({str tag="lastminutes" args=$data.lastminutes})</p>
    <h3>{str tag="onlineusers" args=$data.count}</h3>
    <ul id="onlineusers" class="cr">
    {foreach from=$data.users item=user}
        <li>
            <a href="{$WWWROOT}user/view.php?id={$user->id|escape}"><img src="{$user->profileiconurl|escape}" alt=""> {$user|display_name|escape}</a>
            {if $user->loggedinfrom} ({$user->loggedinfrom|escape}){/if}
        </li>
    {/foreach}
    </ul>
