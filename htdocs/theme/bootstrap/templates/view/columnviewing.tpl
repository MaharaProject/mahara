<div id="row_{$row}_column_{$column}" class="column columns{$numcolumns}{if $column == 1} firstcolumn{/if}{if $column == $numcolumns} lastcolumn{/if} {if $numcolumns != 1} col-md-6{/if}">
    <div class="column-content">
        <!-- not everything needs masonry -->
        <div class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
        {$blockcontent|safe}
        </div>
    </div>
</div>
