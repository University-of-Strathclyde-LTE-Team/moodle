<?php
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
 * This file is the English translation file containing all required strings
 * used in the plugin
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Module Name
$string['pluginname'] = 'Extensions';
$string['pluginadministration'] = 'Extensions administration';

// Strings for Capabilities
$string['extensions:accessextension']   = 'Access Extensions';
$string['extensions:requestextension']  = 'Request Extension';
$string['extensions:modifyextension']   = 'Modify Extension';
$string['extensions:withdrawextension'] = 'Withdraw Extension';
$string['extensions:revokeextension']   = 'Revoke Extension';
$string['extensions:approveextension']  = 'Approve Extension';
$string['extensions:deleteextension']   = 'Delete Extension';
$string['extensions:readextension']     = 'Read Extension';

$string['messageprovider:local_extensions_notification'] = 'Extensions Notification';
$string['messageprovider:extension_updated'] = 'Extension Updated';

// Other required strings here
$string['enable_extensions']       = 'Enable Extensions';
$string['enable_ind_extensions']   = 'Enable Individual Extensions';
$string['enable_glo_extensions']   = 'Enable Global Extensions';
$string['enable_group_extensions'] = 'Enable Group Extensions';

$string['extensions_cutoff'] = 'Extensions cutoff';

$string['allow_multiple_global_short'] = 'Allow multiple global extensions.';
$string['allow_multiple_global_long'] = 'Allow multiple seperate global extensions for a single activity';

$string['no_grouping_assigned_short'] = 'No grouping assigned.';
$string['no_grouping_assigned']       = 'No Grouping assigned to activity. Selection of any group in this course is possible.';
$string['only_grouping_groups']       = 'Only Groups assigned to this Grouping are available for selection.';

$string['prevent_req_after_subm'] = 'Prevent After Submission.';
$string['prevent_req_after_subm_long'] = 'Prevent extension requests after activity submission has been made.';

$string['prevent_timelimit_reqs'] = 'Prevent Timelimit Requests';
$string['prevent_timelimit_reqs_long'] = 'Prevent requests for timelimit extensions';
$string['timelimit_req_denied'] = 'Requests for timelimit extensions are currently denied by configuration.';

$string['configuration']     = 'Configuration';
$string['group_extension']   = 'Group(s)';

$string['edit_ext_request'] = 'Edit Extension Request';

$string['invalid_activity'] = 'Invalid Activity';

$string['date_extension'] = 'Date extension';
$string['time_extension'] = 'Time extension';

$string['minutes'] = 'Minutes';
$string['date_or_time'] = 'Date or Time extension';

$string['force_extension_enabled'] = "Extensions always enabled";
$string['force_extension_enabled_long'] = "Force extensions to always be enabled for all activities";

$string['must_select_one_group']    = 'You must select at least one group.';
$string['must_select_one_approver'] = 'You must specify at least one Approver';

$string['group_or_class'] = 'Show Group or Class';
$string['group_or_class_long'] = 'Show Group or Class for each Group';

$string['class'] = 'Class';

$string['group_has_extension'] = 'Group \'{$a}\' already has an extension.';

$string['grouping_or_offering'] = 'Show Grouping or Offering';
$string['grouping_or_offering_long'] = 'Show Grouping or Offering for each Grouping';

$string['offering'] = 'Offering';

$string['duplicate_request'] = 'Duplicate Request';
$string['duplicate_request_exists'] = 'Warning: A duplicate extension request exists.';
$string['show_duplicate_warning'] = 'Show Duplicate Requests Warning?';

$string['extension_history'] = 'Extension History';
$string['no_change'] = 'No Change';

$string['max_length_error'] = 'Maximum length of this field has been exceeded.';

$string['approvers'] = 'Approver';
$string['approver_roles']  = 'Users permitted as approvers will be sourced from these roles.';
$string['extension_approvers'] = 'Extension Approvers';

$string['save'] = 'Save activity';

$string['selected_options']  = 'Selected Options';
$string['available_options'] = 'Available Options';
$string['no_options_set']    = 'No Options Set';

$string['group_already_exists'] = 'A group extension already exists for this Activity';

$string['please_select_a_group'] = 'Please select at least one Group to apply this extension to.';

$string['no_deadline_setup'] = 'No central deadline has been configured for this activity. Extension creation is not possible.';

// Status strings:
$string['status_none']      = 'No Status';
$string['status_pending']   = 'Pending';
$string['status_approved']  = 'Approved';
$string['status_denied']    = 'Denied';
$string['status_withdrawn'] = 'Withdrawn';
$string['status_revoked']   = 'Revoked';
$string['status_moreinfo']  = 'More Info Required';
$string['status_deleted']   = 'Deleted';

$string['allow_ext_requests'] = 'Permit extension requests';
$string['settings'] = 'Extension settings';

