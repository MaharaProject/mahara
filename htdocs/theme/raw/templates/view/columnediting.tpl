<div id="row_{$row}_column_{$column}" class="column column-layout columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if} {if $width}col-width-{$width}{/if}" {if $width}style="width:{if $width == 100}{$width }%;{else}{$width - 2}%;{/if}"{/if}>
    <div class="column-header-empty"></div>
    <div class="column-content">
    {$blockcontent|safe}
    </div>
</div>
