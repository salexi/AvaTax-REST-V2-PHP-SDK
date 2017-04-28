<?php 
namespace Avalara;
/*
 * AvaTax Software Development Kit for PHP
 *
 * (c) 2004-2017 Avalara, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category   AvaTax client libraries
 * @package    Avalara.AvaTaxClient
 * @author     Ted Spence <ted.spence@avalara.com>
 * @author     Bob Maidens <bob.maidens@avalara.com>
 * @copyright  2004-2017 Avalara, Inc.
 * @license    https://www.apache.org/licenses/LICENSE-2.0
 * @version    17.5.0-509
 * @link       https://github.com/avadev/AvaTax-REST-V2-PHP-SDK
 */

use GuzzleHttp\Client;

/*****************************************************************************
 *                              API Section                                  *
 *****************************************************************************/

/**
 * An AvaTaxClient object that handles connectivity to the AvaTax v2 API server.
 */
class AvaTaxClient 
{
    /**
     * @var Client     The Guzzle client to use to connect to AvaTax.
     */
    private $client;

    /**
     * @var array      The authentication credentials to use to connect to AvaTax.
     */
    private $auth;

    /**
     * Construct a new AvaTaxClient 
     *
     * @param string $appName      Specify the name of your application here.  Should not contain any semicolons.
     * @param string $appVersion   Specify the version number of your application here.  Should not contain any semicolons.
     * @param string $machineName  Specify the machine name of the machine on which this code is executing here.  Should not contain any semicolons.
     * @param string $environment  Indicates which server to use; acceptable values are "sandbox" or "production", or the full URL of your AvaTax instance.
     */
    public function __construct($appName, $appVersion, $machineName, $environment)
    {
        // Determine startup environment
        $env = 'https://rest.avatax.com';
        if ($environment == "sandbox") {
            $env = 'https://sandbox-rest.avatax.com';
        } else if ((substr($environment, 0, 8) == 'https://') || (substr($environment, 0, 7) == 'http://')) {
            $env = $environment;
        }

        // Configure the HTTP client
        $this->client = new Client([
            'base_url' => $env
        ]);
        
        // Set client options
        $this->client->setDefaultOption('headers', array(
            'Accept' => 'application/json',
            'X-Avalara-Client' => "{$appName}; {$appVersion}; PhpRestClient; 17.5.0-509; {$machineName}"));
    }

    /**
     * Configure this client to use the specified username/password security settings
     *
     * @param  string          $username   The username for your AvaTax user account
     * @param  string          $password   The password for your AvaTax user account
     * @return AvaTaxClient
     */
    public function withSecurity($username, $password)
    {
        $this->auth = [$username, $password];
        return $this;
    }

    /**
     * Configure this client to use Account ID / License Key security
     *
     * @param  int             $accountId      The account ID for your AvaTax account
     * @param  string          $licenseKey     The private license key for your AvaTax account
     * @return AvaTaxClient
     */
    public function withLicenseKey($accountId, $licenseKey)
    {
        $this->auth = [$accountId, $licenseKey];
        return $this;
    }



