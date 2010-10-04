<div id="column_{$column}" class="column columns{$numcolumns}"{if $width} style="width: {$width-2}%;"{/if}>
{if $addremovecolumns}
    <div class="column-header">
    {if $column == 1}
        <div class="add-column-left">
            <input type="image" src="{theme_url filename=images/btn_addcolumn_left.png}" class="addcolumn" name="action_addcolumn_before_1" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}">
        </div>
    {/if}
        <div class="remove-column">
            <input type="image" src="{theme_url filename=images/btn_removecolumn.png}" class="removecolumn" name="action_removecolumn_column_{$column}" alt="{str tag='remove' section='view'}" title="{str tag='removecolumn' section='view'}">
        </div>
    {if $column == $numcolumns}
        <div class="add-column-right">
            <input type="image" src="{theme_url filename=images/btn_addcolumn_right.png}" class="addcolumn" name="action_addcolumn_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}">
        </div>
    {else}
        <div class="add-column-center">
            <input type="image" src="{theme_url filename=images/btn_addcolumn_right.png}" class="addcolumn" name="action_addcolumn_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}">
        </div>
    {/if}
    </div>
{else}
    <div class="column-header-empty"></div>
{/if}
    <div class="column-content">
    {if !$javascript}
        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_addblocktype_column_{$column}_order_1" value="{str tag='addnewblockhere' section='view'}">
        </div>
    {/if} 
    {$blockcontent|safe}
    </div>
</div>
