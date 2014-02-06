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
                        <a href="{$WWWROOT}skin/design.php?id={$skin.id}{if $siteskins}&site=1{/if}" class="btn-big-edit" title="{str tag='clickimagetoedit' section='skin'}">
                            {str tag=editspecific arg1=$skin.title}
                        </a>
                    {/if}
                    {if $skin.metadata && $skin.editable}
                        <a href="{$WWWROOT}skin/index.php?id={$skin.id}&metadata=1" class="btn-big-info" title="{str tag='viewmetadata' section='skin'}">
                            {str tag=viewmetadataspecific section=skin arg1=$skin.title}
                        </a>
                        <div class="skin-metadata {if $id eq $skin.id && $metadata}show{else}hidden{/if}">
                            <input type="image" class="metadataclose" src="{theme_url images/btn_close.png}" alt="{str tag=closemetadata section=skin}" title="{str tag=closemetadata section=skin}" />
                            <div class="metadatatitle"><h2 class="title">{str tag=metatitle section=skin}</h2></div>
                            <div class="metatitle"><span>{str tag=title section=skin}:</span> {$skin.title|escape}</div>
                            <div class="metadisplayname"><span>{str tag=displayname section=skin}:</span> {$skin.metadata.displayname}</div>
                            <div class="metadescription"><span>{str tag=description section=skin}:</span><br>{$skin.metadata.description|clean_html|safe}</div>
                            <div class="metacreationdate"><span>{str tag=creationdate section=skin}:</span> {$skin.metadata.ctime}</div>
                            <div class="metamodifieddate"><span>{str tag=modifieddate section=skin}:</span> {$skin.metadata.mtime}</div>
                        </div>
                    {/if}
                    {if $skin.removable}
                        <a href="{$WWWROOT}skin/export.php?id={$skin.id}" class="btn-big-export"  title="{str tag='exportthisskin' section='skin'}">
                            {str tag=exportspecific section=skin arg1=$skin.title}
                        </a>
                        <a href="{$WWWROOT}skin/delete.php?id={$skin.id}{if $siteskins}&site=1{/if}" class="btn-big-del" title="{str tag='deletethisskin' section='skin'}">
                            {str tag=deletespecific arg1=$skin.title}
                        </a>
                    {else}
                        <div class="skinactions">
                        {if $skin.metadata && !$skin.editable}
                            <a href="{$WWWROOT}skin/index.php?id={$skin.id}&metadata=1" class="btn-big-info" title="{str tag='viewmetadata' section='skin'}">
                                {str tag=viewmetadataspecific section=skin arg1=$skin.title}
                            </a>
                        {/if}
                        {if $skin.type == 'public' && $skin.owner != $user}
                            {if !$skin.favorite}
                                <a href="{$WWWROOT}skin/favorite.php?add={$skin.id}" class="btn-addtofavourites" title="{str tag='addtofavorites' section='skin'}">
                                    {str tag=addtofavoritesspecific section=skin arg1=$skin.title}
                                </a>
                            {else}
                                <a href="{$WWWROOT}skin/favorite.php?del={$skin.id}" class="btn-removefromfavourites" title="{str tag='removefromfavorites' section='skin'}">
                                    {str tag=removefromfavoritesspecific section=skin arg1=$skin.title}
                                </a>
                            {/if}

                        {/if}
                        </div>
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
