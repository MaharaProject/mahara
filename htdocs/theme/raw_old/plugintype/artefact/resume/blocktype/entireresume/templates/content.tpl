<div class="list-group">
    {if $coverletter}
    <div class="list-group-item">
        <h4>{str tag=coverletter section=artefact.resume}</h4>
        {$coverletter|safe}
    </div>
    {/if}

    {if $interest}
    <div class="list-group-item">
        <h4>{str tag=interest section=artefact.resume}</h4>
        {$interest|safe}
    </div>
    {/if}

    {if $contactinformation}
    <div class="list-group-item">
        <h4>{str tag=contactinformation section=artefact.resume}</h4>
        {$contactinformation|safe}
    </div>
    {/if}

    {if $personalinformation}
    <div class="list-group-item">
        <h4>{str tag=personalinformation section=artefact.resume}</h4>
        {$personalinformation|safe}
    </div>
    {/if}

    {if $personalgoal || $academicgoal || $careergoal}
        <div class="list-group-item">
            <h4>{str tag=goals section=artefact.resume}</h4>

            {if $personalgoal}
            <div class="resume-content">
                <h5>{str tag=personalgoal section=artefact.resume}</h5>
                {$personalgoal|safe}
            </div>
            {/if}

            {if $academicgoal}
            <div class="resume-content">
                <h5>{str tag=academicgoal section=artefact.resume}</h5>
                {$academicgoal|safe}
            </div>
            {/if}

            {if $careergoal}
            <div class="resume-content">
                <h5>{str tag=careergoal section=artefact.resume}</h5>
                {$careergoal|safe}
            </div>
            {/if}

        </div>
    {/if}

    {if $personalskill || $academicskill || $workskill}
        <div class="list-group-item">
            <h4>{str tag=skills section=artefact.resume}</h4>

            {if $personalskill}
            <div class="resume-content">
                <h5>{str tag=personalskill section=artefact.resume}</h5>
                {$personalskill|safe}
            </div>
            {/if}

            {if $academicskill}
            <div class="resume-content">
                <h5>{str tag=academicskill section=artefact.resume}</h5>
                {$academicskill|safe}
            </div>
            {/if}

            {if $workskill}
            <div class="resume-content">
                <h5>{str tag=workskill section=artefact.resume}</h5>
                {$workskill|safe}
            </div>
            {/if}
        </div>
    {/if}

    {if $employmenthistory || $educationhistory}
        <div class="list-group-item">
            <h4>{str tag=History section=blocktype.resume/entireresume}</h4>

            {if $employmenthistory}
            <div class="resume-content">
                <h5>{str tag=employmenthistory section=artefact.resume}</h5>
                {$employmenthistory|safe}
            </div>
            {/if}

            {if $educationhistory}
            <div class="resume-content">
                <h5>{str tag=educationhistory section=artefact.resume}</h5>
                {$educationhistory|safe}
            </div>
            {/if}
        </div>
    {/if}


    {if $certification}
    <div class="list-group-item">
        <h4>{str tag=certification section=artefact.resume}</h4>
        {$certification|safe}
    </div>
    {/if}

    {if $book}
    <div class="list-group-item">
        <h4>{str tag=book section=artefact.resume}</h4>
        {$book|safe}
    </div>
    {/if}

    {if $membership}
    <div class="list-group-item">
        <h4>{str tag=membership section=artefact.resume}</h4>
        {$membership|safe}
    </div>
    {/if}
</div>