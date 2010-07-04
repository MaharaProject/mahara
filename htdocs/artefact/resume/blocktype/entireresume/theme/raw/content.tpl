{if $coverletter}<h2>{str tag=coverletter section=artefact.resume}</h2>
{$coverletter|safe}{/if}

{if $interest}<h3>{str tag=interest section=artefact.resume}</h3>
{$interest|safe}{/if}

{if $contactinformation}<h3>{str tag=contactinformation section=artefact.resume}</h3>
{$contactinformation|safe}{/if}

{if $personalinformation}<h3>{str tag=personalinformation section=artefact.resume}</h3>
{$personalinformation|safe}{/if}

{if $employmenthistory || $educationhistory}<h2>{str tag=History section=blocktype.resume/entireresume}</h2>{/if}

{if $employmenthistory}<h3>{str tag=employmenthistory section=artefact.resume}</h3>
{$employmenthistory|safe}{/if}

{if $educationhistory}<h3>{str tag=educationhistory section=artefact.resume}</h3>
{$educationhistory|safe}{/if}

{if $certification}<h2>{str tag=certification section=artefact.resume}</h2>
{$certification|safe}{/if}

{if $book}<h2>{str tag=book section=artefact.resume}</h2>
{$book|safe}{/if}

{if $membership}<h2>{str tag=membership section=artefact.resume}</h2>
{$membership|safe}{/if}
