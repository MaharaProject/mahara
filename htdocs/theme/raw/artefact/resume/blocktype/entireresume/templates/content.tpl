{if $coverletter}<h3>{str tag=coverletter section=artefact.resume}</h3>
{$coverletter|safe}{/if}

{if $interest}<h3>{str tag=interest section=artefact.resume}</h3>
{$interest|safe}{/if}

{if $contactinformation}<h3>{str tag=contactinformation section=artefact.resume}</h3>
{$contactinformation|safe}{/if}

{if $personalinformation}<h3>{str tag=personalinformation section=artefact.resume}</h3>
{$personalinformation|safe}{/if}

{if $personalgoal || $academicgoal || $careergoal}<h3>{str tag=goals section=artefact.resume}</h3>{/if}

{if $personalgoal}<h4>{str tag=personalgoal section=artefact.resume}</h4>
{$personalgoal|safe}{/if}

{if $academicgoal}<h4>{str tag=academicgoal section=artefact.resume}</h4>
{$academicgoal|safe}{/if}

{if $careergoal}<h4>{str tag=careergoal section=artefact.resume}</h4>
{$careergoal|safe}{/if}

{if $personalskill || $academicskill || $workskill}<h3>{str tag=skills section=artefact.resume}</h3>{/if}

{if $personalskill}<h4>{str tag=personalskill section=artefact.resume}</h4>
{$personalskill|safe}{/if}

{if $academicskill}<h4>{str tag=academicskill section=artefact.resume}</h4>
{$academicskill|safe}{/if}

{if $workskill}<h4>{str tag=workskill section=artefact.resume}</h4>
{$workskill|safe}{/if}

{if $employmenthistory || $educationhistory}<h3>{str tag=History section=blocktype.resume/entireresume}</h3>{/if}

{if $employmenthistory}<h4>{str tag=employmenthistory section=artefact.resume}</h4>
{$employmenthistory|safe}{/if}

{if $educationhistory}<h4>{str tag=educationhistory section=artefact.resume}</h4>
{$educationhistory|safe}{/if}

{if $certification}<h3>{str tag=certification section=artefact.resume}</h3>
{$certification|safe}{/if}

{if $book}<h3>{str tag=book section=artefact.resume}</h3>
{$book|safe}{/if}

{if $membership}<h3>{str tag=membership section=artefact.resume}</h3>
{$membership|safe}{/if}
