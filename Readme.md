# RNA 3D Hub Web Code

## Installation

Requirements:

* [Vagrant](https://www.vagrantup.com/)
* [VirtualBox](http://www.virtualbox.org/)

Runs on [Scotch box](https://github.com/scotch-io/scotch-box).

```
git clone 
cd RNA-3D-Hub
# start virtual machine
vagrant up
# edit template file to configure database connection
cp application/config/database_template.php application/config/database.php
# other app configuration
cp application/config/config_template.php application/config/config.php
# create .htaccess from template
cp htaccess .htaccess
# setup port forwarding for remote database access
vagrant ssh
ssh -L 9000:localhost:3306 <user>@rna.bgsu.edu # leave the session open
```

Go to [http://192.168.33.10]() to view the website.
