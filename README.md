[![Build Status](https://travis-ci.org/yuesan/sharedpanel.svg?branch=master)](https://travis-ci.org/yuesan/sharedpanel)

# SharedPanel

Please note that this versin of sharedpanel needs facebook graph API account
and Twitter API account if you want to import post from Facebook and Twitter.

This package includes "twitteroauth.php" and "OAuth.php".
See the license description in each subdirectory.
Except these subdirectories, SharedPanel is licensed under GPL v3 or later.

http://www.gnu.org/copyleft/gpl.html

In this Plugin, you can do:

* Get texts and pictures posted to facebook groups (only public group is avaliable)
* Get texts and pictures posted with hashtag (e.g. #japan) on Twitter (only public account is avaliable)
* Get Email (texts and pictures) sent to the designated Email-Address with designated subjects.
* Get Note on Evernote sent to  the designated Email-Address with designated subjects.

Input texts and pictures to Moodle directly without logging in Moodle.

# How to install

1) Download SharedPanel plugin to your Moodle server from following URI.
2) Unzip sharedpanel.zip and move sharedpanel folder into Moodle mod/ folder,
   and as admin click 'Site administration - Notifications' to let Moodle install sharedpanel
   (like a typical module installation).
3) Get Access Token
If you want to import post from Facebook and Twitter, you need to get Access Token.

# Facebook/Twitter/Mail(IMAPS) Integrations
Shared Panel allows to import cards from Facebook, Twitter and Email(IMAPS). For more information about setting, please refer below.

* [[Facebook]]
* [[Twitter]]
* [[Email]]

# Author
Chikako Nagaoka & KITA Toshihiro

*Some codes in facebook.php was contributed by Go Ohta from Open University, Japan.
*Some codes in email.php include codes by ming (http://qiita.com/ming/items/ce7b8f394cc9b12a2b49)

# License
GNU GPL v3

# Libries
Shared Panel is using libraries below.

## facebook/graph-sdk

* [facebook/graph-sdk](https://github.com/facebook/php-graph-sdk)

```
Copyright 2017 Facebook, Inc.

You are hereby granted a non-exclusive, worldwide, royalty-free license to
use, copy, modify, and distribute this software in source code or binary
form for use in connection with the web services and APIs provided by
Facebook.

As with any software that integrates with the Facebook platform, your use
of this software is subject to the Facebook Developer Principles and
Policies [http://developers.facebook.com/policy/]. This copyright notice
shall be included in all copies or substantial portions of the software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
```

## abraham/twitteroauth

* [abraham/twitteroauth](https://github.com/abraham/twitteroauth)

```
Copyright (c) 2009 Abraham Williams - http://abrah.am - abraham@abrah.am

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

## line/line-bot-sdk
* [line/line-bot-sdk](https://github.com/line/line-bot-sdk-php)

```
Copyright 2016 LINE Corporation

LINE Corporation licenses this file to you under the Apache License,
version 2.0 (the "License"); you may not use this file except in compliance
with the License. You may obtain a copy of the License at:

  https://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
License for the specific language governing permissions and limitations
under the License.
```

