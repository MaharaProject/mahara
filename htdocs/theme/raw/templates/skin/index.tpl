{auto_escape off}
{include file="header.tpl"}

            <div class="rbuttons">
                <form method="post" action="{$WWWROOT}skin/design.php{if $siteskins}?site={$siteskins}{/if}">
                    <input type="submit" class="submit" value="{str tag=createskin section=skin}">
                </form>
                <form method="post" action="{$WWWROOT}skin/import.php?site={$siteskins}">
                    <input type="submit" class="submit" value="{str tag=importskins section=skin}">
                </form>
                <form method="post" action="{$WWWROOT}skin/export.php?site={$siteskins}">
                    <input type="submit" class="submit" value="{str tag=exportskins section=skin}">
                </form>
            </div>
{if !$siteskins}
{$form}
{/if}
{if $skins}
{foreach from=$skins item=skin}
            <div class="skinthumb">
                <div class="skin-controls">
                    {if $skin.editable}
                        <a href="{$WWWROOT}skin/design.php?id={$skin.id}{if $siteskins}&site=1{/if}" class="btn-big-edit" title="{str tag='clickimagetoedit' section='skin'}">{str tag="clickimagetoedit" section="skin"}</a>
                    {/if}
                    {if $skin.removable}
                        <a href="{$WWWROOT}skin/export.php?id={$skin.id}" class="btn-big-export"  title="{str tag='exportthisskin' section='skin'}">{str tag="exportthisskin" section="skin"}</a>
                        <a href="{$WWWROOT}skin/delete.php?id={$skin.id}{if $siteskins}&site=1{/if}" class="btn-big-del" title="{str tag='deletethisskin' section='skin'}">{str tag="deletethisskin" section="skin"}</a>
                    {else}
                        {if $skin.type == 'public' && $skin.owner != $user}
                            {if !$skin.favorite}
                                <div class="skinactions">
                                <a href="{$WWWROOT}skin/favorite.php?add={$skin.id}" class="btn-addtofavourites" title="{str tag='addtofavorites' section='skin'}">{str tag="addtofavorites" section="skin"}</a>
                                </div>
                            {else}
                                <div class="skinactions">
                                <a href="{$WWWROOT}skin/favorite.php?del={$skin.id}" class="btn-removefromfavourites" title="{str tag='removefromfavorites' section='skin'}">{str tag="removefromfavorites" section="skin"}</a>
                                </div>
                            {/if}
                        {/if}
                    {/if}
                </div>
                <div class="skin-header">
                    <h2 class="title">{$skin.title|escape}</h2>
                </div>
                <div class="skin-content">
                    {if $skin.editable}
                    <a title="{str tag=clicktoedit section=skin}" href="{$WWWROOT}skin/design.php?id={$skin.id}{if $siteskins}&site=1{/if}" title="{str tag='clickimagetoedit' section='skin'}"><img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" width="240" height="135"></a>
                    {else}
                    <img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" width="240" height="135">
                    {/if}
                </div>
            </div>
{/foreach}
<div class="cb">{$pagination}</div>
{else}
            <div class="message">{str tag="noskins" section="skin"}</div>
{/if}
{include file="footer.tpl"}
