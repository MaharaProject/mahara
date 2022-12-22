{include file="header.tpl"}
<h2>{str tag=viewscollectionssharedtogroup1 section=view}</h2>
{if $sharedviews.count == '0'}
<p class="no-results">
    {str tag=noviewssharedwithgroupyet section=group}
</p>
{else}
<table id="sharedviewsreport" class="fullwidth groupreport table table-striped">
    <thead>
        <tr>
            <th class="sv sort-column {if $sort == title && $direction == asc}asc{elseif $sort == title}sorted{/if}">
                <a href="{$baseurl}&sort=title{if $sort == title && $direction == asc}&direction=desc{/if}">
                  <span>{str tag=sharedtogroup section=view}</span>
                  {if $sort == title}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == title && $direction == asc}{str tag=ascending}{elseif $sort == title}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
            <th class="sb sort-column {if $sort == owner && $direction == asc}asc{elseif $sort == owner}sorted{/if}">
                <a href="{$baseurl}&sort=owner{if $sort == owner && $direction == asc}&direction=desc{/if}">
                  <span>{str tag=sharedby section=view}</span>
                  {if $sort == owner}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == owner && $direction == asc}{str tag=ascending}{elseif $sort == owner}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
            <th class="mc sort-column {if $sort == membercommentcount && $direction == asc}asc{elseif $sort == membercommentcount}sorted{/if}">
                <a href="{$baseurl}&sort=membercommentcount{if $sort == membercommentcount && $direction == asc}&direction=desc{/if}">
                  <span>{str tag=membercommenters section=group}</span>
                  {if $sort == membercommentcount}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == membercommentcount && $direction == asc}{str tag=ascending}{elseif $sort == membercommentcount}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
            <th class="ec sort-column {if $sort == nonmembercommentcount && $direction == asc}asc{elseif $sort == nonmembercommentcount}sorted{/if}">
                <a href="{$baseurl}&sort=nonmembercommentcount{if $sort == nonmembercommentcount && $direction == asc}&direction=desc{/if}">
                  <span>{str tag=extcommenters section=group}</span>
                  {if $sort == nonmembercommentcount}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == nonmembercommentcount && $direction == asc}{str tag=ascending}{elseif $sort == nonmembercommentcount}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
        </tr>
    </thead>
    <tbody>
        {$sharedviews.tablerows|safe}
    </tbody>
</table>
    {$sharedviews.pagination|safe}
    {if $sharedviews.pagination_js}
    <script>
    {$sharedviews.pagination_js|safe}
    </script>
    {/if}
{/if}

<h2>{str tag=groupviews1 section=view}</h2>
{if $groupviews.count == '0'}
<p class="no-results">
     {str tag=grouphasntcreatedanyviewsyet section=group}
</p>
{else}
<table id="groupviewsreport" class="fullwidth groupreport table table-striped">
    <thead>
        <tr>
            <th class="sv sort-column {if $sort == title && $direction == asc}asc{elseif $sort == title}sorted{/if}">
                <a href="{$baseurl}&sort=title{if $sort == title && $direction == asc}&direction=desc{/if}">
                    <span>{str tag=ownedbygroup section=view}</span>
                    {if $sort == title}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == title && $direction == asc}{str tag=ascending}{elseif $sort == title}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
            <th class="mc sort-column {if $sort == membercommentcount && $direction == asc}asc{elseif $sort == membercommentcount}sorted{/if}">
                <a href="{$baseurl}&sort=membercommentcount{if $sort == membercommentcount && $direction == asc}&direction=desc{/if}">
                    <span>{str tag=membercommenters section=group}</span>
                    {if $sort == membercommentcount}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == membercommentcount && $direction == asc}{str tag=ascending}{elseif $sort == membercommentcount}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
            <th class="ec sort-column {if $sort == nonmembercommentcount && $direction == asc}asc{elseif $sort == nonmembercommentcount}sorted{/if}">
                <a href="{$baseurl}&sort=nonmembercommentcount{if $sort == nonmembercommentcount && $direction == asc}&direction=desc{/if}">
                    <span>{str tag=extcommenters section=group}</span>
                    {if $sort == nonmembercommentcount}<span class="accessible-hidden visually-hidden">({str tag=sortby} {if $sort == nonmembercommentcount && $direction == asc}{str tag=ascending}{elseif $sort == nonmembercommentcount}{str tag=descending}{/if})</span>{/if}
                </a>
            </th>
        </tr>
    </thead>
    <tbody>
        {$groupviews.tablerows|safe}
    </tbody>
</table>
{$groupviews.pagination|safe}
    {if $groupviews.pagination_js}
    <script>
    {$groupviews.pagination_js|safe}
    </script>
    {/if}
{/if}
{include file="footer.tpl"}
