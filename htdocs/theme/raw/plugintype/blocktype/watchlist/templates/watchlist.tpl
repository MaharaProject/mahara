{if $watchlistempty}
<div class="panel-body">
    <p class="lead text-small">{str tag=nopages section=blocktype.watchlist}</p>
</div>
{else}
<ul id="watchlistblock" class="viewlist list-group">
    {*
      I wanted to put author_link_index in templates/author.tpl, but its
      state is non-persistent. So until Dwoo gets smarter...
    *}
    {assign var='author_link_index' value=1}
    {foreach $views as item=view}
        <li class="{cycle values='r0,r1'} list-group-item">
            <h4 class="title list-group-item-heading">
                 <a href="{$view->fullurl}" class="watchlist-showview">
                    {$view->title}
                </a>
            </h4>
            {if $view->sharedby}
                <div class="groupuserdate text-small">
                {if $view->group && $loggedin}
                    <a class="text-link" href="{group_homepage_url($view->groupdata)}">{$view->sharedby}</a>
                {elseif $view->owner && $loggedin}
                    {if $view->anonymous}
                        {if $view->staff_or_admin}
                            {assign var='realauthor' value=$view->sharedby}
                            {assign var='realauthorlink' value=profile_url($view->user)}
                        {/if}
                        {assign var='author' value=get_string('anonymoususer')}
                        {include file=author.tpl}
                        {if $view->staff_or_admin}
                            {assign var='author_link_index' value=$author_link_index+1}
                        {/if}
                    {else}
                        <a class="text-link" href="{profile_url($view->user)}">{$view->sharedby}</a>
                    {/if}
                {else}
                    {$view->sharedby}
                {/if}
                <span class="postedon text-midtone">
                - {if $view->mtime == $view->ctime}{str tag=Created}{else}{str tag=Updated}{/if}
                {$view->mtime|strtotime|format_date:'strftimedate'}</span>
                </div>
            {/if}
        </li>
    {/foreach}
</ul>
{/if}
