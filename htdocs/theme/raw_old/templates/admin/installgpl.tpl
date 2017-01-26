{include file='header.tpl' nosearch=true}
            <h3 class="center"><a href="https://mahara.org">Mahara</a> {str section='admin' tag='release' args=$releaseargs}</h3>

            <div id="gpl-terms">
 {str tag='copyright' section='admin'}
<p>This program is free software; you can redistribute it and/or modify
under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3 of the
License, or (at your option) any later version.</p>

<p>This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.</p>

<p>You should have received a copy of the GNU General Public License
along with this program.  If not, see <a href="http://www.gnu.org/licenses/gpl.html">http://www.gnu.org/licenses/</a>.
            </div>

            <div class="alert alert-info">Before you install Mahara, you may want to check out the <a href="https://wiki.mahara.org/wiki/Release_Notes">release notes</a> for this release.</div>

            <form action="{$WWWROOT}admin/upgrade.php" method="post" class="center">
            <input type="hidden" name="urlsecret" value="{get_config('urlsecret')}">
            <input class="submit btn btn-primary" type="submit" value="{str tag=installmahara section=admin}"></form>

{include file='admin/upgradefooter.tpl'}
