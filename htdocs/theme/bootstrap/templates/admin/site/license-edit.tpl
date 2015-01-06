{include file="header.tpl"}
    <p>{str tag=sitelicensesdescription section=admin}</p>
    {if !$enabled}
    <p>{str tag=sitelicensesdisablednote section=admin args=$WWWROOT}</p>
    {/if}
    {$form|safe}
{include file="footer.tpl"}
