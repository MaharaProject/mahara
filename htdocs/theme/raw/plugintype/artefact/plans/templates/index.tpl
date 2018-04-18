{include file="header.tpl"}
<div class="btn-group btn-group-top">
    <a class="btn btn-secondary" href="{$WWWROOT}artefact/plans/new.php">
        <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
        {str section="artefact.plans" tag="newplan"}</a>
</div>
{if !$plans.data}
    <div class="no-results">{$strnoplansaddone|safe}</div>
{else}
    <div id="planswrap" class="view-container">
        <div id="planslist">
            {$plans.tablerows|safe}
        </div>
       {$plans.pagination|safe}
    </div>
{/if}
{include file="footer.tpl"}
