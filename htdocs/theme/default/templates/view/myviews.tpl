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
        <button class="fr" type="button"><strong>{str tag="deletethisview" section="view"}</strong></button>
        <button class="fr" type="button"><strong>{str tag ="editthisview" section="view"}</strong></button>
    {/if}
    <h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a></h3>
    {if $view.description}
        {$view.description}
    {/if}
    {if !$view.submittedto}
        <button type="button">{str tag="editviewnameanddescription" section="view"}</button>
    {/if}
    <ul>
    {if $view.artefacts}
        <li>
        {str tag="artefactsinthisview" section="view"}:
        {foreach from=$view.artefacts item=artefact name=artefacts}
            <a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$view.id}">{$artefact.title|escape}</a>{if !$smarty.foreach.artefacts.last}, {/if}
        {/foreach}
        </li>
    {/if}
    <li>
    {if $view.access}
        {$view.access}
        <br>
    {/if}
    {if $view.accessgroups}
        {str tag="whocanseethisview" section="view"}:
        {foreach from=$view.accessgroups item=accessgroup name=artefacts}
            {if $accessgroup.accesstype == 'loggedin'}
                {str tag="loggedin" section="view"}{elseif $accessgroup.accesstype == 'public'}
                {str tag="public" section="view"}{elseif $accessgroup.accesstype == 'friends'}
                <a href="{$WWWROOT}user/">{str tag="friends" section="view"}</a>{elseif $accessgroup.accesstype == 'group'}
                <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name|escape}</a>{elseif $accessgroup.accesstype == 'tutorgroup'}
                <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name|escape} ({str tag="tutors" section="view"})</a>{elseif $accessgroup.accesstype == 'user'}
                <a href="{$WWWROOT}user/view.php?id={$accessgroup.id}">{$accessgroup.id|display_name|escape}</a>{/if}{if !$smarty.foreach.artefacts.last},{/if}
        {/foreach}
    {else}
    {str tag="nobodycanseethisview" section="view"}
    {/if}
    <button type="button">{str tag="editviewaccess" section="view"}</button>
    </li>
    {if $view.submittedto}
        <li>{$view.submittedto}</li>
    {/if}
    </ul>
    {if $view.submitto}
        {$view.submitto}
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

