{include file="header.tpl"}
{include file="sidebar.tpl"}
{include file="columnleftstart.tpl"}

{if ($caneditgroupview)}
<span class="addicon fr">
<a href="{$WWWROOT}view/edit.php?group={$groupid}">{str tag="createview" section="view"}</a>
</span>
{/if}
<h2>{$heading}</h2>

{include file="group/tabstart.tpl" current="views"}
{if $member}
  <ul>
    <li>
    {if $shared}<a href="groupviews.php?group={$groupid}">{str tag="viewsownedbygroup" section="view"}</a>
    {else}{str tag="viewsownedbygroup" section="view"}
    {/if}
    </li>
    <li>
    {if $shared}{str tag="viewssharedtogroup" section="view"}
    {else}<a href="groupviews.php?group={$groupid}&shared=1">{str tag="viewssharedtogroup" section="view"}</a>
    {/if}
    </li>
  </ul>
{/if}
{if $views}
<table id="myviewstable">

{foreach from=$views item=view}
    <tr class="{cycle values=r0,r1}">
    <td><h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a></h3></td>
    <td>
        by <a href="{$WWWROOT}{if $view.group}group{else}user{/if}/view.php?id={if $view.group}{$view.group}{else}{$view.owner}{/if}">{if $view.sharedby}{$view.sharedby}{else}{$groupname}{/if}</a>
    </td>
    <td>{$view.description}</td>
    </tr>
{/foreach}

</table>

<div class="center">{$pagination}</div>

{else}
<div class="message">{str tag="noviews" section="view"}</div>
{/if}
{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}

