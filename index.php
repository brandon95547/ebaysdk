<?php
/* ini_set('display_errors', 1);
error_reporting(E_ALL); */

class RaptorEbaySdk {
  private $sandboxEndpoint = 'https://api.sandbox.ebay.com/identity/v1/oauth2/token';
  private $productionEndpoint = 'https://api.ebay.com/identity/v1/oauth2/token';

  // sandbox credentials
  private $clientID = 'BrandonS-NinjaSco-SBX-cbce8d332-f12926ef';
  private $clientSecret = 'SBX-bce8d332d830-8421-426a-bc9d-57e9';

  // production credentials
  private $pclientID = 'BrandonS-NinjaSco-PRD-fc8eaa52b-f75f9146';
  private $pclientSecret = 'PRD-c8eaa52b093e-f38b-41d9-bde8-307c';
  
  public function getToken() {
    $codeAuth = base64_encode($this->clientID . ':' . $this->clientSecret);
    $ch = curl_init($this->sandboxEndpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/x-www-form-urlencoded',
      'Authorization: Basic ' . $codeAuth
    ));
    curl_setopt($ch, CURLHEADER_SEPARATE, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope");

    $response = curl_exec($ch);
    $responseArray = json_decode($response, true);
    $info = curl_getinfo($ch);
    curl_close($ch);

    return isset($responseArray['access_token']) ? $responseArray['access_token'] : null;
  }

  public function searchItems($token) {
    $ch = curl_init('https://api.sandbox.ebay.com/buy/browse/v1/item_summary/search?q=drone');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: IAF ' . $token
    ));
    curl_setopt($ch, CURLHEADER_SEPARATE, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $responseArray = json_decode($response, true);
    $info = curl_getinfo($ch);
    curl_close($ch);

    echo '<pre>';
    print_r($responseArray);

  }

  public function searchCompletedItems($options) {
    $ch = curl_init('https://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findCompletedItems&SERVICE-VERSION=1.7.0&SECURITY-APPNAME=' . $this->pclientID . '&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&keywords=' . $options['keyword'] . '&itemFilter(0).name=Condition&itemFilter(0).value=3000&itemFilter(1).name=SoldItemsOnly&itemFilter(1).value=true&itemFilter(2).name=MinPrice&itemFilter(2).value=40&paginationInput.entriesPerPage=2000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $response = json_decode($response, true);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return $response;
  }

}

$sdk = new RaptorEbaySdk();
// $token = $sdk->getToken();
// $response = $sdk->searchItems($token);

$options = array(
  'keyword' => urlencode('womens shoes')
);
$response = $sdk->searchCompletedItems($options);
$listings = isset($response['findCompletedItemsResponse'][0]['searchResult'][0]['item']) ? $response['findCompletedItemsResponse'][0]['searchResult'][0]['item'] : null;

if($listings) {
  echo '<table>';
  $count = 1;
  foreach($listings as $item) {

    /*
      itemId, 
      title,
      globalId,
      primaryCategory,
      galleryURL
      viewItemURL,
      location,
      shippingInfo
    */

    $price = $item['sellingStatus'][0]['currentPrice'][0]['__value__'];

    echo '<tr><td>' . $count . '</td><td>' . $item['title'][0] . '</td><td style="color: green">$' . $price . '</td></tr>';

    $count++;
  }
  echo '</table>';
}