{foreach from=$items item=view}
    <li class="list-group-item">
        <a href="{$view.fullurl}" class="outer-link">
              <span class="sr-only">{$view.title}</span>
        </a>
        <h5 class="text-inline">{$view.title}</h5>
            {if $view.sharedby}
                <span class="owner inner-link text-small text-midtone">
                    {str tag=by section=view}
                     {if $view.group}
                        <a href="{group_homepage_url($view.groupdata)}" class="text-success text-small">
                            {$view.sharedby}
                        </a>,
                    {elseif $view.owner}
                        {if $view.anonymous}
                                {if $view.staff_or_admin}
                                    {assign var='realauthor' value=$view.sharedby}
                                    {assign var='realauthorlink' value=profile_url($view.user)}
                                {/if}
                                {assign var='author' value=get_string('anonymoususer')}
                                {include file=author.tpl},
                        {else}
                            <a href="{profile_url($view.user)}" class="text-success text-small">
                                {$view.sharedby}
                            </a>,
                        {/if}
                    {else}
                        {$view.sharedby}
                    {/if}
                </span>

                <span class="postedon text-small text-midtone">
                {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
                {$view.mtime|strtotime|format_date: 'strftimedate'}
                </span>
            {/if}

            {if $view.description}
            <small class="">
                {$view.description|str_shorten_html:100:true|strip_tags|safe}
            </small>
            {/if}

            {if $view.tags}
                <small class="tags mt0">
                    <strong class="">{str tag=tags}:</strong>
                    <span class="inner-link">
                        {list_tags owner=$view.owner tags=$view.tags}
                    </span>
                </small>
            {/if}
    </li>
    {if $view.template}
        <li class="list-group-item text-small list-group-item-default">
            {$view.form|safe}
        </li>
    {/if}
{/foreach}
