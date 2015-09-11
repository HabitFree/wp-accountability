# wp-accountability

## Setting Up Your Development Environment

### Installing PHP, MySQL, PhpMyAdmin, and WordPress

Install [Bitnami Wordpress Stack](https://bitnami.com/stack/wordpress), taking careful 
note of the login details and other settings chosen during installation.

Run `open /Applications/wordpress-4.3-0/manager-osx.app/` in a terminal, start all servers, and then access 
`http://localhost:8080/wordpress/` in a browser to view the site.

GIT clone `wp-accountability` into the `plugins` directory and (if desired) `hf-theme` into the `themes` directory.
Activate the plugin and (optionally) the theme within the WordPress admin panel 
(`http://localhost:8080/wordpress/wp-admin`).

### Setting Up WordPress Testing in PhpStorm

Based on [this tutorial](http://codesymphony.co/writing-wordpress-plugin-unit-tests/).

#### Mac OS X 10.6.8

These instructions assume that you've already installed the Bitnami WordPress Stack. Note that you may need to modify paths based on the version of WordPress you install.

##### Set up PHPUnit with WordPress

1. In terminal: 
`cd /Applications/wordpress-4.3-0/apps/wordpress/htdocs/wp-content/plugins/wp-accountability/tests/`
2. `mkdir wordpress-dev`
3. `cd wordpress-dev`
4. `svn co http://develop.svn.wordpress.org/trunk/`
5. In a browser, navigate to `http://localhost:8080/phpmyadmin/`
6. Log in with `root` and the password you set while installing Bitnami Wordpress Stack
7. Create a new user called `wp_test`, taking careful note of its password, and instructing PhpMyAdmin to 
"Create database with same name and grant all privileges."
8. `cd trunk`
9. `nano wp-tests-config-sample.php`
10. Change `define( 'DB_NAME', 'youremptytestdbnamehere' );` to `define( 'DB_NAME', 'wp_test' );`
11. Change `define( 'DB_USER', 'yourusernamehere' );` to `define( 'DB_USER', 'wp_test' );`
12. Change `yourpasswordhere` in `define( 'DB_PASSWORD', 'yourpasswordhere' );` to the password you set for the `wp_test` user
13. Change `define( 'DB_HOST', 'localhost' );` to `define( 'DB_HOST', 'localhost:/Applications/wordpress-4.3-0/mysql/tmp/mysql.sock' );`
14. `^x` to exit and save; save as `wp-tests-config.php` while exiting
15. `svn up`
16. Download the latest stable release of [PHPUnit](https://phpunit.de/index.html) and move it to 
`/Applications/wordpress-4.3-0/apps/wordpress/htdocs/wp-content/plugins/wp-accountability/tests`
16. `cd /Applications/wordpress-4.3-0/apps/wordpress/htdocs/wp-content/plugins/wp-accountability/tests`
17. `chmod +x phpunit.phar`
18. `cp phpunit.phar /usr/local/bin/` (be sure to leave a copy in the `tests` folder)
19. `cd tests/wordpress-dev/trunk/`
20. Run `phpunit.phar`. If your installation of phpunit.phar is correct, PHPUnit should attempt to run the WordPress development tests. Otherwise, you should see a lot of html and error messages (commonly mysql connect error messages).

##### Set up PHPUnit testing in PhpStorm

1. Open the `wp-accountability` folder in PhpStorm
2. Open PhpStorm preferences and select `Languages > PHP`
3. Click the three dots by the "Interpreter" drop-down menu
4. Click the plus sign at the top of the "Interpreters" dialog that appears and select "Other remote..."
5. Click the three dots by "PHP executable"
6. Set the path to the executable to `/Applications/wordpress-4.3-0/php/bin/php`
7. Apply changes and close the "Interpreters" dialog
8. Back in preferences, go to `Languages > PHP > PHPUnit`
9. Set `PHPUnit library` to `Path to phpunit.phar`
10. Set path to `/Applications/wordpress-4.3-0/apps/wordpress/htdocs/wp-content/plugins/wp-accountability/tests`
11. Apply changes
12. Right-click `tests/phpunit.xml` and select "Run phpunit.xml". If all is well, the HabitFree plugin tests should run within PhpStorm.

##### Diagnosing Database Errors

Before you try fixing the connection error, be sure it is really a connection error. One common error occurs when the test database becomes corrupted. To determine if that's the problem:

1. In terminal, run `cd /Applications/wordpress-4.3-0/apps/wordpress/htdocs/wp-content/plugins/wp-accountability/tests/wordpress-dev/trunk/`
2. `phpunit.phar >error.htm`
3. `open error.htm`

If the page that opens says the following, you have a test database corruption issue:

"One or more database tables are unavailable. The database may need to be repaired."

If that is the case, log into localhost:8080/phpmyadmin and drop all the tables from the wp_test database.

If that isn't the probblem, make sure you are using a `DB_HOST` url of this format:

     localhost:/Applications/wordpress-4.3-0/mysql/tmp/mysql.sock

Double-check that `mysql.sock` is indeed at that location. If not, modify url as needed. One common problem is that you're using a different version of WordPress, so need to change the `wordpress-4.3-0` to match.

Here's how to find your `mysql.sock` file:

1. Run `open /Applications/wordpress-4.3-0/manager-osx.app/` in terminal
2. In the window that appears, select the "Manage Servers" tab.
3. Select "MySQL Database" from the server list.
4. Click "Configure."
5. Click "Open Log."
6. The last line in the window that appears should say something like: `Version: '5.6.26'  socket: '/Applications/wordpress-4.3-0/mysql/tmp/mysql.sock'  port: 3306  Source distribution`
