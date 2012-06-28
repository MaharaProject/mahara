{include file="header.tpl"}

{if !$sharedviews && !$groupviews}
<p>{str tag=youhaventcreatedanyviewsyet section=view}</p>
{else}

<table class="fullwidth sharedviewsreport">
  <thead>
    <tr>
      <th class="sv {if $sort == title && $direction == asc}asc{elseif $sort == title}sorted{/if}">
        <a href="{$baseurl}&sort=title{if $sort == title && $direction == asc}&direction=desc{/if}">{str tag=viewssharedtogroup section=view}</a>
      </th>
      <th class="sb {if $sort == sharedby && $direction == asc}asc{elseif $sort == sharedby}sorted{/if}">
        <a href="{$baseurl}&sort=sharedby{if $sort == sharedby && $direction == asc}&direction=desc{/if}">{str tag=sharedby section=view}</a>
      </th>
      <th class="mc {if $sort == mcomments && $direction == asc}asc{elseif $sort == mcomments}sorted{/if}">
        <a href="{$baseurl}&sort=mcomments{if $sort == mcomments && $direction == asc}&direction=desc{/if}">{str tag=membercommenters section=group}</a>
      </th>
      <th class="ec {if $sort == ecomments && $direction == asc}asc{elseif $sort == ecomments}sorted{/if}">
        <a href="{$baseurl}&sort=ecomments{if $sort == ecomments && $direction == asc}&direction=desc{/if}">{str tag=extcommenters section=group}</a>
      </th>
    </tr>
  </thead>
  <tbody>
{if $sharedviews}
{foreach from=$sharedviews item=view}
    <tr class="{cycle values='r0,r1'}">
      <td><a href="{$view.baseurl}">{$view.title}</a></td>
      <td>
{if $view.owner}
        <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.user->id|display_name:null:true|escape}</a>
{elseif $view.group}
        <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.groupname|escape}</a>
{elseif $view.institution}
        <a href="{$WWWROOT}institution/view.php?id={$view.institution}">{$view.institution|escape}</a>
{/if}
      </td>
      <td>
        <ul>
{foreach from=$view.comments key=commenter item=info}
        {if $info.member}<li><a href="{$WWWROOT}user/view.php?id={$info.commenter}">{$info.commenter|display_name:null:true|escape}</a><span class="right">{$info.count}</span></li>{/if}
{/foreach}
        </ul>
{if $view.mcomments > 0}<p class="right">{$view.mcomments} {str tag=comments section=artefact.comment}</p>{/if}
      </td>
      <td>
        <ul>
{foreach from=$view.comments key=commenter item=info}
        {if $info.commenter|is_string}
          <li>{$info.commenter}<span class="right">{$info.count}</span></li>
        {elseif ! $info.member}
          <li><a href="{$WWWROOT}user/view.php?id={$info.commenter}">{$info.commenter|display_name:null:true|escape}</a><span class="right">{$info.count}</span></li>
        {/if}
{/foreach}
        </ul>
{if $view.ecomments > 0}<p class="right">{$view.ecomments} {str tag=comments section=artefact.comment}</p>{/if}
      </td>
    </tr>
{/foreach}
{elseif $svcount > 0}
    <tr class="{cycle values='r0,r1'}"><td colspan="4" class="message">{str tag=groupsharedviewsscrolled section=group}<td></tr>
{else}
    <tr class="{cycle values='r0,r1'}"><td colspan="4" class="message">{str tag=noviewssharedwithgroupyet section=group}<td></tr>
{/if}
  </tbody>
</table>

<br/><br/>

<table class="fullwidth groupviewsreport">
  <thead>
  <thead>
    <tr>
      <th class="sv {if $sort == title && $direction == asc}asc{elseif $sort == title}sorted{/if}">
        <a href="{$baseurl}&sort=title{if $sort == title && $direction == asc}&direction=desc{/if}">{str tag=viewsownedbygroup section=view}</a>
      </th>
      <th class="mc {if $sort == mcomments && $direction == asc}asc{elseif $sort == mcomments}sorted{/if}">
        <a href="{$baseurl}&sort=mcomments{if $sort == mcomments && $direction == asc}&direction=desc{/if}">{str tag=membercommenters section=group}</a>
      </th>
      <th class="ec {if $sort == ecomments && $direction == asc}asc{elseif $sort == ecomments}sorted{/if}">
        <a href="{$baseurl}&sort=ecomment{if $sort == ecomments && $direction == asc}&direction=desc{/if}">{str tag=extcommenters section=group}</a>
      </th>
    </tr>
  </thead>
  <tbody>
{if $groupviews}
{foreach from=$groupviews item=view}
    <tr class="{cycle values='r0,r1'}">
      <td><a href="{$view.fullurl}">{$view.title}</a></td>
      <td>
        <ul>
{foreach from=$view.comments key=commenter item=info}
        {if $info.member}<li><a href="{$WWWROOT}user/view.php?id={$info.commenter}">{$info.commenter|display_name:null:true|escape}</a><span class="right">{$info.count}</span></li>{/if}
{/foreach}
        </ul>
{if $view.mcomments > 0}<p class="right">{$view.mcomments} {str tag=comments section=artefact.comment}</p>{/if}
      </td>
      <td>
        <ul>
{foreach from=$view.comments key=commenter item=info}
        {if $info.commenter|is_string}
          <li>{$info.commenter}<span class="right">{$info.count}</span></li>
        {elseif ! $info.member}
          <li><a href="{$WWWROOT}user/view.php?id={$info.commenter}">{$info.commenter|display_name:null:true|escape}</a><span class="right">{$info.count}</span></li>
        {/if}
{/foreach}
        </ul>
{if $view.ecomments > 0}<p class="right">{$view.ecomments} {str tag=comments section=artefact.comment}</p>{/if}
      </td>
    </tr>
{/foreach}
{elseif $gvcount > 0}
    <tr class="{cycle values='r0,r1'}"><td colspan="3" class="message">{str tag=groupcreatedviewsscrolled section=group}<td></tr>
{else}
    <tr class="{cycle values='r0,r1'}"><td colspan="3" class="message">{str tag=grouphasntcreatedanyviewsyet section=group}<td></tr>
{/if}
  </tbody>
</table>
{$pagination|safe}

{/if}
{include file="footer.tpl"}
