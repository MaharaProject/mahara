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
            <div class="grouppageswrap">
{$searchform|safe}

{if $query}
                <h2 id="searchresultsheading" class="accessible-hidden">{str tag=Results}</h2>
{/if}

{if $views}
                <div id="myviews" class="listing">
{foreach from=$views item=view}
                    <div class="listrow {cycle values='r0,r1'}">
                        <h3 class="title">
                        {if $view.issitetemplate}
                            {$view.displaytitle}
                        {else}
                            <a href="{$view.fullurl}">{$view.displaytitle}</a>
                        {/if}
                        </h3>
                        <div class="fr btns2">
{if !$view.submittedto && (!$view.locked || $editlocked)}
                            <a href="{$WWWROOT}view/blocks.php?id={$view.id}&{$querystring}" title="{str tag ="editcontentandlayout" section="view"}"><img src="{theme_image_url filename='btn_edit'}" alt="{str(tag=editspecific arg1=$view.displaytitle)|escape:html|safe}"></a>
{/if}
{if !$view.submittedto && $view.removable && (!$view.locked || $editlocked)}
                            <a href="{$WWWROOT}view/delete.php?id={$view.id}&{$querystring}" title="{str tag=deletethisview section=view}"><img src="{theme_image_url filename='btn_deleteremove'}" alt="{str(tag=deletespecific arg1=$view.displaytitle)|escape:html|safe}"></a>
{/if}
                        </div>{* rbuttons *}
{if $view.submittedto}
                        <div class="detail submitted-viewitem">{$view.submittedto|clean_html|safe}</div>
{elseif $view.type == 'profile'}
                        <div class="detail">{str tag=profiledescription}</div>
{elseif $view.type == 'dashboard'}
                        <div class="detail">{str tag=dashboarddescription}</div>
{elseif $view.type == 'grouphomepage'}
                        <div class="detail">{str tag=grouphomepagedescription section=view}</div>
{elseif $view.description}
                        <div class="detail">{$view.description|str_shorten_html:110:true|strip_tags|safe}</div>
{/if}
                        <div class="cb"></div>
                    </div>
{/foreach}
                </div>
{$pagination|safe}
            </div>
{else}
            <div class="message">{if $GROUP}{str tag="noviewstosee" section="group"}{elseif $institution}{str tag="noviews" section="view"}{else}{str tag="youhavenoviews" section="view"}{/if}</div>
            </div>
{/if}
{include file="footer.tpl"}
