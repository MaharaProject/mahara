<form action="{$boxtype}.php" method="post" class="pieform form-inline with-heading">
    <div>
        <label class="inline-label">{str section='module.multirecipientnotification' tag='labelsearch'}</label>
        <input type="text" name="search" id="search" autofocus="autofocus" value="{$searchdata->searchtext}">
        <input type="hidden" name="searcharea" value="All_data">
        <button type="submit" name="buttonsubmit" class="btn btn-secondary submitcancel submit">{str tag='go'}</button>
    </div>
</form>
{if $searchdata->searchtext}
<div class="notifications-tabswrap">
    <span class="accessible-hidden sr-only">{str section='activity' tag='messagetype'}</span>
    <ul class="in-page-tabs searchtab nav nav-tabs">
        {foreach from=$searchdata->tabs item=term}
        <li class="{if $searchdata->searcharea === $term->name}current-tab active{/if}">
            {if $term->count > 0}
                <a class="{if $searchdata->searcharea === $term->name}current-tab active{/if}" href="{$searchdata->searchurl}{$term->name}">
                {str section='module.multirecipientnotification' tag=$term->tag}{if $term->count > 0} <span class="countresults">({$term->count})</span>{/if}
                </a>
            {else}
                <a class="inactive">
                    {str section='module.multirecipientnotification' tag=$term->tag}
                    <span class="accessible-hidden sr-only">({str tag=tab} {str tag=disabled})</span>
                </a>
            {/if}
        </li>
        {/foreach}
    </ul>
</div>
{/if}
