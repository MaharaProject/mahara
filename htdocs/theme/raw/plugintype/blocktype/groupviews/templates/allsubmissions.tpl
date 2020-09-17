{foreach from=$items item=item}
    <li class="list-group-item flush">
        <h4 class="list-group-item-heading"><a href="{$item.url}">{$item.name|str_shorten_text:60:true}</a></h4>
        <span class="owner metadata inner-link text-small">
            {str tag=by section=view}
            <a href="{$item.ownerurl}" class="text-link text-small">
            {$item.ownername}
            </a>
        </span>

        {* submittedstatus == '2' is equivalent to PENDING_RELEASE *}
        <div class="detail text-small text-midtone">
            {str tag=timeofsubmission section=view}:
            {$item.submittedtime|format_date}

            {if $item.submittedstatus == '2'}-
            {str tag=submittedpendingrelease section=view}
            {/if}
        </div>

    </li>
{/foreach}
