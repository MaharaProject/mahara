{if $item->access}
    {strip}
    {foreach from=$item->access item=row name=ags}
        {if $row->accesstype == 'loggedin'}
            {str tag="registeredusers" section="view"}
        {elseif $row->accesstype == 'public'}
            {str tag="public" section="view"}
        {elseif $row->accesstype == 'friends'}
            {str tag="friends" section="view"}
        {elseif $row->group}
            <a href="{$WWWROOT}group/view.php?id={$row->group}">{$row->group|group_display_name}</a>{if $row->role} ({$row->group|group_display_role:$row->role}){/if}
        {elseif $row->institution}
            <a href="{$WWWROOT}institution/index.php?institution={$row->institution}">{$row->institution|institution_display_name}</a>
        {elseif $row->usr}
            <a href="{$WWWROOT}user/view.php?id={$row->usr}">{$row->usr|display_name:null:true:true}</a>
        {/if}
        {if $row->startdate}
            {if $row->stopdate}
                <span class="date"> {$row->startdate|strtotime|format_date:'strfdaymonthyearshort'}&rarr;{$row->stopdate|strtotime|format_date:'strfdaymonthyearshort'}</span>
            {else}
                <span class="date"> {str tag=after} {$row->startdate|strtotime|format_date:'strfdaymonthyearshort'}</span>
            {/if}
        {elseif $row->stopdate}
            <span class="date"> {str tag=before} {$row->stopdate|strtotime|format_date:'strfdaymonthyearshort'}</span>
        {/if}
        {if $row->secreturls}{str tag=secreturls section=view} ({$row->secreturls}){/if}
        {if !$dwoo.foreach.ags.last}, {/if}
    {/foreach}
    {/strip}
{/if}
