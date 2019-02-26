<div id="row_{$row}_column_{$column}" class="column column-layout columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if} {if $width}col-width-{$width}{/if}" {if $width}style="width:{if $width == 100}{$width }%;{else}{$width - 2}%;{/if}"{/if}>
{if $addremovecolumns}
        <div class="btn-header btn-group {if $column == 1}btn-three{else}btn-two{/if}">
        {if $column == 1}
            <button name="action_addcolumn_row_{$row}_before_1" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}" data-action="addcolumn" class="js-add-column-left addcolumn btn btn-secondary btn-sm text-left">
                <span class="icon-long-arrow-left icon-rotate-45 icon" role="presentation"></span>
                <span class="icon-plus icon" role="presentation"></span>
            </button>
        {/if}
            <button name="action_removecolumn_row_{$row}_column_{$column}" alt="{str tag='remove' section='view'}" title="{str tag='removecolumn' section='view'}" class="js-remove-column removecolumn text-center btn btn-secondary btn-sm">
                <span class="icon-times-circle icon-lg icon" role="presentation"></span>
            </button>
        {if $column == $numcolumns}
            <button name="action_addcolumn_row_{$row}_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}"  data-action="addcolumn" class="js-add-column-right addcolumn btn btn-secondary btn-sm text-right">
                 <span class="icon-plus icon" role="presentation"></span>
                 <span class="icon-long-arrow-right icon-rotate-45 icon" role="presentation"></span>
            </button>
        {else}
            <button name="action_addcolumn_row_{$row}_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}"  data-action="addcolumn" class="js-add-column-center addcolumn btn btn-secondary btn-sm text-right">
                 <span class="icon-plus icon" role="presentation"></span>
                 <span class="icon-long-arrow-right icon-rotate-45 icon" role="presentation"></span>
            </button>
        {/if}
        </div>
{else}
    <div class="column-header-empty"></div>
{/if}
    <div class="column-content">
    {$blockcontent|safe}
    </div>
</div>
