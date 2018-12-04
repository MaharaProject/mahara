{include file="header.tpl"}
    {if !$noedit}
    <div class="btn-top-right btn-group btn-group-top {if $GROUP} pagetabs{/if}">
        <a id="addview-button" class="btn btn-secondary" href="{$WWWROOT}view/editlayout.php?new=1{$urlparamsstr}">
            <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
            {str section=mahara tag=add}
        </a>
        <a id="copyview-button" class="btn btn-secondary" href="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">
            <span class="icon icon-files-o icon-lg left" role="presentation" aria-hidden="true"></span>
            {str section=mahara tag=copy}
        </a>
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
                        {str tag="noviews1" section="view"}
                    {else}
                        {str tag="youhavenoviews1" section="view"}
                    {/if}
                </div>
            {/if}

    </div>
    {* Modal form *}
    <div tabindex="0" class="modal fade" id="addview-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <span class="icon icon-lg icon-plus"></span>
                        {str tag=confirmaddtitle section=view}
                    </h4>
                </div>
                <div class="modal-body">
                    <p>{str tag=confirmadddesc section=view}</p>
                    <div class="btn-group">
                        <button id="add-collection-button" type="button" class="btn btn-secondary"><span class="icon icon-folder-open"></span> {str tag=Collection section=collection}</button>
                        <button id="add-view-button" type="button" class="btn btn-secondary"><span class="icon icon-file-text"></span> {str tag=view}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

    var addurl = $j("#addview-button").attr('href');

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
