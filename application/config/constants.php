<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

defined('BACKEND_URLPREFIX') OR define('BACKEND_URLPREFIX', 'acp');

defined('TICKET_FLAG_REQUESTED') 	OR define('TICKET_FLAG_REQUESTED', 'REQUESTED');
defined('TICKET_FLAG_OPENED') 		OR define('TICKET_FLAG_OPENED', 'OPENED');
defined('TICKET_FLAG_PROGRESS') 	OR define('TICKET_FLAG_PROGRESS', 'PROGRESS');
defined('TICKET_FLAG_FINISHED') 	OR define('TICKET_FLAG_FINISHED', 'FINISHED');
defined('TICKET_FLAG_CLOSED') 		OR define('TICKET_FLAG_CLOSED', 'CLOSED');
defined('TICKET_FLAG_HOLD') 		OR define('TICKET_FLAG_HOLD', 'HOLD');
defined('TICKET_FLAG_CANCEL') 		OR define('TICKET_FLAG_CANCEL', 'CANCEL');

defined('TICKET_SOURCE_EMAIL') 	OR define('TICKET_SOURCE_EMAIL', 'EMAIL');
defined('TICKET_SOURCE_WEB') 		OR define('TICKET_SOURCE_WEB', 'WEB');
defined('TICKET_SOURCE_PHONE') 	OR define('TICKET_SOURCE_PHONE', 'PHONE');
defined('TICKET_SOURCE_CHAT') 	OR define('TICKET_SOURCE_CHAT', 'CHAT');

defined('TICKET_PRIORITY_HIGH') 	OR define('TICKET_PRIORITY_HIGH', 1);
defined('TICKET_PRIORITY_NORMAL') OR define('TICKET_PRIORITY_NORMAL', 2);
defined('TICKET_PRIORITY_LOW') 		OR define('TICKET_PRIORITY_LOW', 3);

define('TICKET_EVENT_REQUEST', 'TICKET_REQUEST');
define('TICKET_EVENT_CANCEL', 'TICKET_CANCEL');
define('TICKET_EVENT_RESPONSE', 'TICKET_RESPONSE'); // ticket response by helpdesk group
define('TICKET_EVENT_STAFF_RESPONSE', 'TICKET_STAFF_RESPONSE');
define('TICKET_EVENT_APPROVE', 'TICKET_APPROVE');
define('TICKET_EVENT_CLOSED', 'TICKET_CLOSED');
define('TICKET_EVENT_PROGRESS', 'TICKET_PROGRESS');
define('TICKET_EVENT_OPENED', 'TICKET_OPENED');
define('TICKET_EVENT_ADD_STAFF', 'TICKET_ADD_STAFF');
define('TICKET_EVENT_REMOVE_STAFF', 'TICKET_REMOVE_STAFF');
define('TICKET_EVENT_DELEGATION', 'TICKET_DELEGATION');
define('TICKET_EVENT_CHANGE_LEVEL', 'TICKET_CHANGE_LEVEL');
define('TICKET_EVENT_HOLD', 'TICKET_HOLD');
define('TICKET_EVENT_CLAIM', 'TICKET_CLAIM');
define('TICKET_EVENT_FINISH', 'TICKET_FINISH');
define('TICKET_EVENT_WAIT_NEXT_LEVEL', 'TICKET_WAITING_NEXT_LEVEL');
define('TICKET_EVENT_ACCEPT', 'TICKET_ACCEPT');
define('TICKET_EVENT_CHANGE_CATEGORY', 'TICKET_CHANGE_CATEGORY');