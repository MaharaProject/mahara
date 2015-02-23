{include file="header.tpl"}
<div class="text-right btn-top-right">
    <a class="btn btn-success" href="{$WWWROOT}artefact/plans/new.php">{str section="artefact.plans" tag="newplan"}</a>
</div>
<div id="planswrap" class="plan-wrapper">
{if !$plans.data}
    <div class="message">{$strnoplansaddone|safe}</div>
{else}
<div id="planslist" class="fullwidth listing">
        {$plans.tablerows|safe}
</div>
   {$plans.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