$string['show_indiv_group'] = 'Show Individual &amp; Group Extensions Menu';

$string['ext_configure_activities'] = 'Configure Activities';
$string['ext_configure_activity']   = 'Configure Activity';
$string['manage_extensions']        = 'Manage extensions';

$string['show_pending_count'] = 'Show Pending Extensions count';

// Copied extensions strings are below:
$string['ext_module'] = 'Module';

$string['days_prior'] = ' days prior.'; // must have space at beginning!
$string['days_after'] = ' days after due date.'; // must have space at beginning!

$string['extensiondate']            = 'Extension Date';
$string['extrequestdate']           = 'Date of Request';
$string['extapprover']              = 'Request Sent To';
$string['extstatus']                = 'Status';
$string['extextension']             = 'Extension';
$string['extselectassignment']      = 'Activity Name';
$string['extglobalextsettings']     = 'Group Extension Settings';
$string['extglobalextdate']         = 'Group Extension Date';
$string['extglobalextopen']         = 'Group Extension Open';
$string['extglobalextclosed']       = 'Group Extension Closed';
$string['extreason']                = 'Reason for Extension';
$string['extapplyto']               = 'Apply Extension To';
$string['exthideglobal']            = 'Hide Global Extension from students';
$string['exthidegroup']             = 'Hide Group Extension from students';
$string['extindivextopen']          = 'Individual Extension Open';
$string['extindivextclosed']        = 'Individual Extension Closed';
$string['extsaveapply']             = 'Save and Apply Extension';
$string['extassessmentname']        = 'Assessment Name';
$string['extduedate']               = 'Due Date';
$string['extcurrduedate']           = 'Current Due Date';
$string['extgroups']                = 'Groups';
$string['exthidden']                = 'Hidden';
$string['extdate']                  = 'Extension Date';
$string['extapproveddate']          = 'Approved Extension Date';
$string['extcreator']               = 'Creator';
$string['extedit']                  = 'Actions';
$string['extstudentname']           = 'Student';
$string['extusername']              = 'Username';
$string['extstatus']                = 'Status';
$string['extclass']                 = 'Class';
$string['extsentto']                = 'Sent to';
$string['extapprove']               = 'Approve';
$string['extgrouping']              = 'Grouping';
$string['extrequests']              = 'Extension Requests';
$string['extsupdoc']                = 'Supporting Documentation';
$string['extrequestdateacst']       = 'Extension Date/Time Requested (Australian Central Standard Time)';
$string['extsendto']                = 'Send to Staff Member';
$string['extapproval']              = 'Approval';
$string['extresponse']              = 'Message to Student';
$string['extapplyfilters']          = 'Apply Filters(s)';
$string['extclearfilter']           = 'Clear Filter';
$string['extfilterby']              = 'Filter By:';
$string['extassessment']            = 'Assessment';

$string['ext_submit_changes']       = 'Submit Changes';
$string['extdiscard']               = 'Discard Changes';
$string['extexitwithout']           = 'Cancel';
$string['extsubmitreq']             = 'Request Extension';

$string['extresubmitreq']           = 'Resubmit Request';
$string['extwithdraw']              = 'Withdraw Request';

$string['extmessnotpermitted']      = 'This assessment activity does not permit extension requests. Please contact your Course Coordinator directly to discuss your request';
$string['extnotpermitted_staff']    = 'This assessment activity does not permit extension requests. ';
$string['extmessalready']           = 'An extension request has already been submitted for this assessment. You cannot request additional extensions until an existing request has been addressed by a staff member or withdrawn by the student';
$string['extmesscoursenotallowed']  = 'This course does not allow extensions. Please contact your Course Coordinator';
$string['extmessnotupdated']        = 'The assessment items in this course have not been updated to conform with the new standard. If you require assistance with this please contact your Online Adviser.';
$string['extduedatepassed']         = 'Due Date for this assessment item has passed. Please contact your Course Coordinator.';
$string['extbeforedue']             = 'Requested Extension Date was before the Current Due date';
$string['extalreadysubmitted']      = 'A submission has already been made for this activity. Extensions cannot be requested after a submission has been made.';
$string['extmaxsubmission']         = 'Maximum number of submissions has already been made, Extensions cannot be requested after reaching the maximum number of submissions.';
$string['extnomorereq']             = 'No Extension Requests may be made under 24 hours before the current due date. Please contact your Course Coordinator.';

$string['ext_not_allowed']          = '<b>Extensions are not allowed for this Activity.</b>';
$string['ext_are_you_sure']         = 'Are you sure you would like to approve the selected requests?';

$string['ext_doc_num']          = 'Document $a';

$string['ext_delete_docs']      = 'Delete documents when extension decision processed?';

$string['extreasonfor']         = 'Reason for Extension';
$string['extsupporting']        = 'Supporting Documentation';
$string['extasmntdue']          = 'Activity Due Date';
$string['extsubmission']        = 'Submission Date Requested';

