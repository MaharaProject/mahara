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
                <h3>{$skin.title|escape}</h3>
                {if $skin.editable}<a title="{str tag=clicktoedit section=skin}" href="{$WWWROOT}skin/design.php?id={$skin.id}{if $siteskins}&site=1{/if}"><img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" width="240" height="135" style="border:1px solid #333"></a>
                {else}<img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" width="240" height="135">{/if}
                {if $skin.removable}<div class="skinactions" id="skinactions">
                    <a href="{$WWWROOT}skin/delete.php?id={$skin.id}{if $siteskins}&site=1{/if}" class="btn-del">{str tag="deletethisskin" section="skin"}</a>
                    <a href="{$WWWROOT}skin/export.php?id={$skin.id}" class="btn-add">{str tag="exportthisskin" section="skin"}</a>
                </div>{else}
                    {if $skin.type == 'public' && $skin.owner != $user}
                        {if !$skin.favorite}
                            <div class="skinactions">
                            <a href="{$WWWROOT}skin/favorite.php?add={$skin.id}" class="btn-add">{str tag="addtofavorites" section="skin"}</a>
                            </div>
                        {else}
                            <div class="skinactions">
                            <a href="{$WWWROOT}skin/favorite.php?del={$skin.id}" class="btn-del">{str tag="removefromfavorites" section="skin"}</a>
                            </div>
                        {/if}
                    {/if}
                {/if}
            </div>
{/foreach}
<div style="clear:both;">{$pagination}</div>
{else}
            <div class="message">{str tag="noskins" section="skin"}</div>
{/if}
{include file="footer.tpl"}
