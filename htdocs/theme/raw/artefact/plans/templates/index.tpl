{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/plans/new.php">{str section="artefact.plans" tag="newplan"}</a>
    </div>
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
