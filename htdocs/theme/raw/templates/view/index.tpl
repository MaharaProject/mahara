{include file="header.tpl"}
    {if !$noedit}
    <div class="btn-top-right btn-group btn-group-top {if $GROUP} pagetabs{/if}">
      {if !$outcomesgroup || $role === 'admin'}
        <button id="addview-button" class="btn btn-secondary" type="button" data-bs-target="{$WWWROOT}view/editlayout.php?new=1{$urlparamsstr}" >
        <span class="icon icon-plus left" role="presentation" aria-hidden="true" ></span>
        {str section=mahara tag=Create}
        </button>
      {/if}
      {if !$outcomesgroup}
        <button id="copyview-button" class="btn btn-secondary" type="button" data-url="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">
            <span class="icon icon-regular icon-clone left" role="presentation" aria-hidden="true"></span>
            {str section=mahara tag=copy}
        </button>
      {/if}
    </div>
    {/if}
    {$searchform|safe}

    <div class="grouppageswrap view-container">

            {if $views}
                <div id="myviews" class="row">
                {$viewresults|safe}
                </div>
            {else}
                <div class="no-results">
                    {if $GROUP}
                        {str tag="noviewstosee" section="group"}
                    {elseif $institution}
                        {str tag="noviews2" section="view"}
                    {else}
                        {str tag="youhavenoviews2" section="view"}
                    {/if}
                </div>
            {/if}

    </div>
    {* Modal form *}
    <div tabindex="0" class="modal fade" id="addview-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                    <h1 class="modal-title">
                        <span class="icon icon-plus"></span>
                        {str tag=confirmaddtitle1 section=view}
                    </h1>
                </div>
                <div class="modal-body">
                    <p>{str tag=confirmadddesc section=view}</p>
                    <div class="btn-group">
                        <button id="add-collection-button" type="button" class="btn btn-secondary"><span class="icon icon-folder-open"></span> {str tag=Collection section=collection}</button>
                        <button id="add-view-button" type="button" class="btn btn-secondary"><span class="icon icon-regular icon-file-alt"></span> {str tag=view}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

    var addurl = $j("#addview-button").attr('data-bs-target');

    $j("#addview-button").on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $j("#addview-form").modal('show');
    });

    $j("#add-view-button").on('click', function() {
        window.location = addurl;
    });
    $j("#add-collection-button").on('click', function() {
        // redirect to the collection section
        var url = addurl.replace(/view\/editlayout/, 'collection/edit');
        window.location = url;
    });

    $('.modal').on('shown.bs.modal', function() {
        $('#add-collection-button').trigger("focus");
    });
    $('.modal').on('d-none.bs.modal', function() {
        $('#addview-button').trigger("focus");
    });
    </script>

{include file="footer.tpl"}
