
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
    <hr />
{/if}

{if $sharedviews}
    <h4 class="title list-group-item-heading">
        {str tag="viewssharedtogroup" section="view"}:
    </h4>
    {if $sharedviews.count > 0}
        <ul id="sharedviewlist" class="list-group grouppages">
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
        <div class="card-body">
            <span class="lead text-small">{str tag=nosharedviewsyet section=view}</span>
        </div>
    {/if}
    <hr />
{/if}


{if $sharedcollections}
    <h4 class="title list-group-item-heading">
        {str tag="collectionssharedtogroup" section="collection"}:
    </h4>
    {if $sharedcollections.count > 0}
        <ul id="sharedcollectionlist" class="list-group grouppages">
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
        <div class="card-body">
            <span class="lead text-small">{str tag=nosharedcollectionsyet section=collection}</span>
        </div>
    {/if}
    <hr />
{/if}

{if $allsubmitted}
    <h4 class="title list-group-item-heading">
        {str tag="submissionstogroup" section="view"}:
    </h4>
    {if $allsubmitted.count > 0}
        <ul id="allsubmissionlist" class="list-group grouppages">
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
        <div class="card-body">
            <span class="lead text-small">{str tag=nosubmittedviewscollectionsyet section=view}</span>
        </div>
    {/if}
    <hr />
{/if}
{if $nosubmissions}
    <h4 class="title list-group-item-heading">
        {str tag="nosubmissionsfrom" section="view"}:
    </h4>
    <ul id="nosubmissionslist" class="list-group grouppages">
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
    <hr />
{/if}

{if $mysubmitted || $group_view_submission_form}
    <h4 class="title list-group-item-heading">
        {if $group_view_submission_form}
            {str tag="submittogroup" section="view"}:
        {else}
            {str tag="yoursubmissions" section="view"}:
        {/if}
    </h4>
    <ul id="groupviewlist" class="list-group grouppages">
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
