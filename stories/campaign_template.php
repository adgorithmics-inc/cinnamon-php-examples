<?php
require 'vendor/autoload.php';

echo 'Story: Operations with Campaign Template.' . PHP_EOL;

/**
 * A graphql client is instantiated with the provided cinnamon endpoint.
 *
 */

$cinnamon = \Softonic\GraphQL\ClientBuilder::build(getenv('CINNAMON_ENDPOINT'));

/**
 * We need to login using our username and password in order to
 * retrieve a token that allows us to use the service.
 *
 */

$mutation = <<<'GQL'
    mutation($user: UserLoginInput!) {
        login(input: $user) {
            token
            user {
                id
            }
        }
    }
GQL;

$variables = [
    'user' => [
        'email' => getenv('CINNAMON_USER'),
        'password' => getenv('CINNAMON_PASSWORD')
    ]
];

$response = $cinnamon->query($mutation, $variables);
$token = $response->getData()["login"]["token"];
$current_user_id = $response->getData()["login"]["user"]["id"];

echo "User with id: $current_user_id was successfuly authenticated" . PHP_EOL;

/**
 * We reconfigure our Cinnamon client to send the retrieved token for every
 * request from now on.
 *
 */

$options = [
    'headers' => [
        'Authorization' => 'Bearer ' . $token
    ]
];

$cinnamon = \Softonic\GraphQL\ClientBuilder::build(
    getenv('CINNAMON_ENDPOINT'),
    $options
);

/**
 * We're going to fetch a Campaign Template.
 *
 */

$campaign_template_id = "5d77139f39051e7587e7be44";

$query = <<<'GQL'
    query($id: ObjectId!) {
        campaignTemplate(id: $id) {
            id
            creationDate
            lastChangeDate
            name
            description
            platform
            remoteId
            marketplace {
                id
                name
            }
        }
    }
GQL;

$variables = [
    'id' => $campaign_template_id
];

$response = $cinnamon->query($query, $variables);
$campaign_template_content = $response->getData()['campaignTemplate'];

echo "Fetched a Campaign Template with id: $campaign_template_id" . PHP_EOL;
print_r($campaign_template_content);

