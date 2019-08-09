<?php
require 'vendor/autoload.php';

echo 'Story: Creating Objects.' . PHP_EOL;

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
 * Let's create an Organization consisting of just our User.
 *
 */

$mutation = <<<'GQL'
    mutation($organization: OrganizationInput!) {
        createOrganization(input: $organization) {
            id
        }
    }
GQL;

$variables = [
    'organization' => [
        'name' => 'My Organization',
        'userId' => $current_user_id,
        'tier' => 'Standard'
    ]
];

$response = $cinnamon->query($mutation, $variables);
$current_organization_id = $response->getData()["createOrganization"]["id"];

echo "Created organization with id: $current_organization_id" . PHP_EOL;

/**
 * We're going now to create a Media Channel related to our Organization.
 *
 */

$mutation = <<<'GQL'
    mutation($mediaChannel: MediaChannelInput!) {
        createMediaChannel(input: $mediaChannel) {
            id
        }
    }
GQL;

$variables = [
    'mediaChannel' => [
        'organizationId' => $current_organization_id,
        'platform' => 'facebook',
        'remoteId' => 'a_facebook_media_channel_id',
        'name' => 'Test Media Channel'
    ]
];

$response = $cinnamon->query($mutation, $variables);
$current_media_channel_id = $response->getData()["createMediaChannel"]["id"];

echo "Created Media Channel with id: $current_media_channel_id" . PHP_EOL;

/**
 * We're going now to create a Marketplace referencing our Media Channel and Organization.
 *
 */

$mutation = <<<'GQL'
    mutation($marketplace: MarketplaceInput!) {
        createMarketplace(input: $marketplace) {
            id
        }
    }
GQL;

$variables = [
    'marketplace' => [
        'organizationId' => $current_organization_id,
        'mediaChannelIds' => [$current_media_channel_id],
        'name' => 'Test Marketplace'
    ]
];

$response = $cinnamon->query($mutation, $variables);
$current_marketplace_id = $response->getData()["createMarketplace"]["id"];

echo "Created Marketplace with id: $current_marketplace_id" . PHP_EOL;

/**
 * Let's add some vendors to our Marketplace.
 *
 */

$mutation = <<<'GQL'
    mutation($firstVendor: VendorInput!, $secondVendor: VendorInput!) {
        firstVendor: createVendor(input: $firstVendor) {
            id
        }
        secondVendor: createVendor(input: $secondVendor) {
            id
        }
    }
GQL;

$variables = [
    'firstVendor' => [
        'marketplaceId' => $current_marketplace_id,
        'name' => 'My First Vendor'
    ],
    'secondVendor' => [
        'marketplaceId' => $current_marketplace_id,
        'name' => 'My Second Vendor'
    ]
];

$response = $cinnamon->query($mutation, $variables);
$first_vendor_id = $response->getData()['firstVendor']['id'];
$second_vendor_id = $response->getData()['secondVendor']['id'];

echo "Created a Vendor with id: $first_vendor_id" . PHP_EOL;
echo "Created another Vendor with id: $second_vendor_id" . PHP_EOL;

/*
 * We will create now a Catalog Source and a Catalog for each Vendor.
 *
 */

$mutation = <<<'GQL'
	mutation(
		$firstVendorCatalogSource: CatalogSourceInput!
		$secondVendorCatalogSource: CatalogSourceInput!
	) {
		firstVendorCatalogSource: createCatalogSource(
			input: $firstVendorCatalogSource
		) {
			id
		}
		secondVendorCatalogSource: createCatalogSource(
			input: $secondVendorCatalogSource
		) {
			id
		}
	}
GQL;

$variables = [
    'firstVendorCatalogSource' => [
        'name' => 'A Test Catalog Source',
        'vendorId' => $first_vendor_id,
        'platform' => 'facebook',
        'remoteId' => 'a_facebook_catalog_source_id',
        'productSetRemoteId' => 'a_facebook_product_set_id'
    ],
    'secondVendorCatalogSource' => [
        'name' => 'Another Test Catalog Source',
        'vendorId' => $second_vendor_id,
        'platform' => 'facebook',
        'remoteId' => 'another_facebook_catalog_source_id',
        'productSetRemoteId' => 'another_facebook_product_set_id'
    ]
];