$string['ext_staff_feedback']   = 'Staff Feedback';
$string['ext_response_mesg']    = 'Message to student';

$string['ext_select_all']       = 'Select/Deselect All';
$string['ext_appr_selected']    = 'Quick Approve Selected';
$string['ext_no_docs']          = 'No documents provided';
$string['ext_request_detail']   = 'Request Details';
$string['ext_not_approver']     = 'Sorry, you are not listed as an approver for this Activity';
$string['ext_saved']            = 'Extension Saved';
$string['ext_request_edit']     = 'Manage Extension Request';

$string['ext_message_required'] = 'A response must be entered when a request is denied';

$string['ext_group_ext']       = 'Group Extensions';
$string['ext_group_ext_edit']  = 'Create/Edit Group Extensions';

$string['create'] = 'Create';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';

$string['ext_global_ext']         = 'Global Extensions';
$string['ext_global_ext_create']  = 'Create global extension';
$string['ext_global_ext_edit']    = 'Edit global extension';

$string['ext_other_req_exists'] = '<span class=\'box errorbox errorboxcontent\'>Another Extension Request for this Activity and User exists!</span>';
$string['ext_already_pending']  = 'There is already a pending request for this Activity. No further requests can be made at this time.';

$string['ext_student_requests'] = 'Extension Requests';
$string['ext_current_requests'] = 'Current Extension Requests';
$string['ext_new_request']      = 'New Request';
$string['ext_do_request']       = 'Request Extension';
$string['ext_none_exist']       = 'You have no current extension requests for this course';

$string['new_extension_request'] = 'New Extension Request';

$string['ext_email_request_subject']  = '$a - New Extension Request Received';
$string['ext_email_header']           = 'Dear $a,<br /><br />You have received an extension request from the following student: ';
$string['ext_email_request_followup'] = 'Please note: this email is configured to utilise Microsoft Outlook Follow-up functionality in order to remind recipients of the email 24 hours after receipt. However this functionality may not be compatible with all email clients. Users will also be notified through alerts in learnonline.';
$string['ext_email_use_link']         = 'Please use the link above to view and action the request.';
$string['ext_email_donot_reply']      = 'This email is sent by an automated service. Please do not reply to this email.';
$string['ext_email_student_id']       = 'Student ID:';
$string['ext_email_response_subject'] = 'Extension Request Status';
$string['ext_email_response_header']  = '';
$string['ext_email_response_text']    = 'Dear $a,<br /><br />Your extension request has been updated. Please select the link below to view the current status of the request.';
$string['ext_email_response_link']    = ' ';
$string['ext_email_group_body']      = 'Dear {$a->name}, <br /><br />' .
        'A group extension has been applied to the assessment ' .
        '\'{$a->asmnt_name}\' that you are a part of. Please access ' .
        'the assessment to view the details of this extension.';
$string['ext_email_link']             = 'Please select this link to view the request.';

$string['ext_indiv_req'] = 'Individual &amp; Group';
$string['ext_glob_req']  = 'Global';

$string['please_select']       = 'Please select';
$string['current_timelimit']   = 'Current timelimit';
$string['requested_timelimit'] = 'Requested timelimit';
$string['approved_timelimit']  = 'Approved timelimit';

$string['exttype']  = 'Type';
$string['ext_individual'] = 'Individual';
$string['ext_group']      = 'Group';
$string['ext_global']     = 'Global';

$string['group_submission'] = 'Group Submission';
$string['group_submission_text'] = 'This extension request will be on behalf of your group for this assessment.';

$string['ext_indiv_exts'] = 'Individual &amp; Group Extensions';
$string['ext_glob_exts']  = 'Global Extensions';

$string['please_select'] = 'Please select: ';

$string['ext_no_summ']           = 'There are no assignments available that allow extensions.';
$string['ext_event_title_group'] = 'Class Due Date Extension';

$string['ext_event_title']   = 'Individual Due Date Extension';
$string['ext_event_title_ind']   = 'Individual Due Date Extension';

$string['ext_event_description']     = 'Automatic Extension applied by Course Coordinator';
$string['ext_event_description_ind'] = 'Automatic Extension as requested by Student and approved by Course Coordinator.';

$string['extnotopenyet'] = 'Sorry, this activity is not yet open for submissions. You may only request an extension when the activity is accepting submissions.';

$string['startdate'] = 'Open Date';
$string['duedate']   = 'Due Date';
$string['ext_granted_before_due'] = 'Extension Date cannot be prior to Due Date';
$string['ext_act_add_reason']     = 'Added by Course Coordinator';
$string['ext_act_section_header'] = 'Create Individual Extension';
$string['ext_act_grant_ext']      = 'Grant Extension';
$string['ext_act_ext_date']       = 'Ext Date:';
$string['ext_student_name'] = 'Student Name';

