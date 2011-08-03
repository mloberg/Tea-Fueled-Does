## 1.1.5

There is a new file inside content/_config called redis.php. This is the config file for the new redis class.
Please move this file into your content/_config folder.

## 1.1.6

There is a need admin hook in hooks.php, please update this file

## 1.1.8

There is a new folder inside of content called templates. This is used for the new templating system.
Please move this folder into your content folder, or create a folder called 'templates' inside your content folder

## 1.1.9

Redis config file in content/_config has been updated. This is not a required change, but brings some improvements.

## 1.2

The admin backend has it's own master. It's in content/masters/admin.php.
Please copy this file over to your masters folder, or create a file called admin.php within your masters directory.

## 1.2.1

Amazon S3 changes. Please update your code to fit these changes. (http://teafueleddoes.com/docs/amazon-s3)

## 1.2.2

Partials can only be rendered from the partials folder.

## 1.2.9

Updated *content/ajax/ajax.php* call method to send 404 if no file or method is found.

## 1.3.0

Flash method is now a class. You can still call flashes like you used to before (*$this->flash()*), but it's recommended to change to the new format *$this->flash->message(message, type, options)*

The render class was removed. For flash renders now use *$this->flash->render()* instead of *$this->render->flash*

Removed *js/form.js*. Use placeholder (or write your own function) instead.

## 1.3.1

BASE_DIR is now the directory outside of the PUBLIC_DIR not APP_DIR.

*tfd/_config.php* is now *tfd/bootstrap.php*. Other changes to *public/index.php* have also been made as well. I recommend replacing the old index.php with the new one.

The time isn't rendered to the page every call anymore. You will have to use *$this->profile()* to get an array of time and memory usage.

## 1.3.3

Added some options to config files.

* LOGIN_TIME in *general.php*
* ADD_USER in *environments.php*

Logins uses a user secret key, which is a key unique to each user. Add a column to you user table called secret.

## 1.3.6

Added maintenance mode. This adds a master called *maintenance.php*. Also adds another config option in *content/_config/general.php*.

## 1.3.8

Removed cookie based user authentication. This removes the LOGIN_TIME config option in *content/_config/general.php*.
