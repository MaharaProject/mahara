{include file="header.tpl"}
{if $cancreate}
    <div class="btn-top-right btn-group btn-group-top">
        <a href="{$WWWROOT}group/edit.php" class="btn btn-secondary creategroup">
            <span class="icon icon-lg icon-plus left" role="presentation" aria-hidden="true"></span>
            {str tag="creategroup" section="group"}
        </a>
    </div>
{/if}
{$form|safe}
{if $groups}
    <div class="card view-container">
        <h2 class="card-header">{str tag=Results}</h2>
        <div id="findgroups" class="list-group">
            {$groupresults|safe}
        </div>
    </div>
    {$pagination|safe}
    {if $pagination_js}
        <script>
        {$pagination_js|safe}
        </script>
    {/if}
{else}
    <p class="no-results">
        {str tag="nogroupsfound" section="group"}
        {str tag="trysearchingforgroups1" section="group" arg1=$WWWROOT}
    </p>
{/if}
{include file="footer.tpl"}
