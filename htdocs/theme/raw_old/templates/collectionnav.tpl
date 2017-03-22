<div id="collectionnavwrap" class="collection-nav">
    {if count($collection) > 1}
        <button type="button" class="btn btn-default prevpage invisible">
            <span class="icon left icon-chevron-left" role="presentation" aria-hidden="true"></span>
            {str tag="prevpage" section="collection"}
        </button>
        <button type="button" class="btn btn-default nextpage invisible">   {str tag="nextpage" section="collection"}
            <span class="icon right icon-chevron-right" role="presentation" aria-hidden="true"></span>
        </button>
    {/if}

    {if $maintitle}<h2>{str tag="Collection" section="collection"}: {$maintitle|safe}</h2>{/if}

    {* should the collection description go here? might need a read more concertina to prevent it being too long *}

    <p class="navlabel">{str tag="navtopage" section="collection"}</p>
    <nav class="custom-dropdown dropdown">
        <ul class="hidden">
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
        <span class="picker form-control">{str tag="viewingpage" section="collection"}<span id="currentindex" data-currentindex="{$currentindex}">{$currentindex + 1}</span>/{count($collection)}</span>
    </nav>
</div>
