{include file="header.tpl"}

{if $GROUP}<h2>{str tag=groupviews section=view}</h2>
{/if}
            <div class="rbuttons{if $GROUP} pagetabs{/if}">
                {$createviewform|safe}
                <form method="post" action="{$WWWROOT}view/choosetemplate.php">
                    <input type="submit" class="submit" value="{str tag="copyaview" section="view"}">
{if $GROUP}
                    <input type="hidden" name="group" value="{$GROUP->id}" />
{elseif $institution}
                    <input type="hidden" name="institution" value="{$institution}">
{/if}
                </form>
            </div>
{if $institution}                {$institutionselector|safe}{/if}

{$searchform|safe}

{if $views}
            <table id="myviews" class="fullwidth listing">
                <tbody>
{foreach from=$views item=view}
                    <tr class="{cycle values='r0,r1'}">
                        <td>
{if $view.type == 'profile'}
                            <h3><a href="{$WWWROOT}user/view.php">{str tag=profileviewtitle section=view}</a></h3>
{elseif $view.type == 'dashboard'}
                            <h3><a href="{$WWWROOT}">{str tag=dashboardviewtitle section=view}</a></h3>
{elseif $view.type == 'grouphomepage'}
                            <h3><a href="{$WWWROOT}group/view.php?id={$GROUP->id}">{str tag=grouphomepage section=view}</a></h3>
{else}
                            <h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title}</a></h3>
{/if}

{if $view.submittedto}
                              <div class="submitted-viewitem">{$view.submittedto|clean_html|safe}</div>
{elseif $view.type == 'profile'}
                              <div class="videsc">{str tag=profiledescription}</div>
{elseif $view.type == 'dashboard'}
                              <div class="videsc">{str tag=dashboarddescription}</div>
{elseif $view.type == 'grouphomepage'}
                              <div class="videsc">{str tag=grouphomepagedescription section=view}</div>
{elseif $view.description}
                              <div class="videsc">{$view.description|str_shorten_html:110:true|strip_tags|safe}</div>
{/if}
                        </td>
                        <td class="right buttonscell btns2">
{if !$view.submittedto}
                                <a href="{$WWWROOT}view/blocks.php?id={$view.id}" title="{str tag ="editcontentandlayout" section="view"}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
{/if}
{if !$view.submittedto && $view.removable}
                                <a href="{$WWWROOT}view/delete.php?id={$view.id}" title="{str tag=deletethisview section=view}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a>
{/if}
                        </td>{* rbuttons *}
                    </tr>
{/foreach}
                </tbody>
            </table>
{$pagination|safe}
{else}
            <div class="message">{if $GROUP}{str tag="noviewstosee" section="group"}{elseif $institution}{str tag="noviews" section="view"}{else}{str tag="youhavenoviews" section="view"}{/if}</div>
{/if}
{include file="footer.tpl"}
