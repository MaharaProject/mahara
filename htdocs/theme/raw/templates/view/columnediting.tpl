<div id="row_{$row}_column_{$column}" class="column columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if}"{if $width} style="width: {$width-2}%;"{/if}>
{if $addremovecolumns}
    <div class="column-header">
    {if $column == 1}
        <div class="add-column-left">
            <input type="image" src="{theme_url filename=images/btn_addcolumnleft.png}" class="addcolumn" name="action_addcolumn_row_{$row}_before_1" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}">
        </div>
    {/if}
        <div class="remove-column">
            <input type="image" src="{theme_url filename=images/btn_removecolumn.png}" class="removecolumn" name="action_removecolumn_row_{$row}_column_{$column}" alt="{str tag='remove' section='view'}" title="{str tag='removecolumn' section='view'}">
        </div>
    {if $column == $numcolumns}
        <div class="add-column-right">
            <input type="image" src="{theme_url filename=images/btn_addcolumnright.png}" class="addcolumn" name="action_addcolumn_row_{$row}_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}">
        </div>
    {else}
        <div class="add-column-center">
            <input type="image" src="{theme_url filename=images/btn_addcolumnright.png}" class="addcolumn" name="action_addcolumn_row_{$row}_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}">
        </div>
    {/if}
    </div>
{else}
    <div class="column-header-empty"></div>
{/if}
    <div class="column-content">
    {if !$javascript}
        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_addblocktype_row_{$row}_column_{$column}_order_1" value="{str tag='addnewblockhere' section='view'}">
        </div>
    {/if} 
    {$blockcontent|safe}
    </div>
</div>
