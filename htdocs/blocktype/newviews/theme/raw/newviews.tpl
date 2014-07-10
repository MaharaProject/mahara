{if $views}
  <div class="viewlist fullwidth listing">
  {*
     I wanted to put author_link_index in templates/author.tpl, but its
     state is non-persistent. So until Dwoo gets smarter...
  *}
  {assign var='author_link_index' value=1}
  {foreach from=$views item=view}
    <div class="{cycle values='r0,r1'} listrow">
            <h3 class="title"><a href="{$view.fullurl}">{$view.title}</a></h3>
            <div class="detail">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
            {if $view.sharedby}
            <div class="groupuserdate">
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
                    {if $view.staff_or_admin}
                      {assign var='author_link_index' value=`$author_link_index+1`}
                    {/if}
                  {else}
                    <a href="{profile_url($view.user)}">{$view.sharedby}</a>
                  {/if}
                {else}
                  {$view.sharedby}
                {/if}
             	<span class="postedon">
                  - {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
                  {$view.mtime|strtotime|format_date:'strftimedate'}</span>
            </div>
            {/if}
        </div>
  {/foreach}
  </div>
{else}
  {str tag=noviews section=view}
{/if}
