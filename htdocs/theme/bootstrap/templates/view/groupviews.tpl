{include file="header.tpl"}

{if $views}
    <table id="groupviews" class="fullwidth">
    {foreach from=$views item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                <h4><a href="{$view.fullurl}">{$view.title}</a></h4>
                {if $view.description}
                    <div>{$view.description|clean_html|safe}</div>
                {/if}
            </td>
            <td class="right">
                {if $view.copyform}{$view.copyform|safe}{/if}
            </td>
        </tr>
    {/foreach}
    </table>
    <div class="center">{$pagination|safe}</div>
{else}
<div class="message">{str tag="noviewstosee" section="group"}</div>
{/if}

{include file="footer.tpl"}
