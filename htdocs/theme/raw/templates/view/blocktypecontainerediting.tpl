    <div class="blockinstance cb{if $configure} configure{/if}" id="blockinstance_{$id}{if $configure}_configure{/if}">
        <div class="blockinstance-header">
            <h4>{if $configure}{$configtitle}: {str tag=Configure section=view}{else}{$title|default:"[$strnotitle]"}{/if}</h4>
        </div>
        <div class="blockinstance-controls">
        {foreach from=$movecontrols item=item}
            <input type="image" src="{theme_url filename='images/move-block-`$item.dir`.png'}" class="movebutton" name="action_moveblockinstance_id_{$id}_column_{$item.column}_order_{$item.order}" alt="{$item.arrow}" title="{$item.title}">
        {/foreach}
        {if $configurable && !$configure}    <input type="image" src="{theme_url filename=images/configure-block.png}" class="configurebutton" name="action_configureblockinstance_id_{$id}" alt="&bull;" title="{$strconfigtitletext}">{/if}
            <input type="image" src="{theme_url filename=images/remove-block.png}" class="deletebutton" name="action_removeblockinstance_id_{$id}" alt="X" title="{$strremovetitletext}">
        </div>
        <div class="blockinstance-content">
            {$content|safe}
        </div>
    </div>
    {if !$javascript && !$configure}
        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_addblocktype_column_{$column}_order_{$order+1}" value="{str tag='addnewblockhere' section='view'}">
        </div>
    {/if}
