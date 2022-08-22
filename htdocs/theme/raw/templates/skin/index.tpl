{include file="header.tpl"}

<div class="btn-top-right btn-group btn-group-top">
    <button data-url="{$WWWROOT}skin/design.php{if $siteskins}?site=1{/if}" class="btn btn-secondary button" type="submit">
        <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag=createskin section=skin}
    </button>
    <button type="button" class="btn btn-secondary dropdown-toggle" title="{str tag='moreoptions'}" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="icon icon-ellipsis-h" role="presentation" aria-hidden="true"></span>
        <span class="visually-hidden">{str tag="moreoptions"}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" role="menu">
        <li class="dropdown-item with-icon">
            <a href="{$WWWROOT}skin/import.php{if $siteskins}?site=1{/if}" type="submit">
                <span class="icon icon-upload left" role="presentation" aria-hidden="true"></span>
                <span class="link-text">{str tag=importskinsmenu section=skin}</span>
            </a>
        </li>
        <li class="dropdown-item with-icon">
            <a href="{$WWWROOT}skin/export.php{if $siteskins}?site=1{/if}" type="submit">
                <span class="icon icon-download left" role="presentation" aria-hidden="true"></span>
                <span class="link-text">{str tag=exportskinsmenu section=skin}</span>
            </a>
        </li>
    </ul>
</div>

{if !$siteskins}
    {$form|safe}
{/if}

{if $skins}
<div class="row skins view-container">
    {foreach from=$skins item=skin}
    <div class="skin">
        <div class="card">
            <h2 class="card-header {if $skin.metadata} has-link {/if}">
                <a href="" type="button" title="{str tag='viewmetadata' section='skin'}" class="title-link" data-bs-toggle="modal" data-bs-target="#skindata-modal-{$skin.id}" aria-labelledby="skin-info">
                    {$skin.title|escape|safe}
                    <span class="help float-end">
                        <span class="icon icon-info-circle link-indicator" role="presentation" aria-hidden="true"></span>
                        <span class="visually-hidden">
                            {str tag=viewmetadataspecific section=skin arg1=$skin.title}
                        </span>
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
                <button data-url="{$WWWROOT}skin/design.php?id={$skin.id}{if $skin.type == 'site'}&site=1{/if}" type="button" title="{str tag='editthisskin' section='skin'}" class="btn btn-secondary btn-sm"
                    {if $skin.type == 'site'} onclick="return confirm('{str tag='editsiteskin?' section='skin'}');"{/if}
                    >
                    <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">
                        {str tag=editspecific arg1=$skin.title}
                    </span>
                </button>
                {/if}


                {if $skin.removable}
                <button data-url="{$WWWROOT}skin/export.php?id={$skin.id}" type="button" title="{str tag='exportthisskin' section='skin'}" class="btn btn-secondary btn-sm">
                    <span class="icon icon-download" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">
                        {str tag=exportspecific section=skin arg1=$skin.title}
                    </span>
                </button>

                <button data-url="{$WWWROOT}skin/delete.php?id={$skin.id}{if $skin.type == 'site'}&site=1{/if}" type="button" title="{str tag='deletethisskin' section='skin'}" class="btn btn-secondary btn-sm">
                    <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">
                        {str tag=deletespecific arg1=$skin.title}
                    </span>
                </button>

                {else}
                <div class="skinactions">
                    {if $skin.type == 'public' && $skin.owner != $user}
                        {if !$skin.favorite}
                        <button data-url="{$WWWROOT}skin/favorite.php?add={$skin.id}" title="{str tag='addtofavorites' section='skin'}" type="button" class="btn btn-secondary btn-sm">
                            <span class="icon icon-regular icon-heart" role="presentation" aria-hidden="true"></span>
                            <span class="visually-hidden">
                                {str tag=addtofavoritesspecific section=skin arg1=$skin.title}
                            </span>
                        </button>

                        {else}
                        <button data-url="{$WWWROOT}skin/favorite.php?del={$skin.id}" title="{str tag='removefromfavorites' section='skin'}" type="button" class="btn btn-secondary btn-sm">
                            <span class="icon icon-heart" role="presentation" aria-hidden="true"></span>
                            <span class="visually-hidden">
                            {str tag=removefromfavoritesspecific section=skin arg1=$skin.title}
                            </span>
                        </button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                        <h1 class="modal-title" id="skin-info">
                            {str tag=metatitle section=skin}
                        </h1>
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
                            {$skin.metadata.description}
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
