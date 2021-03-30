{include file="header.tpl"}

<div class="card bg-danger view-container">
    <h2 class="card-header">
        {str tag="deleteviewconfirm1" section="view"}
    </h2>
    <div class="card-body">
        {if $collectionnote}<p class="lead">{$collectionnote|clean_html|safe}</p>{/if}
        {if $landingpagenote}<p class="lead">{$landingpagenote}</p>{/if}
        <p>{if $view->get('owner')}
        {str tag="deleteviewconfirmbackup1" section="view" arg1=$WWWROOT arg2=$view->get('id')}
        {/if}</p>
        <p>{str tag="deleteviewconfirmnote3" section="view"}</p>
        {$form|safe}
    </div>
</div>

{include file="footer.tpl"}
