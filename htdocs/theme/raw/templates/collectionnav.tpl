<div id="collectionnavwrap" class="collection-nav">

    {if $maintitle}<h2>{$maintitle|safe}</h2>{/if}

    {* should the collection description go here? might need a read more concertina to prevent it being too long *}

    <span id="collectionbtns" class="collection-nav-btns">
      <nav class="custom-dropdown dropdown">
          {foreach from=$collection item=view name=page}
              {if $view->view == $viewid}
                  {$currentindex = $dwoo.foreach.page.index}
              {/if}
          {/foreach}
          <span class="picker form-control" tabindex="0" data-toggle="collapse" data-target="#pagelist" aria-expanded="false" role="button" aria-controls="#pagelist">{str tag="viewingpage" section="collection"}<span id="currentindex" data-currentindex="{$currentindex}">{$currentindex + 1}</span>/{count($collection)}</span>
          <ul id="pagelist" class="collapse">
              {foreach from=$collection item=view name=page}
              <li>
                  {if $view->view == $viewid}
                      {$currentindex = $dwoo.foreach.page.index}
                      <span data-index="{$dwoo.foreach.page.index}" data-location="{$view->fullurl}">{$view->title}</span>
                  {else}
                      <a href="{$view->fullurl}" data-index="{$dwoo.foreach.page.index}" data-location="{$view->fullurl}">{$view->title}</a>
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
    {if $gradeselect}
        {$gradeselect}
    {/if}
</div>
