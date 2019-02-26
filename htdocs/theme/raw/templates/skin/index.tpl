{include file="header.tpl"}

<div class="btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}skin/design.php{if $siteskins}?site=1{/if}" class="btn btn-secondary button" type="submit">
        <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag=createskin section=skin}
    </a>
    <a href="{$WWWROOT}skin/import.php{if $siteskins}?site=1{/if}" class="btn btn-secondary button" type="submit">
        <span class="icon icon-code icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag=importskins section=skin}
    </a>
    <a href="{$WWWROOT}skin/export.php{if $siteskins}?site=1{/if}" class="btn btn-secondary button" type="submit">
        <span class="icon icon-download icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag=exportskins section=skin}
    </a>
</div>

{if !$siteskins}
    {$form|safe}
{/if}

{if $skins}
<div class="row skins">
    {foreach from=$skins item=skin}
    <div class="skin">
        <div class="card">
            <h2 class="card-header {if $skin.metadata} has-link {/if}">
                <a href="" type="button" title="{str tag='viewmetadata' section='skin'}" class="title-link" data-toggle="modal" data-target="#skindata-modal-{$skin.id}" aria-labelledby="skin-info">
                    {$skin.title|escape|safe}
                    <span class="icon icon-info-circle float-right link-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">
                        {str tag=viewmetadataspecific section=skin arg1=$skin.title}
                    </span>
                </a>
            </h2>

            <div class="skin-content">
                {if $skin.editable}
                <a title="{str tag=clicktoedit section=skin}" href="{$WWWROOT}skin/design.php?id={$skin.id}{if $siteskins}&site=1{/if}">
                    <img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" alt="{str(tag=skinpreviewedit section=skin arg1=$skin.title)|escape}" width="100%">
                </a>
                {else}
                <img src="{$WWWROOT}skin/thumb.php?id={$skin.id}" alt="{str(tag=skinpreview section=skin arg1=$skin.title)|escape}" width="100%">
                {/if}
            </div>
            <div class="skin-controls card-footer">
                {if $skin.editable}
                <a href="{$WWWROOT}skin/design.php?id={$skin.id}{if $skin.type == 'site'}&site=1{/if}" title="{str tag='editthisskin' section='skin'}" {if $skin.type == 'site'} onclick="return confirm('{str tag='editsiteskin?' section='skin'}');"{/if} class="btn btn-secondary btn-sm">
                    <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">
                        {str tag=editspecific arg1=$skin.title}
                    </span>
                </a>
                {/if}


                {if $skin.removable}
                <a href="{$WWWROOT}skin/export.php?id={$skin.id}" title="{str tag='exportthisskin' section='skin'}" class="btn btn-secondary btn-sm">
                    <span class="icon icon-download icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">
                        {str tag=exportspecific section=skin arg1=$skin.title}
                    </span>
                </a>

                <a href="{$WWWROOT}skin/delete.php?id={$skin.id}{if $skin.type == 'site'}&site=1{/if}" title="{str tag='deletethisskin' section='skin'}" class="btn btn-secondary btn-sm">
                    <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">
                        {str tag=deletespecific arg1=$skin.title}
                    </span>
                </a>

                {else}
                <div class="skinactions">
                    {if $skin.type == 'public' && $skin.owner != $user}
                        {if !$skin.favorite}
                        <a href="{$WWWROOT}skin/favorite.php?add={$skin.id}" title="{str tag='addtofavorites' section='skin'}" class="btn btn-secondary btn-sm">
                            <span class="icon icon-heart-o icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">
                                {str tag=addtofavoritesspecific section=skin arg1=$skin.title}
                            </span>
                        </a>

                        {else}
                        <a href="{$WWWROOT}skin/favorite.php?del={$skin.id}" title="{str tag='removefromfavorites' section='skin'}" class="btn btn-secondary btn-sm">
                            <span class="icon icon-heart icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">
                            {str tag=removefromfavoritesspecific section=skin arg1=$skin.title}
                            </span>
                        </a>
                        {/if}
                    {/if}
                </div>
                {/if}
            </div>
        </div>
        {if $skin.metadata}
        <div id="skindata-modal-{$skin.id}" tabindex="-1" class="skin-metadata modal fade" role="dialog" aria-labelledby="gridSystemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{str tag=Close}">
                            <span aria-hidden="true">&times;</span>
                        </button>

                        <h4 class="modal-title" id="skin-info">
                            {str tag=metatitle section=skin}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p class="metatitle">
                            <strong>{str tag=title section=skin}:</strong>
                            {$skin.title}
                        </p>

                        <p class="metadisplayname">
                            <strong>{str tag=displayname section=skin}:</strong>
                            <a href="{$skin.metadata.profileurl}">
                                {$skin.metadata.displayname}
                            </a>
                        </p>

                        <p class="metadescription">
                            <strong>{str tag=description section=skin}:</strong>
                            <br>{$skin.metadata.description}
                        </p>

                        <p class="metacreationdate">
                            <strong>{str tag=creationdate section=skin}:</strong>
                            {$skin.metadata.ctime}
                        </p>

                        <p class="metamodifieddate">
                            <strong>{str tag=modifieddate section=skin}:</strong>
                            {$skin.metadata.mtime}
                        </p>
                    </div>

                </div>
            </div>
        </div>
        {/if}
    </div>
    {/foreach}
</div>
<div class="">
    {$pagination|safe}
</div>

{else}

<p class="no-results">
    {str tag="noskins" section="skin"}
</p>

{/if}
{include file="footer.tpl"}
