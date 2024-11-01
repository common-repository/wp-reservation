=== WP-Reservation booking system ===
Contributors: saint
Donate link: http://reservation.isaev.asia/donate
Tags: reservation, booking, hotels, hostels, apartments, rooms, resources, gym, cars, theater, cinema, doctors, rentals, workers
Requires at least: 3.5
Tested up to: 4.4.2
Stable tag: 1.5.4

WP-Reservation - This WordPress plugin allows for the creation of own sites with a booking system. It can apply to their sites hostels, hotels, hotels portal, travel companies, doctors, GYM.


== Description ==

This WordPress plugin allows for the creation of own sites with a booking system. It can apply to their sites hostels and hotels.
Your users accessing the site, will fill a special order form and place your reservation. During the checkout process automatically register the user in WordPress.
To order management created an administrative part. You can always see the true information about orders and customers, to confirm payment of your reservation, as well as a book by hand.

Features:

- Your clients can make reservation of resources(rooms, cars, doctors ...) directly on your site 
- You can book multiple resourcess in one order 
- During registration will automatically register the customer in WP 
- Sending notification of registration email 
- The customer can see his orders 
- The client may send a notice of refusal to order 
- Administrators can create orders manually 
- Administrator can change, delete and confirm orders 
- Administrators can - add / delete / edit resources 
- You can use the shares for different types of discounts in different seasons 
- Administrators can send a message to email client

== Installation ==

1. Upload the whole wp-reservation directory into your WordPress plugins directory.

2. Activate the plugin on your WordPress plugins page

3. Edit or create a page on your blog which includes the text {RESERVATION}  and visit 
   the page you have edited or created. You should see your reservation in action.
   Or try  three other interfaces {RESERVATION2}, {RESERVATION3}, {RESERVATION4}

4. For PRO version added tag {RESUSERPAGE} . This tag will show your customers their orders and order status. Users can cancel it or continue to pay.
   
4. For PRO version added tags {RESERVATION+id1,id2,...} and {RESERVATION-id1,id2,...} for reservation only one resource on page or all resources exclude one. Examples {RESERVATION+103},{RESERVATION+103,104},{RESERVATION-103}   
   

Uninstalling:

1. Deactivate the plugin on the plugins page of your blog dashboard

2. Delete the uploaded files for the plugin

3. Remove the text {RESERVATION} from the page you were using to show reservation, or delete that page   

== Frequently Asked Questions ==

Please visit <a href="http://reservation.isaev.asia/faq/">wp-reservation.isaev.asia/faq/</a>.

== Screenshots ==

1. Interface {RESERVATION4} first page

2. Orders table

3. Reservation being used on a blog page

4. Admin page, editing type of resources

5. Admin page, editing special offers

6. Alert box

== Changelog ==

= 0.5 =

Inital beta release

= 0.5.1 =

- Changed some text messages
- Rebuilded admin menus

= 0.5.2 =

- Added spanish translation (thanks Melvis Leon)
- Changed mail form
- New tabs on admin pages
- Adding CSS for admin panel
- Changed options page 
- Fixed several translation errors

= 0.6 =

- Fixed bug with access rights to the plugin 
- Added settings of access rights 
- Don't worked cursor in the admin menu 
- Added a field phone in a user profile 
- Time of arrival is recorded in the order comment

= 0.6.1 =

- Fixed translation errors 
- Fixed "Save" button in settings

= 0.6.2 = 
- Fixed problem "No information about free resources" for not wp_ table prefixes (thanks Gaz and Rodion)
- Fixed substitutions language for registration emails (for other languages, not english)
- Fixed calendar css and js (not correctly worked with some themes) (thanks Gaz)
- Fixed a compatibility problem with some plugins

= 0.7 = 
- Completely changed the system of resources. This is the first step toward the creation of reservations for various businesses. Such as rental cars, gyms, clinics, hotelsm, hostels

= 0.7.1 = 
- Fixed error with save changes on resources page

= 0.7.2 = 
- Added buttons for add first resource, and expand|collapse buttons for resource tree