    /**
     * Checks if the current user is subscribed to a specific service
     *
     * Returns a subscription object for the current account, or 404 Not Found if this subscription is not enabled for this account.
    * This API call is intended to allow you to identify whether you have the necessary account configuration to access certain
    * features of AvaTax, and would be useful in debugging access privilege problems.
     *
     * 
     * @param string $serviceTypeId The service to check (See ServiceTypeId::* for a list of allowable values)
     * @return SubscriptionModel
     */
    public function getMySubscription($serviceTypeId)
    {
        $path = "/api/v2/utilities/subscriptions/{$serviceTypeId}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all services to which the current user is subscribed
     *
     * Returns the list of all subscriptions enabled for the current account.
    * This API is intended to help you determine whether you have the necessary subscription to use certain API calls
    * within AvaTax.
     *
     * 
     * @return FetchResult
     */
    public function listMySubscriptions()
    {
        $path = "/api/v2/utilities/subscriptions";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Tests connectivity and version of the service
     *
     * This API helps diagnose connectivity problems between your application and AvaTax; you may call this API even 
    * if you do not have verified connection credentials.
    * The results of this API call will help you determine whether your computer can contact AvaTax via the network,
    * whether your authentication credentials are recognized, and the roundtrip time it takes to communicate with
    * AvaTax.
     *
     * 
     * @return PingResultModel
     */
    public function ping()
    {
        $path = "/api/v2/utilities/ping";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Make a single REST call to the AvaTax v2 API server
     *
     * @param string $apiUrl           The relative path of the API on the server
     * @param string $verb             The HTTP verb being used in this request
     * @param string $guzzleParams     The Guzzle parameters for this request, including query string and body parameters
     */
    private function restCall($apiUrl, $verb, $guzzleParams)
    {
        // Set authentication on the parameters
        if (!isset($guzzleParams['auth'])){
            $guzzleParams['auth'] = $this->auth;
        }
    
        // Contact the server
        try {
            $request = $this->client->createRequest($verb, $apiUrl, $guzzleParams);
            $response = $this->client->send($request);
            $body = $response->getBody();
            return json_decode($body);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

/*****************************************************************************
 *                              Object Models                                *
 *****************************************************************************/


/**
 * An AvaTax account.
 */
class AccountModel
{

    /**
     * @var int The unique ID number assigned to this account.
     */
    public $id;

    /**
     * @var string The name of this account.
     */
    public $name;

    /**
     * @var string The earliest date on which this account may be used.
     */
    public $effectiveDate;

    /**
     * @var string If this account has been closed, this is the last date the account was open.
     */
    public $endDate;

    /**
     * @var string The current status of this account. (See AccountStatusId::* for a list of allowable values)
     */
    public $accountStatusId;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var SubscriptionModel[] Optional: A list of subscriptions granted to this account. To fetch this list, add the query string "?$include=Subscriptions" to your URL.
     */
    public $subscriptions;

    /**
     * @var UserModel[] Optional: A list of all the users belonging to this account. To fetch this list, add the query string "?$include=Users" to your URL.
     */
    public $users;

}

/**
 * Represents a service that this account has subscribed to.
 */
class SubscriptionModel
{

    /**
     * @var int The unique ID number of this subscription.
     */
    public $id;

    /**
     * @var int The unique ID number of the account this subscription belongs to.
     */
    public $accountId;

    /**
     * @var int The unique ID number of the service that the account is subscribed to.
     */
    public $subscriptionTypeId;

    /**
     * @var string A friendly description of the service that the account is subscribed to.
     */
    public $subscriptionDescription;

    /**
     * @var string The date when the subscription began.
     */
    public $effectiveDate;

    /**
     * @var string If the subscription has ended or will end, this date indicates when the subscription ends.
     */
    public $endDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * An account user who is permitted to use AvaTax.
 */
class UserModel
{

    /**
     * @var int The unique ID number of this user.
     */
    public $id;

    /**
     * @var int The unique ID number of the account to which this user belongs.
     */
    public $accountId;

    /**
     * @var int If this user is locked to one company (and its children), this is the unique ID number of the company to which this user belongs.
     */
    public $companyId;

    /**
     * @var string The username which is used to log on to the AvaTax website, or to authenticate against API calls.
     */
    public $userName;

    /**
     * @var string The first or given name of the user.
     */
    public $firstName;

    /**
     * @var string The last or family name of the user.
     */
    public $lastName;

    /**
     * @var string The email address to be used to contact this user. If the user has forgotten a password, an email can be sent to this email address with information on how to reset this password.
     */
    public $email;

    /**
     * @var string The postal code in which this user resides.
     */
    public $postalCode;

    /**
     * @var string The security level for this user. (See SecurityRoleId::* for a list of allowable values)
     */
    public $securityRoleId;

    /**
     * @var string The status of the user's password. (See PasswordStatusId::* for a list of allowable values)
     */
    public $passwordStatus;

    /**
     * @var boolean True if this user is currently active.
     */
    public $isActive;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Message object
 */
class ErrorDetail
{

    /**
     * @var string Name of the error or message. (See ErrorCodeId::* for a list of allowable values)
     */
    public $code;

    /**
     * @var int Unique ID number referring to this error or message.
     */
    public $number;

    /**
     * @var string Concise summary of the message, suitable for display in the caption of an alert box.
     */
    public $message;

    /**
     * @var string A more detailed description of the problem referenced by this error message, suitable for display in the contents area of an alert box.
     */
    public $description;

    /**
     * @var string Indicates the SOAP Fault code, if this was related to an error that corresponded to AvaTax SOAP v1 behavior.
     */
    public $faultCode;

    /**
     * @var string URL to help for this message
     */
    public $helpLink;

    /**
     * @var string Item the message refers to, if applicable. This is used to indicate a missing or incorrect value.
     */
    public $refersTo;

    /**
     * @var string Severity of the message (See SeverityLevel::* for a list of allowable values)
     */
    public $severity;

}

/**
 * Represents a request for a new account with Avalara for a new subscriber.
* Contains information about the account requested and the rate plan selected.
 */
class NewAccountRequestModel
{

    /**
     * @var string[] The list of products to which this account would like to subscribe.
     */
    public $products;

    /**
     * @var string The name of the connector that will be the primary method of access used to call the account created.
For a list of available connectors, please contact your Avalara representative.
     */
    public $connectorName;

    /**
     * @var string An approved partner account can be referenced when provisioning an account, allowing a link between 
the partner and the provisioned account.
     */
    public $parentAccountNumber;

    /**
     * @var string Identifies a referring partner for the assessment of referral-based commissions.
     */
    public $referrerId;

    /**
     * @var string Zuora-generated Payment ID to which the new account should be associated. For free trial accounts, an empty string is acceptable.
     */
    public $paymentMethodId;

    /**
     * @var string The date on which the account should take effect. If null, defaults to today.
     */
    public $effectiveDate;

    /**
     * @var string The date on which the account should expire. If null, defaults to a 90-day trial account.
     */
    public $endDate;

    /**
     * @var string Account Name
     */
    public $accountName;

    /**
     * @var string First Name of the primary contact person for this account
     */
    public $firstName;

    /**
     * @var string Last Name of the primary contact person for this account
     */
    public $lastName;

    /**
     * @var string Title of the primary contact person for this account
     */
    public $title;

    /**
     * @var string Phone number of the primary contact person for this account
     */
    public $phoneNumber;

    /**
     * @var string Email of the primary contact person for this account
     */
    public $email;

    /**
     * @var string If no password is supplied, an a tempoarary password is generated by the system and emailed to the user. The user will 
be challenged to change this password upon logging in to the Admin Console. If supplied, will be the set password for 
the default created user, and the user will not be challenged to change their password upon login to the Admin Console.
     */
    public $userPassword;

}

/**
 * Represents information about a newly created account
 */
class NewAccountModel
{

    /**
     * @var int This is the ID number of the account that was created
     */
    public $accountId;

    /**
     * @var string This is the email address to which credentials were mailed
     */
    public $accountDetailsEmailedTo;

    /**
     * @var string The date and time when this account was created
     */
    public $createdDate;

    /**
     * @var string The date and time when account information was emailed to the user
     */
    public $emailedDate;

    /**
     * @var string If this account includes any limitations, specify them here
     */
    public $limitations;

}

/**
 * Represents a request for a free trial account for AvaTax.
* Free trial accounts are only available on the Sandbox environment.
 */
class FreeTrialRequestModel
{

    /**
     * @var string The first or given name of the user requesting a free trial.
     */
    public $firstName;

    /**
     * @var string The last or family name of the user requesting a free trial.
     */
    public $lastName;

    /**
     * @var string The email address of the user requesting a free trial.
     */
    public $email;

    /**
     * @var string The company or organizational name for this free trial. If this account is for personal use, it is acceptable 
to use your full name here.
     */
    public $company;

    /**
     * @var string The phone number of the person requesting the free trial.
     */
    public $phone;

}

/**
 * Represents a license key reset request.
 */
class ResetLicenseKeyModel
{

    /**
     * @var int The primary key of the account ID to reset
     */
    public $accountId;

    /**
     * @var boolean Set this value to true to reset the license key for this account.
This license key reset function will only work when called using the credentials of the account administrator of this account.
     */
    public $confirmResetLicenseKey;

}

/**
 * Represents a license key for this account.
 */
class LicenseKeyModel
{

    /**
     * @var int The primary key of the account
     */
    public $accountId;

    /**
     * @var string This is your private license key. You must record this license key for safekeeping.
If you lose this key, you must contact the ResetLicenseKey API in order to request a new one.
Each account can only have one license key at a time.
     */
    public $privateLicenseKey;

    /**
     * @var string If your software allows you to specify the HTTP Authorization header directly, this is the header string you 
should use when contacting Avalara to make API calls with this license key.
     */
    public $httpRequestHeader;

}

/**
 * Represents one configuration setting for this account
 */
class AccountConfigurationModel
{

    /**
     * @var int The unique ID number of the account to which this setting applies
     */
    public $accountId;

    /**
     * @var string The category of the configuration setting. Avalara-defined categories include `AddressServiceConfig` and `TaxServiceConfig`. Customer-defined categories begin with `X-`.
     */
    public $category;

    /**
     * @var string The name of the configuration setting
     */
    public $name;

    /**
     * @var string The current value of the configuration setting
     */
    public $value;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * TextCase info for input address
 */
class AddressValidationInfo
{

    /**
     * @var string Specify the text case for the validated address result. If not specified, will return uppercase. (See TextCase::* for a list of allowable values)
     */
    public $textCase;

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * Address Resolution Model
 */
class AddressResolutionModel
{

    /**
     * @var AddressInfo The original address
     */
    public $address;

    /**
     * @var ValidatedAddressInfo[] The validated address or addresses
     */
    public $validatedAddresses;

    /**
     * @var CoordinateInfo The geospatial coordinates of this address
     */
    public $coordinates;

    /**
     * @var string The resolution quality of the geospatial coordinates (See ResolutionQuality::* for a list of allowable values)
     */
    public $resolutionQuality;

    /**
     * @var TaxAuthorityInfo[] List of informational and warning messages regarding this address
     */
    public $taxAuthorities;

    /**
     * @var AvaTaxMessage[] List of informational and warning messages regarding this address
     */
    public $messages;

}

/**
 * Represents an address to resolve.
 */
class AddressInfo
{

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * Represents a validated address
 */
class ValidatedAddressInfo
{

    /**
     * @var string Address type code. One of: 
* F - Firm or company address
* G - General Delivery address
* H - High-rise or business complex
* P - PO Box address
* R - Rural route address
* S - Street or residential address
     */
    public $addressType;

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * Coordinate Info
 */
class CoordinateInfo
{

    /**
     * @var float Latitude
     */
    public $latitude;

    /**
     * @var float Longitude
     */
    public $longitude;

}

/**
 * Tax Authority Info
 */
class TaxAuthorityInfo
{

    /**
     * @var string Avalara Id
     */
    public $avalaraId;

    /**
     * @var string Jurisdiction Name
     */
    public $jurisdictionName;

    /**
     * @var string Jurisdiction Type (See JurisdictionType::* for a list of allowable values)
     */
    public $jurisdictionType;

    /**
     * @var string Signature Code
     */
    public $signatureCode;

}

/**
 * Informational or warning messages returned by AvaTax with a transaction
 */
class AvaTaxMessage
{

    /**
     * @var string A brief summary of what this message tells us
     */
    public $summary;

    /**
     * @var string Detailed information that explains what the summary provided
     */
    public $details;

    /**
     * @var string Information about what object in your request this message refers to
     */
    public $refersTo;

    /**
     * @var string A category that indicates how severely this message affects the results
     */
    public $severity;

    /**
     * @var string The name of the code or service that generated this message
     */
    public $source;

}

/**
 * Represents a batch of uploaded documents.
 */
class BatchModel
{

    /**
     * @var int The unique ID number of this batch.
     */
    public $id;

    /**
     * @var string The user-friendly readable name for this batch.
     */
    public $name;

    /**
     * @var int The Account ID number of the account that owns this batch.
     */
    public $accountId;

    /**
     * @var int The Company ID number of the company that owns this batch.
     */
    public $companyId;

    /**
     * @var string The type of this batch. (See BatchType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string This batch's current processing status (See BatchStatus::* for a list of allowable values)
     */
    public $status;

    /**
     * @var string Any optional flags provided for this batch
     */
    public $options;

    /**
     * @var string The agent used to create this batch
     */
    public $batchAgent;

    /**
     * @var string The date/time when this batch started processing
     */
    public $startedDate;

    /**
     * @var int The number of records in this batch; determined by the server
     */
    public $recordCount;

    /**
     * @var int The current record being processed
     */
    public $currentRecord;

    /**
     * @var string The date/time when this batch was completely processed
     */
    public $completedDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var BatchFileModel[] The list of files contained in this batch.
     */
    public $files;

}

/**
 * Represents one file in a batch upload.
 */
class BatchFileModel
{

    /**
     * @var int The unique ID number assigned to this batch file.
     */
    public $id;

    /**
     * @var int The unique ID number of the batch that this file belongs to.
     */
    public $batchId;

    /**
     * @var string Logical Name of file (e.g. "Input" or "Error").
     */
    public $name;

    /**
     * @var string Content of the batch file. (This value is encoded as a Base64 string)
     */
    public $content;

    /**
     * @var int Size of content, in bytes.
     */
    public $contentLength;

    /**
     * @var string Content mime type (e.g. text/csv). This is used for HTTP downloading.
     */
    public $contentType;

    /**
     * @var string File extension (e.g. CSV).
     */
    public $fileExtension;

    /**
     * @var int Number of errors that occurred when processing this file.
     */
    public $errorCount;

}

/**
 * A company or business entity.
 */
class CompanyModel
{

    /**
     * @var int The unique ID number of this company.
     */
    public $id;

    /**
     * @var int The unique ID number of the account this company belongs to.
     */
    public $accountId;

    /**
     * @var int If this company is fully owned by another company, this is the unique identity of the parent company.
     */
    public $parentCompanyId;

    /**
     * @var string If this company files Streamlined Sales Tax, this is the PID of this company as defined by the Streamlined Sales Tax governing board.
     */
    public $sstPid;

    /**
     * @var string A unique code that references this company within your account.
     */
    public $companyCode;

    /**
     * @var string The name of this company, as shown to customers.
     */
    public $name;

    /**
     * @var boolean This flag is true if this company is the default company for this account. Only one company may be set as the default.
     */
    public $isDefault;

    /**
     * @var int If set, this is the unique ID number of the default location for this company.
     */
    public $defaultLocationId;

    /**
     * @var boolean This flag indicates whether tax activity can occur for this company. Set this flag to true to permit the company to process transactions.
     */
    public $isActive;

    /**
     * @var string For United States companies, this field contains your Taxpayer Identification Number. 
This is a nine digit number that is usually called an EIN for an Employer Identification Number if this company is a corporation, 
or SSN for a Social Security Number if this company is a person.
This value is required if you subscribe to Avalara Managed Returns or the SST Certified Service Provider services, 
but it is optional if you do not subscribe to either of those services.
     */
    public $taxpayerIdNumber;

    /**
     * @var boolean Set this flag to true to give this company its own unique tax profile.
If this flag is true, this company will have its own Nexus, TaxRule, TaxCode, and Item definitions.
If this flag is false, this company will inherit all profile values from its parent.
     */
    public $hasProfile;

    /**
     * @var boolean Set this flag to true if this company must file its own tax returns.
For users who have Returns enabled, this flag turns on monthly Worksheet generation for the company.
     */
    public $isReportingEntity;

    /**
     * @var string If this company participates in Streamlined Sales Tax, this is the date when the company joined the SST program.
     */
    public $sstEffectiveDate;

    /**
     * @var string The two character ISO-3166 country code of the default country for this company.
     */
    public $defaultCountry;

    /**
     * @var string This is the three character ISO-4217 currency code of the default currency used by this company.
     */
    public $baseCurrencyCode;

    /**
     * @var string Indicates whether this company prefers to round amounts at the document level or line level. (See RoundingLevelId::* for a list of allowable values)
     */
    public $roundingLevelId;

    /**
     * @var boolean Set this value to true to receive warnings in API calls via SOAP.
     */
    public $warningsEnabled;

    /**
     * @var boolean Set this flag to true to indicate that this company is a test company.
If you have Returns enabled, Test companies will not file tax returns and can be used for validation purposes.
     */
    public $isTest;

    /**
     * @var string Used to apply tax detail dependency at a jurisdiction level. (See TaxDependencyLevelId::* for a list of allowable values)
     */
    public $taxDependencyLevelId;

    /**
     * @var boolean Set this value to true to indicate that you are still working to finish configuring this company.
While this value is true, no tax reporting will occur and the company will not be usable for transactions.
     */
    public $inProgress;

    /**
     * @var string Business Identification No
     */
    public $businessIdentificationNo;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var ContactModel[] Optional: A list of contacts defined for this company. To fetch this list, add the query string "?$include=Contacts" to your URL.
     */
    public $contacts;

    /**
     * @var ItemModel[] Optional: A list of items defined for this company. To fetch this list, add the query string "?$include=Items" to your URL.
     */
    public $items;

    /**
     * @var LocationModel[] Optional: A list of locations defined for this company. To fetch this list, add the query string "?$include=Locations" to your URL.
     */
    public $locations;

    /**
     * @var NexusModel[] Optional: A list of nexus defined for this company. To fetch this list, add the query string "?$include=Nexus" to your URL.
     */
    public $nexus;

    /**
     * @var SettingModel[] Optional: A list of settings defined for this company. To fetch this list, add the query string "?$include=Settings" to your URL.
     */
    public $settings;

    /**
     * @var TaxCodeModel[] Optional: A list of tax codes defined for this company. To fetch this list, add the query string "?$include=TaxCodes" to your URL.
     */
    public $taxCodes;

    /**
     * @var TaxRuleModel[] Optional: A list of tax rules defined for this company. To fetch this list, add the query string "?$include=TaxRules" to your URL.
     */
    public $taxRules;

    /**
     * @var UPCModel[] Optional: A list of UPCs defined for this company. To fetch this list, add the query string "?$include=UPCs" to your URL.
     */
    public $upcs;

}

/**
 * A contact person for a company.
 */
class ContactModel
{

    /**
     * @var int The unique ID number of this contact.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this contact belongs.
     */
    public $companyId;

    /**
     * @var string A unique code for this contact.
     */
    public $contactCode;

    /**
     * @var string The first or given name of this contact.
     */
    public $firstName;

    /**
     * @var string The middle name of this contact.
     */
    public $middleName;

    /**
     * @var string The last or family name of this contact.
     */
    public $lastName;

    /**
     * @var string Professional title of this contact.
     */
    public $title;

    /**
     * @var string The first line of the postal mailing address of this contact.
     */
    public $line1;

    /**
     * @var string The second line of the postal mailing address of this contact.
     */
    public $line2;

    /**
     * @var string The third line of the postal mailing address of this contact.
     */
    public $line3;

    /**
     * @var string The city of the postal mailing address of this contact.
     */
    public $city;

    /**
     * @var string The state, region, or province of the postal mailing address of this contact.
     */
    public $region;

    /**
     * @var string The postal code or zip code of the postal mailing address of this contact.
     */
    public $postalCode;

    /**
     * @var string The ISO 3166 two-character country code of the postal mailing address of this contact.
     */
    public $country;

    /**
     * @var string The email address of this contact.
     */
    public $email;

    /**
     * @var string The main phone number for this contact.
     */
    public $phone;

    /**
     * @var string The mobile phone number for this contact.
     */
    public $mobile;

    /**
     * @var string The facsimile phone number for this contact.
     */
    public $fax;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents an item in your company's product catalog.
 */
class ItemModel
{

    /**
     * @var int The unique ID number of this item.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that owns this item.
     */
    public $companyId;

    /**
     * @var string A unique code representing this item.
     */
    public $itemCode;

    /**
     * @var int The unique ID number of the tax code that is applied when selling this item.
When creating or updating an item, you can either specify the Tax Code ID number or the Tax Code string; you do not need to specify both values.
     */
    public $taxCodeId;

    /**
     * @var string The unique code string of the Tax Code that is applied when selling this item.
When creating or updating an item, you can either specify the Tax Code ID number or the Tax Code string; you do not need to specify both values.
     */
    public $taxCode;

    /**
     * @var string A friendly description of this item in your product catalog.
     */
    public $description;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * A location where this company does business.
* Some jurisdictions may require you to list all locations where your company does business.
 */
class LocationModel
{

    /**
     * @var int The unique ID number of this location.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that operates at this location.
     */
    public $companyId;

    /**
     * @var string A code that identifies this location. Must be unique within your company.
     */
    public $locationCode;

    /**
     * @var string A friendly name for this location.
     */
    public $description;

    /**
     * @var string Indicates whether this location is a physical place of business or a temporary salesperson location. (See AddressTypeId::* for a list of allowable values)
     */
    public $addressTypeId;

    /**
     * @var string Indicates the type of place of business represented by this location. (See AddressCategoryId::* for a list of allowable values)
     */
    public $addressCategoryId;

    /**
     * @var string The first line of the physical address of this location.
     */
    public $line1;

    /**
     * @var string The second line of the physical address of this location.
     */
    public $line2;

    /**
     * @var string The third line of the physical address of this location.
     */
    public $line3;

    /**
     * @var string The city of the physical address of this location.
     */
    public $city;

    /**
     * @var string The county name of the physical address of this location. Not required.
     */
    public $county;

    /**
     * @var string The state, region, or province of the physical address of this location.
     */
    public $region;

    /**
     * @var string The postal code or zip code of the physical address of this location.
     */
    public $postalCode;

    /**
     * @var string The two character ISO-3166 country code of the physical address of this location.
     */
    public $country;

    /**
     * @var boolean Set this flag to true to indicate that this is the default location for this company.
     */
    public $isDefault;

    /**
     * @var boolean Set this flag to true to indicate that this location has been registered with a tax authority.
     */
    public $isRegistered;

    /**
     * @var string If this location has a different business name from its legal entity name, specify the "Doing Business As" name for this location.
     */
    public $dbaName;

    /**
     * @var string A friendly name for this location.
     */
    public $outletName;

    /**
     * @var string The date when this location was opened for business, or null if not known.
     */
    public $effectiveDate;

    /**
     * @var string If this place of business has closed, the date when this location closed business.
     */
    public $endDate;

    /**
     * @var string The most recent date when a transaction was processed for this location. Set by AvaTax.
     */
    public $lastTransactionDate;

    /**
     * @var string The date when this location was registered with a tax authority. Not required.
     */
    public $registeredDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var LocationSettingModel[] Extra information required by certain jurisdictions for filing.
For a list of settings recognized by Avalara, query the endpoint "/api/v2/definitions/locationquestions". 
To determine the list of settings required for this location, query the endpoint "/api/v2/companies/(id)/locations/(id)/validate".
     */
    public $settings;

}

/**
 * Represents a declaration of nexus within a particular taxing jurisdiction.
 */
class NexusModel
{

    /**
     * @var int The unique ID number of this declaration of nexus.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that declared nexus.
     */
    public $companyId;

    /**
     * @var string The two character ISO-3166 country code of the country in which this company declared nexus.
     */
    public $country;

    /**
     * @var string The two or three character ISO region code of the region, state, or province in which this company declared nexus.
     */
    public $region;

    /**
     * @var string The jurisdiction type of the jurisdiction in which this company declared nexus. (See JurisTypeId::* for a list of allowable values)
     */
    public $jurisTypeId;

    /**
     * @var string The code identifying the jurisdiction in which this company declared nexus.
     */
    public $jurisCode;

    /**
     * @var string The common name of the jurisdiction in which this company declared nexus.
     */
    public $jurisName;

    /**
     * @var string The date when this nexus began. If not known, set to null.
     */
    public $effectiveDate;

    /**
     * @var string If this nexus will end or has ended on a specific date, set this to the date when this nexus ends.
     */
    public $endDate;

    /**
     * @var string The short name of the jurisdiction.
     */
    public $shortName;

    /**
     * @var string The signature code of the boundary region as defined by Avalara.
     */
    public $signatureCode;

    /**
     * @var string The state assigned number of this jurisdiction.
     */
    public $stateAssignedNo;

    /**
     * @var string (DEPRECATED) The type of nexus that this company is declaring.
Please use NexusTaxTypeGroupId instead. (See NexusTypeId::* for a list of allowable values)
     */
    public $nexusTypeId;

    /**
     * @var string Indicates whether this nexus is defined as origin or destination nexus. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var boolean True if you are also declaring local nexus within this jurisdiction.
Many U.S. states have options for declaring nexus in local jurisdictions as well as within the state.
     */
    public $hasLocalNexus;

    /**
     * @var string If you are declaring local nexus within this jurisdiction, this indicates whether you are declaring only 
a specified list of local jurisdictions, all state-administered local jurisdictions, or all local jurisdictions. (See LocalNexusTypeId::* for a list of allowable values)
     */
    public $localNexusTypeId;

    /**
     * @var boolean Set this value to true if your company has a permanent establishment within this jurisdiction.
     */
    public $hasPermanentEstablishment;

    /**
     * @var string Optional - the tax identification number under which you declared nexus.
     */
    public $taxId;

    /**
     * @var boolean For the United States, this flag indicates whether this particular nexus falls within a U.S. State that participates 
in the Streamlined Sales Tax program. For countries other than the US, this flag is null.
     */
    public $streamlinedSalesTax;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var string The type of nexus that this company is declaring.Replaces NexusTypeId.
Use /api/v2/definitions/nexustaxtypegroup for a list of tax type groups.
     */
    public $nexusTaxTypeGroup;

}

/**
 * This object is used to keep track of custom information about a company.
* A setting can refer to any type of data you need to remember about this company object.
* When creating this object, you may define your own "set", "name", and "value" parameters.
* To define your own values, please choose a "set" name that begins with "X-" to indicate an extension.
 */
class SettingModel
{

    /**
     * @var int The unique ID number of this setting.
     */
    public $id;

    /**
     * @var int The unique ID number of the company this setting refers to.
     */
    public $companyId;

    /**
     * @var string A user-defined "set" containing this name-value pair.
     */
    public $set;

    /**
     * @var string A user-defined "name" for this name-value pair.
     */
    public $name;

    /**
     * @var string The value of this name-value pair.
     */
    public $value;

}

/**
 * Represents a tax code that can be applied to items on a transaction.
* A tax code can have specific rules for specific jurisdictions that change the tax calculation behavior.
 */
class TaxCodeModel
{

    /**
     * @var int The unique ID number of this tax code.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that owns this tax code.
     */
    public $companyId;

    /**
     * @var string A code string that identifies this tax code.
     */
    public $taxCode;

    /**
     * @var string The type of this tax code.
     */
    public $taxCodeTypeId;

    /**
     * @var string A friendly description of this tax code.
     */
    public $description;

    /**
     * @var string If this tax code is a subset of a different tax code, this identifies the parent code.
     */
    public $parentTaxCode;

    /**
     * @var boolean True if this tax code type refers to a physical object. Read only field.
     */
    public $isPhysical;

    /**
     * @var int The Avalara Goods and Service Code represented by this tax code.
     */
    public $goodsServiceCode;

    /**
     * @var string The Avalara Entity Use Code represented by this tax code.
     */
    public $entityUseCode;

    /**
     * @var boolean True if this tax code is active and can be used in transactions.
     */
    public $isActive;

    /**
     * @var boolean True if this tax code has been certified by the Streamlined Sales Tax governing board.
By default, you should leave this value empty.
     */
    public $isSSTCertified;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents a tax rule that changes the behavior of Avalara's tax engine for certain products in certain jurisdictions.
 */
class TaxRuleModel
{

    /**
     * @var int The unique ID number of this tax rule.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that owns this tax rule.
     */
    public $companyId;

    /**
     * @var int The unique ID number of the tax code for this rule.
When creating or updating a tax rule, you may specify either the taxCodeId value or the taxCode value.
     */
    public $taxCodeId;

    /**
     * @var string The code string of the tax code for this rule.
When creating or updating a tax rule, you may specify either the taxCodeId value or the taxCode value.
     */
    public $taxCode;

    /**
     * @var string For U.S. tax rules, this is the state's Federal Information Processing Standard (FIPS) code.
     */
    public $stateFIPS;

    /**
     * @var string The name of the jurisdiction to which this tax rule applies.
     */
    public $jurisName;

    /**
     * @var string The code of the jurisdiction to which this tax rule applies.
     */
    public $jurisCode;

    /**
     * @var string The type of the jurisdiction to which this tax rule applies. (See JurisTypeId::* for a list of allowable values)
     */
    public $jurisTypeId;

    /**
     * @var string The type of customer usage to which this rule applies.
     */
    public $customerUsageType;

    /**
     * @var string Indicates which tax types to which this rule applies. (See MatchingTaxType::* for a list of allowable values)
     */
    public $taxTypeId;

    /**
     * @var string (DEPRECATED) Enumerated rate type to which this rule applies. Please use rateTypeCode instead. (See RateType::* for a list of allowable values)
     */
    public $rateTypeId;

    /**
     * @var string Indicates the code of the rate type that applies to this rule. Use `/api/v2/definitions/ratetypes` for a full list of rate type codes.
     */
    public $rateTypeCode;

    /**
     * @var string This type value determines the behavior of the tax rule.
You can specify that this rule controls the product's taxability or exempt / nontaxable status, the product's rate 
(for example, if you have been granted an official ruling for your product's rate that differs from the official rate), 
or other types of behavior. (See TaxRuleTypeId::* for a list of allowable values)
     */
    public $taxRuleTypeId;

    /**
     * @var boolean Set this value to true if this tax rule applies in all jurisdictions.
     */
    public $isAllJuris;

    /**
     * @var float The corrected rate for this tax rule.
     */
    public $value;

    /**
     * @var float The maximum cap for the price of this item according to this rule.
     */
    public $cap;

    /**
     * @var float The per-unit threshold that must be met before this rule applies.
     */
    public $threshold;

    /**
     * @var string Custom option flags for this rule.
     */
    public $options;

    /**
     * @var string The first date at which this rule applies. If null, this rule will apply to all dates prior to the end date.
     */
    public $effectiveDate;

    /**
     * @var string The last date for which this rule applies. If null, this rule will apply to all dates after the effective date.
     */
    public $endDate;

    /**
     * @var string A friendly name for this tax rule.
     */
    public $description;

    /**
     * @var string For U.S. tax rules, this is the county's Federal Information Processing Standard (FIPS) code.
     */
    public $countyFIPS;

    /**
     * @var boolean If true, indicates this rule is for Sales Tax Pro.
     */
    public $isSTPro;

    /**
     * @var string The two character ISO 3166 country code for the locations where this rule applies.
     */
    public $country;

    /**
     * @var string The state, region, or province name for the locations where this rule applies.
     */
    public $region;

    /**
     * @var string The sourcing types to which this rule applies. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var string The group Id of tax types supported by Avalara. Refer to /api/v2/definitions/taxtypegroups for types we support.
     */
    public $taxTypeGroup;

    /**
     * @var string The Id of sub tax types supported by Avalara. Refer to /api/v2/definitions/taxsubtypes for types we support.
     */
    public $taxSubType;

    /**
     * @var int Id for TaxTypeMapping object
     */
    public $taxTypeMappingId;

    /**
     * @var int Id for RateTypeTaxTypeMapping object
     */
    public $rateTypeTaxTypeMappingId;

}

/**
 * One Universal Product Code object as defined for your company.
 */
class UPCModel
{

    /**
     * @var int The unique ID number for this UPC.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this UPC belongs.
     */
    public $companyId;

    /**
     * @var string The 12-14 character Universal Product Code, European Article Number, or Global Trade Identification Number.
     */
    public $upc;

    /**
     * @var string Legacy Tax Code applied to any product sold with this UPC.
     */
    public $legacyTaxCode;

    /**
     * @var string Description of the product to which this UPC applies.
     */
    public $description;

    /**
     * @var string If this UPC became effective on a certain date, this contains the first date on which the UPC was effective.
     */
    public $effectiveDate;

    /**
     * @var string If this UPC expired or will expire on a certain date, this contains the last date on which the UPC was effective.
     */
    public $endDate;

    /**
     * @var int A usage identifier for this UPC code.
     */
    public $usage;

    /**
     * @var int A flag indicating whether this UPC code is attached to the AvaTax system or to a company.
     */
    public $isSystem;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents the answer to one local jurisdiction question for a location.
 */
class LocationSettingModel
{

    /**
     * @var int The unique ID number of the location question answered.
     */
    public $questionId;

    /**
     * @var string The answer the user provided.
     */
    public $value;

}

/**
 * Company Initialization Model
 */
class CompanyInitializationModel
{

    /**
     * @var string Company Name
     */
    public $name;

    /**
     * @var string Company Code - used to distinguish between companies within your accounting system
     */
    public $companyCode;

    /**
     * @var string Vat Registration Id - leave blank if not known.
     */
    public $vatRegistrationId;

    /**
     * @var string United States Taxpayer ID number, usually your Employer Identification Number if you are a business or your 
Social Security Number if you are an individual.
This value is required if you subscribe to Avalara Managed Returns or the SST Certified Service Provider services, 
but it is optional if you do not subscribe to either of those services.
     */
    public $taxpayerIdNumber;

    /**
     * @var string Address Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string Two character ISO 3166 Region code for this company's primary business location.
     */
    public $region;

    /**
     * @var string Postal Code
     */
    public $postalCode;

    /**
     * @var string Two character ISO 3166 Country code for this company's primary business location.
     */
    public $country;

    /**
     * @var string First Name
     */
    public $firstName;

    /**
     * @var string Last Name
     */
    public $lastName;

    /**
     * @var string Title
     */
    public $title;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string Phone Number
     */
    public $phoneNumber;

    /**
     * @var string Mobile Number
     */
    public $mobileNumber;

    /**
     * @var string Fax Number
     */
    public $faxNumber;

}

/**
 * Status of an Avalara Managed Returns funding configuration for a company
 */
class FundingStatusModel
{

    /**
     * @var int The unique ID number of this funding request
     */
    public $requestId;

    /**
     * @var int SubledgerProfileID
     */
    public $subledgerProfileID;

    /**
     * @var string CompanyID
     */
    public $companyID;

    /**
     * @var string Domain
     */
    public $domain;

    /**
     * @var string Recipient
     */
    public $recipient;

    /**
     * @var string Sender
     */
    public $sender;

    /**
     * @var string DocumentKey
     */
    public $documentKey;

    /**
     * @var string DocumentType
     */
    public $documentType;

    /**
     * @var string DocumentName
     */
    public $documentName;

    /**
     * @var FundingESignMethodReturn MethodReturn
     */
    public $methodReturn;

    /**
     * @var string Status
     */
    public $status;

    /**
     * @var string ErrorMessage
     */
    public $errorMessage;

    /**
     * @var string LastPolled
     */
    public $lastPolled;

    /**
     * @var string LastSigned
     */
    public $lastSigned;

    /**
     * @var string LastActivated
     */
    public $lastActivated;

    /**
     * @var int TemplateRequestId
     */
    public $templateRequestId;

}

/**
 * Represents the current status of a funding ESign method
 */
class FundingESignMethodReturn
{

    /**
     * @var string Method
     */
    public $method;

    /**
     * @var boolean JavaScriptReady
     */
    public $javaScriptReady;

    /**
     * @var string The actual javascript to use to render this object
     */
    public $javaScript;

}

/**
 * 
 */
class FundingInitiateModel
{

    /**
     * @var boolean Set this value to true to request an email to the recipient
     */
    public $requestEmail;

    /**
     * @var string If you have requested an email for funding setup, this is the recipient who will receive an 
email inviting them to setup funding configuration for Avalara Managed Returns. The recipient can
then click on a link in the email and setup funding configuration for this company.
     */
    public $fundingEmailRecipient;

    /**
     * @var boolean Set this value to true to request an HTML-based funding widget that can be embedded within an 
existing user interface. A user can then interact with the HTML-based funding widget to set up
funding information for the company.
     */
    public $requestWidget;

}

/**
 * Represents one configuration setting for this company
 */
class CompanyConfigurationModel
{

    /**
     * @var int The unique ID number of the account to which this setting applies
     */
    public $companyId;

    /**
     * @var string The category of the configuration setting. Avalara-defined categories include `AddressServiceConfig` and `TaxServiceConfig`. Customer-defined categories begin with `X-`.
     */
    public $category;

    /**
     * @var string The name of the configuration setting
     */
    public $name;

    /**
     * @var string The current value of the configuration setting
     */
    public $value;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Identifies all nexus that match a particular tax form
 */
class NexusByTaxFormModel
{

    /**
     * @var string The code of the tax form that was requested
     */
    public $formCode;

    /**
     * @var int The company ID of the company that was used to load the companyNexus array. If this value is null, no company data was loaded.
     */
    public $companyId;

    /**
     * @var NexusModel[] A list of all Avalara-defined nexus that are relevant to this tax form
     */
    public $nexusDefinitions;

    /**
     * @var NexusModel[] A list of all currently-defined company nexus that are related to this tax form
     */
    public $companyNexus;

}

/**
 * Information about Avalara-defined tax code types.
* This list is used when creating tax codes and tax rules.
 */
class TaxCodeTypesModel
{

    /**
     * @var object The list of Avalara-defined tax code types.
     */
    public $types;

}

/**
 * Represents a service or a subscription type.
 */
class SubscriptionTypeModel
{

    /**
     * @var int The unique ID number of this subscription type.
     */
    public $id;

    /**
     * @var string The friendly name of the service this subscription type represents.
     */
    public $description;

}

/**
 * Represents a single security role.
 */
class SecurityRoleModel
{

    /**
     * @var int The unique ID number of this security role.
     */
    public $id;

    /**
     * @var string A description of this security role
     */
    public $description;

}

/**
 * Tax Authority Model
 */
class TaxAuthorityModel
{

    /**
     * @var int The unique ID number of this tax authority.
     */
    public $id;

    /**
     * @var string The friendly name of this tax authority.
     */
    public $name;

    /**
     * @var int The type of this tax authority.
     */
    public $taxAuthorityTypeId;

    /**
     * @var int The unique ID number of the jurisdiction for this tax authority.
     */
    public $jurisdictionId;

}

/**
 * Represents a form that can be filed with a tax authority.
 */
class TaxAuthorityFormModel
{

    /**
     * @var int The unique ID number of the tax authority.
     */
    public $taxAuthorityId;

    /**
     * @var string The form name of the form for this tax authority.
     */
    public $formName;

}

/**
 * An extra property that can change the behavior of tax transactions.
 */
class ParameterModel
{

    /**
     * @var int The unique ID number of this property.
     */
    public $id;

    /**
     * @var string The service category of this property. Some properties may require that you subscribe to certain features of avatax before they can be used.
     */
    public $category;

    /**
     * @var string The name of the property. To use this property, add a field on the "properties" object of a /api/v2/companies/(code)/transactions/create call.
     */
    public $name;

    /**
     * @var string The data type of the property. (See ParameterBagDataType::* for a list of allowable values)
     */
    public $dataType;

    /**
     * @var string A full description of this property.
     */
    public $description;

}

/**
 * Information about questions that the local jurisdictions require for each location
 */
class LocationQuestionModel
{

    /**
     * @var int The unique ID number of this location setting type
     */
    public $id;

    /**
     * @var string This is the prompt for this question
     */
    public $question;

    /**
     * @var string If additional information is available about the location setting, this contains descriptive text to help
you identify the correct value to provide in this setting.
     */
    public $description;

    /**
     * @var string If available, this regular expression will verify that the input from the user is in the expected format.
     */
    public $regularExpression;

    /**
     * @var string If available, this is an example value that you can demonstrate to the user to show what is expected.
     */
    public $exampleValue;

    /**
     * @var string Indicates which jurisdiction requires this question
     */
    public $jurisdictionName;

    /**
     * @var string Indicates which type of jurisdiction requires this question (See JurisdictionType::* for a list of allowable values)
     */
    public $jurisdictionType;

    /**
     * @var string Indicates the country that this jurisdiction belongs to
     */
    public $jurisdictionCountry;

    /**
     * @var string Indicates the state, region, or province that this jurisdiction belongs to
     */
    public $jurisdictionRegion;

}

/**
 * Represents an ISO 3166 recognized country
 */
class IsoCountryModel
{

    /**
     * @var string The two character ISO 3166 country code
     */
    public $code;

    /**
     * @var string The full name of this country as it is known in US English
     */
    public $name;

    /**
     * @var boolean True if this country is a member of the European Union
     */
    public $isEuropeanUnion;

}

/**
 * Represents a region, province, or state within a country
 */
class IsoRegionModel
{

    /**
     * @var string The two-character ISO 3166 country code this region belongs to
     */
    public $countryCode;

    /**
     * @var string The three character ISO 3166 region code
     */
    public $code;

    /**
     * @var string The full name, using localized characters, for this region
     */
    public $name;

    /**
     * @var string The word in the local language that classifies what type of a region this represents
     */
    public $classification;

    /**
     * @var boolean For the United States, this flag indicates whether a U.S. State participates in the Streamlined
Sales Tax program. For countries other than the US, this flag is null.
     */
    public $streamlinedSalesTax;

}

/**
 * Represents a code describing the intended use for a product that may affect its taxability
 */
class EntityUseCodeModel
{

    /**
     * @var string The Avalara-recognized entity use code for this definition
     */
    public $code;

    /**
     * @var string The name of this entity use code
     */
    public $name;

    /**
     * @var string Text describing the meaning of this use code
     */
    public $description;

    /**
     * @var string[] A list of countries where this use code is valid
     */
    public $validCountries;

}

/**
 * Tax Authority Type Model
 */
class TaxAuthorityTypeModel
{

    /**
     * @var int The unique ID number of this tax Authority customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var string Tax Authority Group
     */
    public $taxAuthorityGroup;

}

/**
 * Tax Notice Status Model
 */
class NoticeStatusModel
{

    /**
     * @var int The unique ID number of this tax authority type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean True if a tax notice in this status is considered 'open' and has more work expected to be done before it is closed.
     */
    public $isOpen;

    /**
     * @var int If a list of status values is to be displayed in a dropdown, they should be displayed in this numeric order.
     */
    public $sortOrder;

}

/**
 * Tax Authority Model
 */
class NoticeCustomerTypeModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * Tax Notice Reason Model
 */
class NoticeReasonModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * FilingFrequency Model
 */
class FilingFrequencyModel
{

    /**
     * @var int The unique ID number of this filing frequency.
     */
    public $id;

    /**
     * @var string The description name of this filing frequency
     */
    public $description;

}

/**
 * Tax Notice FilingType Model
 */
class NoticeFilingTypeModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * Tax Notice Type Model
 */
class NoticeTypeModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * Tax Authority Model
 */
class NoticeCustomerFundingOptionModel
{

    /**
     * @var int The unique ID number of this tax notice customer FundingOption.
     */
    public $id;

    /**
     * @var string The description name of this tax authority FundingOption.
     */
    public $description;

    /**
     * @var boolean A flag if the FundingOption is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the FundingOptions
     */
    public $sortOrder;

}

/**
 * Tax Notice Priority Model
 */
class NoticePriorityModel
{

    /**
     * @var int The unique ID number of this tax notice customer Priority.
     */
    public $id;

    /**
     * @var string The description name of this tax authority Priority.
     */
    public $description;

    /**
     * @var boolean A flag if the Priority is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the Prioritys
     */
    public $sortOrder;

}

/**
 * NoticeResponsibility Model
 */
class NoticeResponsibilityModel
{

    /**
     * @var int The unique ID number of this notice responsibility.
     */
    public $id;

    /**
     * @var string The description name of this notice responsibility
     */
    public $description;

    /**
     * @var boolean Defines if the responsibility is active
     */
    public $isActive;

    /**
     * @var int The sort order of this responsibility
     */
    public $sortOrder;

}

/**
 * NoticeRootCause Model
 */
class NoticeRootCauseModel
{

    /**
     * @var int The unique ID number of this notice RootCause.
     */
    public $id;

    /**
     * @var string The description name of this notice RootCause
     */
    public $description;

    /**
     * @var boolean Defines if the RootCause is active
     */
    public $isActive;

    /**
     * @var int The sort order of this RootCause
     */
    public $sortOrder;

}

/**
 * Represents a list of statuses of returns available in skyscraper
 */
class SkyscraperStatusModel
{

    /**
     * @var string The specific name of the returns available in skyscraper
     */
    public $name;

    /**
     * @var string[] The tax form codes available to file through skyscrper
     */
    public $taxFormCodes;

    /**
     * @var string The country of the returns
     */
    public $country;

    /**
     * @var string They Scraper type (See ScraperType::* for a list of allowable values)
     */
    public $scraperType;

    /**
     * @var boolean Indicates if the return is currently available
     */
    public $isAvailable;

    /**
     * @var string The expected response time of the call
     */
    public $expectedResponseTime;

    /**
     * @var string Message on the returns
     */
    public $message;

    /**
     * @var requiredFilingCalendarDataFieldModel[] A list of required fields to file
     */
    public $requiredFilingCalendarDataFields;

}

/**
 * Represents a verification request using Skyscraper for a company
 */
class requiredFilingCalendarDataFieldModel
{

    /**
     * @var string Region of the verification request
     */
    public $name;

    /**
     * @var string Username that we are using for verification
     */
    public $description;

}

/**
 * Represents an override of tax jurisdictions for a specific address.
* 
* During the time period represented by EffDate through EndDate, all tax decisions for addresses matching
* this override object will be assigned to the list of jurisdictions designated in this object.
 */
class JurisdictionOverrideModel
{

    /**
     * @var int The unique ID number of this override.
     */
    public $id;

    /**
     * @var int The unique ID number assigned to this account.
     */
    public $accountId;

    /**
     * @var string A description of why this jurisdiction override was created.
     */
    public $description;

    /**
     * @var string The street address of the physical location affected by this override.
     */
    public $line1;

    /**
     * @var string The city address of the physical location affected by this override.
     */
    public $city;

    /**
     * @var string The two or three character ISO region code of the region, state, or province affected by this override.
     */
    public $region;

    /**
     * @var string The two character ISO-3166 country code of the country affected by this override.
Note that only United States addresses are affected by the jurisdiction override system.
     */
    public $country;

    /**
     * @var string The postal code of the physical location affected by this override.
     */
    public $postalCode;

    /**
     * @var string The date when this override first takes effect. Set this value to null to affect all dates up to the end date.
     */
    public $effectiveDate;

    /**
     * @var string The date when this override will cease to take effect. Set this value to null to never expire.
     */
    public $endDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var JurisdictionModel[] A list of the tax jurisdictions that will be assigned to this overridden address.
     */
    public $jurisdictions;

    /**
     * @var int The TaxRegionId of the new location affected by this jurisdiction override.
     */
    public $taxRegionId;

    /**
     * @var string The boundary level of this override (See BoundaryLevel::* for a list of allowable values)
     */
    public $boundaryLevel;

    /**
     * @var boolean True if this is a default boundary
     */
    public $isDefault;

}

/**
 * Represents information about a single legal taxing jurisdiction
 */
class JurisdictionModel
{

    /**
     * @var string The code that is used to identify this jurisdiction
     */
    public $code;

    /**
     * @var string The name of this jurisdiction
     */
    public $name;

    /**
     * @var string The type of the jurisdiction, indicating whether it is a country, state/region, city, for example. (See JurisdictionType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var float The base rate of tax specific to this jurisdiction.
     */
    public $rate;

    /**
     * @var float The "Sales" tax rate specific to this jurisdiction.
     */
    public $salesRate;

    /**
     * @var string The Avalara-supplied signature code for this jurisdiction.
     */
    public $signatureCode;

    /**
     * @var string The state assigned code for this jurisdiction, if any.
     */
    public $region;

    /**
     * @var float The "Seller's Use" tax rate specific to this jurisdiction.
     */
    public $useRate;

}

/**
 * Resource File Type Model
 */
class ResourceFileTypeModel
{

    /**
     * @var int The resource file type id
     */
    public $resourceFileTypeId;

    /**
     * @var string The name of the file type
     */
    public $name;

}

/**
 * Rate type Model
 */
class RateTypeModel
{

    /**
     * @var string The unique ID number of this tax authority.
     */
    public $id;

    /**
     * @var string Description of this rate type.
     */
    public $description;

    /**
     * @var string Country code for this rate type
     */
    public $country;

}

/**
 * 
 */
class AvaFileFormModel
{

    /**
     * @var int Unique Id of the form
     */
    public $id;

    /**
     * @var string Name of the file being returned
     */
    public $returnName;

    /**
     * @var string Name of the submitted form
     */
    public $formName;

    /**
     * @var string A description of the submitted form
     */
    public $description;

    /**
     * @var string The date this form starts to take effect
     */
    public $effDate;

    /**
     * @var string The date the form finishes to take effect
     */
    public $endDate;

    /**
     * @var string State/Province/Region where the form is submitted for
     */
    public $region;

    /**
     * @var string The country this form is submitted for
     */
    public $country;

    /**
     * @var int The type of the form being submitted
     */
    public $formTypeId;

    /**
     * @var int 
     */
    public $filingOptionTypeId;

    /**
     * @var int The type of the due date
     */
    public $dueDateTypeId;

    /**
     * @var int Due date
     */
    public $dueDay;

    /**
     * @var int 
     */
    public $efileDueDateTypeId;

    /**
     * @var int The date by when the E-filing should be submitted
     */
    public $efileDueDay;

    /**
     * @var string The time of day by when the E-filing should be submitted
     */
    public $efileDueTime;

    /**
     * @var boolean Whether the customer has discount
     */
    public $hasVendorDiscount;

    /**
     * @var int The way system does the rounding
     */
    public $roundingTypeId;

}

/**
 * 
 */
class TaxTypeGroupModel
{

    /**
     * @var int The unique ID number of this tax type group.
     */
    public $id;

    /**
     * @var string The unique human readable Id of this tax type group.
     */
    public $taxTypeGroup;

    /**
     * @var string The description of this tax type group.
     */
    public $description;

}

/**
 * 
 */
class TaxSubTypeModel
{

    /**
     * @var int The unique ID number of this tax sub-type.
     */
    public $id;

    /**
     * @var string The unique human readable Id of this tax sub-type.
     */
    public $taxSubType;

    /**
     * @var string The description of this tax sub-type.
     */
    public $description;

    /**
     * @var string The upper level group of tax types.
     */
    public $taxTypeGroup;

}

/**
 * 
 */
class NexusTaxTypeGroupModel
{

    /**
     * @var int The unique ID number of this nexus tax type group.
     */
    public $id;

    /**
     * @var string The unique human readable Id of this nexus tax type group.
     */
    public $nexusTaxTypeGroupId;

    /**
     * @var string The description of this nexus tax type group.
     */
    public $description;

}

/**
 * Represents a commitment to file a tax return on a recurring basis.
* Only used if you subscribe to Avalara Returns.
 */
class FilingCalendarModel
{

    /**
     * @var int The unique ID number of this filing calendar.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this filing calendar belongs.
     */
    public $companyId;

    /**
     * @var string The name of the tax form to file.
     */
    public $returnName;

    /**
     * @var string If this calendar is for a location-specific tax return, specify the location code here. To file for all locations, leave this value NULL.
     */
    public $locationCode;

    /**
     * @var string If this calendar is for a location-specific tax return, specify the location-specific behavior here. (See OutletTypeId::* for a list of allowable values)
     */
    public $outletTypeId;

    /**
     * @var string Specify the ISO 4217 currency code for the currency to remit for this tax return. For all tax returns in the United States, specify "USD".
     */
    public $paymentCurrency;

    /**
     * @var string The frequency on which this tax form is filed. (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequencyId;

    /**
     * @var int A 16-bit bitmap containing a 1 for each month when the return should be filed.
     */
    public $months;

    /**
     * @var string Tax Registration ID for this Region - in the U.S., this is for your state.
     */
    public $stateRegistrationId;

    /**
     * @var string Tax Registration ID for the local jurisdiction, if any.
     */
    public $localRegistrationId;

    /**
     * @var string The Employer Identification Number or Taxpayer Identification Number that is to be used when filing this return.
     */
    public $employerIdentificationNumber;

    /**
     * @var string The first line of the physical address to be used when filing this tax return.
     */
    public $line1;

    /**
     * @var string The second line of the physical address to be used when filing this tax return.
Please note that some tax forms do not support multiple address lines.
     */
    public $line2;

    /**
     * @var string The city name of the physical address to be used when filing this tax return.
     */
    public $city;

    /**
     * @var string The state, region, or province of the physical address to be used when filing this tax return.
     */
    public $region;

    /**
     * @var string The postal code or zip code of the physical address to be used when filing this tax return.
     */
    public $postalCode;

    /**
     * @var string The two character ISO-3166 country code of the physical address to be used when filing this return.
     */
    public $country;

    /**
     * @var string The phone number to be used when filing this return.
     */
    public $phone;

    /**
     * @var string Special filing instructions to be used when filing this return.
Please note that requesting special filing instructions may incur additional costs.
     */
    public $customerFilingInstructions;

    /**
     * @var string The legal entity name to be used when filing this return.
     */
    public $legalEntityName;

    /**
     * @var string The earliest date for the tax period when this return should be filed.
This date specifies the earliest date for tax transactions that should be reported on this filing calendar.
Please note that tax is usually filed one month in arrears: for example, tax for January transactions is typically filed during the month of February.
     */
    public $effectiveDate;

    /**
     * @var string The last date for the tax period when this return should be filed.
This date specifies the last date for tax transactions that should be reported on this filing calendar.
Please note that tax is usually filed one month in arrears: for example, tax for January transactions is typically filed during the month of February.
     */
    public $endDate;

    /**
     * @var string The method to be used when filing this return. (See FilingTypeId::* for a list of allowable values)
     */
    public $filingTypeId;

    /**
     * @var string If you file electronically, this is the username you use to log in to the tax authority's website.
     */
    public $eFileUsername;

    /**
     * @var string If you file electronically, this is the password or pass code you use to log in to the tax authority's website.
     */
    public $eFilePassword;

    /**
     * @var int If you are required to prepay a percentage of taxes for future periods, please specify the percentage in whole numbers; 
for example, the value 90 would indicate 90%.
     */
    public $prepayPercentage;

    /**
     * @var string The type of tax to report on this return. (See MatchingTaxType::* for a list of allowable values)
     */
    public $taxTypeId;

    /**
     * @var string Internal filing notes.
     */
    public $internalNotes;

    /**
     * @var string Custom filing information field for Alabama.
     */
    public $alSignOn;

    /**
     * @var string Custom filing information field for Alabama.
     */
    public $alAccessCode;

    /**
     * @var string Custom filing information field for Maine.
     */
    public $meBusinessCode;

    /**
     * @var string Custom filing information field for Iowa.
     */
    public $iaBen;

    /**
     * @var string Custom filing information field for Connecticut.
     */
    public $ctReg;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other1Name;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other1Value;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other2Name;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other2Value;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other3Name;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other3Value;

    /**
     * @var int The unique ID of the tax authority of this return.
     */
    public $taxAuthorityId;

    /**
     * @var string The name of the tax authority of this return.
     */
    public $taxAuthorityName;

    /**
     * @var string The type description of the tax authority of this return.
     */
    public $taxAuthorityType;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Model with options for adding a new filing calendar
 */
class CycleAddOptionModel
{

    /**
     * @var boolean True if this form can be added and filed for the current cycle. "Current cycle" is considered one month before the month of today's date.
     */
    public $available;

    /**
     * @var string The period start date for the customer's first transaction in the jurisdiction being added
     */
    public $transactionalPeriodStart;

    /**
     * @var string The period end date for the customer's last transaction in the jurisdiction being added
     */
    public $transactionalPeriodEnd;

    /**
     * @var string The jurisdiction-assigned due date for the form
     */
    public $filingDueDate;

    /**
     * @var string A descriptive name of the cycle and due date of form.
     */
    public $cycleName;

    /**
     * @var string The filing frequency of the form
     */
    public $frequencyName;

    /**
     * @var string A code assigned to the filing frequency
     */
    public $filingFrequencyCode;

    /**
     * @var string The filing frequency of the request (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequencyId;

    /**
     * @var string An explanation for why this form cannot be added for the current cycle
     */
    public $cycleUnavailableReason;

    /**
     * @var string[] A list of outlet codes that can be assigned to this form for the current cycle
     */
    public $availableLocationCodes;

}

/**
 * Cycle Safe Expiration results.
 */
class CycleExpireModel
{

    /**
     * @var boolean Whether or not the filing calendar can be expired.
e.g. if user makes end date of a calendar earlier than latest filing, this would be set to false.
     */
    public $success;

    /**
     * @var string The message to present to the user if expiration is successful or unsuccessful.
     */
    public $message;

    /**
     * @var CycleExpireOptionModel[] A list of options for expiring the filing calendar.
     */
    public $cycleExpirationOptions;

}

/**
 * Options for expiring a filing calendar.
 */
class CycleExpireOptionModel
{

    /**
     * @var string The period start date for the customer's first transaction in the jurisdiction being expired.
     */
    public $transactionalPeriodStart;

    /**
     * @var string The period end date for the customer's last transaction in the jurisdiction being expired.
     */
    public $transactionalPeriodEnd;

    /**
     * @var string The jurisdiction-assigned due date for the form.
     */
    public $filingDueDate;

    /**
     * @var string A descriptive name of the cycle and due date of the form.
     */
    public $cycleName;

}

/**
 * An edit to be made on a filing calendar.
 */
class FilingCalendarEditModel
{

    /**
     * @var string The name of the field to be modified.
     */
    public $fieldName;

    /**
     * @var int The unique ID of the filing calendar question. "Filing calendar question" is the wording displayed to users for a given field.
     */
    public $questionId;

    /**
     * @var object The current value of the field.
     */
    public $oldValue;

    /**
     * @var object The new/proposed value of the field.
     */
    public $newValue;

}

/**
 * Model with options for actual filing calendar output based on user edits to filing calendar.
 */
class CycleEditOptionModel
{

    /**
     * @var boolean Whether or not changes can be made to the filing calendar.
     */
    public $success;

    /**
     * @var string The message to present to the user when calendar is successfully or unsuccessfully changed.
     */
    public $message;

    /**
     * @var boolean Whether or not the user should be warned of a change, because some changes are risky and may be being done not in accordance with jurisdiction rules.
For example, user would be warned if user changes filing frequency to new frequency with a start date during an accrual month of the existing frequency.
     */
    public $customerMustApprove;

    /**
     * @var boolean True if the filing calendar must be cloned to allow this change; false if the existing filing calendar can be changed itself.
     */
    public $mustCloneFilingCalendar;

    /**
     * @var string The effective date of the filing calendar (only applies if cloning).
     */
    public $clonedCalendarEffDate;

    /**
     * @var string The expired end date of the old filing calendar (only applies if cloning).
     */
    public $expiredCalendarEndDate;

}

/**
 * Represents a commitment to file a tax return on a recurring basis.
* Only used if you subscribe to Avalara Returns.
 */
class FilingRequestModel
{

    /**
     * @var int The unique ID number of this filing request.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this filing request belongs.
     */
    public $companyId;

    /**
     * @var string The current status of this request (See FilingRequestStatus::* for a list of allowable values)
     */
    public $filingRequestStatusId;

    /**
     * @var FilingRequestDataModel The data model object of the request
     */
    public $data;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents a commitment to file a tax return on a recurring basis.
* Only used if you subscribe to Avalara Returns.
 */
class FilingRequestDataModel
{

    /**
     * @var int The company return ID if requesting an update.
     */
    public $companyReturnId;

    /**
     * @var string The return name of the requested calendar
     */
    public $returnName;

    /**
     * @var string The filing frequency of the request (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequencyId;

    /**
     * @var string State registration ID of the company requesting the filing calendar.
     */
    public $registrationId;

    /**
     * @var int The months of the request
     */
    public $months;

    /**
     * @var string The type of tax to report on this return. (See MatchingTaxType::* for a list of allowable values)
     */
    public $taxTypeId;

    /**
     * @var string Location code of the request
     */
    public $locationCode;

    /**
     * @var string Filing cycle effective date of the request
     */
    public $effDate;

    /**
     * @var string Filing cycle end date of the request
     */
    public $endDate;

    /**
     * @var boolean Flag if the request is a clone of a current filing calendar
     */
    public $isClone;

    /**
     * @var string The region this request is for
     */
    public $region;

    /**
     * @var int The tax authority id of the return
     */
    public $taxAuthorityId;

    /**
     * @var string The tax authority name on the return
     */
    public $taxAuthorityName;

    /**
     * @var FilingAnswerModel[] Filing question answers
     */
    public $answers;

}

/**
 * 
 */
class FilingAnswerModel
{

    /**
     * @var int The ID number for a filing question
     */
    public $filingQuestionId;

    /**
     * @var object The value of the answer for the filing question identified by filingQuestionId
     */
    public $answer;

}

/**
 * This is the output model coming from skyscraper services
 */
class LoginVerificationOutputModel
{

    /**
     * @var int The job Id returned from skyscraper
     */
    public $jobId;

    /**
     * @var string The operation status of the job
     */
    public $operationStatus;

    /**
     * @var string The message returned from the job
     */
    public $message;

    /**
     * @var boolean Indicates if the login was successful
     */
    public $loginSuccess;

}

/**
 * Represents a verification request using Skyscraper for a company
 */
class LoginVerificationInputModel
{

    /**
     * @var int CompanyId that we are verifying the login information for
     */
    public $companyId;

    /**
     * @var int AccountId of the login verification
     */
    public $accountId;

    /**
     * @var string Region of the verification request
     */
    public $region;

    /**
     * @var string Username that we are using for verification
     */
    public $username;

    /**
     * @var string Password we are using for verification
     */
    public $password;

    /**
     * @var string Additional options of the verification
     */
    public $additionalOptions;

    /**
     * @var int Bulk Request Id of the verification
     */
    public $bulkRequestId;

    /**
     * @var int Priority of the verification request
     */
    public $priority;

}

/**
 * Represents a listing of all tax calculation data for filings and for accruing to future filings.
 */
class FilingModel
{

    /**
     * @var int The unique ID number of this filing.
     */
    public $id;

    /**
     * @var int The unique ID number of the company for this filing.
     */
    public $companyId;

    /**
     * @var int The month of the filing period for this tax filing. 
The filing period represents the year and month of the last day of taxes being reported on this filing. 
For example, an annual tax filing for Jan-Dec 2015 would have a filing period of Dec 2015.
     */
    public $month;

    /**
     * @var int The year of the filing period for this tax filing.
The filing period represents the year and month of the last day of taxes being reported on this filing. 
For example, an annual tax filing for Jan-Dec 2015 would have a filing period of Dec 2015.
     */
    public $year;

    /**
     * @var string Indicates whether this is an original or an amended filing. (See WorksheetTypeId::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var FilingRegionModel[] A listing of regional tax filings within this time period.
     */
    public $filingRegions;

}

/**
 * Regions
 */
class FilingRegionModel
{

    /**
     * @var int The unique ID number of this filing region.
     */
    public $id;

    /**
     * @var int The filing id that this region belongs too
     */
    public $filingId;

    /**
     * @var string The two-character ISO-3166 code for the country.
     */
    public $country;

    /**
     * @var string The two or three character region code for the region.
     */
    public $region;

    /**
     * @var float The sales amount.
     */
    public $salesAmount;

    /**
     * @var float The taxable amount.
     */
    public $taxableAmount;

    /**
     * @var float The tax amount.
     */
    public $taxAmount;

    /**
     * @var float The tax amount due.
     */
    public $taxDueAmount;

    /**
     * @var float The amount collected by Avalara for this region
     */
    public $collectAmount;

    /**
     * @var float Total remittance amount of all returns in region
     */
    public $totalRemittanceAmount;

    /**
     * @var float The non-taxable amount.
     */
    public $nonTaxableAmount;

    /**
     * @var float Consumer use tax liability.
     */
    public $consumerUseTaxAmount;

    /**
     * @var float Consumer use non-taxable amount.
     */
    public $consumerUseNonTaxableAmount;

    /**
     * @var float Consumer use taxable amount.
     */
    public $consumerUseTaxableAmount;

    /**
     * @var string The date the filing region was approved.
     */
    public $approveDate;

    /**
     * @var string The start date for the filing cycle.
     */
    public $startDate;

    /**
     * @var string The end date for the filing cycle.
     */
    public $endDate;

    /**
     * @var boolean Whether or not you have nexus in this region.
     */
    public $hasNexus;

    /**
     * @var string The current status of the filing region. (See FilingStatusId::* for a list of allowable values)
     */
    public $status;

    /**
     * @var FilingReturnModel[] A list of tax returns in this region.
     */
    public $returns;

    /**
     * @var FilingsCheckupSuggestedFormModel[] A list of tax returns in this region.
     */
    public $suggestReturns;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Filing Returns Model
 */
class FilingReturnModel
{

    /**
     * @var int The unique ID number of this filing return.
     */
    public $id;

    /**
     * @var int The region id that this return belongs too
     */
    public $filingRegionId;

    /**
     * @var int The unique ID number of the filing calendar associated with this return.
     */
    public $filingCalendarId;

    /**
     * @var int The resourceFileId of the return. Will be null if not available.
     */
    public $resourceFileId;

    /**
     * @var int Tax Authority ID of this return
     */
    public $taxAuthorityId;

    /**
     * @var string The current status of the filing return. (See FilingStatusId::* for a list of allowable values)
     */
    public $status;

    /**
     * @var string The filing frequency of the return. (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequency;

    /**
     * @var string The date the return was filed by Avalara.
     */
    public $filedDate;

    /**
     * @var string The start date of this return
     */
    public $startPeriod;

    /**
     * @var string The end date of this return
     */
    public $endPeriod;

    /**
     * @var float The sales amount.
     */
    public $salesAmount;

    /**
     * @var string The filing type of the return. (See FilingTypeId::* for a list of allowable values)
     */
    public $filingType;

    /**
     * @var string The name of the form.
     */
    public $formName;

    /**
     * @var float The remittance amount of the return.
     */
    public $remitAmount;

    /**
     * @var string The unique code of the form.
     */
    public $formCode;

    /**
     * @var string A description for the return.
     */
    public $description;

    /**
     * @var float The taxable amount.
     */
    public $taxableAmount;

    /**
     * @var float The tax amount.
     */
    public $taxAmount;

    /**
     * @var float The amount collected by avalara for this return
     */
    public $collectAmount;

    /**
     * @var float The tax due amount.
     */
    public $taxDueAmount;

    /**
     * @var float The non-taxable amount.
     */
    public $nonTaxableAmount;

    /**
     * @var float The non-taxable due amount.
     */
    public $nonTaxableDueAmount;

    /**
     * @var float Consumer use tax liability.
     */
    public $consumerUseTaxAmount;

    /**
     * @var float Consumer use non-taxable amount.
     */
    public $consumerUseNonTaxableAmount;

    /**
     * @var float Consumer use taxable amount.
     */
    public $consumerUseTaxableAmount;

    /**
     * @var float Total amount of adjustments on this return
     */
    public $totalAdjustments;

    /**
     * @var FilingAdjustmentModel[] The Adjustments for this return.
     */
    public $adjustments;

    /**
     * @var float Total amount of augmentations on this return
     */
    public $totalAugmentations;

    /**
     * @var FilingAugmentationModel[] The Augmentations for this return.
     */
    public $augmentations;

    /**
     * @var string Accrual type of the return (See AccrualType::* for a list of allowable values)
     */
    public $accrualType;

    /**
     * @var int The month of the filing period for this tax filing. 
The filing period represents the year and month of the last day of taxes being reported on this filing. 
For example, an annual tax filing for Jan-Dec 2015 would have a filing period of Dec 2015.
     */
    public $month;

    /**
     * @var int The year of the filing period for this tax filing.
The filing period represents the year and month of the last day of taxes being reported on this filing. 
For example, an annual tax filing for Jan-Dec 2015 would have a filing period of Dec 2015.
     */
    public $year;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

}

/**
 * Worksheet Checkup Report Suggested Form Model
 */
class FilingsCheckupSuggestedFormModel
{

    /**
     * @var int Tax Authority ID of the suggested form returned
     */
    public $taxAuthorityId;

    /**
     * @var string Country of the suggested form returned
     */
    public $country;

    /**
     * @var string Region of the suggested form returned
     */
    public $region;

    /**
     * @var string 
     */
    public $returnName;

    /**
     * @var string Name of the suggested form returned
     */
    public $taxFormCode;

}

/**
 * A model for return adjustments.
 */
class FilingAdjustmentModel
{

    /**
     * @var int The unique ID number for the adjustment.
     */
    public $id;

    /**
     * @var int The filing return id that this applies too
     */
    public $filingId;

    /**
     * @var float The adjustment amount.
     */
    public $amount;

    /**
     * @var string The filing period the adjustment is applied to. (See AdjustmentPeriodTypeId::* for a list of allowable values)
     */
    public $period;

    /**
     * @var string The type of the adjustment. (See AdjustmentTypeId::* for a list of allowable values)
     */
    public $type;

    /**
     * @var boolean Whether or not the adjustment has been calculated.
     */
    public $isCalculated;

    /**
     * @var string The account type of the adjustment. (See PaymentAccountTypeId::* for a list of allowable values)
     */
    public $accountType;

    /**
     * @var string A descriptive reason for creating this adjustment.
     */
    public $reason;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * A model for return augmentations.
 */
class FilingAugmentationModel
{

    /**
     * @var int The unique ID number for the augmentation.
     */
    public $id;

    /**
     * @var int The filing return id that this applies too
     */
    public $filingId;

    /**
     * @var float The field amount.
     */
    public $fieldAmount;

    /**
     * @var string The field name.
     */
    public $fieldName;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Rebuild a set of filings.
 */
class RebuildFilingsModel
{

    /**
     * @var boolean Set this value to true in order to rebuild the filings.
     */
    public $rebuild;

}

/**
 * Approve a set of filings.
 */
class ApproveFilingsModel
{

    /**
     * @var boolean Set this value to true in order to approve the filings.
     */
    public $approve;

}

/**
 * Results of the Worksheet Checkup report
 */
class FilingsCheckupModel
{

    /**
     * @var FilingsCheckupAuthorityModel[] A collection of authorities in the report
     */
    public $authorities;

}

/**
 * Cycle Safe Expiration results.
 */
class FilingsCheckupAuthorityModel
{

    /**
     * @var int Unique ID of the tax authority
     */
    public $taxAuthorityId;

    /**
     * @var string Location Code of the tax authority
     */
    public $locationCode;

    /**
     * @var string Name of the tax authority
     */
    public $taxAuthorityName;

    /**
     * @var int Type Id of the tax authority
     */
    public $taxAuthorityTypeId;

    /**
     * @var int Jurisdiction Id of the tax authority
     */
    public $jurisdictionId;

    /**
     * @var float Amount of tax collected in this tax authority
     */
    public $tax;

    /**
     * @var string Tax Type collected in the tax authority
     */
    public $taxTypeId;

    /**
     * @var FilingsCheckupSuggestedFormModel[] Suggested forms to file due to tax collected
     */
    public $suggestedForms;

}

/**
 * Tells you whether this location object has been correctly set up to the local jurisdiction's standards
 */
class LocationValidationModel
{

    /**
     * @var boolean True if the location has a value for each jurisdiction-required setting.
The user is required to ensure that the values are correct according to the jurisdiction; this flag
does not indicate whether the taxing jurisdiction has accepted the data you have provided.
     */
    public $settingsValidated;

    /**
     * @var LocationQuestionModel[] A list of settings that must be defined for this location
     */
    public $requiredSettings;

}

/**
 * Represents a letter received from a tax authority regarding tax filing.
* These letters often have the warning "Notice" printed at the top, which is why
* they are called "Notices".
 */
class NoticeModel
{

    /**
     * @var int The unique ID number of this notice.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this notice belongs.
     */
    public $companyId;

    /**
     * @var int The status id of the notice
     */
    public $statusId;

    /**
     * @var string The status of the notice
     */
    public $status;

    /**
     * @var string The received date of the notice
     */
    public $receivedDate;

    /**
     * @var string The closed date of the notice
     */
    public $closedDate;

    /**
     * @var float The total remmitance amount for the notice
     */
    public $totalRemit;

    /**
     * @var string NoticeCustomerTypeID can be retrieved from the definitions API (See NoticeCustomerType::* for a list of allowable values)
     */
    public $customerTypeId;

    /**
     * @var string The country the notice is in
     */
    public $country;

    /**
     * @var string The region the notice is for
     */
    public $region;

    /**
     * @var int The tax authority id of the notice
     */
    public $taxAuthorityId;

    /**
     * @var string The filing frequency of the notice (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequency;

    /**
     * @var string The filing type of the notice (See FilingTypeId::* for a list of allowable values)
     */
    public $filingTypeId;

    /**
     * @var string The ticket reference number of the notice
     */
    public $ticketReferenceNo;

    /**
     * @var string The ticket reference url of the notice
     */
    public $ticketReferenceUrl;

    /**
     * @var string The sales force case of the notice
     */
    public $salesForceCase;

    /**
     * @var string The URL to the sales force case
     */
    public $salesForceCaseUrl;

    /**
     * @var string The tax period of the notice
     */
    public $taxPeriod;

    /**
     * @var int The notice reason id
     */
    public $reasonId;

    /**
     * @var string The notice reason
     */
    public $reason;

    /**
     * @var int The tax notice type id
     */
    public $typeId;

    /**
     * @var string The tax notice type description
     */
    public $type;

    /**
     * @var string The notice customer funding options (See FundingOption::* for a list of allowable values)
     */
    public $customerFundingOptionId;

    /**
     * @var string The priority of the notice (See NoticePriorityId::* for a list of allowable values)
     */
    public $priorityId;

    /**
     * @var string Comments from the customer on this notice
     */
    public $customerComment;

    /**
     * @var boolean Indicator to hide from customer
     */
    public $hideFromCustomer;

    /**
     * @var string Expected resolution date of the notice
     */
    public $expectedResolutionDate;

    /**
     * @var boolean Indicator to show customer this resolution date
     */
    public $showResolutionDateToCustomer;

    /**
     * @var int The unique ID number of the user that closed the notice
     */
    public $closedByUserId;

    /**
     * @var string The user who created the notice
     */
    public $createdByUserName;

    /**
     * @var int The unique ID number of the user that owns the notice
     */
    public $ownedByUserId;

    /**
     * @var string The description of the notice
     */
    public $description;

    /**
     * @var int The ava file form id of the notice
     */
    public $avaFileFormId;

    /**
     * @var int The id of the revenue contact
     */
    public $revenueContactId;

    /**
     * @var int The id of the compliance contact
     */
    public $complianceContactId;

    /**
     * @var string The document reference of the notice
     */
    public $documentReference;

    /**
     * @var string The jurisdiction name of the notice
     */
    public $jurisdictionName;

    /**
     * @var string The jurisdiction type of the notice
     */
    public $jurisdictionType;

    /**
     * @var NoticeCommentModel[] Additional comments on the notice
     */
    public $comments;

    /**
     * @var NoticeFinanceModel[] Finance details of the notice
     */
    public $finances;

    /**
     * @var NoticeResponsibilityDetailModel[] Notice Responsibility Details
     */
    public $responsibility;

    /**
     * @var NoticeRootCauseDetailModel[] Notice Root Cause Details
     */
    public $rootCause;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents communication between Avalara and the company regarding the processing of a tax notice.
 */
class NoticeCommentModel
{

    /**
     * @var int The unique ID number of this notice.
     */
    public $id;

    /**
     * @var int The ID of the notice this comment is attached too
     */
    public $noticeId;

    /**
     * @var string The date this comment was entered
     */
    public $date;

    /**
     * @var string TaxNoticeComment
     */
    public $comment;

    /**
     * @var int TaxNoticeCommentUserId
     */
    public $commentUserId;

    /**
     * @var string TaxNoticeCommentUserName
     */
    public $commentUserName;

    /**
     * @var int taxNoticeCommentTypeId
     */
    public $commentTypeId;

    /**
     * @var string taxNoticeCommentType (See CommentType::* for a list of allowable values)
     */
    public $commentType;

    /**
     * @var string TaxNoticeCommentLink
     */
    public $commentLink;

    /**
     * @var string TaxNoticeFileName
     */
    public $taxNoticeFileName;

    /**
     * @var int resourceFileId
     */
    public $resourceFileId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var ResourceFileUploadRequestModel An attachment to the detail
     */
    public $attachmentUploadRequest;

}

/**
 * Represents estimated financial results from responding to a tax notice.
 */
class NoticeFinanceModel
{

    /**
     * @var int 
     */
    public $id;

    /**
     * @var int 
     */
    public $noticeId;

    /**
     * @var string 
     */
    public $noticeDate;

    /**
     * @var string 
     */
    public $dueDate;

    /**
     * @var string 
     */
    public $noticeNumber;

    /**
     * @var float 
     */
    public $taxDue;

    /**
     * @var float 
     */
    public $penalty;

    /**
     * @var float 
     */
    public $interest;

    /**
     * @var float 
     */
    public $credits;

    /**
     * @var float 
     */
    public $taxAbated;

    /**
     * @var float 
     */
    public $customerPenalty;

    /**
     * @var float 
     */
    public $customerInterest;

    /**
     * @var float 
     */
    public $cspFeeRefund;

    /**
     * @var string resourceFileId
     */
    public $fileName;

    /**
     * @var int resourceFileId
     */
    public $resourceFileId;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var ResourceFileUploadRequestModel An attachment to the finance detail
     */
    public $attachmentUploadRequest;

}

/**
 * NoticeResponsibility Model
 */
class NoticeResponsibilityDetailModel
{

    /**
     * @var int The unique ID number of this filing frequency.
     */
    public $id;

    /**
     * @var int TaxNoticeId
     */
    public $noticeId;

    /**
     * @var int TaxNoticeResponsibilityId
     */
    public $taxNoticeResponsibilityId;

    /**
     * @var string The description name of this filing frequency
     */
    public $description;

}

/**
 * NoticeRootCause Model
 */
class NoticeRootCauseDetailModel
{

    /**
     * @var int The unique ID number of this filing frequency.
     */
    public $id;

    /**
     * @var int TaxNoticeId
     */
    public $noticeId;

    /**
     * @var int TaxNoticeRootCauseId
     */
    public $taxNoticeRootCauseId;

    /**
     * @var string The description name of this root cause
     */
    public $description;

}

/**
 * A request to upload a file to Resource Files
 */
class ResourceFileUploadRequestModel
{

    /**
     * @var string This stream contains the bytes of the file being uploaded. (This value is encoded as a Base64 string)
     */
    public $content;

    /**
     * @var string The username adding the file
     */
    public $username;

    /**
     * @var int The account ID to which this file will be attached.
     */
    public $accountId;

    /**
     * @var int The company ID to which this file will be attached.
     */
    public $companyId;

    /**
     * @var string The original name of this file.
     */
    public $name;

    /**
     * @var int The resource type ID of this file.
     */
    public $resourceFileTypeId;

    /**
     * @var int Length of the file in bytes.
     */
    public $length;

}

/**
 * Password Change Model
 */
class PasswordChangeModel
{

    /**
     * @var string Old Password
     */
    public $oldPassword;

    /**
     * @var string New Password
     */
    public $newPassword;

}

/**
 * Set Password Model
 */
class SetPasswordModel
{

    /**
     * @var string New Password
     */
    public $newPassword;

}

/**
 * Point-of-Sale Data Request Model
 */
class PointOfSaleDataRequestModel
{

    /**
     * @var string A unique code that references a company within your account.
     */
    public $companyCode;

    /**
     * @var string The date associated with the response content. Default is current date. This field can be used to backdate or postdate the response content.
     */
    public $documentDate;

    /**
     * @var string The format of your response. Formats include JSON, CSV, and XML. (See PointOfSaleFileType::* for a list of allowable values)
     */
    public $responseType;

    /**
     * @var string[] A list of tax codes to include in this point-of-sale file. If no tax codes are specified, response will include all distinct tax codes associated with the Items within your company.
     */
    public $taxCodes;

    /**
     * @var string[] A list of location codes to include in this point-of-sale file. If no location codes are specified, response will include all locations within your company.
     */
    public $locationCodes;

    /**
     * @var boolean Set this value to true to include Juris Code in the response.
     */
    public $includeJurisCodes;

    /**
     * @var string A unique code assoicated with the Partner you may be working with. If you are not working with a Partner or your Partner has not provided you an ID, leave null. (See PointOfSalePartnerId::* for a list of allowable values)
     */
    public $partnerId;

}

/**
 * Tax Rate Model
 */
class TaxRateModel
{

    /**
     * @var float Total Rate
     */
    public $totalRate;

    /**
     * @var RateModel[] Rates
     */
    public $rates;

}

/**
 * Rate Model
 */
class RateModel
{

    /**
     * @var float Rate
     */
    public $rate;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Type (See JurisdictionType::* for a list of allowable values)
     */
    public $type;

}

/**
 * A single transaction - for example, a sales invoice or purchase order.
 */
class TransactionModel
{

    /**
     * @var int The unique ID number of this transaction.
     */
    public $id;

    /**
     * @var string A unique customer-provided code identifying this transaction.
     */
    public $code;

    /**
     * @var int The unique ID number of the company that recorded this transaction.
     */
    public $companyId;

    /**
     * @var string The date on which this transaction occurred.
     */
    public $date;

    /**
     * @var string The date when payment was made on this transaction. By default, this should be the same as the date of the transaction.
     */
    public $paymentDate;

    /**
     * @var string The status of the transaction. (See DocumentStatus::* for a list of allowable values)
     */
    public $status;

    /**
     * @var string The type of the transaction. For Returns customers, a transaction type of "Invoice" will be reported to the tax authorities.
A sales transaction represents a sale from the company to a customer. A purchase transaction represents a purchase made by the company.
A return transaction represents a customer who decided to request a refund after purchasing a product from the company. An inventory 
transfer transaction represents goods that were moved from one location of the company to another location without changing ownership. (See DocumentType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string If this transaction was created as part of a batch, this code indicates which batch.
     */
    public $batchCode;

    /**
     * @var string The three-character ISO 4217 currency code that was used for payment for this transaction.
     */
    public $currencyCode;

    /**
     * @var string The customer usage type for this transaction. Customer usage types often affect exemption or taxability rules.
     */
    public $customerUsageType;

    /**
     * @var string CustomerVendorCode
     */
    public $customerVendorCode;

    /**
     * @var string If this transaction was exempt, this field will contain the word "Exempt".
     */
    public $exemptNo;

    /**
     * @var boolean If this transaction has been reconciled against the company's ledger, this value is set to true.
     */
    public $reconciled;

    /**
     * @var string If this transaction was made from a specific reporting location, this is the code string of the location.
For customers using Returns, this indicates how tax will be reported according to different locations on the tax forms.
     */
    public $locationCode;

    /**
     * @var string The customer-supplied purchase order number of this transaction.
     */
    public $purchaseOrderNo;

    /**
     * @var string A user-defined reference code for this transaction.
     */
    public $referenceCode;

    /**
     * @var string The salesperson who provided this transaction. Not required.
     */
    public $salespersonCode;

    /**
     * @var string If a tax override was applied to this transaction, indicates what type of tax override was applied. (See TaxOverrideTypeId::* for a list of allowable values)
     */
    public $taxOverrideType;

    /**
     * @var float If a tax override was applied to this transaction, indicates the amount of tax that was requested by the customer.
     */
    public $taxOverrideAmount;

    /**
     * @var string If a tax override was applied to this transaction, indicates the reason for the tax override.
     */
    public $taxOverrideReason;

    /**
     * @var float The total amount of this transaction.
     */
    public $totalAmount;

    /**
     * @var float The amount of this transaction that was exempt.
     */
    public $totalExempt;

    /**
     * @var float The total tax calculated for all lines in this transaction.
     */
    public $totalTax;

    /**
     * @var float The portion of the total amount of this transaction that was taxable.
     */
    public $totalTaxable;

    /**
     * @var float If a tax override was applied to this transaction, indicates the amount of tax Avalara calculated for the transaction.
     */
    public $totalTaxCalculated;

    /**
     * @var string If this transaction was adjusted, indicates the unique ID number of the reason why the transaction was adjusted. (See AdjustmentReason::* for a list of allowable values)
     */
    public $adjustmentReason;

    /**
     * @var string If this transaction was adjusted, indicates a description of the reason why the transaction was adjusted.
     */
    public $adjustmentDescription;

    /**
     * @var boolean If this transaction has been reported to a tax authority, this transaction is considered locked and may not be adjusted after reporting.
     */
    public $locked;

    /**
     * @var string The two-or-three character ISO region code of the region for this transaction.
     */
    public $region;

    /**
     * @var string The two-character ISO 3166 code of the country for this transaction.
     */
    public $country;

    /**
     * @var int If this transaction was adjusted, this indicates the version number of this transaction. Incremented each time the transaction
is adjusted.
     */
    public $version;

    /**
     * @var string The software version used to calculate this transaction.
     */
    public $softwareVersion;

    /**
     * @var int The unique ID number of the origin address for this transaction.
     */
    public $originAddressId;

    /**
     * @var int The unique ID number of the destination address for this transaction.
     */
    public $destinationAddressId;

    /**
     * @var string If this transaction included foreign currency exchange, this is the date as of which the exchange rate was calculated.
     */
    public $exchangeRateEffectiveDate;

    /**
     * @var float If this transaction included foreign currency exchange, this is the exchange rate that was used.
     */
    public $exchangeRate;

    /**
     * @var boolean If true, this seller was considered the importer of record of a product shipped internationally.
     */
    public $isSellerImporterOfRecord;

    /**
     * @var string Description of this transaction.
     */
    public $description;

    /**
     * @var string Email address associated with this transaction.
     */
    public $email;

    /**
     * @var string VAT business identification number used for this transaction.
     */
    public $businessIdentificationNo;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var string Tax date for this transaction
     */
    public $taxDate;

    /**
     * @var TransactionLineModel[] Optional: A list of line items in this transaction. To fetch this list, add the query string "?$include=Lines" or "?$include=Details" to your URL.
     */
    public $lines;

    /**
     * @var TransactionAddressModel[] Optional: A list of line items in this transaction. To fetch this list, add the query string "?$include=Addresses" to your URL.
     */
    public $addresses;

    /**
     * @var TransactionLocationTypeModel[] Optional: A list of location types in this transaction. To fetch this list, add the query string "?$include=Addresses" to your URL.
     */
    public $locationTypes;

    /**
     * @var TransactionModel[] If this transaction has been adjusted, this list contains all the previous versions of the document.
     */
    public $history;

    /**
     * @var TransactionSummary[] Contains a summary of tax on this transaction.
     */
    public $summary;

    /**
     * @var object Contains a list of extra parameters that were set when the transaction was created.
     */
    public $parameters;

    /**
     * @var AvaTaxMessage[] List of informational and warning messages regarding this API call. These messages are only relevant to the current API call.
     */
    public $messages;

}

/**
 * One line item on this transaction.
 */
class TransactionLineModel
{

    /**
     * @var int The unique ID number of this transaction line item.
     */
    public $id;

    /**
     * @var int The unique ID number of the transaction to which this line item belongs.
     */
    public $transactionId;

    /**
     * @var string The line number or code indicating the line on this invoice or receipt or document.
     */
    public $lineNumber;

    /**
     * @var int The unique ID number of the boundary override applied to this line item.
     */
    public $boundaryOverrideId;

    /**
     * @var string The customer usage type for this line item. Usage type often affects taxability rules.
     */
    public $customerUsageType;

    /**
     * @var string A description of the item or service represented by this line.
     */
    public $description;

    /**
     * @var int The unique ID number of the destination address where this line was delivered or sold.
In the case of a point-of-sale transaction, the destination address and origin address will be the same.
In the case of a shipped transaction, they will be different.
     */
    public $destinationAddressId;

    /**
     * @var int The unique ID number of the origin address where this line was delivered or sold.
In the case of a point-of-sale transaction, the origin address and destination address will be the same.
In the case of a shipped transaction, they will be different.
     */
    public $originAddressId;

    /**
     * @var float The amount of discount that was applied to this line item. This represents the difference between list price and sale price of the item.
In general, a discount represents money that did not change hands; tax is calculated on only the amount of money that changed hands.
     */
    public $discountAmount;

    /**
     * @var int The type of discount, if any, that was applied to this line item.
     */
    public $discountTypeId;

    /**
     * @var float The amount of this line item that was exempt.
     */
    public $exemptAmount;

    /**
     * @var int The unique ID number of the exemption certificate that applied to this line item.
     */
    public $exemptCertId;

    /**
     * @var string If this line item was exempt, this string contains the word 'Exempt'.
     */
    public $exemptNo;

    /**
     * @var boolean True if this item is taxable.
     */
    public $isItemTaxable;

    /**
     * @var boolean True if this item is a Streamlined Sales Tax line item.
     */
    public $isSSTP;

    /**
     * @var string The code string of the item represented by this line item.
     */
    public $itemCode;

    /**
     * @var float The total amount of the transaction, including both taxable and exempt. This is the total price for all items.
To determine the individual item price, divide this by quantity.
     */
    public $lineAmount;

    /**
     * @var float The quantity of products sold on this line item.
     */
    public $quantity;

    /**
     * @var string A user-defined reference identifier for this transaction line item.
     */
    public $ref1;

    /**
     * @var string A user-defined reference identifier for this transaction line item.
     */
    public $ref2;

    /**
     * @var string The date when this transaction should be reported. By default, all transactions are reported on the date when the actual transaction took place.
In some cases, line items may be reported later due to delayed shipments or other business reasons.
     */
    public $reportingDate;

    /**
     * @var string The revenue account number for this line item.
     */
    public $revAccount;

    /**
     * @var string Indicates whether this line item was taxed according to the origin or destination. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var float The amount of tax generated for this line item.
     */
    public $tax;

    /**
     * @var float The taxable amount of this line item.
     */
    public $taxableAmount;

    /**
     * @var float The tax calculated for this line by Avalara. If the transaction was calculated with a tax override, this amount will be different from the "tax" value.
     */
    public $taxCalculated;

    /**
     * @var string The code string for the tax code that was used to calculate this line item.
     */
    public $taxCode;

    /**
     * @var int The unique ID number for the tax code that was used to calculate this line item.
     */
    public $taxCodeId;

    /**
     * @var string The date that was used for calculating tax amounts for this line item. By default, this date should be the same as the document date.
In some cases, for example when a consumer returns a product purchased previously, line items may be calculated using a tax date in the past
so that the consumer can receive a refund for the correct tax amount that was charged when the item was originally purchased.
     */
    public $taxDate;

    /**
     * @var string The tax engine identifier that was used to calculate this line item.
     */
    public $taxEngine;

    /**
     * @var string If a tax override was specified, this indicates the type of tax override. (See TaxOverrideTypeId::* for a list of allowable values)
     */
    public $taxOverrideType;

    /**
     * @var string VAT business identification number used for this transaction.
     */
    public $businessIdentificationNo;

    /**
     * @var float If a tax override was specified, this indicates the amount of tax that was requested.
     */
    public $taxOverrideAmount;

    /**
     * @var string If a tax override was specified, represents the reason for the tax override.
     */
    public $taxOverrideReason;

    /**
     * @var boolean True if tax was included in the purchase price of the item.
     */
    public $taxIncluded;

    /**
     * @var TransactionLineDetailModel[] Optional: A list of tax details for this line item. To fetch this list, add the query string "?$include=Details" to your URL.
     */
    public $details;

    /**
     * @var TransactionLineLocationTypeModel[] Optional: A list of location types for this line item. To fetch this list, add the query string "?$include=LineLocationTypes" to your URL.
     */
    public $lineLocationTypes;

    /**
     * @var object Contains a list of extra parameters that were set when the transaction was created.
     */
    public $parameters;

}

/**
 * An address used within this transaction.
 */
class TransactionAddressModel
{

    /**
     * @var int The unique ID number of this address.
     */
    public $id;

    /**
     * @var int The unique ID number of the document to which this address belongs.
     */
    public $transactionId;

    /**
     * @var string The boundary level at which this address was validated. (See BoundaryLevel::* for a list of allowable values)
     */
    public $boundaryLevel;

    /**
     * @var string The first line of the address.
     */
    public $line1;

    /**
     * @var string The second line of the address.
     */
    public $line2;

    /**
     * @var string The third line of the address.
     */
    public $line3;

    /**
     * @var string The city for the address.
     */
    public $city;

    /**
     * @var string The region, state, or province for the address.
     */
    public $region;

    /**
     * @var string The postal code or zip code for the address.
     */
    public $postalCode;

    /**
     * @var string The country for the address.
     */
    public $country;

    /**
     * @var int The unique ID number of the tax region for this address.
     */
    public $taxRegionId;

    /**
     * @var string Latitude for this address (CALC - 13394)
     */
    public $latitude;

    /**
     * @var string Longitude for this address (CALC - 13394)
     */
    public $longitude;

}

/**
 * Information about a location type
 */
class TransactionLocationTypeModel
{

    /**
     * @var int Location type ID for this location type in transaction
     */
    public $documentLocationTypeId;

    /**
     * @var int Transaction ID
     */
    public $documentId;

    /**
     * @var int Address ID for the transaction
     */
    public $documentAddressId;

    /**
     * @var string Location type code
     */
    public $locationTypeCode;

}

/**
 * Summary information about an overall transaction.
 */
class TransactionSummary
{

    /**
     * @var string Two character ISO-3166 country code.
     */
    public $country;

    /**
     * @var string Two or three character ISO region, state or province code, if applicable.
     */
    public $region;

    /**
     * @var string The type of jurisdiction that collects this tax. (See JurisdictionType::* for a list of allowable values)
     */
    public $jurisType;

    /**
     * @var string Jurisdiction Code for the taxing jurisdiction
     */
    public $jurisCode;

    /**
     * @var string The name of the jurisdiction that collects this tax.
     */
    public $jurisName;

    /**
     * @var int The unique ID of the Tax Authority Type that collects this tax.
     */
    public $taxAuthorityType;

    /**
     * @var string The state assigned number of the jurisdiction that collects this tax.
     */
    public $stateAssignedNo;

    /**
     * @var string The tax type of this tax. (See TaxType::* for a list of allowable values)
     */
    public $taxType;

    /**
     * @var string The name of the tax.
     */
    public $taxName;

    /**
     * @var string Group code when special grouping is enabled.
     */
    public $taxGroup;

    /**
     * @var string (DEPRECATED) Indicates the tax rate type. Please use rateTypeCode instead. (See RateType::* for a list of allowable values)
     */
    public $rateType;

    /**
     * @var string Indicates the code of the rate type. Use `/api/v2/definitions/ratetypes` for a full list of rate type codes.
     */
    public $rateTypeCode;

    /**
     * @var float Tax Base - The adjusted taxable amount.
     */
    public $taxable;

    /**
     * @var float Tax Rate - The rate of taxation, as a fraction of the amount.
     */
    public $rate;

    /**
     * @var float Tax amount - The calculated tax (Base * Rate).
     */
    public $tax;

    /**
     * @var float Tax Calculated by Avalara AvaTax. This may be overriden by a TaxOverride.TaxAmount.
     */
    public $taxCalculated;

    /**
     * @var float The amount of the transaction that was non-taxable.
     */
    public $nonTaxable;

    /**
     * @var float The amount of the transaction that was exempt.
     */
    public $exemption;

}

/**
 * An individual tax detail element. Represents the amount of tax calculated for a particular jurisdiction, for a particular line in an invoice.
 */
class TransactionLineDetailModel
{

    /**
     * @var int The unique ID number of this tax detail.
     */
    public $id;

    /**
     * @var int The unique ID number of the line within this transaction.
     */
    public $transactionLineId;

    /**
     * @var int The unique ID number of this transaction.
     */
    public $transactionId;

    /**
     * @var int The unique ID number of the address used for this tax detail.
     */
    public $addressId;

    /**
     * @var string The two character ISO 3166 country code of the country where this tax detail is assigned.
     */
    public $country;

    /**
     * @var string The two-or-three character ISO region code for the region where this tax detail is assigned.
     */
    public $region;

    /**
     * @var string For U.S. transactions, the Federal Information Processing Standard (FIPS) code for the county where this tax detail is assigned.
     */
    public $countyFIPS;

    /**
     * @var string For U.S. transactions, the Federal Information Processing Standard (FIPS) code for the state where this tax detail is assigned.
     */
    public $stateFIPS;

    /**
     * @var float The amount of this line that was considered exempt in this tax detail.
     */
    public $exemptAmount;

    /**
     * @var int The unique ID number of the exemption reason for this tax detail.
     */
    public $exemptReasonId;

    /**
     * @var boolean True if this detail element represented an in-state transaction.
     */
    public $inState;

    /**
     * @var string The code of the jurisdiction to which this tax detail applies.
     */
    public $jurisCode;

    /**
     * @var string The name of the jurisdiction to which this tax detail applies.
     */
    public $jurisName;

    /**
     * @var int The unique ID number of the jurisdiction to which this tax detail applies.
     */
    public $jurisdictionId;

    /**
     * @var string The Avalara-specified signature code of the jurisdiction to which this tax detail applies.
     */
    public $signatureCode;

    /**
     * @var string The state assigned number of the jurisdiction to which this tax detail applies.
     */
    public $stateAssignedNo;

    /**
     * @var string The type of the jurisdiction to which this tax detail applies. (See JurisTypeId::* for a list of allowable values)
     */
    public $jurisType;

    /**
     * @var float The amount of this line item that was considered nontaxable in this tax detail.
     */
    public $nonTaxableAmount;

    /**
     * @var int The rule according to which portion of this detail was considered nontaxable.
     */
    public $nonTaxableRuleId;

    /**
     * @var string The type of nontaxability that was applied to this tax detail. (See TaxRuleTypeId::* for a list of allowable values)
     */
    public $nonTaxableType;

    /**
     * @var float The rate at which this tax detail was calculated.
     */
    public $rate;

    /**
     * @var int The unique ID number of the rule according to which this tax detail was calculated.
     */
    public $rateRuleId;

    /**
     * @var int The unique ID number of the source of the rate according to which this tax detail was calculated.
     */
    public $rateSourceId;

    /**
     * @var string For Streamlined Sales Tax customers, the SST Electronic Return code under which this tax detail should be applied.
     */
    public $serCode;

    /**
     * @var string Indicates whether this tax detail applies to the origin or destination of the transaction. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var float The amount of tax for this tax detail.
     */
    public $tax;

    /**
     * @var float The taxable amount of this tax detail.
     */
    public $taxableAmount;

    /**
     * @var string The type of tax that was calculated. Depends on the company's nexus settings as well as the jurisdiction's tax laws. (See TaxType::* for a list of allowable values)
     */
    public $taxType;

    /**
     * @var string The name of the tax against which this tax amount was calculated.
     */
    public $taxName;

    /**
     * @var int The type of the tax authority to which this tax will be remitted.
     */
    public $taxAuthorityTypeId;

    /**
     * @var int The unique ID number of the tax region.
     */
    public $taxRegionId;

    /**
     * @var float The amount of tax that was calculated. This amount may be different if a tax override was used.
If the customer specified a tax override, this calculated tax value represents the amount of tax that would
have been charged if Avalara had calculated the tax for the rule.
     */
    public $taxCalculated;

    /**
     * @var float The amount of tax override that was specified for this tax line.
     */
    public $taxOverride;

    /**
     * @var string (DEPRECATED) The rate type for this tax detail. Please use rateTypeCode instead. (See RateType::* for a list of allowable values)
     */
    public $rateType;

    /**
     * @var string Indicates the code of the rate type that was used to calculate this tax detail. Use `/api/v2/definitions/ratetypes` for a full list of rate type codes.
     */
    public $rateTypeCode;

    /**
     * @var float Number of units in this line item that were calculated to be taxable according to this rate detail.
     */
    public $taxableUnits;

    /**
     * @var float Number of units in this line item that were calculated to be nontaxable according to this rate detail.
     */
    public $nonTaxableUnits;

    /**
     * @var float Number of units in this line item that were calculated to be exempt according to this rate detail.
     */
    public $exemptUnits;

    /**
     * @var string When calculating units, what basis of measurement did we use for calculating the units?
     */
    public $unitOfBasis;

}

/**
 * Represents information about location types stored in a line
 */
class TransactionLineLocationTypeModel
{

    /**
     * @var int The unique ID number of this line location address model
     */
    public $documentLineLocationTypeId;

    /**
     * @var int The unique ID number of the document line associated with this line location address model
     */
    public $documentLineId;

    /**
     * @var int The address ID corresponding to this model
     */
    public $documentAddressId;

    /**
     * @var string The location type code corresponding to this model
     */
    public $locationTypeCode;

}

/**
 * A request to adjust tax for a previously existing transaction
 */
class AdjustTransactionModel
{

    /**
     * @var string A reason code indicating why this adjustment was made (See AdjustmentReason::* for a list of allowable values)
     */
    public $adjustmentReason;

    /**
     * @var string If the AdjustmentReason is "Other", specify the reason here
     */
    public $adjustmentDescription;

    /**
     * @var CreateTransactionModel Replace the current transaction with tax data calculated for this new transaction
     */
    public $newTransaction;

}

/**
 * Create a transaction
 */
class CreateTransactionModel
{

    /**
     * @var string Document Type: if not specified, a document with type of SalesOrder will be created by default (See DocumentType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string Transaction Code - the internal reference code used by the client application. This is used for operations such as
Get, Adjust, Settle, and Void. If you leave the transaction code blank, a GUID will be assigned to each transaction.
     */
    public $code;

    /**
     * @var string Company Code - Specify the code of the company creating this transaction here. If you leave this value null,
your account's default company will be used instead.
     */
    public $companyCode;

    /**
     * @var string Transaction Date - The date on the invoice, purchase order, etc.
     */
    public $date;

    /**
     * @var string Salesperson Code - The client application salesperson reference code.
     */
    public $salespersonCode;

    /**
     * @var string Customer Code - The client application customer reference code.
     */
    public $customerCode;

    /**
     * @var string Customer Usage Type - The client application customer or usage type. For a list of 
available usage types, see `/api/v2/definitions/entityusecodes`.
     */
    public $customerUsageType;

    /**
     * @var float Discount - The discount amount to apply to the document. This value will be applied only to lines
that have the `discounted` flag set to true. If no lines have `discounted` set to true, this discount
cannot be applied.
     */
    public $discount;

    /**
     * @var string Purchase Order Number for this document
This is required for single use exemption certificates to match the order and invoice with the certificate.
     */
    public $purchaseOrderNo;

    /**
     * @var string Exemption Number for this document
     */
    public $exemptionNo;

    /**
     * @var AddressesModel Default addresses for all lines in this document
     */
    public $addresses;

    /**
     * @var LineItemModel[] Document line items list
     */
    public $lines;

    /**
     * @var object Special parameters for this transaction.
To get a full list of available parameters, please use the /api/v2/definitions/parameters endpoint.
     */
    public $parameters;

    /**
     * @var string Reference Code used to reference the original document for a return invoice
     */
    public $referenceCode;

    /**
     * @var string Sets the sale location code (Outlet ID) for reporting this document to the tax authority.
     */
    public $reportingLocationCode;

    /**
     * @var boolean Causes the document to be committed if true. This option is only applicable for invoice document 
types, not orders.
     */
    public $commit;

    /**
     * @var string BatchCode for batch operations.
     */
    public $batchCode;

    /**
     * @var TaxOverrideModel Specifies a tax override for the entire document
     */
    public $taxOverride;

    /**
     * @var string 3 character ISO 4217 currency code.
     */
    public $currencyCode;

    /**
     * @var string Specifies whether the tax calculation is handled Local, Remote, or Automatic (default). This only 
applies when using an AvaLocal server. (See ServiceMode::* for a list of allowable values)
     */
    public $serviceMode;

    /**
     * @var float Currency exchange rate from this transaction to the company base currency.
     */
    public $exchangeRate;

    /**
     * @var string Effective date of the exchange rate.
     */
    public $exchangeRateEffectiveDate;

    /**
     * @var string Sets the POS Lane Code sent by the User for this document.
     */
    public $posLaneCode;

    /**
     * @var string VAT business identification number for the customer for this transaction. This number will be used for all lines 
in the transaction, except for those lines where you have defined a different business identification number.

If you specify a VAT business identification number for the customer in this transaction and you have also set up
a business identification number for your company during company setup, this transaction will be treated as a 
business-to-business transaction for VAT purposes and it will be calculated according to VAT tax rules.
     */
    public $businessIdentificationNo;

    /**
     * @var boolean Specifies if the Transaction has the seller as IsSellerImporterOfRecord
     */
    public $isSellerImporterOfRecord;

    /**
     * @var string Description
     */
    public $description;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string If the user wishes to request additional debug information from this transaction, specify a level higher than 'normal' (See TaxDebugLevel::* for a list of allowable values)
     */
    public $debugLevel;

}

/**
 * A series of addresses information in a GetTax call
 */
class AddressesModel
{

    /**
     * @var AddressLocationInfo If this transaction occurred at a retail point-of-sale location, use this
     */
    public $singleLocation;

    /**
     * @var AddressLocationInfo If this transaction was shipped from a warehouse location to a customer location, specify both "ShipFrom" and "ShipTo".
     */
    public $shipFrom;

    /**
     * @var AddressLocationInfo If this transaction was shipped from a warehouse location to a customer location, specify both "ShipFrom" and "ShipTo".
     */
    public $shipTo;

    /**
     * @var AddressLocationInfo The place of business where you receive the customer's order.
     */
    public $pointOfOrderOrigin;

    /**
     * @var AddressLocationInfo The place of business where you accept/approve the customer’s order,
thereby becoming contractually obligated to make the sale.
     */
    public $pointOfOrderAcceptance;

}

/**
 * Represents one line item in a transaction
 */
class LineItemModel
{

    /**
     * @var string Line number within this document
     */
    public $number;

    /**
     * @var float Quantity of items in this line
     */
    public $quantity;

    /**
     * @var float Total amount for this line
     */
    public $amount;

    /**
     * @var AddressesModel Specify any differences for addresses between this line and the rest of the document
     */
    public $addresses;

    /**
     * @var string Tax Code - System or Custom Tax Code.
     */
    public $taxCode;

    /**
     * @var string Customer Usage Type - The client application customer or usage type.
     */
    public $customerUsageType;

    /**
     * @var string Item Code (SKU)
     */
    public $itemCode;

    /**
     * @var string Exemption number for this line
     */
    public $exemptionCode;

    /**
     * @var boolean True if the document discount should be applied to this line
     */
    public $discounted;

    /**
     * @var boolean Indicates if line has Tax Included; defaults to false
     */
    public $taxIncluded;

    /**
     * @var string Revenue Account
     */
    public $revenueAccount;

    /**
     * @var string Reference 1 - Client specific reference field
     */
    public $ref1;

    /**
     * @var string Reference 2 - Client specific reference field
     */
    public $ref2;

    /**
     * @var string Item description. This is required for SST transactions if an unmapped ItemCode is used.
     */
    public $description;

    /**
     * @var string VAT business identification number for the customer for this line item. If you leave this field empty,
this line item will use whatever business identification number you provided at the transaction level.

If you specify a VAT business identification number for the customer in this transaction and you have also set up
a business identification number for your company during company setup, this transaction will be treated as a 
business-to-business transaction for VAT purposes and it will be calculated according to VAT tax rules.
     */
    public $businessIdentificationNo;

    /**
     * @var TaxOverrideModel Specifies a tax override for this line
     */
    public $taxOverride;

    /**
     * @var object Special parameters that apply to this line within this transaction.
To get a full list of available parameters, please use the /api/v2/definitions/parameters endpoint.
     */
    public $parameters;

}

/**
 * Represents a tax override for a transaction
 */
class TaxOverrideModel
{

    /**
     * @var string Identifies the type of tax override (See TaxOverrideType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var float Indicates a total override of the calculated tax on the document. AvaTax will distribute
the override across all the lines.
     */
    public $taxAmount;

    /**
     * @var string The override tax date to use
     */
    public $taxDate;

    /**
     * @var string This provides the reason for a tax override for audit purposes. It is required for types 2-4.
     */
    public $reason;

}

/**
 * Represents an address to resolve.
 */
class AddressLocationInfo
{

    /**
     * @var string If you wish to use the address of an existing location for this company, specify the address here.
Otherwise, leave this value empty.
     */
    public $locationCode;

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * A request to void a previously created transaction
 */
class VoidTransactionModel
{

    /**
     * @var string Please specify the reason for voiding or cancelling this transaction (See VoidReasonCode::* for a list of allowable values)
     */
    public $code;

}

/**
 * Settle this transaction with your ledger by executing one or many actions against that transaction. 
* You may use this endpoint to verify the transaction, change the transaction's code, and commit the transaction for reporting purposes.
* This endpoint may be used to execute any or all of these actions at once.
 */
class SettleTransactionModel
{

    /**
     * @var VerifyTransactionModel To use the "Settle" endpoint to verify a transaction, fill out this value.
     */
    public $verify;

    /**
     * @var ChangeTransactionCodeModel To use the "Settle" endpoint to change a transaction's code, fill out this value.
     */
    public $changeCode;

    /**
     * @var CommitTransactionModel To use the "Settle" endpoint to commit a transaction for reporting purposes, fill out this value.
If you use Avalara Returns, committing a transaction will cause that transaction to be filed.
     */
    public $commit;

}

/**
 * Verify this transaction by matching it to values in your accounting system.
 */
class VerifyTransactionModel
{

    /**
     * @var string Transaction Date - The date on the invoice, purchase order, etc.
     */
    public $verifyTransactionDate;

    /**
     * @var float Total Amount - The total amount (not including tax) for the document.
     */
    public $verifyTotalAmount;

    /**
     * @var float Total Tax - The total tax for the document.
     */
    public $verifyTotalTax;

}

/**
 * Settle this transaction with your ledger by verifying its amounts.
* If the transaction is not yet committed, you may specify the "commit" value to commit it to the ledger and allow it to be reported.
* You may also optionally change the transaction's code by specifying the "newTransactionCode" value.
 */
class ChangeTransactionCodeModel
{

    /**
     * @var string To change the transaction code for this transaction, specify the new transaction code here.
     */
    public $newCode;

}

/**
 * Commit this transaction as permanent
 */
class CommitTransactionModel
{

    /**
     * @var boolean Set this value to be true to commit this transaction.
Committing a transaction allows it to be reported on a tax return. Uncommitted transactions will not be reported.
     */
    public $commit;

}

/**
 * Commit this transaction as permanent
 */
class LockTransactionModel
{

    /**
     * @var boolean Set this value to be true to commit this transaction.
Committing a transaction allows it to be reported on a tax return. Uncommitted transactions will not be reported.
     */
    public $isLocked;

}

/**
 * Bulk lock documents model
 */
class BulkLockTransactionModel
{

    /**
     * @var int[] List of documents to lock
     */
    public $documentIds;

    /**
     * @var boolean The lock status to set for the documents designated in this API
     */
    public $isLocked;

}

/**
 * Returns information about transactions that were locked
 */
class BulkLockTransactionResult
{

    /**
     * @var int Number of records that have been modified
     */
    public $numberOfRecords;

}

/**
 * Create or adjust transaction model
 */
class CreateOrAdjustTransactionModel
{

    /**
     * @var CreateTransactionModel The create transaction model to be created or updated.
     */
    public $createTransactionModel;

}

/**
 * Information about a previously created transaction
 */
class AuditTransactionModel
{

    /**
     * @var int Unique ID number of the company that created this transaction
     */
    public $companyId;

    /**
     * @var string Server timestamp, in UTC, of the date/time when the original transaction was created
     */
    public $serverTimestamp;

    /**
     * @var string Length of time the original API call took
     */
    public $serverDuration;

    /**
     * @var string api call status (See ApiCallStatus::* for a list of allowable values)
     */
    public $apiCallStatus;

    /**
     * @var OriginalApiRequestResponseModel Original API request/response
     */
    public $original;

    /**
     * @var ReconstructedApiRequestResponseModel Reconstructed API request/response
     */
    public $reconstructed;

}

/**
 * Represents the exact API request and response from the original transaction API call, if available
 */
class OriginalApiRequestResponseModel
{

    /**
     * @var string API request
     */
    public $request;

    /**
     * @var string API response
     */
    public $response;

}

/**
 * This model contains a reconstructed CreateTransaction request object that could potentially be used
* to recreate this transaction.
* 
* Note that the API changes over time, and this reconstructed model is likely different from the exact request
* that was originally used to create this transaction.
 */
class ReconstructedApiRequestResponseModel
{

    /**
     * @var CreateTransactionModel API request
     */
    public $request;

}

/**
 * Refund a committed transaction
 */
class RefundTransactionModel
{

    /**
     * @var string the committed transaction code to be refunded
     */
    public $refundTransactionCode;

    /**
     * @var string The date of the refund. If null, today's date will be used
     */
    public $refundDate;

    /**
     * @var string Type of this refund (See RefundType::* for a list of allowable values)
     */
    public $refundType;

    /**
     * @var float Percentage for refund
     */
    public $refundPercentage;

    /**
     * @var string[] Process refund for these lines
     */
    public $refundLines;

}

/**
 * Model to add specific lines to exising transaction
 */
class AddTransactionLineModel
{

    /**
     * @var string company code
     */
    public $companyCode;

    /**
     * @var string document code for the transaction to add lines
     */
    public $transactionCode;

    /**
     * @var string document type (See DocumentType::* for a list of allowable values)
     */
    public $documentType;

    /**
     * @var LineItemModel[] List of lines to be added
     */
    public $lines;

    /**
     * @var boolean Option to renumber lines after add. After renumber, the line number becomes: "1", "2", "3", ...
     */
    public $renumber;

}

/**
 * Model to specify lines to be removed
 */
class RemoveTransactionLineModel
{

    /**
     * @var string company code
     */
    public $companyCode;

    /**
     * @var string document code for the transaction to add lines
     */
    public $transactionCode;

    /**
     * @var string document type (See DocumentType::* for a list of allowable values)
     */
    public $documentType;

    /**
     * @var string[] List of lines to be added
     */
    public $lines;

    /**
     * @var boolean ption to renumber lines after removal. After renumber, the line number becomes: "1", "2", "3", ...
     */
    public $renumber;

}

/**
 * User Entitlement Model
 */
class UserEntitlementModel
{

    /**
     * @var string[] List of API names and categories that this user is permitted to access
     */
    public $permissions;

    /**
     * @var string What access privileges does the current user have to see companies? (See CompanyAccessLevel::* for a list of allowable values)
     */
    public $accessLevel;

    /**
     * @var int[] The identities of all companies this user is permitted to access
     */
    public $companies;

}

/**
 * Ping Result Model
 */
class PingResultModel
{

    /**
     * @var string Version number
     */
    public $version;

    /**
     * @var boolean Returns true if you provided authentication for this API call; false if you did not.
     */
    public $authenticated;

    /**
     * @var string Returns the type of authentication you provided, if authenticated (See AuthenticationTypeId::* for a list of allowable values)
     */
    public $authenticationType;

    /**
     * @var string The username of the currently authenticated user, if any.
     */
    public $authenticatedUserName;

    /**
     * @var int The ID number of the currently authenticated user, if any.
     */
    public $authenticatedUserId;

    /**
     * @var int The ID number of the currently authenticated user's account, if any.
     */
    public $authenticatedAccountId;

}


/*****************************************************************************
 *                              Enumerated constants                         *
 *****************************************************************************/


/**
 * Lists of acceptable values for the enumerated data type TextCase
 */
class TextCase
{
    const C_UPPER = "Upper";
    const C_MIXED = "Mixed";

}


/**
 * Lists of acceptable values for the enumerated data type DocumentType
 */
class DocumentType
{
    const C_SALESORDER = "SalesOrder";
    const C_SALESINVOICE = "SalesInvoice";
    const C_PURCHASEORDER = "PurchaseOrder";
    const C_PURCHASEINVOICE = "PurchaseInvoice";
    const C_RETURNORDER = "ReturnOrder";
    const C_RETURNINVOICE = "ReturnInvoice";
    const C_INVENTORYTRANSFERORDER = "InventoryTransferOrder";
    const C_INVENTORYTRANSFERINVOICE = "InventoryTransferInvoice";
    const C_REVERSECHARGEORDER = "ReverseChargeOrder";
    const C_REVERSECHARGEINVOICE = "ReverseChargeInvoice";
    const C_ANY = "Any";

}


/**
 * Lists of acceptable values for the enumerated data type PointOfSaleFileType
 */
class PointOfSaleFileType
{
    const C_JSON = "Json";
    const C_CSV = "Csv";
    const C_XML = "Xml";

}


/**
 * Lists of acceptable values for the enumerated data type PointOfSalePartnerId
 */
class PointOfSalePartnerId
{
    const C_DMA = "DMA";
    const C_AX7 = "AX7";

}


/**
 * Lists of acceptable values for the enumerated data type ServiceTypeId
 */
class ServiceTypeId
{
    const C_NONE = "None";
    const C_AVATAXST = "AvaTaxST";
    const C_AVATAXPRO = "AvaTaxPro";
    const C_AVATAXGLOBAL = "AvaTaxGlobal";
    const C_AUTOADDRESS = "AutoAddress";
    const C_AUTORETURNS = "AutoReturns";
    const C_TAXSOLVER = "TaxSolver";
    const C_AVATAXCSP = "AvaTaxCsp";
    const C_TWE = "Twe";
    const C_MRS = "Mrs";
    const C_AVACERT = "AvaCert";
    const C_AUTHORIZATIONPARTNER = "AuthorizationPartner";
    const C_CERTCAPTURE = "CertCapture";
    const C_AVAUPC = "AvaUpc";
    const C_AVACUT = "AvaCUT";
    const C_AVALANDEDCOST = "AvaLandedCost";
    const C_AVALODGING = "AvaLodging";
    const C_AVABOTTLE = "AvaBottle";

}


/**
 * Lists of acceptable values for the enumerated data type AccountStatusId
 */
class AccountStatusId
{
    const C_INACTIVE = "Inactive";
    const C_ACTIVE = "Active";
    const C_TEST = "Test";
    const C_NEW = "New";

}


/**
 * Lists of acceptable values for the enumerated data type SecurityRoleId
 */
class SecurityRoleId
{
    const C_NOACCESS = "NoAccess";
    const C_SITEADMIN = "SiteAdmin";
    const C_ACCOUNTOPERATOR = "AccountOperator";
    const C_ACCOUNTADMIN = "AccountAdmin";
    const C_ACCOUNTUSER = "AccountUser";
    const C_SYSTEMADMIN = "SystemAdmin";
    const C_REGISTRAR = "Registrar";
    const C_CSPTESTER = "CSPTester";
    const C_CSPADMIN = "CSPAdmin";
    const C_SYSTEMOPERATOR = "SystemOperator";
    const C_TECHNICALSUPPORTUSER = "TechnicalSupportUser";
    const C_TECHNICALSUPPORTADMIN = "TechnicalSupportAdmin";
    const C_TREASURYUSER = "TreasuryUser";
    const C_TREASURYADMIN = "TreasuryAdmin";
    const C_COMPLIANCEUSER = "ComplianceUser";
    const C_COMPLIANCEADMIN = "ComplianceAdmin";
    const C_PROSTORESOPERATOR = "ProStoresOperator";
    const C_COMPANYUSER = "CompanyUser";
    const C_COMPANYADMIN = "CompanyAdmin";
    const C_COMPLIANCETEMPUSER = "ComplianceTempUser";
    const C_COMPLIANCEROOTUSER = "ComplianceRootUser";
    const C_COMPLIANCEOPERATOR = "ComplianceOperator";
    const C_SSTADMIN = "SSTAdmin";

}


/**
 * Lists of acceptable values for the enumerated data type PasswordStatusId
 */
class PasswordStatusId
{
    const C_USERCANNOTCHANGE = "UserCannotChange";
    const C_USERCANCHANGE = "UserCanChange";
    const C_USERMUSTCHANGE = "UserMustChange";

}


/**
 * Lists of acceptable values for the enumerated data type ErrorCodeId
 */
class ErrorCodeId
{
    const C_SERVERCONFIGURATION = "ServerConfiguration";
    const C_ACCOUNTINVALIDEXCEPTION = "AccountInvalidException";
    const C_COMPANYINVALIDEXCEPTION = "CompanyInvalidException";
    const C_ENTITYNOTFOUNDERROR = "EntityNotFoundError";
    const C_VALUEREQUIREDERROR = "ValueRequiredError";
    const C_RANGEERROR = "RangeError";
    const C_RANGECOMPAREERROR = "RangeCompareError";
    const C_RANGESETERROR = "RangeSetError";
    const C_TAXPAYERNUMBERREQUIRED = "TaxpayerNumberRequired";
    const C_COMMONPASSWORD = "CommonPassword";
    const C_WEAKPASSWORD = "WeakPassword";
    const C_STRINGLENGTHERROR = "StringLengthError";
    const C_EMAILVALIDATIONERROR = "EmailValidationError";
    const C_EMAILMISSINGERROR = "EmailMissingError";
    const C_PARSERFIELDNAMEERROR = "ParserFieldNameError";
    const C_PARSERFIELDVALUEERROR = "ParserFieldValueError";
    const C_PARSERSYNTAXERROR = "ParserSyntaxError";
    const C_PARSERTOOMANYPARAMETERSERROR = "ParserTooManyParametersError";
    const C_PARSERUNTERMINATEDVALUEERROR = "ParserUnterminatedValueError";
    const C_DELETEUSERSELFERROR = "DeleteUserSelfError";
    const C_OLDPASSWORDINVALID = "OldPasswordInvalid";
    const C_CANNOTCHANGEPASSWORD = "CannotChangePassword";
    const C_CANNOTCHANGECOMPANYCODE = "CannotChangeCompanyCode";
    const C_DATEFORMATERROR = "DateFormatError";
    const C_NODEFAULTCOMPANY = "NoDefaultCompany";
    const C_AUTHENTICATIONEXCEPTION = "AuthenticationException";
    const C_AUTHORIZATIONEXCEPTION = "AuthorizationException";
    const C_VALIDATIONEXCEPTION = "ValidationException";
    const C_INACTIVEUSERERROR = "InactiveUserError";
    const C_AUTHENTICATIONINCOMPLETE = "AuthenticationIncomplete";
    const C_BASICAUTHINCORRECT = "BasicAuthIncorrect";
    const C_IDENTITYSERVERERROR = "IdentityServerError";
    const C_BEARERTOKENINVALID = "BearerTokenInvalid";
    const C_MODELREQUIREDEXCEPTION = "ModelRequiredException";
    const C_ACCOUNTEXPIREDEXCEPTION = "AccountExpiredException";
    const C_VISIBILITYERROR = "VisibilityError";
    const C_BEARERTOKENNOTSUPPORTED = "BearerTokenNotSupported";
    const C_INVALIDSECURITYROLE = "InvalidSecurityRole";
    const C_INVALIDREGISTRARACTION = "InvalidRegistrarAction";
    const C_REMOTESERVERERROR = "RemoteServerError";
    const C_NOFILTERCRITERIAEXCEPTION = "NoFilterCriteriaException";
    const C_OPENCLAUSEEXCEPTION = "OpenClauseException";
    const C_JSONFORMATERROR = "JsonFormatError";
    const C_UNHANDLEDEXCEPTION = "UnhandledException";
    const C_REPORTINGCOMPANYMUSTHAVECONTACTSERROR = "ReportingCompanyMustHaveContactsError";
    const C_COMPANYPROFILENOTSET = "CompanyProfileNotSet";
    const C_MODELSTATEINVALID = "ModelStateInvalid";
    const C_DATERANGEERROR = "DateRangeError";
    const C_INVALIDDATERANGEERROR = "InvalidDateRangeError";
    const C_DELETEINFORMATION = "DeleteInformation";
    const C_CANNOTCREATEDELETEDOBJECTS = "CannotCreateDeletedObjects";
    const C_CANNOTMODIFYDELETEDOBJECTS = "CannotModifyDeletedObjects";
    const C_RETURNNAMENOTFOUND = "ReturnNameNotFound";
    const C_INVALIDADDRESSTYPEANDCATEGORY = "InvalidAddressTypeAndCategory";
    const C_DEFAULTCOMPANYLOCATION = "DefaultCompanyLocation";
    const C_INVALIDCOUNTRY = "InvalidCountry";
    const C_INVALIDCOUNTRYREGION = "InvalidCountryRegion";
    const C_BRAZILVALIDATIONERROR = "BrazilValidationError";
    const C_BRAZILEXEMPTVALIDATIONERROR = "BrazilExemptValidationError";
    const C_BRAZILPISCOFINSERROR = "BrazilPisCofinsError";
    const C_JURISDICTIONNOTFOUNDERROR = "JurisdictionNotFoundError";
    const C_MEDICALEXCISEERROR = "MedicalExciseError";
    const C_RATEDEPENDSTAXABILITYERROR = "RateDependsTaxabilityError";
    const C_RATEDEPENDSEUROPEERROR = "RateDependsEuropeError";
    const C_INVALIDRATETYPECODE = "InvalidRateTypeCode";
    const C_RATETYPENOTSUPPORTED = "RateTypeNotSupported";
    const C_CANNOTUPDATENESTEDOBJECTS = "CannotUpdateNestedObjects";
    const C_UPCCODEINVALIDCHARS = "UPCCodeInvalidChars";
    const C_UPCCODEINVALIDLENGTH = "UPCCodeInvalidLength";
    const C_INCORRECTPATHERROR = "IncorrectPathError";
    const C_INVALIDJURISDICTIONTYPE = "InvalidJurisdictionType";
    const C_MUSTCONFIRMRESETLICENSEKEY = "MustConfirmResetLicenseKey";
    const C_DUPLICATECOMPANYCODE = "DuplicateCompanyCode";
    const C_TINFORMATERROR = "TINFormatError";
    const C_DUPLICATENEXUSERROR = "DuplicateNexusError";
    const C_UNKNOWNNEXUSERROR = "UnknownNexusError";
    const C_PARENTNEXUSNOTFOUND = "ParentNexusNotFound";
    const C_INVALIDTAXCODETYPE = "InvalidTaxCodeType";
    const C_CANNOTACTIVATECOMPANY = "CannotActivateCompany";
    const C_DUPLICATEENTITYPROPERTY = "DuplicateEntityProperty";
    const C_REPORTINGENTITYERROR = "ReportingEntityError";
    const C_INVALIDRETURNOPERATIONERROR = "InvalidReturnOperationError";
    const C_CANNOTDELETECOMPANY = "CannotDeleteCompany";
    const C_COUNTRYOVERRIDESNOTAVAILABLE = "CountryOverridesNotAvailable";
    const C_JURISDICTIONOVERRIDEMISMATCH = "JurisdictionOverrideMismatch";
    const C_DUPLICATESYSTEMTAXCODE = "DuplicateSystemTaxCode";
    const C_SSTOVERRIDESNOTAVAILABLE = "SSTOverridesNotAvailable";
    const C_NEXUSDATEMISMATCH = "NexusDateMismatch";
    const C_TECHSUPPORTAUDITREQUIRED = "TechSupportAuditRequired";
    const C_NEXUSPARENTDATEMISMATCH = "NexusParentDateMismatch";
    const C_BEARERTOKENPARSEUSERIDERROR = "BearerTokenParseUserIdError";
    const C_RETRIEVEUSERERROR = "RetrieveUserError";
    const C_INVALIDCONFIGURATIONSETTING = "InvalidConfigurationSetting";
    const C_INVALIDCONFIGURATIONVALUE = "InvalidConfigurationValue";
    const C_INVALIDENUMVALUE = "InvalidEnumValue";
    const C_BATCHSALESAUDITMUSTBEZIPPEDERROR = "BatchSalesAuditMustBeZippedError";
    const C_BATCHZIPMUSTCONTAINONEFILEERROR = "BatchZipMustContainOneFileError";
    const C_BATCHINVALIDFILETYPEERROR = "BatchInvalidFileTypeError";
    const C_POINTOFSALEFILESIZE = "PointOfSaleFileSize";
    const C_POINTOFSALESETUP = "PointOfSaleSetup";
    const C_GETTAXERROR = "GetTaxError";
    const C_ADDRESSCONFLICTEXCEPTION = "AddressConflictException";
    const C_DOCUMENTCODECONFLICT = "DocumentCodeConflict";
    const C_MISSINGADDRESS = "MissingAddress";
    const C_INVALIDPARAMETER = "InvalidParameter";
    const C_INVALIDPARAMETERVALUE = "InvalidParameterValue";
    const C_COMPANYCODECONFLICT = "CompanyCodeConflict";
    const C_DOCUMENTFETCHLIMIT = "DocumentFetchLimit";
    const C_ADDRESSINCOMPLETE = "AddressIncomplete";
    const C_ADDRESSLOCATIONNOTFOUND = "AddressLocationNotFound";
    const C_MISSINGLINE = "MissingLine";
    const C_INVALIDADDRESSTEXTCASE = "InvalidAddressTextCase";
    const C_DOCUMENTNOTCOMMITTED = "DocumentNotCommitted";
    const C_MULTIDOCUMENTTYPESERROR = "MultiDocumentTypesError";
    const C_INVALIDDOCUMENTTYPESTOFETCH = "InvalidDocumentTypesToFetch";
    const C_BADDOCUMENTFETCH = "BadDocumentFetch";
    const C_SERVERUNREACHABLE = "ServerUnreachable";
    const C_SUBSCRIPTIONREQUIRED = "SubscriptionRequired";
    const C_ACCOUNTEXISTS = "AccountExists";
    const C_INVITATIONONLY = "InvitationOnly";
    const C_ZTBLISTCONNECTORFAIL = "ZTBListConnectorFail";
    const C_ZTBCREATESUBSCRIPTIONSFAIL = "ZTBCreateSubscriptionsFail";
    const C_FREETRIALNOTAVAILABLE = "FreeTrialNotAvailable";
    const C_INVALIDDOCUMENTSTATUSFORREFUND = "InvalidDocumentStatusForRefund";
    const C_REFUNDTYPEANDPERCENTAGEMISMATCH = "RefundTypeAndPercentageMismatch";
    const C_INVALIDDOCUMENTTYPEFORREFUND = "InvalidDocumentTypeForRefund";
    const C_REFUNDTYPEANDLINEMISMATCH = "RefundTypeAndLineMismatch";
    const C_NULLREFUNDPERCENTAGEANDLINES = "NullRefundPercentageAndLines";
    const C_INVALIDREFUNDTYPE = "InvalidRefundType";
    const C_REFUNDPERCENTAGEFORTAXONLY = "RefundPercentageForTaxOnly";
    const C_LINENOOUTOFRANGE = "LineNoOutOfRange";
    const C_REFUNDPERCENTAGEOUTOFRANGE = "RefundPercentageOutOfRange";
    const C_TAXRATENOTAVAILABLEFORFREEINTHISCOUNTRY = "TaxRateNotAvailableForFreeInThisCountry";
    const C_FILINGCALENDARCANNOTBEDELETED = "FilingCalendarCannotBeDeleted";
    const C_INVALIDEFFECTIVEDATE = "InvalidEffectiveDate";
    const C_NONOUTLETFORM = "NonOutletForm";
    const C_QUESTIONNOTNEEDEDFORTHISADDRESS = "QuestionNotNeededForThisAddress";
    const C_QUESTIONNOTVALIDFORTHISADDRESS = "QuestionNotValidForThisAddress";
    const C_CANNOTMODIFYLOCKEDTRANSACTION = "CannotModifyLockedTransaction";
    const C_LINEALREADYEXISTS = "LineAlreadyExists";
    const C_LINEDOESNOTEXIST = "LineDoesNotExist";
    const C_LINESNOTSPECIFIED = "LinesNotSpecified";

}


/**
 * Lists of acceptable values for the enumerated data type SeverityLevel
 */
class SeverityLevel
{
    const C_SUCCESS = "Success";
    const C_WARNING = "Warning";
    const C_ERROR = "Error";
    const C_EXCEPTION = "Exception";

}


/**
 * Lists of acceptable values for the enumerated data type ResolutionQuality
 */
class ResolutionQuality
{
    const C_NOTCODED = "NotCoded";
    const C_EXTERNAL = "External";
    const C_COUNTRYCENTROID = "CountryCentroid";
    const C_REGIONCENTROID = "RegionCentroid";
    const C_PARTIALCENTROID = "PartialCentroid";
    const C_POSTALCENTROIDGOOD = "PostalCentroidGood";
    const C_POSTALCENTROIDBETTER = "PostalCentroidBetter";
    const C_POSTALCENTROIDBEST = "PostalCentroidBest";
    const C_INTERSECTION = "Intersection";
    const C_INTERPOLATED = "Interpolated";
    const C_ROOFTOP = "Rooftop";
    const C_CONSTANT = "Constant";

}


/**
 * Lists of acceptable values for the enumerated data type JurisdictionType
 */
class JurisdictionType
{
    const C_COUNTRY = "Country";
    const C_COMPOSITE = "Composite";
    const C_STATE = "State";
    const C_COUNTY = "County";
    const C_CITY = "City";
    const C_SPECIAL = "Special";

}


/**
 * Lists of acceptable values for the enumerated data type BatchType
 */
class BatchType
{
    const C_AVACERTUPDATE = "AvaCertUpdate";
    const C_AVACERTUPDATEALL = "AvaCertUpdateAll";
    const C_BATCHMAINTENANCE = "BatchMaintenance";
    const C_COMPANYLOCATIONIMPORT = "CompanyLocationImport";
    const C_DOCUMENTIMPORT = "DocumentImport";
    const C_EXEMPTCERTIMPORT = "ExemptCertImport";
    const C_ITEMIMPORT = "ItemImport";
    const C_SALESAUDITEXPORT = "SalesAuditExport";
    const C_SSTPTESTDECKIMPORT = "SstpTestDeckImport";
    const C_TAXRULEIMPORT = "TaxRuleImport";
    const C_TRANSACTIONIMPORT = "TransactionImport";
    const C_UPCBULKIMPORT = "UPCBulkImport";
    const C_UPCVALIDATIONIMPORT = "UPCValidationImport";

}


/**
 * Lists of acceptable values for the enumerated data type BatchStatus
 */
class BatchStatus
{
    const C_WAITING = "Waiting";
    const C_SYSTEMERRORS = "SystemErrors";
    const C_CANCELLED = "Cancelled";
    const C_COMPLETED = "Completed";
    const C_CREATING = "Creating";
    const C_DELETED = "Deleted";
    const C_ERRORS = "Errors";
    const C_PAUSED = "Paused";
    const C_PROCESSING = "Processing";

}


/**
 * Lists of acceptable values for the enumerated data type RoundingLevelId
 */
class RoundingLevelId
{
    const C_LINE = "Line";
    const C_DOCUMENT = "Document";

}


/**
 * Lists of acceptable values for the enumerated data type TaxDependencyLevelId
 */
class TaxDependencyLevelId
{
    const C_DOCUMENT = "Document";
    const C_STATE = "State";
    const C_TAXREGION = "TaxRegion";
    const C_ADDRESS = "Address";

}


/**
 * Lists of acceptable values for the enumerated data type AddressTypeId
 */
class AddressTypeId
{
    const C_LOCATION = "Location";
    const C_SALESPERSON = "Salesperson";

}


/**
 * Lists of acceptable values for the enumerated data type AddressCategoryId
 */
class AddressCategoryId
{
    const C_STOREFRONT = "Storefront";
    const C_MAINOFFICE = "MainOffice";
    const C_WAREHOUSE = "Warehouse";
    const C_SALESPERSON = "Salesperson";
    const C_OTHER = "Other";

}


/**
 * Lists of acceptable values for the enumerated data type JurisTypeId
 */
class JurisTypeId
{
    const C_STA = "STA";
    const C_CTY = "CTY";
    const C_CIT = "CIT";
    const C_STJ = "STJ";
    const C_CNT = "CNT";

}


/**
 * Lists of acceptable values for the enumerated data type NexusTypeId
 */
class NexusTypeId
{
    const C_NONE = "None";
    const C_SALESORSELLERSUSETAX = "SalesOrSellersUseTax";
    const C_SALESTAX = "SalesTax";
    const C_SSTVOLUNTEER = "SSTVolunteer";
    const C_SSTNONVOLUNTEER = "SSTNonVolunteer";

}


/**
 * Lists of acceptable values for the enumerated data type Sourcing
 */
class Sourcing
{
    const C_MIXED = "Mixed";
    const C_DESTINATION = "Destination";
    const C_ORIGIN = "Origin";

}


/**
 * Lists of acceptable values for the enumerated data type LocalNexusTypeId
 */
class LocalNexusTypeId
{
    const C_SELECTED = "Selected";
    const C_STATEADMINISTERED = "StateAdministered";
    const C_ALL = "All";

}


/**
 * Lists of acceptable values for the enumerated data type MatchingTaxType
 */
class MatchingTaxType
{
    const C_EXCISE = "Excise";
    const C_LODGING = "Lodging";
    const C_BOTTLE = "Bottle";
    const C_ALL = "All";
    const C_BOTHSALESANDUSETAX = "BothSalesAndUseTax";
    const C_CONSUMERUSETAX = "ConsumerUseTax";
    const C_CONSUMERSUSEANDSELLERSUSETAX = "ConsumersUseAndSellersUseTax";
    const C_CONSUMERUSEANDSALESTAX = "ConsumerUseAndSalesTax";
    const C_FEE = "Fee";
    const C_VATINPUTTAX = "VATInputTax";
    const C_VATNONRECOVERABLEINPUTTAX = "VATNonrecoverableInputTax";
    const C_VATOUTPUTTAX = "VATOutputTax";
    const C_RENTAL = "Rental";
    const C_SALESTAX = "SalesTax";
    const C_USETAX = "UseTax";

}


/**
 * Lists of acceptable values for the enumerated data type RateType
 */
class RateType
{
    const C_REDUCEDA = "ReducedA";
    const C_REDUCEDB = "ReducedB";
    const C_FOOD = "Food";
    const C_GENERAL = "General";
    const C_INCREASEDSTANDARD = "IncreasedStandard";
    const C_LINENRENTAL = "LinenRental";
    const C_MEDICAL = "Medical";
    const C_PARKING = "Parking";
    const C_SUPERREDUCED = "SuperReduced";
    const C_REDUCEDR = "ReducedR";
    const C_STANDARD = "Standard";
    const C_ZERO = "Zero";

}


/**
 * Lists of acceptable values for the enumerated data type TaxRuleTypeId
 */
class TaxRuleTypeId
{
    const C_RATERULE = "RateRule";
    const C_RATEOVERRIDERULE = "RateOverrideRule";
    const C_BASERULE = "BaseRule";
    const C_EXEMPTENTITYRULE = "ExemptEntityRule";
    const C_PRODUCTTAXABILITYRULE = "ProductTaxabilityRule";
    const C_NEXUSRULE = "NexusRule";

}


/**
 * Lists of acceptable values for the enumerated data type ParameterBagDataType
 */
class ParameterBagDataType
{
    const C_STRING = "String";
    const C_BOOLEAN = "Boolean";
    const C_NUMERIC = "Numeric";

}


/**
 * Lists of acceptable values for the enumerated data type ScraperType
 */
class ScraperType
{
    const C_LOGIN = "Login";
    const C_CUSTOMERDORDATA = "CustomerDorData";

}


/**
 * Lists of acceptable values for the enumerated data type BoundaryLevel
 */
class BoundaryLevel
{
    const C_ADDRESS = "Address";
    const C_ZIP9 = "Zip9";
    const C_ZIP5 = "Zip5";

}


/**
 * Lists of acceptable values for the enumerated data type OutletTypeId
 */
class OutletTypeId
{
    const C_NONE = "None";
    const C_SCHEDULE = "Schedule";
    const C_DUPLICATE = "Duplicate";
    const C_CONSOLIDATED = "Consolidated";

}


/**
 * Lists of acceptable values for the enumerated data type FilingFrequencyId
 */
class FilingFrequencyId
{
    const C_MONTHLY = "Monthly";
    const C_QUARTERLY = "Quarterly";
    const C_SEMIANNUALLY = "SemiAnnually";
    const C_ANNUALLY = "Annually";
    const C_BIMONTHLY = "Bimonthly";
    const C_OCCASIONAL = "Occasional";
    const C_INVERSEQUARTERLY = "InverseQuarterly";

}


/**
 * Lists of acceptable values for the enumerated data type FilingTypeId
 */
class FilingTypeId
{
    const C_PAPERRETURN = "PaperReturn";
    const C_ELECTRONICRETURN = "ElectronicReturn";
    const C_SER = "SER";
    const C_EFTPAPER = "EFTPaper";
    const C_PHONEPAPER = "PhonePaper";
    const C_SIGNATUREREADY = "SignatureReady";
    const C_EFILECHECK = "EfileCheck";

}


/**
 * Lists of acceptable values for the enumerated data type FilingRequestStatus
 */
class FilingRequestStatus
{
    const C_NEW = "New";
    const C_VALIDATED = "Validated";
    const C_PENDING = "Pending";
    const C_ACTIVE = "Active";
    const C_PENDINGSTOP = "PendingStop";
    const C_INACTIVE = "Inactive";
    const C_CHANGEREQUEST = "ChangeRequest";
    const C_REQUESTAPPROVED = "RequestApproved";
    const C_REQUESTDENIED = "RequestDenied";

}


/**
 * Lists of acceptable values for the enumerated data type WorksheetTypeId
 */
class WorksheetTypeId
{
    const C_ORIGINAL = "Original";
    const C_AMENDED = "Amended";
    const C_TEST = "Test";

}


/**
 * Lists of acceptable values for the enumerated data type FilingStatusId
 */
class FilingStatusId
{
    const C_PENDINGAPPROVAL = "PendingApproval";
    const C_DIRTY = "Dirty";
    const C_APPROVEDTOFILE = "ApprovedToFile";
    const C_PENDINGFILING = "PendingFiling";
    const C_PENDINGFILINGONBEHALF = "PendingFilingOnBehalf";
    const C_FILED = "Filed";
    const C_FILEDONBEHALF = "FiledOnBehalf";
    const C_RETURNACCEPTED = "ReturnAccepted";
    const C_RETURNACCEPTEDONBEHALF = "ReturnAcceptedOnBehalf";
    const C_PAYMENTREMITTED = "PaymentRemitted";
    const C_VOIDED = "Voided";
    const C_PENDINGRETURN = "PendingReturn";
    const C_PENDINGRETURNONBEHALF = "PendingReturnOnBehalf";
    const C_DONOTFILE = "DoNotFile";
    const C_RETURNREJECTED = "ReturnRejected";
    const C_RETURNREJECTEDONBEHALF = "ReturnRejectedOnBehalf";
    const C_APPROVEDTOFILEONBEHALF = "ApprovedToFileOnBehalf";

}


/**
 * Lists of acceptable values for the enumerated data type AccrualType
 */
class AccrualType
{
    const C_FILING = "Filing";
    const C_ACCRUAL = "Accrual";

}


/**
 * Lists of acceptable values for the enumerated data type AdjustmentPeriodTypeId
 */
class AdjustmentPeriodTypeId
{
    const C_NONE = "None";
    const C_CURRENTPERIOD = "CurrentPeriod";
    const C_NEXTPERIOD = "NextPeriod";

}


/**
 * Lists of acceptable values for the enumerated data type AdjustmentTypeId
 */
class AdjustmentTypeId
{
    const C_OTHER = "Other";
    const C_CURRENTPERIODROUNDING = "CurrentPeriodRounding";
    const C_PRIORPERIODROUNDING = "PriorPeriodRounding";
    const C_CURRENTPERIODDISCOUNT = "CurrentPeriodDiscount";
    const C_PRIORPERIODDISCOUNT = "PriorPeriodDiscount";
    const C_CURRENTPERIODCOLLECTION = "CurrentPeriodCollection";
    const C_PRIORPERIODCOLLECTION = "PriorPeriodCollection";
    const C_PENALTY = "Penalty";
    const C_INTEREST = "Interest";
    const C_DISCOUNT = "Discount";
    const C_ROUNDING = "Rounding";
    const C_CSPFEE = "CspFee";

}


/**
 * Lists of acceptable values for the enumerated data type PaymentAccountTypeId
 */
class PaymentAccountTypeId
{
    const C_NONE = "None";
    const C_ACCOUNTSRECEIVABLEACCOUNTSPAYABLE = "AccountsReceivableAccountsPayable";
    const C_ACCOUNTSRECEIVABLE = "AccountsReceivable";
    const C_ACCOUNTSPAYABLE = "AccountsPayable";

}


/**
 * Lists of acceptable values for the enumerated data type NoticeCustomerType
 */
class NoticeCustomerType
{
    const C_AVATAXRETURNS = "AvaTaxReturns";
    const C_STANDALONE = "StandAlone";
    const C_STRATEGIC = "Strategic";
    const C_SST = "SST";
    const C_TRUSTFILE = "TrustFile";

}


/**
 * Lists of acceptable values for the enumerated data type FundingOption
 */
class FundingOption
{
    const C_PULL = "Pull";
    const C_WIRE = "Wire";

}


/**
 * Lists of acceptable values for the enumerated data type NoticePriorityId
 */
class NoticePriorityId
{
    const C_IMMEDIATEATTENTIONREQUIRED = "ImmediateAttentionRequired";
    const C_HIGH = "High";
    const C_NORMAL = "Normal";
    const C_LOW = "Low";

}


/**
 * Lists of acceptable values for the enumerated data type CommentType
 */
class CommentType
{
    const C_INTERNAL = "Internal";
    const C_CUSTOMER = "Customer";

}


/**
 * Lists of acceptable values for the enumerated data type DocumentStatus
 */
class DocumentStatus
{
    const C_TEMPORARY = "Temporary";
    const C_SAVED = "Saved";
    const C_POSTED = "Posted";
    const C_COMMITTED = "Committed";
    const C_CANCELLED = "Cancelled";
    const C_ADJUSTED = "Adjusted";
    const C_QUEUED = "Queued";
    const C_PENDINGAPPROVAL = "PendingApproval";
    const C_ANY = "Any";

}


/**
 * Lists of acceptable values for the enumerated data type TaxOverrideTypeId
 */
class TaxOverrideTypeId
{
    const C_NONE = "None";
    const C_TAXAMOUNT = "TaxAmount";
    const C_EXEMPTION = "Exemption";
    const C_TAXDATE = "TaxDate";
    const C_ACCRUEDTAXAMOUNT = "AccruedTaxAmount";
    const C_DERIVETAXABLE = "DeriveTaxable";

}


/**
 * Lists of acceptable values for the enumerated data type AdjustmentReason
 */
class AdjustmentReason
{
    const C_NOTADJUSTED = "NotAdjusted";
    const C_SOURCINGISSUE = "SourcingIssue";
    const C_RECONCILEDWITHGENERALLEDGER = "ReconciledWithGeneralLedger";
    const C_EXEMPTCERTAPPLIED = "ExemptCertApplied";
    const C_PRICEADJUSTED = "PriceAdjusted";
    const C_PRODUCTRETURNED = "ProductReturned";
    const C_PRODUCTEXCHANGED = "ProductExchanged";
    const C_BADDEBT = "BadDebt";
    const C_OTHER = "Other";
    const C_OFFLINE = "Offline";

}


/**
 * Lists of acceptable values for the enumerated data type TaxType
 */
class TaxType
{
    const C_LODGING = "Lodging";
    const C_BOTTLE = "Bottle";
    const C_CONSUMERUSE = "ConsumerUse";
    const C_EXCISE = "Excise";
    const C_FEE = "Fee";
    const C_INPUT = "Input";
    const C_NONRECOVERABLE = "Nonrecoverable";
    const C_OUTPUT = "Output";
    const C_RENTAL = "Rental";
    const C_SALES = "Sales";
    const C_USE = "Use";

}


/**
 * Lists of acceptable values for the enumerated data type ServiceMode
 */
class ServiceMode
{
    const C_AUTOMATIC = "Automatic";
    const C_LOCAL = "Local";
    const C_REMOTE = "Remote";

}


/**
 * Lists of acceptable values for the enumerated data type TaxDebugLevel
 */
class TaxDebugLevel
{
    const C_NORMAL = "Normal";
    const C_DIAGNOSTIC = "Diagnostic";

}


/**
 * Lists of acceptable values for the enumerated data type TaxOverrideType
 */
class TaxOverrideType
{
    const C_NONE = "None";
    const C_TAXAMOUNT = "TaxAmount";
    const C_EXEMPTION = "Exemption";
    const C_TAXDATE = "TaxDate";
    const C_ACCRUEDTAXAMOUNT = "AccruedTaxAmount";
    const C_DERIVETAXABLE = "DeriveTaxable";

}


/**
 * Lists of acceptable values for the enumerated data type VoidReasonCode
 */
class VoidReasonCode
{
    const C_UNSPECIFIED = "Unspecified";
    const C_POSTFAILED = "PostFailed";
    const C_DOCDELETED = "DocDeleted";
    const C_DOCVOIDED = "DocVoided";
    const C_ADJUSTMENTCANCELLED = "AdjustmentCancelled";

}


/**
 * Lists of acceptable values for the enumerated data type ApiCallStatus
 */
class ApiCallStatus
{
    const C_ORIGINALAPICALLAVAILABLE = "OriginalApiCallAvailable";
    const C_RECONSTRUCTEDAPICALLAVAILABLE = "ReconstructedApiCallAvailable";
    const C_ANY = "Any";

}


/**
 * Lists of acceptable values for the enumerated data type RefundType
 */
class RefundType
{
    const C_FULL = "Full";
    const C_PARTIAL = "Partial";
    const C_TAXONLY = "TaxOnly";
    const C_PERCENTAGE = "Percentage";

}


/**
 * Lists of acceptable values for the enumerated data type CompanyAccessLevel
 */
class CompanyAccessLevel
{
    const C_NONE = "None";
    const C_SINGLECOMPANY = "SingleCompany";
    const C_SINGLEACCOUNT = "SingleAccount";
    const C_ALLCOMPANIES = "AllCompanies";

}


/**
 * Lists of acceptable values for the enumerated data type AuthenticationTypeId
 */
class AuthenticationTypeId
{
    const C_NONE = "None";
    const C_USERNAMEPASSWORD = "UsernamePassword";
    const C_ACCOUNTIDLICENSEKEY = "AccountIdLicenseKey";
    const C_OPENIDBEARERTOKEN = "OpenIdBearerToken";

}


/*****************************************************************************
 *                              Transaction Builder                          *
 *****************************************************************************/

/**
 * TransactionBuilder helps you construct a new transaction using a literate interface
 */
class TransactionBuilder
{
    /**
     * The in-progress model
     */
    private $_model;

    /**
     * Keeps track of the line number when adding multiple lines
     */
    private $_line_number;
    
    /**
     * The client that will be used to create the transaction
     */
    private $_client;
        
    /**
     * TransactionBuilder helps you construct a new transaction using a literate interface
     *
     * @param AvaTaxClient  $client        The AvaTaxClient object to use to create this transaction
     * @param string        $companyCode   The code of the company for this transaction
     * @param DocumentType  $type          The type of transaction to create (See DocumentType::* for a list of allowable values)
     * @param string        $customerCode  The customer code for this transaction
     */
    public function __construct($client, $companyCode, $type, $customerCode)
    {
        $this->_client = $client;
        $this->_line_number = 1;
        $this->_model = [
            'companyCode' => $companyCode,
            'customerCode' => $customerCode,
            'date' => date('Y-m-d H:i:s'),
            'type' => $type,
            'lines' => [],
        ];
    }

    /**
     * Set the commit flag of the transaction.
     *
     * @return
     */
    public function withCommit()
    {
        $this->_model['commit'] = true;
        return $this;
    }

    /**
     * Enable diagnostic information
     *
     * @return  TransactionBuilder
     */
    public function withDiagnostics()
    {
        $this->_model['debugLevel'] = Constants::TAXDEBUGLEVEL_DIAGNOSTIC;
        return $this;
    }

    /**
     * Set a specific discount amount
     *
     * @param   float               $discount
     * @return  TransactionBuilder
     */
    public function withDiscountAmount($discount)
    {
        $this->_model['discount'] = $discount;
        return $this;
    }

    /**
     * Set if discount is applicable for the current line
     *
     * @param   boolean             discounted
     * @return  TransactionBuilder
     */
    public function withItemDiscount($discounted)
    {
        $l = GetMostRecentLine("WithItemDiscount");
        $l['discounted'] = $discounted;
        return $this;
    }

    /**
     * Set a specific transaction code
     *
     * @param   string              code
     * @return  TransactionBuilder
     */
    public function withTransactionCode($code)
    {
        $this->_model['code'] = $code;
        return $this;
    }

    /**
     * Set the document type
     *
     * @param   string              type    (See DocumentType::* for a list of allowable values)
     * @return  TransactionBuilder
     */
    public function withType($type)
    {
        $this->_model['type'] = $type;
        return $this;
    }

    /**
     * Add a parameter at the document level
     *
     * @param   string              name
     * @param   string              value
     * @return  TransactionBuilder
     */
    public function withParameter($name, $value)
    {
        if (empty($this->_model['parameters'])) $this->_model['parameters'] = [];
        $this->_model['parameters'][$name] = $value;
        return $this;
    }

    /**
     * Add a parameter to the current line
     *
     * @param   string              name
     * @param   string              value
     * @return  TransactionBuilder
     */
    public function withLineParameter($name, $value)
    {
        $l = GetMostRecentLine("WithLineParameter");
        if (empty($l['parameters'])) $l['parameters'] = [];
        $l[$name] = $value;
        return $this;
    }

    /**
     * Add an address to this transaction
     *
     * @param   string              type          Address Type (See AddressType::* for a list of allowable values)
     * @param   string              line1         The street address, attention line, or business name of the location.
     * @param   string              line2         The street address, business name, or apartment/unit number of the location.
     * @param   string              line3         The street address or apartment/unit number of the location.
     * @param   string              city          City of the location.
     * @param   string              region        State or Region of the location.
     * @param   string              postalCode    Postal/zip code of the location.
     * @param   string              country       The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        if (empty($this->_model['addresses'])) $this->_model['addresses'] = [];
        $ai = [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'city' => $city,
            'region' => $region,
            'postalCode' => $postalCode,
            'country' => $country
        ];
        $this->_model['addresses'][$type] = $ai;
        return $this;
    }

    /**
     * Add a lat/long coordinate to this transaction
     *
     * @param   string              $type       Address Type (See AddressType::* for a list of allowable values)
     * @param   float               $latitude   The latitude of the geolocation for this transaction
     * @param   float               $longitude  The longitude of the geolocation for this transaction
     * @return  TransactionBuilder
     */
     public function withLatLong($type, $latitude, $longitude)
    {
        $this->_model['addresses'][$type] = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        return $this;
    }

    /**
     * Add an address to this line
     *
     * @param   string              type        Address Type (See AddressType::* for a list of allowable values)
     * @param   string              line1       The street address, attention line, or business name of the location.
     * @param   string              line2       The street address, business name, or apartment/unit number of the location.
     * @param   string              line3       The street address or apartment/unit number of the location.
     * @param   string              city        City of the location.
     * @param   string              region      State or Region of the location.
     * @param   string              postalCode  Postal/zip code of the location.
     * @param   string              country     The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withLineAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $line = $this->GetMostRecentLine("WithLineAddress");
        $line['addresses'][$type] = [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'city' => $city,
            'region' => $region,
            'postalCode' => $postalCode,
            'country' => $country
        ];
        return $this;
    }

    /**
     * Add a document-level Tax Override to the transaction.
     *  - A TaxDate override requires a valid DateTime object to be passed.
     * TODO: Verify Tax Override constraints and add exceptions.
     *
     * @param   string              $type       Type of the Tax Override (See TaxOverrideType::* for a list of allowable values)
     * @param   string              $reason     Reason of the Tax Override.
     * @param   float               $taxAmount  Amount of tax to apply. Required for a TaxAmount Override.
     * @param   date                $taxDate    Date of a Tax Override. Required for a TaxDate Override.
     * @return  TransactionBuilder
     */
    public function withTaxOverride($type, $reason, $taxAmount, $taxDate)
    {
        $this->_model['taxOverride'] = [
            'type' => $type,
            'reason' => $reason,
            'taxAmount' => $taxAmount,
            'taxDate' => $taxDate
        ];

        // Continue building
        return $this;
    }

    /**
     * Add a line-level Tax Override to the current line.
     *  - A TaxDate override requires a valid DateTime object to be passed.
     * TODO: Verify Tax Override constraints and add exceptions.
     *
     * @param   string              $type        Type of the Tax Override (See TaxOverrideType::* for a list of allowable values)
     * @param   string              $reason      Reason of the Tax Override.
     * @param   float               $taxAmount   Amount of tax to apply. Required for a TaxAmount Override.
     * @param   date                $taxDate     Date of a Tax Override. Required for a TaxDate Override.
     * @return  TransactionBuilder
     */
    public function withLineTaxOverride($type, $reason, $taxAmount, $taxDate)
    {
        // Address the DateOverride constraint.
        if (($type == Constants::TAXOVERRIDETYPE_TAXDATE) && (empty($taxDate))) {
            throw new Exception("A valid date is required for a Tax Date Tax Override.");
        }

        $line = $this->GetMostRecentLine("WithLineTaxOverride");
        $line['taxOverride'] = [
            'type' => $type,
            'reason' => $reason,
            'taxAmount' => $taxAmount,
            'taxDate' => $taxDate
        ];

        // Continue building
        return $this;
    }

    /**
     * Add a line to this transaction
     *
     * @param   float               $amount      Value of the item.
     * @param   float               $quantity    Quantity of the item.
     * @param   string              $taxCode     Tax Code of the item. If left blank, the default item (P0000000) is assumed.
     * @return  TransactionBuilder
     */
    public function withLine($amount, $quantity, $taxCode)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => $quantity,
            'amount' => $amount,
            'taxCode' => $taxCode
        ];
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Add a line to this transaction
     *
     * @param   float               $amount      Value of the line
     * @param   string              $type        Address Type  (See AddressType::* for a list of allowable values)
     * @param   string              $line1       The street address, attention line, or business name of the location.
     * @param   string              $line2       The street address, business name, or apartment/unit number of the location.
     * @param   string              $line3       The street address or apartment/unit number of the location.
     * @param   string              $city        City of the location.
     * @param   string              $region      State or Region of the location.
     * @param   string              $postalCode  Postal/zip code of the location.
     * @param   string              $country     The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withSeparateAddressLine($amount, $type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => 1,
            'amount' => $amount,
            'addresses' => [
                $type => [
                    'line1' => $line1,
                    'line2' => $line2,
                    'line3' => $line3,
                    'city' => $city,
                    'region' => $region,
                    'postalCode' => $postalCode,
                    'country' => $country
                ]
            ]
        ];

        // Put this line in the model
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Add a line with an exemption to this transaction
     *
     * @param   float               $amount         The amount of this line item
     * @param   string              $exemptionCode  The exemption code for this line item
     * @return  TransactionBuilder
     */
    public function withExemptLine($amount, $exemptionCode)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => 1,
            'amount' => $amount,
            'exemptionCode' => $exemptionCode
        ];
        array_push($this->_model['lines'], $l); 
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Checks to see if the current model has a line.
     *
     * @return  TransactionBuilder
     */
    private function getMostRecentLine($memberName)
    {
        $c = count($this->_model['lines']);
        if ($c <= 0) {
            throw new Exception("No lines have been added. The $memberName method applies to the most recent line.  To use this function, first add a line.");
        }

        return $this->_model['lines'][$c-1];
    }

    /**
     * Create this transaction
     *
     * @return  TransactionModel
     */
    public function create()
    {
        return $this->_client->createTransaction(null, $this->_model);
    }

    /**
     * Create a transaction adjustment request that can be used with the AdjustTransaction() API call
     *
     * @return  AdjustTransactionModel
     */
    public function createAdjustmentRequest($desc, $reason)
    {
        return [
            'newTransaction' => $this->_model,
            'adjustmentDescription' => $desc,
            'adjustmentReason' => $reason
        ];
    }
}