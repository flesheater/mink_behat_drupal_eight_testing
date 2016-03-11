<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class InfluencerContext extends RawDrupalContext implements SnippetAcceptingContext {

  public function __construct() {

  }

  /**
   * Find an id anywhere on the page
   *
   * @Given the id :id (should) exist(s)
   */
  public function assertIdExists($id) {
    $exists = $this->assertSession()->elementExists('css', '#' . $id);
    if (null === $exists) {
      throw new \Exception(sprintf('The id "%s" does not exist.', $id));
    }
  }

  /**
   * Find a class anywhere on the page
   *
   * @Given the class :class (should) exist(s)
   */
  public function assertClassExists($class) {
    $exists = $this->assertSession()->elementExists('css', '.' . $class);
    if (null === $exists) {
      throw new \Exception(sprintf('The class "%s" does not exist.', $class));
    }
  }

  /**
   * Find an id anywhere on the page
   *
   * @When I click the id :id
   */
  public function clickId($id, $seconds = 0) {
    $link_element = $this->assertSession()->elementExists('css', '#' . $id);
    if (null === $link_element) {
      throw new \Exception(sprintf('The id "%s" does not exist.', $id));
    }
    else {
      $link_element->click();
      sleep($seconds);
      return;
    }
  }

  /**
   * Resize the browser
   * 
   * @Given the browser size is :x px wide and :y px high
   */
  public function resizeWindow($x, $y) {
    $this->getSession()->resizeWindow($x, $y, 'current');
  }

  /**
   * Find an id anywhere on the page
   *
   * @Then the id :id should be visible
   */
  public function checkVisibleId($id) {

    $container = $this->assertSession()->elementExists('css', '#' . $id);
    if (null === $container) {
      throw new \Exception(sprintf('The id "%s" does not exist.', $id));
    }
    else if (!$container->isVisible()) {
      throw new \Exception(sprintf('The id "%s" is not visible.', $id));
    }

    return;
  }

  /**
   * Find error messages and save a screenshot
   *
   * @Then I should not see (an) error message(s)
   */
  public function errorMessage() {
    $page = $this->getSession()->getPage();
    if ($page->find('css', '.alert-block')) {
      // Remove the submenu so we can see all the errors
      $this->getSession()->getDriver()->executeScript(
      "jQuery('.submenu').hide();"
      );
      sleep(2);
      throw new \Exception('There was an error message on the page.');
    }

    return;
  }

  /**
   * Write something in a textfield
   * 
   * @When I write :text in autocomplete text field with id :id
   */
  public function autocompleteField($text, $id) {
    $this->autocompleteWorking = false;
    $textfield = $this->assertSession()->elementExists('css', '#' . $id);
    if (null === $textfield) {
      throw new \Exception('Textfield doesn\'t exist.');
    }
    else {
      $textfield->setValue($text);
      // Special solution since Mink tabs out. We add a space
      $this->getSession()->getDriver()->executeScript(
      "var press = jQuery.Event('keyup');
         press.which = 32;
         jQuery('#$id').trigger(press);"
      );
    }
  }

  /**
   * Give the og group to a user
   * 
   * @Given user :username belongs to client(s) :clients
   */
  public function setClients($username, $clients) {
    $user = user_load_by_name($username);
    $groups = inf_custom_get_all_groups();
    foreach ($groups as $id => $name) {
      if (strtolower($clients) == strtolower($name)) {
        $node = node_load($id);
        og_group('node', $node->nid, array(
          "entity type" => "user",
          "entity" => $user,
          "membership type" => OG_MEMBERSHIP_TYPE_DEFAULT,
        ));

        og_role_grant('node', $node->nid, $user->uid, 2);
      }
    }
  }

  /**
   * Drag and drop function
   * 
   * @When I drag :type :id to :type2 :id2
   */
  public function dragNDrop($id, $type, $type2, $id2) {
    $types = array('id' => '#', 'class' => '.');
    $page = $this->getSession()->getPage();

    $sel1 = $types[$type] . $id;
    $sel2 = $types[$type] . $id;
    $dragged = $page->find('css', $sel1);
    $target = $page->find('css', $sel2);
    if (!is_object($target)) {
      throw new \Exception(sprintf('The %s "%s" does not exist.', $type2, $id2));
    }
    else if (!is_object($dragged)) {
      throw new \Exception(sprintf('The %s "%s" does not exist.', $type, $id));
    } else {
      $dragged->dragTo($target);
    }
  }
  
  /**
   * Check if inside
   * 
   * @Then the :type :id should be inside the :type2 :id2
   */
  public function checkChild($id, $type, $type2, $id2) {
    $types = array('id' => '#', 'class' => '.');
    $page = $this->getSession()->getPage();

    $sel1 = $types[$type] . $id;
    $sel2 = $types[$type] . $id;
    
    $parent = $page->find('css', $sel2);
    if (!is_object($parent)) {
      throw new \Exception(sprintf('The %s "%s" does not exist.', $type2, $id2));
    } else {
      $child = $parent->find('css', $sel1);
      if (!is_object($child)) {
        throw new \Exception(sprintf('The %s "%s" did not exist or is not inside the %s "%s".', $type, $id, $type2, $id2));
      }
    }
  }

  /**
   * Waiting function
   * 
   * @When I wait :seconds second(s)
   */
  public function waitSeconds($seconds) {
    sleep($seconds);
  }

}
