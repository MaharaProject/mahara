{include file="header.tpl"}
{if $canedit}
    <div class="btn-group btn-group-top">
        <a class="btn btn-secondary" href="{$newPlanLink}">
            <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            {str section="artefact.plans" tag="newplan"}</a>
    </div>
{/if}
{if !$plans.data}
    <div class="no-results">{$strnoplans|safe}</div>
{else}
    <div id="planswrap" class="view-container">
        <div id="planslist">
            {$plans.tablerows|safe}
        </div>
       {$plans.pagination|safe}
    </div>
{/if}
{include file="footer.tpl"}
