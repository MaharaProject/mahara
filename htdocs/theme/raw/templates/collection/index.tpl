{include file="header.tpl"}

<p class="lead view-description">{str tag=collectiondescription section=collection}</p>
{if !$canedit}<p>{str tag=canteditgroupcollections section=collection}</p>{/if}
{if $collections}
<div class="card">
    <div id="mycollections" class="list-group">
        {$collectionhtml|safe}
    </div>
</div>
       {$pagination|safe}
       {if $pagination_js}
       <script>
       {$pagination_js|safe}
       </script>
       {/if}
{else}
    <p class="no-results">
        {str tag=nocollections section=collection}{if $addonelink} <a href={$addonelink}>{str tag=addone}</a>{/if}
    </p>
{/if}
{include file="footer.tpl"}
