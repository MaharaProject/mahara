{foreach from=$items item=item}
    <li class="list-group-item">
        <a href="{$item.url}" class="outer-link">
            <span class="sr-only">{$item.name|str_shorten_text:60:true}</span>
        </a>

        {$item.name|str_shorten_text:60:true}

        <span class="owner metadata inner-link text-small"> -
            {str tag=by section=view}
            <a href="{$item.ownerurl}" class="text-success text-small">
            {$item.ownername}
            </a>
        </span>

        {* submittedstatus == '2' is equivalent to PENDING_RELEASE *}
        <div class="metadata mts text-small">
            {str tag=timeofsubmission section=view}:
            {$item.submittedtime|format_date}

            {if $item.submittedstatus == '2'}-
            {str tag=submittedpendingrelease section=view}
            {/if}
        </div>

    </li>
{/foreach}
