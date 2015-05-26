<div id="row_{$row}_column_{$column}" class="column-layout columns{$numcolumns}{if $column == 1} first{/if}{if $column == $numcolumns} last{/if}" {if $width}style="width:{if $width == 100} {$width}%;{elseif $width == 33}31.3%;{else}{$width - 2}%;{/if}"{/if}>
    <div class="column-content">
        {$blockcontent|safe}
    </div>
</div>
