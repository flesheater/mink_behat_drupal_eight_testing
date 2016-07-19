1. In behat.yml set base_url as the url of your local drupal 8 site

2. In behat.yml set drupal_root as the local path to your drupal 8 site

3. Install the dependancies
 composer install

3.5 In case you want to use phantomjs for testing js - you have to have it installed and running:
 phantomjs --webdriver=8643

4. Run the tests
 bin/behat
