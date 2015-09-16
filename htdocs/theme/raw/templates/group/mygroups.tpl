{include file="header.tpl"}
{if $cancreate}
<div class="text-right btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}group/edit.php" class="btn btn-default creategroup">
        <span class="icon icon-lg icon-plus prd"></span>
        {str tag="creategroup" section="group"}
    </a>
</div>
{/if}
{$form|safe}
{if $groups}
<div class="panel panel-default mtl">
    <h2 class="panel-heading">{str tag=Results}</h2>
    <div id="mygroups" class="list-group">
        {foreach from=$groups item=group}
            {include file="group/group.tpl" group=$group returnto='mygroups'}
        {/foreach}
    </div>
</div>
{$pagination|safe}
{else}
<div class="mtxl ptxl">
    <p class="no-result ptxl lead text-center">
        {str tag="trysearchingforgroups" section="group" args=$searchingforgroups}
    </p>
</div>
{/if}
{include file="footer.tpl"}
