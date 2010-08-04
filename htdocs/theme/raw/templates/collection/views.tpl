{include file="header.tpl"}
{if !$views}
        <div class="message">{str tag=noviews section=collection}</div>
{else}
<table id="collectionviews" width="100%">
    <tbody>
        {foreach from=$views.views item=view}
            <tr class="{cycle values='r0,r1'}">
                <td><label><a href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title}</a></label></td>
                {if $view->master == 1 && !$new}
                    <td>
                        {str tag=currentmaster section=collection}: <a href="{$WWWROOT}view/access.php?id={$view->view}">{str tag=editviewaccess section=collection}</a>
                    </td>
                {else}
                    <td>&nbsp;</td>
                {/if}
                <td>&nbsp;</td>
                <td><a href="{$removeurl}&amp;view={$view->view}">{str tag=remove}</a></td>
                {if $views.count > 1}
                <td class="displayordercontrols">
                    {if $view->displayorder == $views.min}
                        <div id="viewdisplayorder_{$view->view}">
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down"><img src="{theme_url filename='images/move-block-down.png'}" alt="" ></a>
                        </div>
                    {elseif $view->displayorder == $views.max}
                        <div id="viewdisplayorder_{$view->view}">
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up"><img src="{theme_url filename='images/move-block-up.png'}" alt="" ></a>
                        </div>
                    {else}
                        <div id="viewdisplayorder_{$view->view}">
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up"><img src="{theme_url filename='images/move-block-up.png'}" alt="" ></a>
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down"><img src="{theme_url filename='images/move-block-down.png'}" alt="" ></a>
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
    {str tag=noviewsavailable section=collection}
{/if}
</fieldset>
{if $newform}{$newform|safe}{/if}
{include file="footer.tpl"}
