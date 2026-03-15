these files go outside of the site folder...

standard file structure should be:

/var/www/anorrl/
 - assets/
 - - thumbs/
 - renders/
 - site
 - users

of course, you can set the anorrl root directory to where ever but the folders HAVE to be next to where you put the site in.

what needs to be in the root...

settings.env
PrivateKey.pem (generated from RBXSIGTOOLS but any rsa private key generator works)

some info..

anorrldb.sql is meant to be loaded in via phpmyadmin ALSO it is just the structure only ALSO it doesn't have to be named anorrldb (you can change that in settings.env)
settings.env HAS to be changed before being used for production. It is just a template right now.