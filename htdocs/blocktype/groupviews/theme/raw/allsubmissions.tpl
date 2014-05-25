{foreach from=$items item=item}
    <div class="{cycle values='r0,r1'} listrow">
        <h4 class="title"><a href="{$item.url}">{$item.name|str_shorten_text:60:true}</a>
        <span class="owner">{str tag=by section=view} <a href="{$item.ownerurl}">{$item.ownername}</a></span></h4>
        <div class="detail">{str tag=timeofsubmission section=view}: {$item.submittedtime|format_date}</div>
    </div>
{/foreach}
