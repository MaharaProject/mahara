{include file="header.tpl"}
{if $cancreate}
<div class="text-right btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}group/edit.php" class="btn btn-default creategroup">
        <span class="fa fa-lg fa-plus prd text-success"></span> 
        {str tag="creategroup" section="group"}
    </a>
</div>
{/if}
<div class="ptl pbl">
    {$form|safe}
</div>
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
<p class="no-result lead text-center">
    {str tag="trysearchingforgroups" section="group" args=$searchingforgroups}
</p>
{/if}
{include file="footer.tpl"}
