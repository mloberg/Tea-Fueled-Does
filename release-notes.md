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
