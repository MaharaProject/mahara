    <div class="sidebar-header"><h3>{str tag="groupinteractions" section="group"}</h3></div>
    <div class="sidebar-content">
    {if $sbdata}
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
                <a href="{$WWWROOT}interaction/{$interaction->plugin}/view.php?id={$interaction->id}">{$interaction->title}</a>
                {else}
                {$interaction->title}
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