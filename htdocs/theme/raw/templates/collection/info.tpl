{include file="header.tpl"}
  <div class="group-info">
        <div class="fr">
            <ul class="groupuserstatus">
                <li><a href="{$WWWROOT}collection/edit.php?id={$collection->id}" class="btn-edit">{str tag=edit}</a></li>
                <li><a href="{$WWWROOT}collection/delete.php?id={$collection->id}" class="btn-del">{str tag=delete}</a></li>
            </ul>
        </div>
        <ul>
            <li><div>{$collection->description}</div></li>
            <li><label>{str tag=created section=collection}: </label> {$collection->ctime}</li>
            <li><label>{str tag=views section=collection}: </label>
            {if $views}
                {foreach from=$views item=view}
                    <a href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title}</a>,
                {/foreach}
            {else}
                {str tag=none}
            {/if}
            </li>
        </ul>
    </div>
{include file="footer.tpl"}
