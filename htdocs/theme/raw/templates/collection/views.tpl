{auto_escape off}
{include file="header.tpl"}
{if !$views}
        <div class="message">{str tag=noviews section=collection}</div>
{else}
<table id="collectionviews" width="100%">
    <tbody>
        {foreach from=$views.views item=view}
        <tr class="{cycle values='r0,r1'}">
            <td><label><a href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title|safe}</a></label></td>
            {if $view->master == 1}
                <td>
                    {str tag="currentmaster" section="collection"}: <a href="{$WWWROOT}view/access.php?id={$view->view|safe}">{str tag=editaccess section="view"}</a>
                </td>
            {else}
                <td>&nbsp;</td>
            {/if}
            <td>&nbsp;</td>
            <td><a href="{$WWWROOT}collection/deleteview.php?id={$view->collection|safe}&amp;view={$view->view|safe}">{str tag=remove}</a></td>
            <td class="displayordercontrols">
                {if $view->displayorder == $views.min}
                    <div id="viewdisplayorder_{$view->view}">
                        <a href="{$WWWROOT}collection/views.php?id={$view->collection|safe}&amp;v={$view->view|safe}&amp;d=down"><img src="{theme_url filename='images/move-block-down.png'}" alt="" ></a>
                    </div>
                {elseif $view->displayorder == $views.max}
                    <div id="viewdisplayorder_{$view->view}">
                        <a href="{$WWWROOT}collection/views.php?id={$view->collection|safe}&amp;view={$view->view|safe}&amp;direction=up"><img src="{theme_url filename='images/move-block-up.png'}" alt="" ></a>
                    </div>
                {else}
                    <div id="viewdisplayorder_{$view->view}">
                        <a href="{$WWWROOT}collection/views.php?id={$view->collection|safe}&amp;view={$view->view|safe}&amp;direction=up"><img src="{theme_url filename='images/move-block-up.png'}" alt="" ></a>
                        <a href="{$WWWROOT}collection/views.php?id={$view->collection|safe}&amp;view={$view->view|safe}&amp;direction=down"><img src="{theme_url filename='images/move-block-down.png'}" alt="" ></a>
                    </div>
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
<fieldset>
<legend>{$addviews|safe}</legend>
{$form|safe}
</fieldset>
{include file="footer.tpl"}
{/auto_escape}
