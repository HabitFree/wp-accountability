# wp-accountability

## Setting Up Your Development Environment

### General Set-up

Install [Bitnami Wordpress Stack](https://bitnami.com/stack/wordpress), taking careful 
note of the login details and other settings chosen during installation.

Run `open /Applications/wordpress-4.3-0/manager-osx.app/` in a terminal, start all servers, and then access 
`http://localhost:8080/wordpress/` in a browser to view the site.

GIT clone `wp-accountability` into the `plugins` directory and (if desired) `hf-theme` into the `themes` directory.
Activate the plugin and (optionally) the theme within the WordPress admin panel 
(`http://localhost:8080/wordpress/wp-admin`).

### Setting Up WordPress Testing in PhpStorm

Based on [this tutorial](http://codesymphony.co/writing-wordpress-plugin-unit-tests/)

#### Mac OS X 10.6.8

1. In terminal: 
`cd /Applications/wordpress-4.3-0/apps/wordpress/htdocs/wp-content/plugins/wp-accountability/tests/`
2. `mkdir wordpress-dev`
3. `cd wordpress-dev`
4. `svn co http://develop.svn.wordpress.org/trunk/`
5. `cd trunk`
6. In a browser, navigate to `http://localhost:8080/phpmyadmin/`
7. Log in with `root` and the password you set while installing Bitnami Wordpress Stack
8. Create a new user called `wp_test`, taking careful note of its password, and instructing PhpMyAdmin to 
"Create database with same name and grant all privileges."
