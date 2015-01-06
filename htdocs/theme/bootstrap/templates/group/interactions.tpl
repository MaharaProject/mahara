{include file="header.tpl"}
{include file="sidebar.tpl"}

                <h2>{$subheading}</h2>

    <ul>
    {foreach from=$data item=interactions key=plugin}
        <li><a href="{$WWWROOT}interaction/{$plugin}/index.php?group={$group->id}">{$pluginnames.$plugin.plural}</a></li>
        {if $interactions}
            <ul>
            {foreach from=$interactions item=interaction}
                <li>
                    <a href="{$WWWROOT}interaction/{$interaction->plugin}/view.php?id={$interaction->id}">{$interaction->title}</a> [
                    <a href="{$WWWROOT}interaction/edit.php?id={$interaction->id}" class="btn-edit">{str tag='edit'}</a> |
                    <a href="{$WWWROOT}interaction/delete.php?id={$interaction->id}" class="btn-del">{str tag='delete'}</a> ]
                </li>
            {/foreach}
                <li> [ <a href="{$WWWROOT}interaction/edit.php?group={$group->id}&plugin={$plugin}">{str tag='addnewinteraction' args=$pluginnames.$plugin.single section='group'}</a> ]
            </ul>
        {/if}
    {/foreach} 
    </ul>

{include file="footer.tpl"}
