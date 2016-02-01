{include file="header.tpl"}
<h3>{str tag=viewssharedtogroup section=view}</h3>
{if $sharedviews.count == '0'}
<p class="no-results">
    {str tag=noviewssharedwithgroupyet section=group}
</p>
{else}
<table id="sharedviewsreport" class="fullwidth groupreport table table-striped">
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
        {$sharedviews.tablerows|safe}
    </tbody>
</table>
    {$sharedviews.pagination|safe}
    {if $sharedviews.pagination_js}
    <script type="application/javascript">
    {$sharedviews.pagination_js|safe}
    </script>
    {/if}
{/if}

<h3>{str tag=groupviews section=view}</h3>
{if $groupviews.count == '0'}
<p class="no-results">
     {str tag=grouphasntcreatedanyviewsyet section=group}
</p>
{else}
<table id="groupviewsreport" class="fullwidth groupreport table table-striped">
    <thead>
        <tr>
            <th class="sv {if $sort == title && $direction == asc}asc{elseif $sort == title}sorted{/if}">
                <a href="{$baseurl}&sort=title{if $sort == title && $direction == asc}&direction=desc{/if}">
                    {str tag=viewsownedbygroup section=view}
                </a>
            </th>
            <th class="mc {if $sort == mcomments && $direction == asc}asc{elseif $sort == mcomments}sorted{/if}">
                <a href="{$baseurl}&sort=mcomments{if $sort == mcomments && $direction == asc}&direction=desc{/if}">
                    {str tag=membercommenters section=group}
                </a>
            </th>
            <th class="ec {if $sort == ecomments && $direction == asc}asc{elseif $sort == ecomments}sorted{/if}">
                <a href="{$baseurl}&sort=ecomment{if $sort == ecomments && $direction == asc}&direction=desc{/if}">{str tag=extcommenters section=group}
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
    <script type="application/javascript">
    {$groupviews.pagination_js|safe}
    </script>
    {/if}
{/if}
{include file="footer.tpl"}
