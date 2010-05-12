{auto_escape off}
{include file="header.tpl"}

{if $views}
    <table class="fullwidth">
    {foreach from=$views item=view}
        <tr>
            <td class="{cycle values='r0,r1'}">
                <h5><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a> {str tag=by section=view}
                    <a href="{$WWWROOT}{if $view.group}group{else}user{/if}/view.php?id={if $view.group}{$view.group}{else}{$view.owner}{/if}">{if $view.sharedby}{$view.sharedby}{else}{$groupname}{/if}</a>
                </h5>
                <span>
                {if $view.description}
                    {$view.description}
                {/if}
                {if $view.description && $view.artefacts}<br>{/if}
                {if $view.artefacts}
                    <strong>{str tag="artefacts" section="view"}:</strong>
                    {foreach from=$view.artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$view.id}" class="link-artefacts">{$artefact.title|escape}</a>{if !$.foreach.artefacts.last}, {/if}{/foreach}
                {/if}
                {if $view.template}
                    <a href="">{str tag=copythisview section=view}</a>
                {/if}
                </span>
            </td>
        </tr>
    {/foreach}
    </table>
    <div class="center">{$pagination}</div>
{else}
<div class="message">{str tag="noviewstosee" section="group"}</div>
{/if}


{include file="footer.tpl"}

{/auto_escape}
