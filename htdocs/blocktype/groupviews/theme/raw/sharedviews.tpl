{*
   I wanted to put author_link_index in templates/author.tpl, but its
   state is non-persistent. So until Dwoo gets smarter...
*}
{assign var='author_link_index' value=1}
{foreach from=$items item=view}
    <div class="{cycle values='r0,r1'} listrow">
    {if $view.template}
        <div class="s fr">{$view.form|safe}</div>
    {/if}
        <h4 class="title"><a href="{$view.fullurl}">{$view.title}</a>
        {if $view.sharedby}
            <span class="owner"> {str tag=by section=view}
                {if $view.group}
                    <a href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
                {elseif $view.owner}
                    {if $view.anonymous}
                        {if $view.staff_or_admin}
                            {assign var='realauthor' value=$view.sharedby}
                            {assign var='realauthorlink' value=profile_url($view.user)}
                        {/if}
                        {assign var='author' value=get_string('anonymoususer')}
                        {include file=author.tpl}
                        {if $view.staff_or_admin}
                            {assign var='author_link_index' value=`$author_link_index+1`}
                        {/if}
                    {else}
                        <a href="{profile_url($view.user)}">{$view.sharedby}</a>
                    {/if}
                {else}
                    {$view.sharedby}
                {/if}
            </span>
        {/if}
        </h4>
        <div class="detail">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
     {if $view.tags}
        <div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$view.owner tags=$view.tags}</div>
     {/if}
    </div>
{/foreach}
