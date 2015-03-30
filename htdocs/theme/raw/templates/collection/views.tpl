{include file="header.tpl"}
{if $GROUP}
    <h2>{$PAGESUBHEADING}{if $SUBPAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h2>
{/if}
<div id="collectionpageswrap">
<div id="draginstruction">{str tag=collectiondragupdate1 section=collection}</div>
<div id="collectionpages">
<fieldset id="pagestoadd">
<legend>{str tag=addviewstocollection section=collection}</legend>
{if $viewsform}
    <div class="right"><a href="" id="selectall">{str tag=All}</a>&nbsp;&nbsp;<a href="" id="selectnone">{str tag=none}</a></div>
    {$viewsform|safe}
{else}
    {str tag=noviewsavailable section=collection}
{/if}
</fieldset>
<fieldset id="pagesadded">
<legend>{str tag=viewsincollection section=collection}</legend>
{if !$views}
    <div class="message">{str tag=noviews section=collection}</div>
{else}
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
{/if}
</fieldset>
<div class="cb"></div>
</div></div>
<div id="collectiondonewrap"><a class="btn" href="{$baseurl}">{str tag=done}</a></div>

{include file="footer.tpl"}
