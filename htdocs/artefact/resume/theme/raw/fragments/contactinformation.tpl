{if $hascontent}
<table class="resumecontactinfo">
{if $address}
    <tr>
        <th>{str tag='address' section='artefact.internal'}</th>
        <td>{$address|safe}</td>
    </tr>
{/if}
{if $town}
    <tr>
        <th>{str tag='town' section='artefact.internal'}</th>
        <td>{$town|safe}</td>
    </tr>
{/if}
{if $city}
    <tr>
        <th>{str tag='city' section='artefact.internal'}</th>
        <td>{$city|safe}</td>
    </tr>
{/if}
{if $country}
    <tr>
        <th>{str tag='country' section='artefact.internal'}</th>
        <td>{$country|safe}</td>
    </tr>
{/if}
{if $faxnumber}
    <tr>
        <th>{str tag='faxnumber' section='artefact.internal'}</th>
        <td>{$faxnumber|safe}</td>
    </tr>
{/if}
{if $businessnumber}
    <tr>
        <th>{str tag='businessnumber' section='artefact.internal'}</th>
        <td>{$businessnumber|safe}</td>
    </tr>
{/if}
{if $homenumber}
    <tr>
        <th>{str tag='homenumber' section='artefact.internal'}</th>
        <td>{$homenumber|safe}</td>
    </tr>
{/if}
{if $mobilenumber}
    <tr>
        <th>{str tag='mobilenumber' section='artefact.internal'}</th>
        <td>{$mobilenumber|safe}</td>
    </tr>
{/if}
</table>
{/if}
