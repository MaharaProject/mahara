{include file="header.tpl"}

{$form|safe}

{if $instructions}
<p class="lead ptl pbm">
    {$instructions|clean_html|safe}
</p>
{/if}

<div class="memberswrap">

    {if $membershiptypes}
    <div class="membershiptypes pbl">
        {foreach from=$membershiptypes item=item implode="&nbsp;|&nbsp;"}
        {if $item.link}
        <a href="{$item.link}">{$item.name}</a>
        {else}
        <strong>{$item.name}</strong>
        {/if}
        {/foreach}
    </div>
    {/if}

    <div class="panel panel-default mtl">
        {if $membershiptype}
        <h2 id="searchresultsheading" class="panel-heading">
            <span class="sr-only">{str tag=Results}: </span>
            {str tag=pendingmembers section=group}
        </h2>
        {else}
        <h2 id="searchresultsheading" class="panel-heading">
            {str tag=Results}
        </h2>
        {/if}

        <div id="results" class="list-group">
            {$results|safe}
        </div>
    </div>

    {$pagination|safe}

</div>

{include file="footer.tpl"}
