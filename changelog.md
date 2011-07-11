## 1.0.0

* App files don't have (and aren't recommended) to be in a web accessible folder
* Content folder moved outside of main folder
* Config files split up

## 1.0.1

* fixed issue with login
* content can be outside of the web directory

## 1.0.2

* all files with in content/config are loaded

## 1.0.3

* added send_404 method to send 404 error headers
* 404's use send_404 method
* updated htaccess to protect tfd and content folders if those folders are found in the public directory
* added JavaScript class
* added initialize hook

## 1.0.4

* actually included the JavaScript library
* added CSS library
* added render hook

## 1.0.5

* CSS library extended (see content/www/style.php for examples)

## 1.0.6

* Updated style CSS method
* Added add_font method
* Updated htaccess to point font(s) to a fonts folder

## 1.0.7

* ajax enhancements

## 1.0.8

* added flash method to display errors

## 1.0.9

* ajax is now a class
* added script method to js class to load written scripts

## 1.1.0

* fixed flash overflowing issue
* added render class
* added flash styling and "fade out"

## 1.1.1

* added pagination class

## 1.1.2

* minor changes to the postmark library

## 1.1.3

* updated JavaScript Class
  * load accepts an array or string
  * script method that takes functions, vars, etc. to load in between script tags
  * ready method that takes functions, vars, etc. to add between a dom ready function (mootools or jQuery only);

## 1.1.4

* changed how ajax is called (can now be anything before it, as long as it matches the magic ajax path (e.g /post/tfd-ajax/function will work as well as /user/tfd-ajax/function))

## 1.1.5

* fixed flash javascript error
* fixed error with S3 exception
* added math evaluation function
* added Redis support

## 1.1.6

* added admin (dashboard) hook
* fixed error where if tfd wasn't installed in the root, it wouldn't load

## 1.1.7

* fixed default login page
* minor pagination improvements

## 1.1.8

* added the mustache.php template system

## 1.1.9

* Redis will automatically run the auth call if you have a password setup
* Other Redis improvements
* Add user method

## 1.1.9.1

* upload class fixes/enhancements

## 1.1.9.2

* upload class fixes

## 1.1.9.3

* flash enhancements (sticky, edit time)

## 1.1.9.4

* image class fixes

## 1.1.9.5

* save_as method added to image class

## 1.1.9.6

* added link (href) helper

## 1.2

* added admin master

## 1.2.1

* Amazon S3 library improvements

## 1.2.2

* Partial method updated

## 1.2.3

* Changed how environments are set/work

## 1.2.4

* updated form open_upload element to not require method (default is post)
* JavaScript library fixes/enhancements/updates

## 1.2.5

* JavaScript fixes
* add admin user enhancements
* Postmark library enhancements
* Added user agent parser helper

## 1.2.6

* CSS and JavaScript will load using BASE_URL if http(s) is not in the source
* other CSS enhancements
* deleted htaccess rules that point urls ending in css/, js/ and font(s)/

## 1.2.7

* MySQL fixes
* added watermark image method to image manipulation library

## 1.2.8

* Added front end hook
* added $this->is_admin
