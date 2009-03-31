// ** I18N

// Calendar SL language
// Author: Gregor Anzelj, <gregor.anzelj@gmail.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Nedelja",
 "Ponedeljek",
 "Torek",
 "Sreda",
 "\u010cetrtek",
 "Petek",
 "Sobota",
 "Nedelja");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Ned",
 "Pon",
 "Tor",
 "Sre",
 "\u010cet",
 "Pet",
 "Sob",
 "Ned");

 // First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Januar",
 "Februar",
 "Marec",
 "April",
 "Maj",
 "Junij",
 "Julij",
 "Avgust",
 "September",
 "Oktober",
 "November",
 "December");

// short month names
Calendar._SMN = new Array
("Jan",
 "Feb",
 "Mar",
 "Apr",
 "Maj",
 "Jun",
 "Jul",
 "Avg",
 "Sep",
 "Okt",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O koledarju";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Avtor: Mihai Bazon\n" + // don't translate this ;-)
"Za zadnjo razli\u010dico obi\u0161\u010di: http://www.dynarch.com/projects/calendar/\n" +
"Raz\u0161irjeno pod GNU LGPL.  Poglej http://gnu.org/licenses/lgpl.html za podrobnosti." +
"\n\n" +
"Izbiranje datuma:\n" +
"- Uporabi gumbe \xab, \xbb za izbor leta\n" +
"- Uporabi gumbe " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " za izbor meseca\n" +
"- Za hiter izbor zadr\u017ei klik na katerem koli od zgornjih gumbov.\n";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Izbiranje \u010dasa:\n" +
"- Klikni na kateri koli del \u010dasa in ga pove\u010daj\n" +
"- ali Shift-klikni in ga zmanj\u0161aj\n" +
"- ali klikni in povleci za hitrej\u0161i izbor.";

Calendar._TT["TOGGLE"] = "Izberi prvi dan tedna";
Calendar._TT["PREV_YEAR"] = "Prej\u0161nje leto (zadr\u017ei klik za meni)";
Calendar._TT["PREV_MONTH"] = "Prej\u0161nji mesec (zadr\u017ei klik za meni)";
Calendar._TT["GO_TODAY"] = "Pojdi na teko\u010di dan";
Calendar._TT["NEXT_MONTH"] = "Naslednji mesec (zadr\u017ei klik za meni)";
Calendar._TT["NEXT_YEAR"] = "Naslednje leto (zadr\u017ei klik za meni)";
Calendar._TT["SEL_DATE"] = "Izberi datum";
Calendar._TT["DRAG_TO_MOVE"] = "Klikni in povleci na nov polo\u017eaj";
Calendar._TT["PART_TODAY"] = " (danes)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "%s - prika\u017ei kot prvi dan";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Zapri";
Calendar._TT["TODAY"] = "Danes";
Calendar._TT["TIME_PART"] = "(Shift-)klikni ali povleci za spremembo vrednosti";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d.%m.%Y"; // %Y\%m\%d
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "ted";
Calendar._TT["TIME"] = "\u010cas:";
