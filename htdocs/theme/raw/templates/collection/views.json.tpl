<ol class="list-group" id="collectionviews">

    {foreach from=$views.views item=view}
        <li class="list-group-item" id="row_{$view->view}"> 
            {if $views.count > 1}
                {if $view->displayorder == $views.min}
                    
                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                        <span class="icon icon-long-arrow-down prs"></span>
                    </a>
                
                {elseif $view->displayorder == $views.max}
                
                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                        <span class="icon icon-long-arrow-up prs"></span>
                    </a>
                
                {else}
            
                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                        <span class="icon icon-long-arrow-up prs"></span>
                    </a>
                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                        <span class="icon icon-long-arrow-down "></span>
                    </a>
                {/if}   
            {/if}
            <strong>
                <a href="{$view->fullurl}" class="text-link">
                    {$view->title}
                </a>
            </strong>
            {$view->remove|safe}
        </li>
    {/foreach}

</ol>
