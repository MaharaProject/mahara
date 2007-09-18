    <div class="blockinstance" id="blockinstance_{$id}">
        <div class="blockinstance-header">
            <h4>{$title | escape}</h4>
        </div>
        <div class="blockinstance-controls">
        {foreach from=$movecontrols item=item}
            <input type="submit" class="submit movebutton" name="action_moveblockinstance_id_{$id}_column_{$item.column}_order_{$item.order}" value="{$item.arrow}">
        {/foreach}
            <input type="submit" class="submit deletebutton" name="action_removeblockinstance_id_{$id}" value="X">
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
