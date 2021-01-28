{include file="header.tpl"}

{$form|safe}

{if $instructions}
<p class="lead view-description">
    {$instructions|clean_html|safe}
</p>
{/if}

<div class="memberswrap">

    {if $membershiptypes}
    <p class="membershiptypes">
        {foreach from=$membershiptypes item=item implode="&nbsp;|&nbsp;"}
        {if $item.link}
        <a href="{$item.link}">{$item.name}</a>
        {else}
        <strong>{$item.name}</strong>
        {/if}
        {/foreach}
    </p>
    {/if}

    <div class="card">
        {if $membershiptype}
        <h2 id="searchresultsheading" class="card-header">
            <span class="sr-only">{str tag=Results}: </span>
            {str tag=pendingmembers section=group}
        </h2>
        {else}
        <h2 id="searchresultsheading" class="card-header">
            {str tag=Results}
        </h2>
        {/if}

        <div id="membersearchresults" class="list-group">
            {$results|safe}
        </div>
    </div>

    {$pagination|safe}

</div>

{include file="footer.tpl"}
