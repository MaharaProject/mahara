{include file="export:html:header.tpl"}

{if $coverletter}
<h2>{str tag=coverletter section=artefact.resume}</h2>
{$coverletter|safe}
{/if}

{if $interest}
<h2>{str tag=interest section=artefact.resume}</h2>
{$interest|safe}
{/if}

{if $coverletter || $interest}<hr>{/if}

{if $contactinformation}
<div id="contactinformation">
<h2>{str tag=contactinformation section=artefact.resume}</h2>
{$contactinformation|safe}
</div>
{/if}

{if $personalinformation}
<div id="personalinformation">
<h2>{str tag=personalinformation section=artefact.resume}</h2>
{$personalinformation|safe}
</div>
{/if}

{if $contactinformation || $personalinformation}<hr>{/if}

<div id="composites">

{if $employmenthistory}
<h2>{str tag=employmenthistory section=artefact.resume}</h2>
{$employmenthistory|safe}
{/if}

{if $educationhistory}
<h2>{str tag=educationhistory section=artefact.resume}</h2>
{$educationhistory|safe}
{/if}

{if $certification}
<h2>{str tag=certification section=artefact.resume}</h2>
{$certification|safe}
{/if}

{if $book}
<h2>{str tag=book section=artefact.resume}</h2>
{$book|safe}
{/if}

{if $membership}
<h2>{str tag=membership section=artefact.resume}</h2>
{$membership|safe}
{/if}

</div>

{if $employmenthistory || $educationhistory || $certification || $book || $membership}<hr>{/if}

{if $personalgoal || $academicgoal || $careergoal}
<h2>{str tag=myskills section=artefact.resume}</h2>

{if $personalgoal}
<h3>{str tag=personalgoal section=artefact.resume}</h3>
{$personalgoal|safe}
{/if}

{if $academicgoal}
<h3>{str tag=academicgoal section=artefact.resume}</h3>
{$academicgoal|safe}
{/if}

{if $careergoal}
<h3>{str tag=careergoal section=artefact.resume}</h3>
{$careergoal|safe}
{/if}

{/if}

{if $personalskill || $academicskill || $workskill}
<h2>{str tag=mygoals section=artefact.resume}</h2>

{if $personalskill}
<h3>{str tag=personalskill section=artefact.resume}</h3>
{$personalskill|safe}
{/if}

{if $academicskill}
<h3>{str tag=academicskill section=artefact.resume}</h3>
{$academicskill|safe}
{/if}

{if $workskill}
<h3>{str tag=workskill section=artefact.resume}</h3>
{$workskill|safe}
{/if}

{/if}

{include file="export:html:footer.tpl"}
