    <h3>{str tag="groupinteractions" section="group"}</h3>

    {if $data}
    <ul>
    {foreach from=$data item=interactions key=plugin}
        <li><a href="{$WWWROOT}interaction/{$plugin}/index.php?group={$group->id}">{str tag=nameplural section='interaction.$plugin}</a></li>
        {if $interactions}
            <ul>
            {foreach from=$interactions item=interaction}
                <li><a href="{$WWWROOT}interaction/{$interaction->plugin|escape}/view.php?id={$interaction->id|escape}">{$interaction->title|escape}</a></li>
            {/foreach}
            </ul>
        {/if}
    {/foreach} 
    </ul>
    {else}
    <p>{str tag=nointeractions section=group}</p>
    {/if}
