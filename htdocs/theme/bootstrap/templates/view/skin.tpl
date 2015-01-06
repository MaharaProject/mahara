{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='skin' new=$new issiteview=$issiteview}
<div class="subpage">
    <div class="rbuttons skinsbtns">
        <a class="btn" href="{$WWWROOT}skin/index.php"><span class="btn-config">{str tag=manageskins section=skin}</span></a>
    </div>
    <div class="skins-wrap">
        <div class="currentskin">
            <h2>{str tag=currentskin section=skin}</h2>
            {if !$saved}<div class="message warning">{str tag=notsavedyet section=skin}</div>{/if}
            {if $incompatible}<div class="message warning">{$incompatible}</div>{/if}
            <h3 class="title">{$currenttitle|safe}</h3>
            <img src="{$WWWROOT}skin/thumb.php?id={$currentskin}" width="240" height="135" alt="{$currenttitle}">
            <div class="submitcancel">{$form|safe}</div>
            {if $currentmetadata}
            <div class="skin-metadata">
                <div class="metadisplayname"><span>{str tag=displayname section=skin}:</span> {$currentmetadata.displayname|clean_html|safe}</div>
                <div class="metadescription"><span>{str tag=description section=skin}:</span><br>{$currentmetadata.description|clean_html|safe}</div>
                <div class="metacreationdate"><span>{str tag=creationdate section=skin}:</span> {$currentmetadata.ctime}</div>
                <div class="metamodifieddate"><span>{str tag=modifieddate section=skin}:</span> {$currentmetadata.mtime}</div>
            </div>
            {/if}
        </div>
        <div class="skins-right">
            <h3 class="title">{str tag=userskins section=skin}</h3>
            <div class="userskins">
                <ul class="userskins">
                {foreach from=$userskins item=skin}
                    <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|safe}</span></a></li>
                {/foreach}
                </ul>
            </div>
            <h3 class="title favouriteskins">{str tag=favoriteskins section=skin}</h3>
            <div class="favorskins">
                <ul class="favorskins">
                {foreach from=$favorskins item=skin}
                    <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|safe}</span></a></li>
                {/foreach}
                </ul>
            </div>
            <h3 class="title">{str tag=siteskins section=skin}</h3>
            <div class="siteskins">
                <ul class="siteskins">
                {foreach from=$siteskins item=skin}
                    <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|safe}</span></a></li>
                {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
