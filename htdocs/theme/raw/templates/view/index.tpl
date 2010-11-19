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

{if $views}
            <table id="myviews" class="fullwidth listing">
                <tbody>
{foreach from=$views item=view}
                    <tr class="{cycle values='r0,r1'}">
                        <td><div class="rel">
{if $view.type == 'profile'}
                            <h3><a href="{$WWWROOT}user/view.php">{str tag=profileviewtitle section=view}</a></h3>
{elseif $view.type == 'dashboard'}
                            <h3><a href="{$WWWROOT}">{str tag=dashboardviewtitle section=view}</a></h3>
{elseif $view.type == 'grouphomepage'}
                            <h3><a href="{$WWWROOT}group/view.php?id={$GROUP->id}">{str tag=grouphomepage section=view}</a></h3>
{else}
                            <h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title}</a></h3>
{/if}
                            <a class="nojs-hidden-inline expand" href="{$expand}" id="details-{$view.id}"><img src="{theme_url filename='images/expand-down.gif'}" alt="[+]"></a>
                            <div class="rbuttons">
{if !$view.submittedto}
                              <a class="icon btn-edit editink" href="{$WWWROOT}view/blocks.php?id={$view.id}" title="{str tag ="editcontentandlayout" section="view"}">{str tag=edit}</a>
{/if}
                              <div class="viewcontrol">
{if !$view.submittedto && $view.removable}
                                <a href="{$WWWROOT}view/delete.php?id={$view.id}" title="{str tag=deletethisview section=view}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a>
{/if}
                              </div>
                              <div class="viewcontrol">
{if !$view.togglepublic && $view.type != 'profile' && $view.type != 'dashboard' && $view.type != 'grouphomepage'}
                                <a href="{$WWWROOT}view/access.php?id={$view.id}" title="{str tag=editaccess section=view}"><img src="{theme_url filename='images/icon_access.gif'}" alt="{str tag=editaccess}"></a>
{/if}
                              </div>
                            </div>{* rbuttons *}
                            <div class="js-hidden expanded" id="details-{$view.id}-expand">
{if $view.submittedto}
                              <div class="submitted-viewitem">{$view.submittedto|clean_html|safe}</div>
{elseif $view.type == 'profile'}
                              <div class="videsc">{str tag=profiledescription}</div>
{elseif $view.type == 'dashboard'}
                              <div class="videsc">{str tag=dashboarddescription}</div>
{elseif $view.type == 'grouphomepage'}
                              <div class="videsc">{str tag=grouphomepagedescription section=view}</div>
{else}
  {if $view.description}
                              <div class="videsc">{$view.description|clean_html|safe}</div>
  {/if}
                              <div class="videsc"><a class="btn-edit" href="{$WWWROOT}view/edit.php?id={$view.id}">{str tag="edittitleanddescription" section="view"}</a></div>
{/if}
{if $view.tags}
                              <div class="videsc">
                                <div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>
                              </div>
{/if}
{if $view.collection}
                              <div class="videsc">
                                <div class="collection"><label>{str tag=Collection section=collection}:</label> <a href="{$WWWROOT}collection/views.php?id={$view.collection->id}">{$view.collection->name}</a></div>
                              </div>
{/if}
{if $view.artefacts}
                              <div class="videsc">
                                <div class="artefacts"><label>{str tag="artefacts" section="view"}:</label>
                                {foreach from=$view.artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$view.id}" id="link-artefacts">{$artefact.title}</a>{if !$.foreach.artefacts.last}, {/if}{/foreach}</div>
                              </div>
{/if}
{if $view.submitto}
                              <div class="submit-viewitem">{$view.submitto|safe}</div>
{/if}
                              {if $view.togglepublic}{$view.togglepublic|safe}{/if}
                           </div>
                        </div></td>
                    </tr>
{/foreach}
                </tbody>
            </table>
{$pagination|safe}
{else}
            <div class="message">{if $GROUP}{str tag="noviewstosee" section="group"}{elseif $institution}{str tag="noviews" section="view"}{else}{str tag="youhavenoviews" section="view"}{/if}</div>
{/if}
{include file="footer.tpl"}
