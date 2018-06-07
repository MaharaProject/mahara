{include file="header.tpl"}
{if $ADMIN || $INSTITUTIONALADMIN}
<div class="row">
    <div class="col-md-9">
        <div class="card card-secondary">
            <div class="card-body">
                {$form|safe}
            </div>
        </div>
    </div>
</div>
{else}
{$form|safe}
{/if}
{include file="footer.tpl"}
