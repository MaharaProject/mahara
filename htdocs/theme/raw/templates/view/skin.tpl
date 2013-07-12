{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='skin' new=$new}
<div class="subpage rel">
    <div class="cb pt20" />
    <div class="rbuttons pt20">
        <a class="btn" href="{$WWWROOT}skin/index.php">{str tag=manageskins section=skin}</a>
    </div>
    <div class="skintype"><strong>{str tag=currentskin section=skin}</strong>{if !$saved}<br /><small>{str tag=notsavedyet section=skin}</small>{/if}</div>
    <div class="currentskin"><img src="{$WWWROOT}skin/thumb.php?id={$currentskin}" width="180" height="101" alt="{$currenttitle}" style="border:1px solid #333"><span>{$currenttitle|escape}</span></div>
    <hr class="cb pt20" />
    <div class="skintype"><strong>{str tag=userskins section=skin}</strong></div>
    <div class="userskins">
        <ul class="userskins">
        {foreach from=$userskins item=skin}
            <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|escape}</span></a></li>
        {/foreach}
        </ul>
    </div>
    <hr class="cb" />
    <div class="skintype"><strong>{str tag=favoriteskins section=skin}</strong></div>
    <div class="favorskins">
        <ul class="favorskins">
        {foreach from=$favorskins item=skin}
            <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|escape}</span></a></li>
        {/foreach}
        </ul>
    </div>
    <hr class="cb" />
    <div class="skintype"><strong>{str tag=siteskins section=skin}</strong></div>
    <div class="siteskins">
        <ul class="siteskins">
        {foreach from=$siteskins item=skin}
            <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|escape}</span></a></li>
        {/foreach}
        </ul>
    </div>
    <hr class="cb" />
    <div>
        {$form|safe}
    </div>

</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
