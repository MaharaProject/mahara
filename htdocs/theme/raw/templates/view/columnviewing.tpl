<div id="row_{$row}_column_{$column}" class="column columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if}"{if $width} style="width: {$width-2}%;"{/if}>
    <div class="column-content">
    {$blockcontent|safe}
    </div>
</div>
