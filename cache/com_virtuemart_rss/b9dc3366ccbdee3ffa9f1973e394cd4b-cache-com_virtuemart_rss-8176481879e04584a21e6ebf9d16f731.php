<?php die("Access Denied"); ?>#x#a:2:{s:6:"result";a:5:{i:0;O:8:"stdClass":3:{s:4:"link";s:48:"https://virtuemart.net/news/just-a-little-update";s:5:"title";s:20:"Just a little update";s:11:"description";s:2689:"<p>Just a note to my last news. The problem goes on, I just tried to install VirtueMart on the last joomla5.3 and it was not even possible to install it. On the other hand, it is funny to read the patch notes. One patch will fix an issue of joomla 5, but for j5.3, we solved it already in this release. The question is, why we have to solve something, which worked since vm2.6? After this release should be more time to work on WP and make some committs.</p>
<p style="text-align: center;"><a class="btn btn-primary" href="https://extensions.virtuemart.net/support/virtuemart-supporter-membership-bronze-detail">DOWNLOAD VirtueMart 4.4.2 with the Membership<br />NOW</a></p>
<h4>Features</h4>
<ul>
<li>Template vmBasic, Added register and checkout button, removed registration fields from cart view.</li>
<li>Bundles added option "orderableBrowse" for disabling add to car in listing</li>
<li>Added option calculateVariantsOnFreshLoad, which directly calculates the price for the selected variant (with php)</li>
<li>Fixed dragndrop for customs, products, categories, countries</li>
<li>Added missing states list and states edit layouts to new admin template</li>
<li>Hiddden config reuseorders, default is set to 0/off now</li>
</ul>
<h4>For developers</h4>
<ul>
<li>New variable to set origin debug state, vmEcho::$debugSet and vmEcho::$logDebugSet</li>
<li>VmEcho added check for function_exists('var_dump'), which is used if existing</li>
<li>VmJsApi function setPath, we add BE and FE override paths only if given, also added the override paths of the current admin template</li>
</ul>
<h4>Fixes</h4>
<ul>
<li>Fixes for pagination in cowork with RuposTel, correct use of index.php and using categoryId via request over the one set in the menuItem</li>
<li>Fixed problem with jumping thumbs in product edit related products/categories</li>
<li>Enhanced table updater to work also with Index and better check which indexes should be modified</li>
<li>User model, unset register pw in case of fail in log</li>
<li>Added new pattern using vmEcho::$debugSet</li>
<li>Fixed old VmConfig::$_debug against VmEcho::$_debug</li>
<li>Customfields function calculateModificators added check for string before vmJsApi::safe_json_decode</li>
<li>xml format fixes</li>
<li>Translated text for "catalogue mode and accessing cart"</li>
<li>Textinputplugin checks letters only if there are some letters, fixed missing jQuery</li>
<li>Model customs function getCustoms, removed useless return as $data-&gt;items instead just as $data</li>
<li>Removed outdated dead code</li>
<li>PayPal Checkout fixed mix of dynamic and static calls</li>
<li>Use of new VmEcho debugSet pattern and new ppdebug</li>
</ul>";}i:1;O:8:"stdClass":3:{s:4:"link";s:84:"https://virtuemart.net/news/release-of-virtuemart-4-4-and-roadmap-always-push-beyond";s:5:"title";s:57:"Release of VirtueMart 4.4 and Roadmap, always push beyond";s:11:"description";s:10794:"<p>The new version 4.4 has few new features, but reflects the evolving development process. Longterm users know that VirtueMart is developed in a rapid prototyping and KaiZen philosophy style. So if we develop a new feature, our testers sometimes have 10 new test versions a day. On the other hand, we try to evolve the code without hard breaks. For example developers can use the same function to get a category tree, but the technic behind that function drastically changed over the years. No stone was left unturned. </p>
<p>A known joomla ecommerce component announced last weeks, that they stop development. Of course some eager developers created a fork, but they will run into the same problems as the prior developers. And these problems are similar to our problems, in that it is not enough to just keep the core development paid and ongoing, we must as a community also keep our 3rd party developers and encourage new ones to join us. </p>
<p style="text-align: center;"><a class="btn btn-primary" href="//dev.virtuemart.net/attachments/1386">DOWNLOAD VirtueMart 4.4.0<br />NOW</a></p>
<h3>Lets create a VirtueMart for Joomla and Wordpress</h3>
<p>If we manage VirtueMart on Wordpress we can increase our ecosystem drastically. Years ago, I already managed it to see the sample products on Wordpress. To bring VirtueMart alive on WordPress I need people who are at best 3rd party developers for it and who can help to make it a round thing. Furthermore users who know Wp and are willing to test. And finally people who want to setup a real store with VirtueMart and Wordpress.</p>
<p><img class="float-none" style="display: block; margin-left: auto; margin-right: auto;" src="https://virtuemart.net/images/stories/news/Kuhstall_Frienstein_copyright_Max_Milbers.png#joomlaImage://local-images/stories/news/Kuhstall_Frienstein_copyright_Max_Milbers.png?width=800&amp;height=291" width="800" height="291" /></p>
<p>There are 2 main reasons, first we have competitors in the joomla area, compared to 12 years ago and Joomla base itself is shrinking. It is not simple to gain insight using just the download numbers, but I think it is possible to see a trend. Btw, our download numbers were horrible after providing the new installer only for members, because the members downloads are not counted with our redmine system. So yes, it is fragile. Back to Joomla downloads, there are a lot things to consider, for example joomla 4.4 has not a lot downloads, because on the same day they released j5. For example j4.4.9 has only a bit more than 65k updaters. But j4.4.8 has a bit more than 100k. Of course a lot updated to j5 already.</p>
<p>But the trend is,... </p>
<ul>
<li>Joomla 1.5.26 (last joomla 1.5) was downloaded as installer more than 1 Million times and 800k Updaters (ca 1,6 Million pages), Lifetime 4 years</li>
<li>Joomla 2.5.28 (last joomla 2.5) was downloaded as installer more than 450k and 1,6 Million Updaters  (ca 2 Million pages), Lifetime 2 years</li>
<li>Joomla 3.10.12 (last joomla 3) was downloaded as installer just 50k but 1 Million Updaters (ca 1 Million pages), Lifetime 9 years</li>
<li>Joomla 4.3.2 (last joomla 4 without j5) was downloaded as installer just 50k and only 250k Updaters (ca 280K pages)</li>
<li>Joomla 5.1.4 (last joomla 5.1) was downloaded as installer just 50k and only 100k Updaters (ca 130K pages) 3 years old</li>
<li>Joomla 5.2.0 (last joomla 5) was downloaded as installer just 20k and only 80k Updaters (ca 90K pages), 1 years old</li>
</ul>
<p>People loved Joomla 1.5 so much, that they installed the last version more times, than updating. But you can assume that most updaters to joomla 5 are just people who used joomla 4 before. So I think that there are less than 1 million joomla pages left (compared to the aproximate 3 million maxium)<br />and in prior times we also had more mirrors in local communities. Yes I did not count the downloads from, for example the german community page. But I doubt the numbers are very different. It is clear to see that the joomla community is sick of "general updates".<br />From my point of view it is very old school to change the architecture for a major new version. When I grew up with computers, it was a typical problem that old programs just did not work on new operating systems. Windows 3.1 software did not work on DOS, and Win95 software may stopped working on WinXP. The Linux world was not better! But with the time, it changed. Nowadays you can assume that a win7 program works on win10. The same for Linux. But somehow, in the joomla world,... But I still have great hope, that Joomla 5 is the standard for the next 10 years, but not like Joomla 3 (which had core changes until j3.5, so for us it was just 5 years). A system lives by the 3rd party developers joining. The lower the maintenance costs the higher the chance that people use it in the long run. On the other hand there must also be new opportunities for 3rd party developers to earn money. At best with new features and not just maintaining old ones.</p>
<p>The next problem are our free extensions. A lot free extensions vanished, after any big change in the joomla core. When I started with j1.5 there were tons of free extensions for small problems. The next problem is to find a replacement for the old good working extensions. There are still shops running on very outdated joomlas, because the maintainers do not find solutions for already solved problems for Joomla 3, 4, 5.</p>
<p>and here a direct speech of GJC, one of our forum moderators:<br />"It is pretty obvious to me.. the majority of my direct customs are one person small shops -<br />they get by but they aren't making big money, normally the shop is a side line or add on to their other activities.<br />How can I in all honesty come to them and say you must upgrade to J4/5 because it's wonderfully rewritten and uses the latest php etc etc.<br />When I show them it they hate the admin ( changed just for change ) and they ask sensible questions like is it faster? is it better? will it increase my sales? etc..  and of course I have to say no .. your customers will see no difference if I build the template the same as u have now but you get to use extra clicks to reach anything in the admin.<br />My host offers 1 click php 5.2 to 8.4 .. they tell me all phps are "hardened" and that they are happy to offer these versions for the foreseeable future.</p>
<p>I also do a lot of migrations for web agencies that of course really push their clients to upgrade.. <br />I have never yet found a client that liked the new admin or thought anything was any better after the upgrade .. <br />all it did was cost them a lot of money with the suspicion that it probably wasn't necessary ... </p>
<p>I bet when J6 swings around they will tell the agency where they can stick it... "</p>
<p>So in short, our customers dont see any added value by a new joomla core. And new features of Joomla dont need a new core. Joomla 5 itself is super! But Joomla already lost a lot ground against the simple working solutions. Joomla should stop reinventing the wheel all the time. There is so much that the Joomla community could do to enhance joomla without touching the core (except bugfixes). I think, almost any new feature for normal users could have been done in joomla 1.5 as well. For example versioning, better media uploader, and so on. So Joomla should take a break and care for documentation of the code and use the current core to develop new features.<br />Just while I am writing this article, I want to add an image. I use "Insert Image", but I cannot upload in this dialog and I need to enter some path? Then I used "Images and Links". There you can upload an image. Then I set the image for the content page, looks awful. I need class to prevent floating. So I use the generated link there to use again "Insert Image" and can now copy paste the path. To make this better we do not need to change the core, just the editor and that is a typical example.</p>
<p>Of course, all the architectural changes and usability changes are made to make development easier and the code faster, cleaner, more robust, and so on. But all these cool things can't show their value if it changes when it's just starting to run.</p>
<p>But, as I explained the last news, we should have peace against adjustments for the next 3 years at least. We could stand still and wait, but I think we should push forward. I suggest to develop VirtueMart for WordPress. That would open the VirtueMart ecosystem to a lot new potential users. I know that there are Wordpress users waiting for it. Join development here https://forum.virtuemart.net/index.php?topic=152160.0</p>
<h3>The new features which round up 4.4.0</h3>
<h4>New Features</h4>
<ul>
<li>Use category menu items with manufacturer categories</li>
<li>Use manufacturer menu items with manufacturer categories</li>
<li>New optional feature, registration is only available if a product is in the cart</li>
<li>Added option for ask a question to vendor, could be misused for spam similar to ask a question about a product</li>
<li>Plugin trigger plgVmOnSendVmEmail can prevent sending of mail completly by returning false</li>
<li>New plugin trigger for vmError</li>
<li>Sales report with sku </li>
</ul>
<h4>Enhancements/Fixes</h4>
<ul>
<li>Added missing trigger plgVmDisplayLogin to bs5-login layout</li>
<li>Fixed hard coded string "Do you have an account?" <a href="https://forum.virtuemart.net/index.php?topic=152097.0">https://forum.virtuemart.net/index.php?topic=152097.0</a></li>
<li>Grid changes for desktops and styling fixes</li>
<li>Removed unnecessary VmConfig::getConfig</li>
<li>Fixed updating of category_categories table</li>
<li>Router fix productdetail</li>
<li>PayPal Checkout added check for requested userfields published</li>
<li style="color: rgb(224, 62, 45);"><span style="background-color: rgb(255, 255, 255); color: rgb(224, 62, 45);">PayPal Checkout, fixed popup plugin with enabled Joomla HTTPS Headers plugin</span></li>
<li>Moved ajax function recalculate to an own json file, more compatible to joomla</li>
<li>User model considers joomla setting for register mail</li>
<li>Removed unnecessary references</li>
<li>VmModel set _maxItems to public in product model, set the hidden config absMaxProducts to 400 and fixed that maxItems returned one product too much</li>
<li>Small fix for product module</li>
<li>Small fixes for TcPdf and PHP8</li>
<li>Removed Multi-media upload for other views than product edit.</li>
</ul>
<h4>Templaters</h4>
<ul>
<li>The logic which decides if registration should be shown is moved to the view.html. Just use the provided booleans</li>
<li>Enhanced the function for templaters to show register fields and button yes/no and so on</li>
</ul>";}i:2;O:8:"stdClass":3:{s:4:"link";s:94:"https://virtuemart.net/news/vmbasic-the-new-virtuemart-native-bootstrap-5-template-and-layouts";s:5:"title";s:67:"VmBasic, the new VirtueMart native Bootstrap 5 template and layouts";s:11:"description";s:7672:"<h3>New Template vmBasic</h3>
<p>The new template is written by Spyros Petrakis and is kept simple to enable easy modification. It works fast out of the box and is written fully in Bootstrap 5. Mobile ready and simple to configure. Natively supported OPC.</p>
<p>The new layout system of VirtueMart allows you to use different bootstrap layouts and this now comes into play. The layout files of the vmBasic are integrated in the core as files with the prefix bs5-, meaning these basic layouts can easily be used in any other bootstrap 5 templates. This means VirtueMart has arrived in the Bootstrap 5 world finally. Special Virtuemart css classes are reduced, so it is also easier to learn. This should increase the out of the box useablity of templates for VirtueMart. The Bootstrap 5 layout is set for Joomla 5 automatically. Existing layout overrides are kept.</p>
<p><img class="float-none" style="display: block; margin-left: auto; margin-right: auto;" src="https://virtuemart.net/images/stories/news/vmBasic_featured_2024-08-23.jpg" width="693" height="446" /></p>
<h3>Rough times behind, calm waters ahead, let's sail into the future.</h3>
<p>The last few years have been characterised by constant changes in the software environment of VirtueMart. Yeah, I know, I have written this often already, but it really had a massive impact from j3 to j4 to j4.2 to mysql strict mode to php8.0 to php8.2 to bootstrap 5. The errors for the mysql strict mode and PHP8.2 were unpleasant to find. The prior big advantage to use PHP was the simple type juggling, but that has become much stricter and as a result we had to remove a lot small inconsistences in the code. For example, before it was allowed to initialise a variable with false (boolean) and to set it later to "all" (string). Now it must be initialised as string directly. The mysql strict mode created similar problems. Storing of strings formed as integer like "7" were stored correctly before, but now it must be a correct integer. </p>
<p>This created a lot of work, but the mysql strict mode existed for years and the new strict php types just follow developer languages like Java or C, so there are no hidden surprises. After our changes these modernisations should now be catered for. Joomla 5 will be supported the next 3 years at least. There is no release date yet for PHP 9 and the known changes are mostly covered (PHP8 Warnings will become fatals, so we did our homework already to 98%). The development for Bootstrap 6 has not even started yet (officially) and they say a new version would take at least 5 years. This means these problems cannot appear again in this massive way. This is good news especially for shops who were forced to frequently update their template over the last years.</p>
<p style="text-align: center;"><a class="btn btn-primary" href="//dev.virtuemart.net/attachments/download/1378/com_virtuemart.4.2.18.11050_package_or_extract.zip">DOWNLOAD VirtueMart 4.2.18<br />NOW</a></p>
<h4>Interesting new features for shopowners</h4>
<ul>
<li>New bootstrap 5 frontend template (vmbasic)</li>
<li>Moved vmbasic bs5 views and assets in the core folders</li>
<li>Frontend Template system checks now also in media/templates/site/yourTemplate/com_virtuemart... for ressources, this means common joomla 5 templates should work as expected (by VirtuePlanet.com)</li>
<li>Added storing and loading of user addresss to cart for native OPC </li>
<li>New media type webp by vdweb.cz and alxgan https://forum.virtuemart.net/index.php?topic=151601.0 and tagarrison (fancybox)</li>
<li>Enhanced feature recommend a friend, ask a question, call for price, there is now a new option, which allows to use this function only as customer who already bought</li>
</ul>
<h4>Enhancements (or fixes)</h4>
<ul>
<li>Enhanced the user switcher (started with a bug in some templates)</li>
<li>Userfields in the array can now be accessed by name, this makes it a lot easier to controll a form or output of an address</li>
<li>Added manufacturer and manufacturer images for category views with set manufacturerId cart view,</li>
<li>Added "text/csv" to safe mime types. So we can use the vm file uploader also for csv files</li>
<li>Enhanced autochecker of the cart to work also with text fields (for the native OPC)</li>
<li>Enhancements for multi image uploading and enhanced image recognition by a community member</li>
<li>Changed loading of user data in the backend, loaded before only BT form, it checks now for published, but not cart attributs</li>
<li>Added language switcher and vendor module to public svn</li>
<li>Function deleteOldPendingOrder changed rules, it deletes P and U state now, if an orderId is given in the cart.</li>
<li>It is possible to set an OrderId for the cart</li>
<li>Enhanced PayPal Checkout; Button loader with asnchron fetch</li>
<li>Enhanced PayPal Checkout; Disabled trigger plgVmOnUpdateOrderPayment completly. Problem, doing a partial refund on PayPal sets the R Order status, which triggers this function, which does a complete refund.</li>
<li>PayPal removed old debugs, cleaned debug mode, less logging</li>
<li>HandlePaymentUserCancel sets the order status now on P NOT C anylonger.</li>
<li>Enhanced the invoice download button. Appears now also if the pdf is not rendered (because it is then rendered for the download)</li>
<li>Enhanced info messages if safe path is missing</li>
<li>Fixed display of shipment/payment in order/invoice, if something went wrong (fallback to method name)</li>
<li>Enhancement for xml update files</li>
</ul>
<h4>Fixes</h4>
<ul>
<li>vDispatcher adjusted to Joomla 5 by stAn of RuposTel, this means that the recaptcha works again for joomla 5 and also other j4/j5 plugin</li>
<li>Fix for the styling of invalid checkboxes and tos on checkout page.</li>
<li>Fixing the "customer_notified" record broke the comments in the mail.</li>
<li>Fixes for userfields display</li>
<li>vmURI urlencode replacement for PHP8 created a loop accidently</li>
<li>#__virtuemart_order_histories table, increased order_status_code to char 3</li>
<li>Removed js note in cart "unreachable code"</li>
<li>userfields enhanced function getUserFields, added switch to give query (without ordering by)</li>
<li>userfields $_fld-&gt;type == 'webaddress' returns an URL as &lt;a&gt; html element</li>
<li>spwizard added function_exists('str_contains') for people using php 7</li>
<li>Fixed dragndrop for the product view product sorting (by AH)</li>
<li>Important fix, so that editing an order executes the same filters, actions for userfields like coming from the cart</li>
<li>Important fix for storing of customfields. Due accidently using the same variable name, it could happen, that the wrong customfield was set</li>
<li>during the foreach loop on the looped array.</li>
<li>VmUploader shows for admins a complete path and for users just the file name after successful upload</li>
<li>Smaller fix in mediahandler to prevent folders used as image</li>
<li>Enhanced the vmtable, addloggable directly adds setInteger enhanced product table, added variables to be cast to integer</li>
<li>Removed old arrays in userfields</li>
<li>Invoice_locked should be fixed. Considers the object/array problem now </li>
<li>Fix for category dropdown as tree</li>
<li>fixed product model getNeighborProducts if there is no extra "where", can happen if a shop shows only productdetails </li>
<li>Increased varchars of column layout of the product table</li>
</ul>
<p style="text-align: center;"><a class="btn btn-primary" href="//dev.virtuemart.net/attachments/download/1378/com_virtuemart.4.2.18.11050_package_or_extract.zip">DOWNLOAD VirtueMart 4.2.18<br />NOW</a></p>";}i:3;O:8:"stdClass":3:{s:4:"link";s:60:"https://virtuemart.net/news/11000-committs-virtuemart-4-2-12";s:5:"title";s:33:"11000 committs, VirtueMart 4.2.12";s:11:"description";s:6412:"<p>[Hotfix Update 4.2.12]<br />There was a problem with creation of invoices. The Locking boolean was not correctly set and/or wrongly read. At least we know what we have todo next to prevent coding traps. </p>
<p><br />The focus of this release was to identify and fix bugs. In addition, we added small enhancements for the Joomla 4/5 GUI. Tooltips should work again and opening a VM view in the backend opens the VM menu and minimises the joomla one.<br />We also invested a lot time in the routing process. It may be necessary to remove setting the itemid per layout file.</p>
<p>Please check this link for details  https://dev.virtuemart.net/projects/virtuemart/repository/virtuemart/revisions/10996.</p>
<p style="text-align: center;"><a class="btn btn-primary" href="https://extensions.virtuemart.net/support/virtuemart-supporter-membership-bronze-detail">DOWNLOAD VirtueMart 4.2.8 with the Membership<br />NOW</a></p>
<p>All tables are changed to InnoDB. VirtueMart used before a mixed set. Tables which are most time just read were MyIsam and tables which are often read and write (like orders) used InnoDB. MyIsam has not been developed further for years and InnoDB has become faster for all tasks (don't pin me down to "all" of them). The provided server configurations won't pay attention to MyIsam (reserved RAM, for example), so now it seems the best time to switch to InnoDB for all VM tables.</p>
<p><br />We also worked with PayPal Checkout. The merchant onboarding process is sometimes not finished. We found out, that this is caused by popup blockers of the browsers, even without extra popup blocker plugins.</p>
<p>Housekeeping wise, despite all my personal efforts, the email server was still not running right. Reseting password did not work, so I enlisted the aid of our server admin and it is now fixed. Emails were received again at strict hosters. It turned out, everyting in our domain space was configured without www, except the email server. The fix broke emails a day before Easter and unfortunately the holidays meant that we did not identify the issue until a week later. This is software, the idea "Let's do it right, lets update anything" can result in some setbacks.</p>
<p><br />Thanks to our membership, we could contract Spirous Petrakis of yourgeek.gr and the next release will have a new Bootstrap 5 native VirtueMart template by Petrakis. This is no vaporware, the template will be offered to the core team for beta testing right after the release. Offering it to early would have delayed this important maintenance release.</p>
<p>Another result of the VM membership subscription is the multi image upload written by the team of 911websiterepair.com. It simplifies adding media, if you do not want to manipulate the existing one. But make sure that you store your product first!</p>
<h3>General enhanced features</h3>
<ul>
<li>Multi file image upload by 911websiterepair</li>
<li>Classes for userfields by Gerald DWP</li>
<li>Updated product module with option "any product" (by community input)</li>
<li>Enhanced translation for custom drop down by GJC</li>
<li>changed MyISAM to InnoDB</li>
</ul>
<h3>Fixes for Joomla 4/5 and PHP8.2</h3>
<ul>
<li>Fixed seo link of products, category was missing</li>
<li>Opening a VirtueMart backend view minimises the joomla menu automatically. There is an hidden config to disable it.</li>
<li>Fixed tooltips in j5</li>
<li>Updates for TcPDF for PHP8.2</li>
<li>Added full path for vmvalidator to ensure loading in joomla 5</li>
<li>Added lost empty option for select product detail layout in product edit</li>
<li>Revenue, added VendorInformation for interval products </li>
</ul>
<h3>Fixes</h3>
<ul>
<li>Fixes for router to prevent unecessary "result" in the link and added more views to the whitelist</li>
<li>Fixed router problems in j4, correct use of the preprocess. itemids are set correctly, removed unecessary or wrong Itemids from the layouts</li>
<li>CouponHandler fixed foreach for allowed products, allowed categories</li>
<li>Javascript, replaced all "delegate" against "on"</li>
<li>Fixed that dropdown could not be used to add the same option twice</li>
<li>Fixed removeable and draggable enhanced css</li>
<li>Important fixes in the cart to ensure that the individual cart is correctly linked in the carts array</li>
<li>fixed update of carts</li>
<li>Model category, function getParents uses now a language depended cache</li>
<li>Added itemid to pagination links</li>
<li>Fixed manual installation of "shipping advanced"</li>
<li>Small fixes for mediahandler, removed unecessary loads</li>
<li>Fixed closeBtn to showCloseButton of fancybox</li>
<li>Fixed checkFilterDir if given filterDir is empty</li>
<li>Model users, send registration email only, if mail is active</li>
<li>Fix for customfield Multichild in BE</li>
<li>Fixed multichild variant with radios</li>
<li>Fix for storing shipment address as guest</li>
<li>Better check to allow shopper change, if already switched</li>
<li>Fixed multichild variant with radios</li>
<li>CalculationHelper added order by for loading rules</li>
<li>added missing help icons and other minors for backend views</li>
<li>Structural core fixes Important fix in VmModel function getData, uses reset if an array of ids is given Important fix for VmController</li>
<li>function getStrByAcl, uses now the unfiltered POST data</li>
<li>added vmJsApi::writeJS to correct places.</li>
<li>calculationHelper added order by for loading rules</li>
</ul>
<p>and a lot adjustments for php8.2, removed unused or dangerous code</p>
<h3>For developers</h3>
<ul>
<li>orderdone view has now the orderId</li>
<li>getPluginMethods with new $userId dependence</li>
<li>product Model added importVMPlugins to begin of function sortSearchListQuery</li>
<li>added register of vmrouterHelper for autoloading cart helper</li>
<li>Layout orderdone now set by cart-&gt;layout</li>
<li>enhanced Exception message if sending of email fails</li>
</ul>
<h4>PayPal Checkout</h4>
<ul>
<li>PayPal Checkout enhanced merchant onboarding. Added notice to disable popup blockers</li>
<li>Minor fixes for PayPal  function updateStatusForOneOrder called by PayPal now with triggers</li>
</ul>
<p style="text-align: center;"><a class="btn btn-primary" href="https://extensions.virtuemart.net/support/virtuemart-supporter-membership-bronze-detail">DOWNLOAD VirtueMart 4.2.8 with the Membership<br />NOW</a></p>";}i:4;O:8:"stdClass":3:{s:4:"link";s:51:"https://virtuemart.net/news/virtuemart-for-joomla-5";s:5:"title";s:23:"VirtueMart for Joomla 5";s:11:"description";s:7864:"<p>Joomla 5 is the new long term release of Joomla. Surprisingly it was very easy to adjust VirtueMart 4 for Joomla 5. This was planned as the christmas release, but now it has become a Happy New Year release with some delay.</p>
<h4>Project development through Membership</h4>
<p>Sören, the founder of VirtueMart, helped me to update the entire infrastructure of the VirtueMart project (with Membership fundings). Our infrastructure, email server, svn, redmine, forum and our websites were quite outdated. So it was time to update everything. We now have a new redmine (web-based project management software) for dev.virtuemart.net and the forum and mail server have also been updated. I have been working on the latter for weeks to configure it with DMARC, SPF, DKIM, reverse DNS and so on. Finally, I moved all pages to the web host hetzner and updated all virtuemart.net pages to Joomla 5, with the exception of demo.virtuemart.net. Any of our virtuemart.net pages are running now with the new template of <a href="https://www.virtueplanet.com/joomla-templates/vp-neoteric" target="_blank" rel="noopener">Neoteric</a> of VirtuePlanet, with extra customisations (with Membership fundings) <br />But a new native BS5 template is already in the pipeline. If all goes well, we will soon have a new VirtueMart-native BS5 template that will be used as the new template for the core, on the demo and of course with Joomla 5! So we did a great step to ensure a great lifetime for the project (with Membership fundings).</p>
<h4>Core testing</h4>
<p>The testing team was really very active. PayPal Checkout was tested very thoroughly. The new plugin works now also in the old redirect mode, but with the new API. This means a merchant can provide his users the new way with the enhanced PayPal (Express) button, or the old way, just using the cart checkout button in the same cart. The new unbranded Alternative Payment options available for different countries are also very interesting in special for Europeans.<br /><br />The core got updated for Joomla 5, of course it still needs the compatibility plugin. This was surprisingly easy. So I decided to use joomla 5 for new vm pages. Also updating a joomla 4 vm shop to joomla 5 is very easy (just check the languages!). Abhishek of VirtuePlanet donated code for minified css files and for the new child templates in joomla 5. Some shops run already since weeks on Joomla 5. Good Work Joomla!</p>
<p>GJC tested inheriting of parent properties and found a severe bug, which prevented that properties with _ were inherited, but this was only intended for the properties beginning with _.<br />Next important fix is in the cart, some redirects lead to the orderdone function. But this should be only done by the payment plugin. Only the plugin knows, if the payment was successfull</p>
<p style="text-align: center;"><a class="btn btn-primary" href="https://extensions.virtuemart.net/support/virtuemart-supporter-membership-bronze-detail">DOWNLOAD VirtueMart 4.2.6 with the Membership<br />NOW</a></p>
<h3>Features and Fixes:</h3>
<h4>General core</h4>
<ul>
<li>Important fix for "private" values starting with _</li>
<li>Abhishek integrated children templates and new setPath function by Abhishek (VirtuePlanet), which works with the new child template and with minified files</li>
<li>Very important cart fix, never use the layout orderdone for redirects in the function checkoutData</li>
<li>new Feature "set Real Image Size" in the IMG tag directly. This helps lazy loading and prevents jumping of the images loading the page</li>
<li>Fix for overrides of layouts with BS prefix. All developers should prefer getBaseLayout over getLayout</li>
<li>enhanced message if membership server cannot be reached</li>
<li>Tools GDPR tab; removed deletion of files by mdate or cdate. If someone moves the files, then the dates are updated and so this idea does not work safely</li>
<li>GDPR- removing invoices needs at least 3 in years</li>
<li>Log view, added an option to download or to delete a file</li>
<li>Changed getInvoiceFolder system, so that we can work in future with folders per year. The last problem is to either write a fallback system or tool which copies the data or both</li>
<li>Put more invoice functions to the right place the model, proxy functions for BC are there.</li>
<li>Little fix to prevent strange calls to controllers</li>
<li>user model stores value block in admin</li>
<li>removed old db-&gt;getErrorMsg(), raiseError and so on</li>
<li>Enhanced the functions getPluginCustomData and getPluginProductDataCustom</li>
<li>Removed minors like "Deprecated: Creation of dynamic property is deprecated" or null not allowed</li>
<li>some minors, removed outdated code, added default value, etc</li>
<li>Updated links to docs</li>
</ul>
<p> </p>
<ul>
<li>Enhanced installer script in j5 style for tcPDF by Stefan</li>
<li>Changed the "creator" and such stuff for tcpdf, should now work with given version (not VirtueMart 2 anylonger!)</li>
</ul>
<h4>Compatibility PHP8.x, Joomla 4/5</h4>
<ul>
<li>Multivariant radioboxes Javascript for Joomla 4</li>
<li>shopfunctions.php JPath::clean added check for empty</li>
<li>added joomla trigger to vmview onBeforeDisplay and onAfterDisplay</li>
<li>For Joomla 5, replaced uppercase Select with select</li>
<li>a try catch for getVarsToPushByXML (thx stAn)</li>
<li>JVM_VERSION works now also with version numbers with suffixes (-elts Version)</li>
<li>enhanced xml of modules for single installation (thx Stefan)</li>
<li>tcpdf.php, added if not empty and is array check, Small fixes for the install of TcPDF</li>
</ul>
<h4>Plugins</h4>
<ul>
<li>authorize.net increased size of authorizenet_response_account_number</li>
<li>PayPal Checkout, added PayPal developer mode to PayPal, which enables the sandbox</li>
<li>PayPal Checkout, enhanced form, added autocomplete off</li>
<li>PayPal Checkout, getPayPalAccessToken is only executed if the minimal data is available</li>
<li>PayPal Checkout, fixed return link if no button is used</li>
<li>PayPal Checkout, fixes to prevent js errors if no button is used</li>
<li>PayPal Checkout, checkout without button is more robust</li>
<li>PayPal Checkout, Unuseable products are greyed, but selectable</li>
<li>PayPal Checkout, replaced all cUrl against VmConnector::getHttp</li>
<li>PayPal Checkout, if capture is unsucessful block checkout</li>
<li>PayPal Checkout, admin js adjusted to grey out options on j4/j5</li>
<li>PayPal Checkout, changing login data overwrites it for all payments, sandbox is seperate</li>
<li>PayPal Checkout, message if you are already logged in</li>
</ul>
<h4>Important for developers and templaters</h4>
<ul>
<li>Fix for overrides of layouts with BS prefix. <span style="color: rgb(224, 62, 45);">All developers should prefer function getBaseLayout over getLayout</span></li>
<li>add to cart works now also with given data as POST.</li>
<li>Given ordernumber of the cart is used for the order</li>
<li>$cart-&gt;couponCode to $cart-&gt;cartData['couponCode'], $cart-&gt;couponCode is for internal use of the cart. The reason is that we must check the coupon on any update. So the value in cartData is the entered coupon and the value of the cart is the selected coupon. To clear both values, we have a new function clearCoupon</li>
<li>removed confusing doubled virtuemart_product_id inputs. Check revision 10944 and compare it with your template.</li>
<li>Multivariants with children. Replaced the js to use data-cvsel instead of the class cvselection. Check your override sublayouts/customfield.php</li>
<li>Somehow the automaticSelectedShipment switched into the template and changes the rendering of a shipment plugin. This was also for payment plugin and already removed. Templaters pay attention to the new code, thank you ( I know it was this strange way for years)</li>
</ul>";}}s:6:"output";s:0:"";}