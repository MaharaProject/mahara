{if $coverletter}<h2>Cover Letter</h2>
{$coverletter}{/if}

{if $interest}<h3>Interests</h3>
{$interest}{/if}

{if $contactinformation}<h3>Contact Information</h3>
{$contactinformation}{/if}

{if $personalinformation}<h3>Personal Information</h3>
{$personalinformation}{/if}

{if $employmenthistory || $educationhistory}<h2>History</h2>{/if}

{if $employmenthistory}<h3>Employment</h3>
{$employmenthistory}{/if}

{if $educationhistory}<h3>Education</h3>
{$educationhistory}{/if}

{if $certification}<h2>Certifications</h2>
{$certification}{/if}

{if $book}<h2>Books Published</h2>
{$book}{/if}

{if $membership}<h2>Professional Memberships</h2>
{$membership}{/if}
