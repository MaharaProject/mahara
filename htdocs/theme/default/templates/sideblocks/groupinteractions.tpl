    <h3>{str tag="groupinteractions" section="group"}</h3>

    {if $data}
    <ul>
    {foreach from=$data item=interaction}
        <li><a href="{$WWWROOT}interaction/{$interaction.plugin|escape}/?id={$interaction.id|escape}">{$interaction.name|escape}</a></li>
    {/foreach}
    </ul>
    {else}
    <p>{str tag=nointeractions section=group}</p>
    {/if}
