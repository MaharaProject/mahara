<tr class="matrixsumrow {$location}">
    <td>{str tag="sumofstatuses" section="module.framework"}</td>

    {if $enabled->readyforassessment}
        <td class="completedsum readyforassessment text-center">
            <span class="visually-hidden">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->readyforassessment.title}</span>
            <span>
                {$statustotals.readyforassessment}
            </span>
        </td>
        <td class="smartevidencedash text-center">-</td>
    {/if}

    {if $enabled->dontmatch}
        <td class="completedsum dontmatch text-center">
            <span class="visually-hidden">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->dontmatch.title}</span>
            <span>
                {$statustotals.dontmatch}
            </span>
        </td>
        <td class="smartevidencedash text-center">-</td>
    {/if}

    {if $enabled->partiallycomplete}
        <td class="completedsum partiallycomplete text-center">
            <span class="visually-hidden">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->partiallycomplete.title}</span>
            <span>
                {$statustotals.partiallycomplete}
            </span>
        </td>
        <td class="smartevidencedash text-center">-</td>
    {/if}

    {if $enabled->completed}
        <td class="completedsum completed text-center">
            <span class="visually-hidden">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->completed.title}</span>
            <span>
                {$statustotals.completed}
            </span>
        </td>
    {/if}
    <td class="completedsum" colspan="{$viewcount}">&nbsp;</td>
</tr>