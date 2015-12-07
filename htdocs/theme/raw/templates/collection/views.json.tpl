<ol class="list-group" id="collectionviews">
    {foreach from=$views.views item=view}
        <li class="list-group-item" id="row_{$view->view}">
            {if $views.count > 1}
                {if $view->displayorder == $views.min}
                    <a class="btn btn-xs text-default order-sort-control single-arrow-down" href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                        <span class="icon icon-lg icon-long-arrow-down text-midtone" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=moveitemdown}</span>
                    </a>
                {elseif $view->displayorder == $views.max}
                    <a class="btn btn-xs text-default order-sort-control single-arrow-up text-midtone" href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                        <span class="icon icon-lg icon-long-arrow-up left" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=moveitemup}</span>
                    </a>
                {else}
                    <a class="btn btn-xs text-default order-sort-control" href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                        <span class="icon icon-lg icon-long-arrow-up left text-midtone" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=moveitemup}</span>
                    </a>
                    <a class="btn btn-xs text-default order-sort-control" href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                        <span class="icon icon-lg icon-long-arrow-down text-midtone" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=moveitemdown}</span>
                    </a>
                {/if}
            {/if}
            <a href="{$view->fullurl}" class="text-link">
                {$view->title}
            </a>
            {$view->remove|safe}
        </li>
    {/foreach}
</ol>
