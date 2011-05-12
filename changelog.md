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
