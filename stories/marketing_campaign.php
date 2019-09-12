<?php
require 'vendor/autoload.php';

echo 'Story: Operations with Marketing Campaign.' . PHP_EOL;

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
 * We're going now to create a Marketing Campaign related to a given Campaign Template and
 * fill it with a list of Products.
 *
 */

$campaign_template_id = "5d77139f39051e7587e7be44";
$product_ids = ["5d77139f39051e7587e3dbe7"];

$mutation = <<<'GQL'
    mutation($input: MarketingCampaignInput!) {
        createMarketingCampaign(input: $input) {
            id
        }
    }
GQL;

$variables = [
    'input' => [
        'campaignTemplateId' => $campaign_template_id,
        'productIds' => $product_ids,
        'creativeSpec' => [ 'json' => 'content '],
        'runTimeSpec' => [
            'dailyBudget' => 'someBudget',
            'spendCap' => '$5',
            'startDate' => '2019-09-12T07:56:07.285Z'
        ],
        'status' => 'PAUSED',
    ]
];

$response = $cinnamon->query($mutation, $variables);
$marketing_campaign_id = $response->getData()['createMarketingCampaign']['id'];

/**
 * We're going to fetch it now to demonstrate the marketingCampaign query.
 *
 */

$query = <<<'GQL'
    query($id: ObjectId!) {
        marketingCampaign(id: $id) {
            id
            creationDate
            lastChangeDate
            status
            creativeSpec
            runTimeSpec
            mediaChannel {
                id
                name
            }
            vendor {
                id
                name
            }
        }
    }
GQL;

$variables = [
    'id' => $marketing_campaign_id
];

$response = $cinnamon->query($query, $variables);
$marketing_campaign_content = $response->getData()['marketingCampaign'];

echo "Fetched a Marketing Campaign with id: $marketing_campaign_id" . PHP_EOL;
print_r($marketing_campaign_content);


/*
 * Now we're going to update it with new creativeSpec, runTimeSpec and status.
 *
 */

$mutation = <<<'GQL'
    mutation($id: ObjectId!, $input: MarketingCampaignUpdateInput!) {
        updateMarketingCampaign(id: $id, input: $input) {
            id
        }
    }
GQL;

$variables = [
    'id' => $marketing_campaign_id,
    'input' => [
        'creativeSpec' => [ 'new' => 'content '],
        'runTimeSpec' => [
            'dailyBudget' => 'anotherBudget',
            'spendCap' => '$10',
            'startDate' => '2019-09-12T09:56:07.285Z'
        ],
        'status' => 'ACTIVE',
    ]
];

$response = $cinnamon->query($mutation, $variables);
$marketing_campaign_id = $response->getData()['updateMarketingCampaign']['id'];

echo "Updated a Marketing Campaign with id: $marketing_campaign_id" . PHP_EOL;


/*
 * And finally let's delete it.
 *
 */

$mutation = <<<'GQL'
    mutation($id: ObjectId!) {
        deleteMarketingCampaign(id: $id) {
            id
        }
    }
GQL;

$variables = [
    'id' => $marketing_campaign_id,
];

$response = $cinnamon->query($mutation, $variables);
$marketing_campaign_id = $response->getData()['deleteMarketingCampaign']['id'];

echo "Deleted a Marketing Campaign with id: $marketing_campaign_id" . PHP_EOL;
