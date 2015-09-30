{foreach from=$data item=result}
    <div class="{cycle name=rows values='r0,r1'} list-group-item">
        <div class="row">
            <div class="col-md-8">
                {if $result->typestr == 'Page'}
                  <span class="icon icon-lg text-default pull-left mts icon-file"></span>
                {elseif $result->typestr == 'Journal entry'}
                  <span class="icon icon-lg text-default pull-left mts icon-blogpost"></span>
                {elseif $result->typestr == 'Collection'}
                  <span class="icon icon-lg text-default pull-left mts icon-folder-open"></span>
                {elseif $result->typestr == 'Image'}
                  <span class="icon icon-lg text-default pull-left mts icon-picture-o"></span>
                {elseif $result->typestr == 'Folder'}
                  <span class="icon icon-lg text-default pull-left mts icon-folder"></span>
                {elseif $result->typestr == 'Plan'}
                  <span class="icon icon-lg text-default pull-left mts icon-plans"></span>
                {elseif $result->typestr == 'Note'}
                  <span class="icon icon-lg text-default pull-left mts icon-textbox"></span>
                {else}
                  <span class="icon icon-lg text-default pull-left mts icon-tag"></span>
                {/if}
                <h3 class="list-group-item-heading title"><a href="{$result->url}" class="mls">{$result->title}</a> <span class="tag-type">({$result->typestr})</span></h3>
                <p class="mbs">{$result->ctime}</p>
                <p class="mbs">{$result->description|str_shorten_html:100|strip_tags|safe}</p>
            </div>
            <div class="col-md-4">
              {if $result->tags}
                  <div class="tags">{str tag=tags}: {list_tags tags=$result->tags owner=$owner}</div>
              {/if}
            </div>
        </div>
    </div>
{/foreach}