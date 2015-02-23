{include file="header.tpl"}
    {if $GROUP}
        <h2>{$PAGESUBHEADING}{if $SUBPAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h2>
    {/if}

    <div class="row" id="collectionpages">
        <div class="col-md-9">
            <p class="lead">{str tag=collectiondragupdate1 section=collection}</p>
        </div>
        <div class="col-md-12">
            <fieldset class="panel panel-default panel-half first" id="pagestoadd">
                <h3 class="panel-heading">
                    {str tag=addviewstocollection section=collection}
                </h3>
                <div class="panel-body">
                    {if $viewsform}
                        <div class="pull-right">
                            <a href="" id="selectall">{str tag=All}</a>&nbsp;&nbsp;
                            <a href="" id="selectnone">{str tag=none}</a>
                        </div>
                        {$viewsform|safe}
                    {else}
                        <div class="message">{str tag=noviewsavailable section=collection}</div>
                    {/if}
                </div>
            </fieldset>
            <fieldset class="panel panel-default panel-half" id="pagesadded">
                <h3 class="panel-heading">{str tag=viewsincollection section=collection}</h3>
                {if !$views}
                    <div class="panel-body">
                        <div class="message dropzone-previews full-width">
                            <div class="dz-message">
                                {str tag=noviews section=collection}
                            </div>
                        </div>
                    </div>
                {else}
                    <table id="collectionviews" class="table">
                        <tbody>
                            {foreach from=$views.views item=view}
                                <tr class="{cycle values='r0,r1'}" id="row_{$view->view}">
                                    {if $views.count > 1}
                                    <td class="displayordercontrols btns2">
                                        {if $view->displayorder == $views.min}
                                            <div id="viewdisplayorder_{$view->view}" class="justdown">
                                                <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down"><img src="{theme_url filename='images/btn_movedown.png'}" alt="{str tag=moveitemdown}" ></a>
                                            </div>
                                        {elseif $view->displayorder == $views.max}
                                            <div id="viewdisplayorder_{$view->view}" class="justup">
                                                <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up"><img src="{theme_url filename='images/btn_moveup.png'}" alt="{str tag=moveitemup}" ></a>
                                            </div>
                                        {else}
                                            <div id="viewdisplayorder_{$view->view}">
                                                <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up"><img src="{theme_url filename='images/btn_moveup.png'}" alt="{str tag=moveitemup}" ></a>
                                                <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down"><img src="{theme_url filename='images/btn_movedown.png'}" alt="{str tag=moveitemdown}" ></a>
                                            </div>
                                        {/if}
                                    </td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                    <td><strong><a href="{$view->fullurl}">{$view->title}</a></strong></td>
                                    <td><div class="fr">{$view->remove|safe}</div></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {/if}
            </fieldset>
        </div>
    </div>
    <div id="collectiondonewrap">
        <a class="btn btn-success" href="{$baseurl}">{str tag=done}</a>
    </div>

{include file="footer.tpl"}
