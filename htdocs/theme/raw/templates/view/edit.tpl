{include file="header.tpl"}

{include file="view/editviewtabs.tpl" selected='title' new=$new issiteview=$issiteview}

{if $ADMIN || $INSTITUTIONALADMIN}
<div class="row">
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                {$editview|safe}
            </div>
        </div>
    </div>
</div>
{else}
<div class="row">
    <div class="col-md-9">
    {$editview|safe}
    </div>
</div>
{/if}
{include file="footer.tpl"}
