<div id="row_{$row}_column_{$column}" class="column columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if} col-md-6">
    <div class="column-content">
    {$blockcontent|safe}
    </div>
</div>
