
{if $groupviews}
    {if $groupviews.count > 0}
        <ul id="groupviewlist" class="list-group allgroup-pages grouppages">
            {$groupviews.tablerows|safe}
        </ul>

        {if $groupviews.pagination}
        <div id="groupviews_page_container" class="d-none pagination-container">
            {$groupviews.pagination|safe}
        </div>
        {/if}

        {if $groupviews.pagination_js}
        <script>
            jQuery(function($) {literal}{{/literal}
                {$groupviews.pagination_js|safe}
                $('#groupviews_page_container').removeClass('d-none');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="card-body">
            <span class="lead text-small">{str tag=nogroupviewsyet section=view}</span>
        </div>
    {/if}
{/if}

{if $sharedviews}
    <h3 class="title">
        {str tag="viewssharedtogroup" section="view"}:
    </h3>
    {if $sharedviews.count > 0}
        <ul id="sharedviewlist" class="list-group list-group-top-border grouppages">
            {$sharedviews.tablerows|safe}
        </ul>

        {if $sharedviews.pagination}
            <div id="sharedviews_page_container" class="d-none pagination-container">
                {$sharedviews.pagination|safe}
            </div>
        {/if}

        {if $sharedviews.pagination_js}
        <script>
            jQuery(function($) {literal}{{/literal}
                {$sharedviews.pagination_js|safe}
                $('#sharedviews_page_container').removeClass('d-none');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="list-group list-group-top-border grouppages">
            <span class="list-group-item flush">{str tag=nosharedviewsyet section=view}</span>
        </div>
    {/if}
{/if}


{if $sharedcollections}
    <h3 class="title">
        {str tag="collectionssharedtogroup" section="collection"}:
    </h3>
    {if $sharedcollections.count > 0}
        <ul id="sharedcollectionlist" class="list-group list-group-top-border grouppages">
            {$sharedcollections.tablerows|safe}
        </ul>

        {if $sharedcollections.pagination}
            <div id="sharedcollections_page_container" class="d-none pagination-container">
            {$sharedcollections.pagination|safe}
            </div>
        {/if}

        {if $sharedcollections.pagination_js}
        <script>
            jQuery(function($) {literal}{{/literal}
                {$sharedcollections.pagination_js|safe}
                $('#sharedcollections_page_container').removeClass('d-none');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="list-group list-group-top-border grouppages">
            <div class="list-group-item flush">{str tag=nosharedcollectionsyet section=collection}</div>
        </div>
    {/if}
{/if}

{if $allsubmitted}
    <h3 class="title">
        {str tag="submissionstogroup" section="view"}:
    </h3>
    {if $allsubmitted.count > 0}
        <ul id="allsubmissionlist" class="list-group list-group-top-border grouppages">
            {$allsubmitted.tablerows|safe}
        </ul>
        {if $allsubmitted.pagination}
            <div id="allsubmitted_page_container" class="d-none pagination-container">
                {$allsubmitted.pagination|safe}
            </div>
        {/if}
        {if $allsubmitted.pagination_js}
        <script>
            jQuery(function($) {literal}{{/literal}
                {$allsubmitted.pagination_js|safe}
                $('#allsubmitted_page_container').removeClass('d-none');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="list-group list-group-top-border grouppages">
            <div class="list-group-item flush">{str tag=nosubmittedviewscollectionsyet section=view}</span>
        </div>
    {/if}
{/if}
{if $nosubmissions}
    <h3 class="title">
        {str tag="nosubmissionsfrom" section="view"}:
    </h3>
    <ul id="nosubmissionslist" class="list-group list-group-top-border grouppages">
        {$nosubmissions.tablerows|safe}
    </ul>
    {if $nosubmissions.pagination}
        <div id="nosubmissions_page_container" class="d-none pagination-container">
            {$nosubmissions.pagination|safe}
        </div>
    {/if}
    {if $nosubmissions.pagination_js}
    <script>
        jQuery(function($) {literal}{{/literal}
            {$nosubmissions.pagination_js|safe}
            $('#nosubmissions_page_container').removeClass('d-none');
        {literal}}{/literal});
    </script>
    {/if}
{/if}

{if $mysubmitted || $group_view_submission_form}
    <h3 class="title">
        {if $group_view_submission_form}
            {str tag="submittogroup" section="view"}:
        {else}
            {str tag="yoursubmissions" section="view"}:
        {/if}
    </h3>
    <ul id="groupviewlist" class="list-group list-group-top-border grouppages">
        {if $mysubmitted}
            {foreach from=$mysubmitted item=item}
                <li class="list-group-item">
                    {if $item.submittedtime}
                        <span>{str tag=youhavesubmittedon section=view arg1=$item.url arg2=$item.name arg3=$item.submittedtime|format_date}</span>
                    {else}
                        {str tag=youhavesubmitted section=view arg1=$item.url arg2=$item.name}
                    {/if}
                    {* submittedstatus == '2' is equivalent to PENDING_RELEASE *}
                    {if $item.submittedstatus == '2'}
                    <span class="text-small">{str tag=submittedpendingrelease section=view}</span>
                    {/if}
                </li>
            {/foreach}
        {/if}
        {if $group_view_submission_form}
        <li class="list-group-item list-group-item-default">
            <div class="submissionform">
                {$group_view_submission_form|safe}
            </div>
        </li>
        {/if}
    </ul>

{/if}
