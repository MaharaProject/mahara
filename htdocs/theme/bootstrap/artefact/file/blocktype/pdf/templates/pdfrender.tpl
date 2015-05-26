<div class="panel-body">
{$html|safe}
</div>
{if $commentcount || $commentcount === '0'}
{$comments|safe}
{/if}
