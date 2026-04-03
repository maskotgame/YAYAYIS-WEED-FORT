these files go outside of the site folder...

standard file structure should be:

/var/www/anorrl/
 - assets/
 - - thumbs/
 - renders/
 - site
 - users

of course, you can set the anorrl root directory to where ever but the folders HAVE to be next to where you put the site in.

also the other folders NEED to be set to the same permissions as the webserver or be owned by the webserver you want to use. every subfolder too.
^^ site is fine you can set it to whatever, the code only reads images from the images directory at MOST.

what needs to be in the root...

settings.json
PrivateKey.pem (generated from RBXSIGTOOLS but any rsa private key generator works)

some info..

anorrldb.sql is meant to be loaded in via phpmyadmin ALSO it is just the structure only ALSO it doesn't have to be named anorrldb (you can change that in settings.json)
settings.json HAS to be changed before being used for production. It is just a template right now.
