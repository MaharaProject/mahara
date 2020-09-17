{if $noresume && $editing}
    <p class="editor-description">{$noresume}</p>
{else}
<div class="list-group">
    {if $coverletter}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=coverletter section=artefact.resume}</h3>
        {$coverletter|safe}
    </div>
    {/if}

    {if $interest}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=interest section=artefact.resume}</h3>
        {$interest|safe}
    </div>
    {/if}

    {if $contactinformation}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=contactinformation section=artefact.resume}</h3>
        {$contactinformation|safe}
    </div>
    {/if}

    {if $personalinformation}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=personalinformation section=artefact.resume}</h3>
        {$personalinformation|safe}
    </div>
    {/if}

    {if $personalgoal || $academicgoal || $careergoal}
        <div class="list-group-item flush">
            {if $personalgoal}
            <div class="resume-content">
                <h3 class="title">{str tag=personalgoal section=artefact.resume}</h3>
                {$personalgoal|safe}
            </div>
            {/if}

            {if $academicgoal}
            <div class="resume-content">
                <h3 class="title">{str tag=academicgoal section=artefact.resume}</h3>
                {$academicgoal|safe}
            </div>
            {/if}

            {if $careergoal}
            <div class="resume-content">
                <h3 class="title">{str tag=careergoal section=artefact.resume}</h3>
                {$careergoal|safe}
            </div>
            {/if}

        </div>
    {/if}

    {if $personalskill || $academicskill || $workskill}
        <div class="list-group-item flush">
            {if $personalskill}
            <div class="resume-content">
                <h3 class="title">{str tag=personalskill section=artefact.resume}</h3>
                {$personalskill|safe}
            </div>
            {/if}

            {if $academicskill}
            <div class="resume-content">
                <h3 class="title">{str tag=academicskill section=artefact.resume}</h3>
                {$academicskill|safe}
            </div>
            {/if}

            {if $workskill}
            <div class="resume-content">
                <h3 class="title">{str tag=workskill section=artefact.resume}</h3>
                {$workskill|safe}
            </div>
            {/if}
        </div>
    {/if}

    {if $employmenthistory || $educationhistory}
        <div class="list-group-item flush">
            {if $employmenthistory}
            <div class="resume-content">
                <h3 class="title">{str tag=employmenthistory section=artefact.resume}</h3>
                {$employmenthistory|safe}
            </div>
            {/if}

            {if $educationhistory}
            <div class="resume-content">
                <h3 class="title">{str tag=educationhistory section=artefact.resume}</h3>
                {$educationhistory|safe}
            </div>
            {/if}
        </div>
    {/if}


    {if $certification}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=certification section=artefact.resume}</h3>
        <div class="resume-content">{$certification|safe}</div>
    </div>
    {/if}

    {if $book}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=book section=artefact.resume}</h3>
        <div class="resume-content">{$book|safe}</div>
    </div>
    {/if}

    {if $membership}
    <div class="list-group-item flush">
        <h3 class="title">{str tag=membership section=artefact.resume}</h3>
        <div class="resume-content">{$membership|safe}</div>
    </div>
    {/if}
</div>
{/if}
