{include file="header.tpl"}
<div class="btn-group btn-group-top">
    <a class="btn btn-default" href="{$WWWROOT}artefact/plans/new.php">
        <span class="fa fa-plus fa-lg prs text-primary"></span>
        {str section="artefact.plans" tag="newplan"}</a>
</div>
{if !$plans.data}
    <div class="lead ptxl">{$strnoplansaddone|safe}</div>
{else}
    <div id="planswrap" class="ptxl">
        <div id="planslist" class="pbl">
            {$plans.tablerows|safe}
        </div>
       {$plans.pagination|safe}
    </div>
{/if}
{include file="footer.tpl"}
