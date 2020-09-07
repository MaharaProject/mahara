<div class="card-body flush">
{if $message}
    <div class="lead text-center content-text">{$message}</div>
{else}
    {if $course.data}
    <div id="coursedata_{$blockid}" class="list-group list-unstyled">
    <p>{$resultstr}</p>
    {$course.tablerows|safe}
    </div>
        {if $course.pagination}
        <div id="course_page_container_{$blockid}" class="hidden">
        {$course.pagination|safe}
        </div>
        <script>
        jQuery(function() {literal}{{/literal}
            {$course.pagination_js|safe}
            jQuery('#course_page_container_{$blockid}').removeClass('hidden');
        {literal}}{/literal});
        </script>
        {/if}
    {else}
    <p>{$resultstr}</p>
    <div class="lead text-center content-text">{str tag='nocourses' section='blocktype.courseinfo'}</div>
    {/if}
{/if}
</div>
