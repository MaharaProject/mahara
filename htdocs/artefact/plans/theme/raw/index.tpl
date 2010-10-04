{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/plans/new.php">{str section="artefact.plans" tag="newplan"}</a>
    </div>
{if !$plans.data}
    <div class="message">{$strnoplansaddone|safe}</div>
{else}
<table id="planslist" class="fullwidth listing">
    <tbody>
        {$plans.tablerows|safe}
    </tbody>
</table>
   {$plans.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
