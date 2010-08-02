{auto_escape on}
{include file="header.tpl"}
{if !$views}
        <div class="message">{str tag=noviews section=collection}</div>
{else}
<table id="collectionviews" width="100%">
    <tbody>
        {foreach from=$views.views item=view}
            <tr class="{cycle values='r0,r1'}">
                <td><label><a href="{$WWWROOT}view/view.php?id={$view->view|safe}">{$view->title|safe}</a></label></td>
                {if $view->master == 1 && !$new}
                    <td>
                        {str tag=currentmaster section=collection}: <a href="{$WWWROOT}view/access.php?id={$view->view|safe}">{str tag=editaccess section=view}</a>
                    </td>
                {else}
                    <td>&nbsp;</td>
                {/if}
                <td>&nbsp;</td>
                <td><a href="{$removeurl|safe}&amp;view={$view->view|safe}">{str tag=remove}</a></td>
                {if $views.count > 1}
                <td class="displayordercontrols">
                    {if $view->displayorder == $views.min}
                        <div id="viewdisplayorder_{$view->view|safe}">
                            <a href="{$displayurl|safe}&amp;view={$view->view|safe}&amp;direction=down"><img src="{theme_url filename='images/move-block-down.png'}" alt="" ></a>
                        </div>
                    {elseif $view->displayorder == $views.max}
                        <div id="viewdisplayorder_{$view->view|safe}">
                            <a href="{$displayurl|safe}&amp;view={$view->view|safe}&amp;direction=up"><img src="{theme_url filename='images/move-block-up.png'}" alt="" ></a>
                        </div>
                    {else}
                        <div id="viewdisplayorder_{$view->view}">
                            <a href="{$displayurl|safe}&amp;view={$view->view|safe}&amp;direction=up"><img src="{theme_url filename='images/move-block-up.png'}" alt="" ></a>
                            <a href="{$displayurl|safe}&amp;view={$view->view|safe}&amp;direction=down"><img src="{theme_url filename='images/move-block-down.png'}" alt="" ></a>
                        </div>
                    {/if}
                </td>
                {else}
                    <td>&nbsp;</td>
                {/if}
            </tr>
        {/foreach}
    </tbody>
</table>
{/if}
<fieldset>
<legend>{str tag=addviews section=collection}</legend>
{if $viewsform}
    {$viewsform|safe}
{else}
    {str tag=noviewstochoose section=collection}
{/if}
</fieldset>
{if $newform}{$newform|safe}{/if}
{include file="footer.tpl"}
{auto_escape off}
