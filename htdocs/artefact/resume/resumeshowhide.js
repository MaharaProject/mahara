/**
 * Javascript for the resume artefact
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

/*
 * TODO

 * move javascript into the resume plugin?
 * test in safari and opera
 * implement for employment history
 */
$j = jQuery;
$j(document).ready(function() {
	$j(".expandable-body").hide();
	$j(".toggle").addClass('expandable');
	$j(".expandable-head").click(function(event) {
		$j(this).next('.expandable-body').toggle();
		$j(this).children(".toggle.expandable").toggleClass('expanded');
	});
});