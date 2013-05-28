GBE
===

Code repository for the Great Boston Burlesque Expo
Based on edits made in 2012 to Intercode

Deploying Intercode for a new convention
========================================

Note: these instructions apply if you're setting up a convention on Intercode
for the first time.  There's a different procedure if you're setting up the
subsequent year of a convention that ran on Intercode last year, which allows
you to keep the plugs and mailing lists from last year.

1) Create a new MySQL database and initialize it by running setup/schema.sql.  

2) Create an initial admin user for yourself:
   INSERT INTO Users (EMail, HashedPassword, FirstName, LastName, Nickname, 
                      Address1, Address2, City, State, Zipcode, Country, 
                      DayPhone, EvePhone, BestTime, HowHeard, PaymentNote, 
                      Priv)
   VALUES            ('your-email-address@example.com', 
                      MD5('your-new-password'),
                      '', '', '', '', '', '', '', '', '', '', '', '', '',
                      '', 'Staff');
                      
3) Copy the site code (in the src directory) to your web server in whatever 
   location you want to serve it from.  The server must support at least 
   PHP 5.1.
   
   (In the Intercon New England installation, we use Vlad the Deployer to
   deploy updates to the app.  It's got some nice advantages, such as
   deploying straight out of version control, and the ability to quickly and
   automatically roll back a bad deploy.  See README.vlad for details.)
   
4) Edit intercon_constants.inc and db_constants.template.inc.  The comments in there should give 
   you some guidance; in particular, you'll want to edit the session_name, all the
   DB_* defines, and pretty much everything under the "Con identifiers"
   comment.  Please read through the entire file and make sure you understand
   what everything does, since this file comprises the entire configuration
   for your instance of the app.
   
4a) Change db_constants.template.inc to db_constants.inc
   
5) There are other files you probably want to replace in your copy of the
   app: the PageBanner image, and likely some of the static .html files.
   
6) The app should now be up and running with your branding!  Try logging into
   it using the email address and password you used above.  If that works,
   try editing your profile to add your personal info.
   
7) Now we need to get PayPal up and running.  Follow the instructions in
   README.paypal.

NOTE:   
You may also want to disable certain features of the app that are
specific to Intercon New England; please _don't_ do this by just
commenting them out.  Instead, create a new constant similar to, e.g.
THURSDAY_ENABLED, and wrap any functionality related to it in an "if"
block that checks this constant.  Then you can submit the patch back
to the maintainers and we'll all be able to benefit from the new
configurability you've added! :)

Intercode PayPal Integration
============================

Intercode uses PayPal to process convention payments such as:

* Registration fee
* T-shirt pre-orders
* Dead dog ticket pre-orders
* Pre-convention tickets

As of the 2011 release, we've implemented PayPal Instant Payment Notification
(IPN) support.  This has the advantage of much greater reliability than the
old link-back method, and is easier to debug issues with using PayPal's
IPN integration console.  However, it does come with increased complexity of
setup.

The main reason for this complexity is that Intercode must be deployed 
separately for each convention you run, but PayPal only allows one IPN
listener URL per account.  Thus, the IPN listener must be able to handle
payments for all the conventions at once.  This means figuring out which 
con database to write payment records into based on the name of the item being
purchased.

The code for PayPal integration is located in the Subversion repository at:
http://interactiveliterature.org/svn/intercon/paypal-integration

It consists of a single PHP file called "ipn-listener.php".  This should be
deployed somewhere separate from your convention site installations.  We
deploy ours to http://interactiveliterature.org/paypal-integration.

In order to use this file, you'll need to change several things in it:

1) The HOME_DIR should be set to the root directory of all your convention
   site installations on the web server.
   
2) Change the "Phone home" email address at the end of the log_paypal_msgs
   function so it's not emailing Nat Budin.
   
3) There's a section late in the file that begins with the line:
   $item_name = $_POST['item_name'];
   
   This is where we figure out which convention is being paid for using the
   PayPal item name.  You'll need to change the regular expression as well
   as the logic below to figure out which convention is being paid for and
   set $con_dir to the appropriate directory.  This should be a directory
   in which a copy of Intercode is deployed, because the next thing the
   script is going to do is try to require('intercon_db.inc') from that
   directory to connect to the appropriate database.

Once that's all done, you'll need to tell PayPal to send IPN requests to
the URL of the ipn-listener.php script.  This can be done from your PayPal
account's control panel.

To test it, set the DEVELOPMENT_MODE flag to 1 in intercon_constants.inc.
This will make the price of everything 5 cents so you can try it out
without having to spend too much money.  (It will also put a red banner on
each page of the site saying that it's in development mode, and direct all
convention email to you rather than the user it was supposed to go to.)
