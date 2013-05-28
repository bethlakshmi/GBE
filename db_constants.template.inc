<?
// Per-con values - The values of all of these should be visible on
// the status page

define (DB_SERVER, 'localhost');
define (DB_NAME,   'ExpoTest');
//define (DB_SERVER, 'mysql.interactiveliterature.org');
//define (DB_NAME,   'intercon_j');

// Database users - These could/should be used to control access to the
// database.  In reality, it's handled by the software.  And the password
// could be (much) stronger, but there's no way to reach the database server
// from outside the domain, so we should be OK.  And it's not like we've got
// credit card numbers or other sensative stuff in here anyway...

define (DB_ADMIN_USR, 'root');
define (DB_ADMIN_PWD, 'root');

define (DB_STAFF_USR, 'root');
define (DB_STAFF_PWD, 'root');

define (DB_USER_USR, 'root');
define (DB_USER_PWD, 'root');

?>