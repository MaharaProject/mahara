{include file="header.tpl"}
{if $cancreate}
<div class="text-right btn-top-right">
    <a href="{$WWWROOT}group/edit.php" class="btn btn-success creategroup">{str tag="creategroup" section="group"}</a>
</div>
{/if}
<div class="ptl pbl">
    {$form|safe}
</div>
{if $groups}
<div class="panel panel-default mtl">
    <h2 class="panel-heading">{str tag=Results}</h2>
    <div id="mygroups" class="listing panel-body">
        {foreach from=$groups item=group}
        <div class="listrow">
            {include file="group/group.tpl" group=$group returnto='mygroups'}
        </div>
        {/foreach}
    </div>
</div>
{$pagination|safe}
{else}
<div class="no-result">{str tag="trysearchingforgroups" section="group" args=$searchingforgroups}</div>
{/if}
{include file="footer.tpl"}
