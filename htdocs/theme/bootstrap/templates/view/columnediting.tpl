<div id="row_{$row}_column_{$column}" class="column column-layout columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if}" {if $width}style="width:{if $width == 100} {$width }%;{elseif $width == 33}31.3%;{else}{$width - 2}%;{/if}"{/if}>
{if $addremovecolumns}
        <div class="btn-header btn-group mbl {if $column == 1}btn-three{else}btn-two{/if}">
        {if $column == 1}
            <button name="action_addcolumn_row_{$row}_before_1" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}" data-action="addcolumn" class="js-add-column-left addcolumn btn btn-success btn-sm text-left">
                <span class="mrs fa-long-arrow-down fa-rotate-45 fa"></span>
                <span class="fa-plus fa "></span>
            </button>
        {/if}
            <button name="action_removecolumn_row_{$row}_column_{$column}" alt="{str tag='remove' section='view'}" title="{str tag='removecolumn' section='view'}" class="js-remove-column removecolumn text-center btn btn-primary btn-sm">
                <span class="fa-times-circle fa"></span>
            </button>
        {if $column == $numcolumns}
            <button name="action_addcolumn_row_{$row}_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}"  data-action="addcolumn" class="js-add-column-right addcolumn btn btn-success btn-sm text-right">
                 <span class="fa-plus fa"></span>
                 <span class="fa-long-arrow-right fa-rotate-45 fa"></span>
            </button>
        {else}
            <button name="action_addcolumn_row_{$row}_before_{$column+1}" alt="{str tag='add' section='view'}" title="{str tag='addcolumn' section='view'}"  data-action="addcolumn" class="js-add-column-center addcolumn btn btn-success btn-sm text-right">
                 <span class="fa-plus fa"></span>
                 <span class="fa-long-arrow-right fa-rotate-45 fa"></span>
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
