{include file="header.tpl"}
<div class="ptl pbl">
{$form|safe}
</div>
{if $groups}
<div class="panel panel-default mtl">
    <h2 class="panel-heading">{str tag=Results}</h2>
    <div id="findgroups" class="panel-body listing">
        {foreach from=$groups item=group}
        <div class="listrow">
            {include file="group/group.tpl" group=$group returnto='mygroups'}
        </div>
        {/foreach}
    </div>
</div>
{$pagination|safe}
{else}
<div class="no-result">
    {str tag="nogroupsfound" section="group"}
</div>
{/if}
{include file="footer.tpl"}
