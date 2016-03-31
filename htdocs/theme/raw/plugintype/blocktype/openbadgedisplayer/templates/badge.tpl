{{$sec = 'blocktype.openbadgedisplayer'}}

<div class="badge-template badge-container">
    <img class="badge-image" src="" />
    <div class="openbadge-details">
        <h3>{{str tag=issuerdetails section=$sec}}</h3>
        <table>
            <tbody>
                <tr class="issuer-name"><td>{{str tag=name section=$sec}}</td><td class="value"></td></tr>
                <tr class="issuer-url"><td>{{str tag=url section=$sec}}</td><td class="value"></td></tr>
                <tr class="issuer-organization"><td>{{str tag=organization section=$sec}}</td><td class="value"></td></tr>
            </tbody>
        </table>

        <h3>{{str tag=badgedetails section=$sec}}</h3>
        <table>
            <tbody>
                <tr class="badge-name"><td>{{str tag=name section=$sec}}</td><td class="value"></td></tr>
                <tr class="badge-description"><td>{{str tag=desc section=$sec}}</td><td class="value"></td></tr>
                <tr class="badge-criteria"><td>{{str tag=criteria section=$sec}}</td><td class="value"></td></tr>
            </tbody>
        </table>

        <h3>{{str tag=issuancedetails section=$sec}}</h3>
        <table>
            <tbody>
                <tr class="issuance-evidence"><td>{{str tag=evidence section=$sec}}</td><td class="value"></td></tr>
                <tr class="issuance-issuedon"><td>{{str tag=issuedon section=$sec}}</td><td class="value"></td></tr>
                <tr class="issuance-expires"><td>{{str tag=expires section=$sec}}</td><td class="value"></td></tr>
            </tbody>
        </table>
    </div>
</div>