{include file="header.tpl"}

    <div>{$instructions|clean_html|safe}</div>
    <div class="memberssearch">
    {if $membershiptypes}
    	<div class="membershiptypes">
        {foreach from=$membershiptypes item=item implode="&nbsp;|&nbsp;"}
            {if $item.link}
               <strong><a href="{$item.link}">{$item.name}</a></strong>
            {else}
               <strong>{$item.name}</strong>
            {/if}
        {/foreach}
        </div>
    {/if}
        <label>{str tag=search}:</label> {$form|safe}
    </div>
    <div class="cb"></div>
    {if $membershiptype}<h3>{str tag=pendingmembers section=group}</h3>{/if}
    <div id="results">
        <table id="membersearchresults" class="tablerenderer fullwidth listing twocolumn">
            <tbody>
            {$results|safe}
            </tbody>
        </table>
    </div>
    {$pagination|safe}

{include file="footer.tpl"}
