{include file="header.tpl"}

{if !$sharedviews && !$groupviews}
<p>{str tag=youhaventcreatedanyviewsyet section=view}</p>
{else}

{if $sharedviews}
<table class="fullwidth groupreport" id="sharedviewsreport">
  <thead>
    <tr>
      <th class="sv {if $sort == 'title' && $direction == 'asc'}asc{elseif $sort == 'title'}sorted{/if}">
        <a href="{$baseurl}&sort=title{if $sort == 'title' && $direction == 'asc'}&direction=desc{/if}">{str tag=viewssharedtogroup section=view}</a>
      </th>
      <th class="sb">
        {str tag=sharedby section=view}
      </th>
      <th class="mc {if ($sort == 'membercommentcount') && ($direction == 'asc')}asc{elseif $sort == 'membercommentcount'}sorted{/if}">
        <a href="{$baseurl}&sort=membercommentcount{if ($sort == 'membercommentcount') && ($direction == 'asc')}&direction=desc{/if}">{str tag=membercommenters section=group}</a>
      </th>
      <th class="ec {if $sort == nonmembercommentcount && $direction == asc}asc{elseif $sort == nonmembercommentcount}sorted{/if}">
        <a href="{$baseurl}&sort=nonmembercommentcount{if $sort == nonmembercommentcount && $direction == asc}&direction=desc{/if}">{str tag=extcommenters section=group}</a>
      </th>
    </tr>
  </thead>
  <tbody>
    {$sharedviews.tablerows|safe}
  </tbody>
</table>
    {if $sharedviews.pagination}
        <div id="sharedviews_page_container" class="hidden center">{$sharedviews.pagination|safe}</div>
    {/if}
    {if $sharedviews.pagination_js}
    <script>
        addLoadEvent(function() {literal}{{/literal}
            {$sharedviews.pagination_js|safe}
            removeElementClass('sharedviews_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
{/if}

{if $groupviews}
<table class="fullwidth groupreport" id="groupviewsreport">
  <thead>
    <tr>
      <th class="sv {if $sort == 'title' && $direction == 'asc'}asc{elseif $sort == 'title'}sorted{/if}">
        <a href="{$baseurl}&sort=title{if $sort == 'title' && $direction == 'asc'}&direction=desc{/if}">{str tag=viewsownedbygroup section=view}</a>
      </th>
      <th class="mc {if ($sort == 'membercommentcount') && ($direction == 'asc')}asc{elseif $sort == 'membercommentcount'}sorted{/if}">
        <a href="{$baseurl}&sort=membercommentcount{if ($sort == 'membercommentcount') && ($direction == 'asc')}&direction=desc{/if}">{str tag=membercommenters section=group}</a>
      </th>
      <th class="ec {if $sort == nonmembercommentcount && $direction == asc}asc{elseif $sort == nonmembercommentcount}sorted{/if}">
        <a href="{$baseurl}&sort=nonmembercommentcount{if $sort == nonmembercommentcount && $direction == asc}&direction=desc{/if}">{str tag=extcommenters section=group}</a>
      </th>
    </tr>
  </thead>
  <tbody>
    {$groupviews.tablerows|safe}
  </tbody>
</table>
    {if $groupviews.pagination}
        <div id="groupviews_page_container" class="hidden center">{$groupviews.pagination|safe}</div>
    {/if}
    {if $groupviews.pagination_js}
    <script>
        addLoadEvent(function() {literal}{{/literal}
            {$groupviews.pagination_js|safe}
            removeElementClass('groupviews_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
{/if}

{include file="footer.tpl"}
