{include file="header.tpl"}
<div class="row">
    <div class="col-lg-9">
        {if $noinstitutionsadmin}<p class="lead">{$noinstitutionsadmin|safe}</p>{/if}
        {if $noinstitutions}
            <p class="lead">{$noinstitutions}</p>
        {else}
            <p class="lead">{str tag=staticpagespagedescription section=admin}</p>
        {/if}
    </div>
    {if $pageeditform}
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                {$pageeditform|safe}
            </div>
        </div>
    </div>
    {/if}
</div>
{include file="footer.tpl"}
