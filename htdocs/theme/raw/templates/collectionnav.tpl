<div id="collectionnavwrap" class="collection-nav">

    {if $maintitle}<div class="collection-title">{$maintitle|safe}</div>{/if}

    {* should the collection description go here? might need a read more concertina to prevent it being too long *}

    <span id="collectionbtns" class="collection-nav-btns">
      <nav class="custom-dropdown dropdown" aria-label="{str tag="Collection" section="collection"}">
        {* Get the current page index *}
        {foreach from=$collection item=view name=page}
            {if ($viewid && $view->view == $viewid) || ($view->progresscompletion && $progresscompletion && !$viewid) || ($view->framework && $framework && !$viewid)}
              {$currentindex = $dwoo.foreach.page.index}
            {/if}
        {/foreach}

        <button class="picker form-control dropdown-toggle"" type=" button" id="currentindex" data-currentindex="{$currentindex}" data-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">
          {* page title and page count *}
          {str tag="viewingpage" section="collection"}{$currentindex + 1}/{count($collection)}
        </button>

        <div id="pagelist" class="dropdown-menu" aria-labelledby="collection navigation dropdown">
          {foreach from=$collection item=view name=page}
            {if ($viewid && $view->view == $viewid)
            || ($view->progresscompletion && $progresscompletion && !$viewid)
            || ($view->framework && $framework && !$viewid)}
              {$currentindex = $dwoo.foreach.page.index}
              <li>
                <a class="dropdown-item active" href="{$view->fullurl}">{$view->title}</a>
              </li>
            {else}
              <li>
                <a class="dropdown-item" href="{$view->fullurl}" data-index="{$dwoo.foreach.page.index}"
                data-location="{$view->fullurl}">{$view->title}</a>
              </li>
            {/if}
          {/foreach}
        </div>
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
