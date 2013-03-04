// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Functions for extensions where required
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function checkUncheckAll(theElement) {

var theForm = theElement.form, z = 0;

	for(z=0; z<theForm.length;z++){
		if(theForm[z].disabled == false){
			if(theForm[z].type == 'checkbox' && theForm[z].checked == false){
				theForm[z].checked = true;
            } else{
                theForm[z].checked = false;
            }
        }
    }
}

function popup() {
	var txt = 'Are you sure you would like to approve these extensions?';
	return confirm(txt);
}