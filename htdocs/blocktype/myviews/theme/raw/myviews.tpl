{if $VIEWS}
    <table id="userviewstable">
    {foreach from=$VIEWS item=item name=view}
        <tr>
            <td class="{cycle values='r0,r1'}">
                <h4><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title}</a></h4>
                {if $item->description}
                  <div>{$item->description|str_shorten_html:100:true|safe}</div>
                {/if}
                {if $item->tags}
                  <div class="tags s"><strong>{str tag=tags}:</strong> {list_tags owner=$item->owner tags=$item->tags}</div>
                {/if}
                {if $item->artefacts}
                  <div class="artefacts s">
                    <strong>{str tag="artefacts" section="view"}:</strong>
                    {foreach from=$item->artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$item->id}" class="link-artefacts">{$artefact.title}</a>{if !$.foreach.artefacts.last}, {/if}{/foreach}
                  </div>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    {str tag='noviewstosee' section='group'}
{/if}

