== SharedPanel plugin version 0.1 ==

Please note that this versin of sharedpanel needs facebook graph API account and Twitter API account if you want to import post from Facebook and Twitter.

This package includes "twitteroauth.php" and "OAuth.php". See the license description in each subdirectory. Except these subdirectories, SharedPanel is licensed under GPL v3 or later.

http://www.gnu.org/copyleft/gpl.html

In this Plugin, you can do:
Get texts and pictures posted to facebook groups (only public group is avaliable)
Get texts and pictures posted with hashtag (e.g. #japan) on Twitter (only public account is avaliable)
Get Email (texts and pictures) sent to the designated Email-Address with designated subjects.
Get Note on Evernote sent to  the designated Email-Address with designated subjects.

Input texts and pictures to Moodle directly without logging in Moodle.

------------------------------------------------------------------------------------------------------

How to install:

1) Download SharedPanel plugin to your Moodle server from following URI.

2) Unzip sharedpanel.zip and move sharedpanel folder into Moodle mod/ folder, and as admin click 'Site administration - Notifications' to let Moodle install sharedpanel (like a typical module installation).

3) Get Access Token
If you want to import post from Facebook and Twitter, you need to get Access Token.

FacebookÅF
If you would like to get information from Facebook, you need to get access token on Facebook Graph API.
Please check following instructions.

(a) Make your application
https://developers.facebook.com/apps/

(b) Get "User Access Token"
https://developers.facebook.com/tools/accesstoken/

Basically, Access Token will expire in 1 hour, so please extend it to 2 months by clicking "Extend Access Token".
*You need to extend Access Token every 2 months.

(c) Write necessary information on
Dashboard / Site administration / Plugins / Activity modules / SharedPanel

Facebook app ID
Facebook secret
Facebook redirect URL
Facebook token


TwitterÅF
If you would like to get posts from Twitter, you need to get access token on Twitter API.
Please check following instructions.

(a) Make your application  
https://apps.twitter.com/

(b) Write necessary information on
Dashboard / Site administration / Plugins / Activity modules / SharedPanel
Twitter consumerKey
Twitter consumerSecret
Twitter accessToken
Twitter accessTokenSecret

4) Setup your activity
When you add the activity "SharedPanel" on your course, you have to setup items on "Edit settings".

Facebook Group ID
Twitter Hashtag
Email address / Password / Subject (email)
Email address / Password / Note title (evernote)

*When you use Evernote, style will be affected by the style of the note itself.
*To use the Email/Evernote import feature, you need to have PHP IMAP module installed on your server.

----------------------------------------------------------------------------------------
Chikako Nagaoka & KITA Toshihiro

*Some codes in facebook.php was contributed by Go Ohta from Open University, Japan.
*Some codes in email.php include codes by ming (http://qiita.com/ming/items/ce7b8f394cc9b12a2b49)

