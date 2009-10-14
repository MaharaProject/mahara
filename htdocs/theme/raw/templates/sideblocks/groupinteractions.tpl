    <h3>{str tag="groupinteractions" section="group"}</h3>

    <div class="sidebar-content">
    {if $data}
    <ul>
    {foreach from=$sbdata.interactiontypes item=interactions key=plugin}
        <li>
        {if $sbdata.membership}
            <a href="{$WWWROOT}interaction/{$plugin}/index.php?group={$sbdata.group}">{str tag=nameplural section='interaction.$plugin}</a>
        {else}
            {str tag=nameplural section='interaction.$plugin}
        {/if}
        </li>
        {if $interactions}
            <ul>
            {foreach from=$interactions item=interaction}
                <li>
                {if $sbdata.membership}
                <a href="{$WWWROOT}interaction/{$interaction->plugin|escape}/view.php?id={$interaction->id|escape}">{$interaction->title|escape}</a>
                {else}
                {$interaction->title|escape}
                {/if}
                </li>
            {/foreach}
            </ul>
        {/if}
    {/foreach} 
    </ul>
    {else}
    <p>{str tag=nointeractions section=group}</p>
    {/if}
</div>