= 0.8 = 
- Fixed small bug in one of the queries (table preffix)
- Updated text messages 
- Fixed URL in plugin page on wp-reservation settings
- Fixed some options
- Fixed problem with new installations (after activated plugin, resources was empty)
- Fixed problem with jQuery tabs and WordPress Post Tabs plugin

= 0.9 = 

- Fixed problem when admin use own email for create reservation
- Fixed suborder page in admin pages
- Added tags {RESERVATION+id1,id2,...} and {RESERVATION-id1,id2,...} for reservation only one resource on page or all resources exclude one. Examples {RESERVATION+103},{RESERVATION+103,104},{RESERVATION-103}
- Fixed sending messages from admin to customer
- Increased quantity of passwords in pass.txt 

= 0.9.1 = 

- Added support for some comercial themes
- Fixed warning in WampServer2
- Changed password generation function
- Added english language files (.po,.mo) for quick change plugin phrases. For example, for replacing the word "Resource"  on a "Room" or "Car". To edit the language files, use Poedit http://www.poedit.net/

= 0.9.2 =

- Fixed error in query on suborder page 
- Add setting "How many days to show for choosing"
- In PRO version reduced the number of steps of ordering if you only use one payment system


= 1.0.1 = 

- Run a Pro version

= 1.0.2 = 

- Added examples of tags in readme.txt

= 1.1 = 

- Added return on home page before payment 

= 1.2 = 

- Fixed a bug with the multiplication of days
- Fixed small errors on suborder admin page 

= 1.3 = 

- Added two new tags {RESERVATION2} new interface with a list only groups, {RESERVATION3} new interface with a list of all resources.
- Changed CSS styles for better support WP 3.2.1
- Added advance payment of # percent(%) of the total order by
- Changed javascripts alerts on jQuery dialogs
- Added the ability to pay for the interrupted order. Only for PRO
- Fixed a bug when you return to the home page

= 1.3.1 = 

- Added new tag {RESERVATION4} new interface with a two calendars and two selects for choose resources (recommended for hotel portals).

= 1.3.2 = 

- Fixed receipt of SberBank
- Add terms of booking (see settings)

= 1.3.3 = 

- Fixed per night calculation for {RESERVATION2}, {RESERVATION3}, {RESERVATION4}
- For resources with the quantity 1, <select> is replaced on <input type="checkbox">, on second  page for all interfaces.

= 1.3.4 = 

- Fixed plugin deactivation
- Fixed error with Call to undefined function (in free version)

= 1.3.5 = 

- Fixed problem in {RESERVATION4} interface
- Fixed a compatibility problem with the plugin WPML Multilingual CMS
- Replaced deprecated functions for compatibility with PHP 5.3.0

= 1.3.6 = 

- Added Orders table for PRO

= 1.3.7 = 

- Changed capacity 1 on green-tick and 0 on red-cross in front-end.

= 1.3.8 = 

- Fixed capability problem with Async Social Sharing plugin
- Removed empty <select> on the second page order 

= 1.3.9 = 

- Added customizable confirmation mails for client and administator for PRO version. You can use this tags:
	[login] - client login
	[password] - client password, 
	[num] - order number
	[sum] - total amount of order
	[datebegin] - arrival date
	[dateend] - end date
	[first_name] - first nam
	[last_name] - last name
	[advance] - advance fee
	[order_details] - order details

= 1.4.0 = 

- Fixed: 
	 Warning: Missing argument 2 for wpdb::prepare()

= 1.5.0 = 

- Fixed "PRO" in submenu
- Added additional currencies for PayPal in PRO
- Fix User-Agent for PayPal in PRO

= 1.5.1 = 

- Changed css for UI dialog

= 1.5.2 = 

- Some fix yet for UI dialog

= 1.5.3 = 

- Tested with WP 4.4.1

= 1.5.4 = 

- Tested with WP 4.4.2
	 
	
== Upgrade Notice ==

= 0.5 =

= 0.7 =
Before upgrade make a backup of DB.