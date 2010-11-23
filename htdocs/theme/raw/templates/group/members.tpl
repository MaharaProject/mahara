{include file="header.tpl"}

    <div class="fr memberssearch">
    {if $membershiptypes}
        {foreach from=$membershiptypes item=item implode="&nbsp;|&nbsp;"}
            {if $item.link}
                <a href="{$item.link}">{$item.name}</a>
            {else}
                {$item.name}
            {/if}
        {/foreach}&nbsp;&nbsp;&nbsp;
    {/if}
        {str tag=search}: {$form|safe}
    </div>
    <div>{$instructions|clean_html|safe}</div>
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
