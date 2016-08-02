# wp-accountability

## Setting Up Your Development Environment with Docker

Note that these instructions will only work within the [HabitFree
Docker environment](https://github.com/HabitFree/hf-docker) and 
after you've started the Docker environment containers using 
`./restart-env.sh`. Follow the instructions [at this 
link](https://github.com/HabitFree/hf-docker/blob/master/README.md)
before going on.

##### Set up PHPUnit with WordPress

0. In terminal, `cd` into `wp-accountability/tests/`
0. `wget https://phar.phpunit.de/phpunit.phar`
0. `chmod +x phpunit.phar`
0. `mkdir wordpress-dev`
0. `cd wordpress-dev`
0. `svn co http://develop.svn.wordpress.org/trunk/`
0. `cd trunk`
0. `cp ../../wp-tests-config.php .`
0. `svn up`
0. `../../phpunit.phar`

If your installation of phpunit.phar is correct, PHPUnit should 
attempt to run the WordPress development tests. Otherwise, you 
should see a lot of html and error messages (commonly mysql 
connect error messages).

If you get this error:

> Your PHP installation appears to be missing the MySQL extension which is required by WordPress.

Do this in Debian-based linux:

```
sudo apt-get install php5-mysql
sudo service apache2 restart
```

And then try running `phpunit.phar` again.

##### Set up PHPUnit testing in PhpStorm

0. Open the `wp-accountability` folder in PhpStorm
0. Open PhpStorm preferences and select `Languages > PHP`
0. Select an appropriate interpreter.
0. Go to `Languages > PHP > PHPUnit`
0. Set `PHPUnit library` to `Path to phpunit.phar`
0. Navigate to and select `wp-accountability/tests/phpunit.phar`
such that the full path is shown in the text box
0. Apply changes and close settings
0. Right-click `tests/phpunit.xml` and select "Run phpunit.xml". 
If all is well, the HabitFree plugin tests should run within PhpStorm.

Want to install the old, terrible, deprecated Bitnami way? 
[Instructions here.](https://github.com/HabitFree/wp-accountability/wiki/Deprecated-Bitnami-based-Installation-Instructions)