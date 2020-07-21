{include file='header.tpl'}

<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                {str tag=embeddedurlsdescription section=admin}
            </div>
        </div>

        {if $grandtotal}
        <div class="card">
            <h2 class="card-header">{str tag=potentialembeddedurls section=admin}</h2>
            <div class="card-body">
                {$checkform|safe}
            </div>
            <table class="table">
            {if $checkurlraw}
            <tr><th colspan="2">{str tag=potentialfor section=admin arg1=$checkurlraw}</th></tr>
            {/if}
                {foreach from=$potentialembeddedurls key=key item=item}
                <tr><td>{str tag="$item->type" section=admin}</td><td>{$item->count}</td></tr>
                {/foreach}
            </table>
            {if $checkurlraw}
            <div class="card-body">
                {$migrateform|safe}
            </div>
            {/if}
        </div>
        {else}
            {str tag=nopotentialembeddedurls section=admin}
        {/if}
    </div>
</div>
{include file='footer.tpl'}
