<div id="collectionviews" class="fullwidth grid">
        {foreach from=$views.views item=view}
            <div class="{cycle values='r0,r1'} collectionpage" id="row_{$view->view}">
                {if $views.count > 1}
                <div class="displayordercontrols btns2">
                    {if $view->displayorder == $views.min}
                        <div id="viewdisplayorder_{$view->view}" class="justdown">
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down"><img src="{theme_image_url filename='btn_movedown'}" alt="{str tag=moveitemdown}" ></a>
                        </div>
                    {elseif $view->displayorder == $views.max}
                        <div id="viewdisplayorder_{$view->view}" class="justup">
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up"><img src="{theme_image_url filename='btn_moveup'}" alt="{str tag=moveitemup}" ></a>
                        </div>
                    {else}
                        <div id="viewdisplayorder_{$view->view}">
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up"><img src="{theme_image_url filename='btn_moveup'}" alt="{str tag=moveitemup}" ></a>
                            <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down"><img src="{theme_image_url filename='btn_movedown'}" alt="{str tag=moveitemdown}" ></a>
                        </div>
                    {/if}
                </div>
                {else}
                    <span>&nbsp;</span>
                {/if}
                    <strong>
                        <a href="{$view->fullurl}">
                            {$view->title}
                        </a>
                    </strong>
                    <div class="fr removepage">
                        {$view->remove|safe}
                    </div>
            </div>
        {/foreach}
</div>
