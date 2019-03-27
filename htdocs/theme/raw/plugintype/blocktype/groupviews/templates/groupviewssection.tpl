{foreach from=$items item=view}
    <li class="list-group-item">
        <a href="{$view.fullurl}" class="outer-link">
            <span class="sr-only">{$view.title}</span>
        </a>
        <div class="clearfix">
            <h5 class="float-left list-group-item-heading">
                {$view.displaytitle}
                <span class="text-small text-midtone">{if $view.collid}({str tag=nviews section=view arg1=$view.numpages}){/if}</span>
                <br>
                <span class="postedon text-small text-midtone">
                    {if $view.mtime == $view.ctime}
                        {str tag=Created}
                    {else}
                        {str tag=Updated}
                    {/if}
                    {$view.mtime|strtotime|format_date}
                </span>
            </h5>
            {if $view.template}
            <div class="grouppage-form inner-link">
                <div class="btn-group btn-group-top only-button">
                    {$view.form|safe}
                </div>
            </div>
            {/if}
        </div>

        {if $view.description}
            <div class="detail text-small">
                {$view.description|str_shorten_html:100:true|strip_tags|safe}
            </div>
        {/if}

        {if $view.tags}
            <div class="tags text-small">
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$view.owner tags=$view.tags}
            </div>
        {/if}
    </li>
{/foreach}
