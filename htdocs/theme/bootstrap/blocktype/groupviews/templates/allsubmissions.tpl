{foreach from=$items item=item}
    <li class="list-group-item text-small text-medium">
        <a href="{$item.url}" class="outer-link">
            <span class="sr-only">{$item.name|str_shorten_text:60:true}</span>
        </a>

        {$item.name|str_shorten_text:60:true}

        <span class="owner metadata inner-link"> -
            {str tag=by section=view}
            <a href="{$item.ownerurl}" class="text-success">
            {$item.ownername}
            </a>
        </span>
        
        {* submittedstatus == '2' is equivalent to PENDING_RELEASE *}
        <small class="detail mts">
            {str tag=timeofsubmission section=view}: 
            {$item.submittedtime|format_date} 
            
            {if $item.submittedstatus == '2'}-
            {str tag=submittedpendingrelease section=view}
            {/if}
        </small>

    </li>
{/foreach}