$response = $cinnamon->query($mutation, $variables);
$first_vendor_catalog_source_id = $response->getData()[
    "firstVendorCatalogSource"
]["id"];
$second_vendor_catalog_source_id = $response->getData()[
    "secondVendorCatalogSource"
]["id"];

echo "Created a Catalog Source with id: $first_vendor_catalog_source_id" .
    PHP_EOL;
echo "Created another Catalog Source with id: $second_vendor_catalog_source_id" .
    PHP_EOL;

$mutation = <<<'GQL'
	mutation(
		$firstVendorCatalog: CatalogInput!
		$secondVendorCatalog: CatalogInput!
	) {
		firstVendorCatalog: createCatalog(
			input: $firstVendorCatalog
		) {
			id
		}
		secondVendorCatalog: createCatalog(
			input: $secondVendorCatalog
		) {
			id
		}
	}
GQL;

$variables = [
    'firstVendorCatalog' => [
        'name' => 'A Test Catalog',
        'vendorId' => $first_vendor_id,
        'catalogSourceIds' => [$first_vendor_catalog_source_id]
    ],
    'secondVendorCatalog' => [
        'name' => 'Another Test Catalog',
        'vendorId' => $second_vendor_id,
        'catalogSourceIds' => [$second_vendor_catalog_source_id]
    ]
];

$response = $cinnamon->query($mutation, $variables);
$first_vendor_catalog_id = $response->getData()["firstVendorCatalog"]["id"];
$second_vendor_catalog_id = $response->getData()["secondVendorCatalog"]["id"];

echo "Created a Catalog with id: $first_vendor_catalog_id" . PHP_EOL;
echo "Created another Catalog with id: $second_vendor_catalog_id" . PHP_EOL;

/*
 * Now we will create 10 products for each Vendor.
 *
 * NOTE: Doesn't work yet due ongoing API changes.

$mutation = <<<'GQL'
    mutation($product: ProductInput!) {
        createProduct(input: $product) {
            id
            name
            sku
        }
    }
GQL;

// Create a relation of vendors, catalogs and catalog sources
$vendors_catalog_sources = [
    [
        'id' => $first_vendor_id,
        'catalog_id' => $first_vendor_catalog_id,
        'catalog_source_id' => $first_vendor_catalog_source_id
    ],
    [
        'id' => $second_vendor_id,
        'catalog_id' => $second_vendor_catalog_id,
        'catalog_source_id' => $second_vendor_catalog_source_id
    ]
];

// Create a collection of products related to each vendor
$products_by_vendor = [];

foreach ($vendors_catalog_sources as $vendor_index => $current_vendor) {
    // Create a new list of products to store the created Products
    $products_by_vendor[$current_vendor['id']] = [];

    foreach (range(1, 10) as $product_index) {
        // Set the mutation variables for the current vendor
        $variables = [
            'product' => [
                'name' =>
                    "Product #$product_index (Vendor: " .
                    ($vendor_index + 1) .
                    ')',
                'sku' => "$vendor_index-$product_index",
                'remoteState' => ['state' => 'example remote state'],
                'vendorId' => $current_vendor['id'],
                'catalogIds' => [$current_vendor['catalog_id']],
                'catalogSourceIds' => [$current_vendor['catalog_source_id']]
            ]
        ];

        // Query the service as usual passing the current mutation variables
        $response = $cinnamon->query($mutation, $variables);
        print_r($response->getErrors());
        $current_product_id = $response->getData()['createProduct']['id'];

        // Save the id of the created Product
        $products_by_vendor[$current_vendor['id']][
            $product_index - 1
        ] = $current_product_id;

        echo "Created Product id: $current_product_id for Vendor id: " .
            $current_vendor['id'] .
            PHP_EOL;
    }
}


*/

echo PHP_EOL;
