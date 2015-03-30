{include file="header.tpl"}

            <div class="rbuttons">
                <form method="post" action="{$WWWROOT}skin/design.php{if $siteskins}?site=1{/if}">
                    <input type="submit" class="submit" value="{str tag=createskin section=skin}">
                </form>
                <form method="post" action="{$WWWROOT}skin/import.php{if $siteskins}?site=1{/if}">
                    <input type="submit" class="submit" value="{str tag=importskins section=skin}">
                </form>
                <form method="post" action="{$WWWROOT}skin/export.php{if $siteskins}?site=1{/if}">
                    <input type="submit" class="submit" value="{str tag=exportskins section=skin}">
                </form>
            </div>
{if !$siteskins}
{$form|safe}
{/if}
{if $skins}
{foreach from=$skins item=skin}
            <div class="skinthumb">
                <div class="skin-controls">
                    {if $skin.editable}
                        <a href="{$WWWROOT}skin/design.php?id={$skin.id}{if $skin.type == 'site'}&site=1{/if}" class="btn-big-edit" title="{str tag='editthisskin' section='skin'}"
                            {if $skin.type == 'site'}onclick="return confirm('{str tag='editsiteskin?' section='skin'}');"{/if}>
                            {str tag=editspecific arg1=$skin.title}
                        </a>
                    {/if}
                    {if $skin.metadata && $skin.editable}
                        <a href="{$WWWROOT}skin/index.php?id={$skin.id}&metadata=1" class="btn-big-info" title="{str tag='viewmetadata' section='skin'}">
                            {str tag=viewmetadataspecific section=skin arg1=$skin.title}
                        </a>
                    {/if}
                    {if $skin.removable}
                        <a href="{$WWWROOT}skin/export.php?id={$skin.id}" class="btn-big-export"  title="{str tag='exportthisskin' section='skin'}">
                            {str tag=exportspecific section=skin arg1=$skin.title}
                        </a>
                        <a href="{$WWWROOT}skin/delete.php?id={$skin.id}{if $skin.type == 'site'}&site=1{/if}" class="btn-big-del" title="{str tag='deletethisskin' section='skin'}">
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
                    {if $skin.metadata}
                        <div class="skin-metadata {if $id eq $skin.id && $metadata}show{else}hidden{/if}">
                            <input type="image" class="metadataclose" src="{theme_image_url btn_close}" alt="{str tag=closemetadata section=skin}" title="{str tag=closemetadata section=skin}" />
                            <div class="metadatatitle"><h2 class="title">{str tag=metatitle section=skin}</h2></div>
                            <div class="metatitle"><span>{str tag=title section=skin}:</span> {$skin.title}</div>
                            <div class="metadisplayname"><span>{str tag=displayname section=skin}:</span> <a href="{$skin.metadata.profileurl}">{$skin.metadata.displayname}</a></div>
                            <div class="metadescription"><span>{str tag=description section=skin}:</span><br>{$skin.metadata.description}</div>
                            <div class="metacreationdate"><span>{str tag=creationdate section=skin}:</span> {$skin.metadata.ctime}</div>
                            <div class="metamodifieddate"><span>{str tag=modifieddate section=skin}:</span> {$skin.metadata.mtime}</div>
                        </div>
                    {/if}
                </div>
                <div class="skin-header">
                    <h2 class="title">{$skin.title|escape}</h2>
                </div>
                <div class="skin-content">
                    {if $skin.editable}
                    <a title="{str tag=clicktoedit section=skin}" href="{$WWWROOT}skin/design.php?id={$skin.id}{if $siteskins}&site=1{/if}">
                        <img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" alt="{str(tag=skinpreviewedit section=skin arg1=$skin.title)|escape}" width="240" height="135">
                    </a>
                    {else}
                    <img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" alt="{str(tag=skinpreview section=skin arg1=$skin.title)|escape}" width="240" height="135">
                    {/if}
                </div>
            </div>
{/foreach}
<div class="cb">{$pagination|safe}</div>
{else}
            <div class="message">{str tag="noskins" section="skin"}</div>
{/if}
{include file="footer.tpl"}
