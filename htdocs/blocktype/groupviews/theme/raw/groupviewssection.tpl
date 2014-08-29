{foreach from=$items item=view}
    <div class="{cycle values='r0,r1'} listrow">
    {if $view.template}
        <div class="s fr">{$view.form|safe}</div>
    {/if}
        <h4 class="title"><a href="{$view.fullurl}">{$view.title}</a>
        </h4>
        <div class="detail">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
     {if $view.tags}
        <div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$view.owner tags=$view.tags}</div>
     {/if}
    </div>
{/foreach}
