{include file="header.tpl"}
{if $GROUP}
    <h2>{$PAGESUBHEADING}{if $SUBPAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h2>
{/if}
{if $ADMIN || $INSTITUTIONALADMIN}
<div class="row">
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                {$form|safe}
            </div>
        </div>
    </div>
</div>
{else}
{$form|safe}
{/if}
{include file="footer.tpl"}
