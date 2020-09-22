<div id="collectionnavwrap" class="collection-nav">

    <span id="collectionbtns" class="collection-nav-btns">
        <nav class="custom-dropdown dropdown">
            {foreach from=$collection item=view name=page}
                {if $viewid && $view->view == $viewid}
                    {$currentindex = $dwoo.foreach.page.index}
                {/if}
            {/foreach}
            <span class="picker form-control" tabindex="0" data-toggle="collapse" data-target="#pagelist" aria-expanded="false" role="button" aria-controls="#pagelist">{str tag="viewingpage" section="collection"}<span id="currentindex" data-currentindex="{$currentindex}">{$currentindex + 1}</span>/{count($collection)}</span>

            <ul id="pagelist" class="collapse">
                {foreach from=$collection item=view name=page}
                <li>
                    {if $viewid && $view->view == $viewid}
                        {$currentindex = $dwoo.foreach.page.index}
                        <span data-index="{$dwoo.foreach.page.index}" data-location="{$view->fullurl}">{$view->title}</span>
                    {else}
                        <a class="colnav"
                        data-index="{$dwoo.foreach.page.index}"
                        data-location="{$view->fullurl}"
                        onclick="fetch_collection_page({$view->view})" href="{$view->fullurl}">{$view->title|str_shorten_text:30:true}</a>
                    {/if}
                </li>
                {/foreach}
            </ul>
        </nav>

      {if count($collection) > 1}
          <button type="button" class="btn btn-secondary prevpage disabled" title='{str tag="prevpage"}'>
              <span class="icon left icon-chevron-left" role="presentation" aria-hidden="true"></span>
          </button>
          <button type="button" class="btn btn-secondary nextpage disabled" title='{str tag="nextpage"}'>
              <span class="icon right icon-chevron-right" role="presentation" aria-hidden="true"></span>
          </button>
      {/if}
    </span>
</div>
<script>
function fetch_collection_page(view) {
    {literal}var params = {};{/literal}
    params.id = view;
    sendjsonrequest(config.wwwroot + 'collection/viewcontent.json.php', params, 'POST', function(data) {
        showPreview('big', data);
        collection_nav_init(true);
    });
}

jQuery(function($) {
    collection_nav_init(true);
});
</script>