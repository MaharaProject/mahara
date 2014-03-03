  {foreach from=$data item=result}
    <div class="{cycle name=rows values='r0,r1'} listrow">
        <div class="listrowright">
          <div class="icon-container s">{if $result->icon}<img src="{$result->icon}" alt="{$result->typestr}">{/if}</div>
          <h3 class="title"><a href="{$result->url}">{$result->title}</a><span class="filetype"> ({$result->typestr})</span></h3>
          <div class="postedon">{$result->ctime}</div>
          <div class="detail">{$result->description|str_shorten_html:100|strip_tags|safe}</div>
          {if $result->tags}
          <div class="tags">{str tag=tags}: {list_tags tags=$result->tags owner=$owner}</div>
          {/if}
        </div>
    </div>
  {/foreach}

