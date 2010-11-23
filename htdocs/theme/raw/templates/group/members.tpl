{include file="header.tpl"}

    {if $membershiptypes}
        <div id="memberoptions" class="fr">
        {foreach from=$membershiptypes item=item implode="&nbsp;&nbsp;|&nbsp;&nbsp;"}
            {if $item.link}
                <a href="{$item.link}">{$item.name}</a>
            {else}
                {$item.name}
            {/if}
        {/foreach}
        </div>
    {/if}
    <div class="fr memberssearch"><span class="fl">{str tag=search}:</span><span class="fr">{$form|safe}</span></div>
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
