{include file="header.tpl"}
{include file="sidebar.tpl"}
{include file="columnleftstart.tpl"}
<h2>My Views</h2>

<div class="fr"><span class="addicon"><a href="{$WWWROOT}view/edit.php">{str tag="createview" section="view"}</a></span></div>
<br>

{if $views}
<table>

{foreach from=$views item=view}
    <tr class="{cycle values=r0,r1}">
    <td>
    {if !$view.submittedto}
        <a href="{$WWWROOT}view/delete.php?id={$view.id}" class="fr"><strong>{str tag="deletethisview" section="view"}</strong></a>
        <a href="{$WWWROOT}view/blocks.php?id={$view.id}" class="fr"><strong>{str tag ="editthisview" section="view"}</strong></a>
    {/if}
    <h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a></h3>
    <div class="viewitem">
    {if !$view.submittedto}
        <a style="text-decoration:underline" href="{$WWWROOT}view/edit.php?id={$view.id}">{str tag="editviewnameanddescription" section="view"}</a>
    {/if}
    {if $view.description}
        {if !$view.submittedto}<br>{/if}
        {$view.description}
    {/if}
    </div>
    <div class="viewitem">
    {if $view.artefacts}
        <strong>{str tag="artefacts" section="view"}:</strong>
        {foreach from=$view.artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$view.id}">{$artefact.title|escape}</a>{if !$smarty.foreach.artefacts.last}, {/if}{/foreach}
    {/if}
    </div>
    <div class="viewitem">
    <a style="text-decoration:underline" href="{$WWWROOT}view/access.php?id={$view.id}">{str tag="editviewaccess" section="view"}</a>
    <br>
    {if $view.access}
        {$view.access}
        <br>
    {/if}
    {if $view.accessgroups}
        {str tag="whocanseethisview" section="view"}:
        {foreach from=$view.accessgroups item=accessgroup name=artefacts}
        {* this is messy, but is like this so there aren't spaces between links and commas *}
            {if $accessgroup.accesstype == 'loggedin'}
                {str tag="loggedin" section="view"}{elseif $accessgroup.accesstype == 'public'}
                {str tag="public" section="view"}{elseif $accessgroup.accesstype == 'friends'}
                <a href="{$WWWROOT}user/">{str tag="friends" section="view"}</a>{elseif $accessgroup.accesstype == 'group'}
                <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name|escape}</a>{elseif $accessgroup.accesstype == 'tutorgroup'}
                <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name|escape}</a> ({str tag="tutors" section="view"}){elseif $accessgroup.accesstype == 'user'}
                <a href="{$WWWROOT}user/view.php?id={$accessgroup.id}">{$accessgroup.id|display_name|escape}</a>{/if}{if !$smarty.foreach.artefacts.last},{/if}
        {/foreach}
    {else}
        {str tag="nobodycanseethisview" section="view"}
    {/if}
    </div>
    {if $view.submittedto}
        <div class="viewitem">
        {$view.submittedto}
        </div>
    {/if}
    {if $view.submitto}
        <div class="viewitem">
        {$view.submitto}
        </div>
    {/if}
    </td>
    </tr>
{/foreach}

</table>

<div class="center">{$pagination}</div>

{else}
{str tag="noviews" section="views"}
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}

