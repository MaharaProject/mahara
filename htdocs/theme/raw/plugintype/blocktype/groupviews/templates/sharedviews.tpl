{foreach from=$items item=view}
    <li class="list-group-item flush">
        <h4 class="list-group-item-heading text-inline">
            <a href="{$view.fullurl}">{$view.title}</a>
        </h4>
        <br>
        {if $view.sharedby}
        <span class="owner inner-link text-small text-midtone">
            {if $view.group}
                <a href="{$view.groupdata->homeurl}" class="text-small">
                     {$view.sharedby}
                </a>
            {elseif $view.owner}
                {if $view.anonymous}
                    {if $view.staff_or_admin}
                        {assign var='realauthor' value=$view.sharedby}
                        {assign var='realauthorlink' value=profile_url($view.user)}
                    {/if}
                    {assign var='author' value=get_string('anonymoususer')}
                    {include file=author.tpl}
                {else}
                    <a href="{profile_url($view.user)}" class="text-small">
                        {$view.sharedby}
                    </a>
                {/if}
            {else}
                {$view.sharedby}
            {/if}
        </span>
        {/if}
        <span class="postedon text-small text-midtone">
            - {if $view.mtime == $view.ctime}
                {str tag=Created}
            {else}
                {str tag=Updated}
            {/if}
            {$view.mtime|strtotime|format_date}
        </span>

        {if $view.template}
            <div class="grouppage-form">
                <div class="btn-group btn-group-top only-button">
                    {$view.form|safe}
                </div>
            </div>
        {/if}

        {if $view.description}
        <div class="detail text-small">
            {$view.description|str_shorten_html:100:true|strip_tags|safe}
        </div>
        {/if}

        {if $view.tags}
            <div class="tags text-small">
                <strong>{str tag=tags}:</strong>
                <span>
                    {list_tags owner=$view.owner tags=$view.tags}
                </span>
            </div>
        {/if}
    </li>
{/foreach}
