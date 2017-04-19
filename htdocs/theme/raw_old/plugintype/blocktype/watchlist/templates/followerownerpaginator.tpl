    {foreach $views as item=owner}
        <li class="list-group-item">
            <h4 class="title list-group-item-heading">
                <span class="icon icon-user"></span>
                <b>{$owner[0].sharedby}</b>
            </h4>
        </li>
        {foreach $owner as item=view}
            <li class="list-group-item">
                <a href="{$view.fullurl}" class=" outer-link watchlist-showview">
                    <span class="sr-only">{$view.title}</span>
                </a>
                <h4 class="title list-group-item-heading">
                    {$view.title}
                </h4>
                {if $view.sharedby}
                    <div class="groupuserdate text-small">
                    {if $view.group && $loggedin}
                    <a class="inner-link" href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
                    {elseif $view.owner && $loggedin}
                        {if $view.anonymous}
                            {if $view.staff_or_admin}
                                {assign var='realauthor' value=$view.sharedby}
                                {assign var='realauthorlink' value=profile_url($view.user)}
                            {/if}
                            {assign var='author' value=get_string('anonymoususer')}
                            {include file=author.tpl}
                        {else}
                            <a class="inner-link" href="{profile_url($view.user)}">{$view.sharedby}</a>
                        {/if}
                    {else}
                        {$view.sharedby}
                    {/if}
                    <span class="postedon text-midtone">
                    - {str tag=Updated} {$view.mtime|strtotime|format_date:'strftimedatetime'}
                    </span>
                    </div>
                {/if}
            </li>
        {/foreach}
    {/foreach}
