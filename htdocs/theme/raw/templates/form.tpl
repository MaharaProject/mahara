{include file="header.tpl"}
{if $pagedescription}
  <p class="lead">{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
{if $ADMIN || $INSTITUTIONALADMIN}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                {$form|safe}
            </div>
        </div>
    </div>
</div>
{else}
{$form|safe}
{/if}

{include file="pagemodal.tpl"}
{include file="footer.tpl"}