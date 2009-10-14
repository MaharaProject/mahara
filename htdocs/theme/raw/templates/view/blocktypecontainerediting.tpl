    <div class="blockinstance{if $configure} configure{/if}" id="blockinstance_{$id}{if $configure}_configure{/if}">
        <div class="blockinstance-header">
            <h4>{$title|default:"[$strnotitle]"|escape}{if $configure}: {str tag=Configure section=view}{/if}</h4>
        </div>
        <div class="blockinstance-controls">
        {foreach from=$movecontrols item=item}
            <input type="image" src="{theme_url filename='images/move-block-`$item.dir`.png'}" class="submit movebutton" name="action_moveblockinstance_id_{$id}_column_{$item.column}_order_{$item.order}" alt="{$item.arrow}" title="{$item.title}">
        {/foreach}
        {if $configurable && !$configure}    <input type="image" src="{theme_url filename=images/configure-block.png}" class="submit configurebutton" name="action_configureblockinstance_id_{$id}" alt="&bull;" title="{$strconfigtitletext|escape}">{/if}
            <input type="image" src="{theme_url filename=images/remove-block.png}" class="cancel deletebutton" name="action_removeblockinstance_id_{$id}" alt="X" title="{$strremovetitletext|escape}">
        </div>
        <div class="blockinstance-content">
            {$content}
        </div>
    </div>
    {if !$javascript && !$configure}
        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_addblocktype_column_{$column}_order_{$order+1}" value="{str tag='addnewblockhere' section='view'}">
        </div>
    {/if}
