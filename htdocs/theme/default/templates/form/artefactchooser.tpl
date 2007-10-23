{if $artefacts}
<table id="{$datatable}">
    <tbody>
        {$artefacts}
    </tbody>
</table>
{$pagination}
{else}
<p>Sorry, no artefacts to choose from</p>
{/if}
