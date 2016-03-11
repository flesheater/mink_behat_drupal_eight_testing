 #features/firstpage.feature
@api
Feature: Drupal 8 Testing
  As logged in user, or as not logged in

  Scenario: Not logged in user
    Given I am not logged in
    Then I should see "Access denied"

  Scenario: Logged in user
    Given users:
    | name     | mail             | roles          |
    | Joe User | joe@example.com  | Administrator  |
    Given I run cron
    And I am logged in as "Joe User"
    Given "page" content:
    | title        | body        | published on       | status | nid	 |
    | Test article | PLACEHOLDER | 04/27/2013 11:11am |      1 |       1 |
    When I visit "node/1"
    Then I should see "Test article"
