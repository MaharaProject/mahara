{if $hascontent}
<table class="resumecontactinfo fullwidth">
{if $address}
    <tr>
        <th class="onethirdwidth">{str tag='address' section='artefact.internal'}</th>
        <td>{$address|safe}</td>
    </tr>
{/if}
{if $town}
    <tr>
        <th class="onethirdwidth">{str tag='town' section='artefact.internal'}</th>
        <td>{$town|safe}</td>
    </tr>
{/if}
{if $city}
    <tr>
        <th class="onethirdwidth">{str tag='city' section='artefact.internal'}</th>
        <td>{$city|safe}</td>
    </tr>
{/if}
{if $country}
    <tr>
        <th class="onethirdwidth">{str tag='country' section='artefact.internal'}</th>
        <td>{$country|safe}</td>
    </tr>
{/if}
{if $faxnumber}
    <tr>
        <th class="onethirdwidth">{str tag='faxnumber' section='artefact.internal'}</th>
        <td>{$faxnumber|safe}</td>
    </tr>
{/if}
{if $businessnumber}
    <tr>
        <th class="onethirdwidth">{str tag='businessnumber' section='artefact.internal'}</th>
        <td>{$businessnumber|safe}</td>
    </tr>
{/if}
{if $homenumber}
    <tr>
        <th class="onethirdwidth">{str tag='homenumber' section='artefact.internal'}</th>
        <td>{$homenumber|safe}</td>
    </tr>
{/if}
{if $mobilenumber}
    <tr>
        <th class="onethirdwidth">{str tag='mobilenumber' section='artefact.internal'}</th>
        <td>{$mobilenumber|safe}</td>
    </tr>
{/if}
</table>
{/if}
{if $license}
<div class="license">
{$license|safe}
</div>
{/if}
