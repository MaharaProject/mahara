    <div class="blockinstance" id="blockinstance_{$id}">
        <div class="blockinstance-header">
            <h4>{$title|default:"[$strnotitle]"|escape}</h4>
        </div>
        <div class="blockinstance-controls">
        {foreach from=$movecontrols item=item}
            {assign var=direction value=$item.dir}
            <input type="image" src="{theme_path location=images/move-block-$direction.png}" class="submit movebutton" name="action_moveblockinstance_id_{$id}_column_{$item.column}_order_{$item.order}" alt="{$item.arrow}" title="{$item.title}">
        {/foreach}
        {if $configurable}    <input type="image" src="{theme_path location=images/configure-block.png}" class="submit configurebutton" name="action_configureblockinstance_id_{$id}" alt="&bull;" title="{str tag='configureblock' section='view'}">{/if}
            <input type="image" src="{theme_path location=images/remove-block.png}" class="submit deletebutton" name="action_removeblockinstance_id_{$id}" alt="X" title="{str tag='removeblock' section='view'}">
        </div>
        <div class="blockinstance-content">
            {$content}
        </div>
    </div>
    {if !$javascript}
        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_addblocktype_column_{$column}_order_{$order+1}" value="{str tag='addnewblockhere' section='view'}">
        </div>
    {/if}
