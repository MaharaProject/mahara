    <table id="collectionviews" class="fullwidth grid">
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
                    <td><label><a href="{$view->fullurl}">{$view->title}</a></label></td>
                    <td><div class="fr">{$view->remove|safe}</div></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
