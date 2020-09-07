{foreach from=$courses.data item=course}
<div class="list-group-item task-item">
    <a class="outer-link collapsed" href="#expand-course-{$course->uniqueid}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-course-{$course->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
        <span class="sr-only">{$course->title}</span>
        <span class="icon icon-chevron-down right collapse-indicator float-right text-inline" role="presentation" aria-hidden="true"></span>
    </a>
    <span class="text-default course-task-heading">{$course->title}</span>
    <span class="float-right course-task-right" style="margin-right:15px;">
          <span class="text-small text-midtone text-inline course-task-hours">{str tag='hours' section='blocktype.courseinfo'}: {$course->cpdhours_display}</span>
    </span>
    {if $course->date}<p class="text-midtone text-small course-date">{str tag='completedondate' section='blocktype.courseinfo'} {$course->date}</p>{/if}

    <div class="collapse course-detail" id="expand-course-{$course->uniqueid}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
        {if $course->organisation}<p><strong>{str tag='organisation' section='blocktype.courseinfo'}:</strong> {$course->organisation}</p>{/if}
        {if $course->type}<p><strong>{str tag='coursetype' section='blocktype.courseinfo'}:</strong> {$course->type}</p>{/if}
    </div>
</div>
{/foreach}
<div class="list-group-item">
    <div class="summaryhours">
        <div class="text-right totalhours">
            <strong>{str tag='totalhours' section='blocktype.courseinfo'}:</strong> {number_format($courses.grandtotalhours, 0)}
        </div>
    </div>
</div>
