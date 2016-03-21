{if $views}
<div class="list-group">
    {foreach from=$views item=view}
    <div class="list-group-item">
        <h4 class="list-group-item-heading text-inline">
            <a href="{$view.fullurl}">{$view.title}</a>
        </h4>
        <span class="text-small text-midtone">{if $view.collid}({str tag=nviews section=view arg1=$view.numpages}){/if}</span>
        {if $view.sharedby}
        <div class="groupuserdate text-small">
            {if $view.group && $loggedin}
            <a href="{$view.groupdata->homeurl}" class="text-link">
                {$view.sharedby}
            </a>
            {elseif $view.owner && $loggedin}
                {if $view.anonymous}
                    {if $view.staff_or_admin}
                    {assign var='realauthor' value=$view.sharedby}
                    {assign var='realauthorlink' value=profile_url($view.user)}
                {/if}

                {assign var='author' value=get_string('anonymoususer')}

                {include file=author.tpl}
            {else}
                <a href="{profile_url($view.user)}" class="text-link">
                    {$view.sharedby}
                </a>
            {/if}
        {else}
            {$view.sharedby}
        {/if}
            <span class="postedon text-midtone">
                - {if $view.mtime == $view.ctime}
                    {str tag=Created}
                {else}
                    {str tag=Updated}
                {/if}
                {$view.mtime|strtotime|format_date:'strftimedate'}
            </span>
        </div>
        {if $view.description}
        <p class="detail list-group-item-text text-small">
            {$view.description|str_shorten_html:100:true|strip_tags|safe}
        </p>
        {/if}
    </div>
    {/foreach}
</div>
{else}
<div class="panel-body">
    <p class="lead text-small">{str tag=noviews section=view}</p>
</div>
{/if}
