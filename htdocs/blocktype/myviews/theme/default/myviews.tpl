{if $VIEWS}
    <table id="userviewstable">
    {foreach from=$VIEWS item=item name=view}
        <tr>
            <td class="r{cycle values=0,1}">
                <h4><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title|escape}</a></h4>
                <span>
                {if $item->description}
                    {$item->description}
                {/if}
                {if $item->description && $item->artefacts}<br>{/if}
                {if $item->artefacts}
                    <strong>{str tag="artefacts" section="view"}:</strong>
                    {foreach from=$item->artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$item->id}" class="link-artefacts">{$artefact.title|escape}</a>{if !$smarty.foreach.artefacts.last}, {/if}{/foreach}
                {/if}
                </span>
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    {str tag='noviewstosee' section='group'}
{/if}
