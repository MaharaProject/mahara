    {foreach $views as item=owner}
        <li class="list-group-item">
            <h3 class="title list-group-item-heading">
                <span class="icon icon-user"></span>
                <strong>{$owner[0].sharedby}</strong>
            </h3>
        </li>
        {foreach $owner as item=view}
            <li class="list-group-item">
                <h3 class="title list-group-item-heading">
                    <a href="{$view.fullurl}">{$view.title}</a>
                </h3>
                {if $view.sharedby}
                    <div class="groupuserdate text-small">
                    {if $view.group && $loggedin}
                    <a href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
                    {elseif $view.owner && $loggedin}
                        {if $view.anonymous}
                            {if $view.staff_or_admin}
                                {assign var='realauthor' value=$view.sharedby}
                                {assign var='realauthorlink' value=profile_url($view.user)}
                            {/if}
                            {assign var='author' value=get_string('anonymoususer')}
                            {include file=author.tpl}
                        {else}
                            <a href="{profile_url($view.user)}">{$view.sharedby}</a>
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
