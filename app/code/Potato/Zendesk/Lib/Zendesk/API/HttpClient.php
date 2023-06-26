<?php

namespace Potato\Zendesk\Lib\Zendesk\API;

/*
 * Dead simple autoloader:
 * spl_autoload_register(function($c){@include 'src/'.preg_replace('#\\\|_(?!.+\\\)#','/',$c).'.php';});
 */

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Potato\Zendesk\Lib\Zendesk\API\Exceptions\AuthException;
use Potato\Zendesk\Lib\Zendesk\API\Middleware\RetryHandler;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Chat;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Activities;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\AppInstallations;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Apps;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Attachments;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\AuditLogs;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Autocomplete;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Automations;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Bookmarks;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Brands;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\CustomRoles;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\DynamicContent;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\GroupMemberships;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Groups;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Incremental;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\JobStatuses;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Locales;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Macros;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\OAuthClients;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\OAuthTokens;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\OrganizationFields;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\OrganizationMemberships;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Organizations;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\OrganizationSubscriptions;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\PushNotificationDevices;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Requests;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\SatisfactionRatings;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Search;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Sessions;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\SharingAgreements;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\SlaPolicies;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\SupportAddresses;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\SuspendedTickets;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Tags;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Targets;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\TicketFields;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\TicketImports;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Tickets;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Triggers;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\TwitterHandles;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\UserFields;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Users;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Core\Views;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Embeddable;
use Potato\Zendesk\Lib\Zendesk\API\Resources\HelpCenter;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Voice;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\InstantiatorTrait;
use Potato\Zendesk\Lib\Zendesk\API\Utilities\Auth;

/**
 * Client class, base level access
 *
 * @method Activities activities()
 * @method Apps apps()
 * @method AppInstallations appInstallations()
 * @method Attachments attachments()
 * @method AuditLogs auditLogs()
 * @method AutoComplete autocomplete()
 * @method Automations automations()
 * @method Bookmarks bookmarks()
 * @method Brands brands()
 * @method CustomRoles customRoles()
 * @method DynamicContent dynamicContent()
 * @method GroupMemberships groupMemberships()
 * @method Groups groups()
 * @method Incremental incremental()
 * @method JobStatuses jobStatuses()
 * @method Locales locales()
 * @method Macros macros()
 * @method OAuthClients oauthClients()
 * @method OAuthTokens oauthTokens()
 * @method OrganizationFields organizationFields()
 * @method OrganizationMemberships organizationMemberships()
 * @method Organizations organizations()
 * @method OrganizationSubscriptions organizationSubscriptions()
 * @method PushNotificationDevices pushNotificationDevices()
 * @method Requests requests()
 * @method SatisfactionRatings satisfactionRatings()
 * @method Search search()
 * @method Sessions sessions()
 * @method SharingAgreements sharingAgreements()
 * @method SlaPolicies slaPolicies()
 * @method SupportAddresses supportAddresses()
 * @method SuspendedTickets suspendedTickets()
 * @method Tags tags()
 * @method Targets targets()
 * @method Tickets tickets()
 * @method TicketFields ticketFields()
 * @method TicketImports ticketImports()
 * @method Triggers triggers()
 * @method TwitterHandles twitterHandles()
 * @method UserFields userFields()
 * @method Users users()
 * @method Views views()
 *
 */
class HttpClient
{
    const VERSION = '2.2.3';

    use InstantiatorTrait;

    /**
     * @var array $headers
     */
    private $headers = [];

    /**
     * @var Auth
     */
    protected $auth;
    /**
     * @var string
     */
    protected $subdomain;
    /**
     * @var string
     */
    protected $scheme;
    /**
     * @var string
     */
    protected $hostname;
    /**
     * @var integer
     */
    protected $port;
    /**
     * @var string
     */
    protected $apiUrl;
    /**
     * @var string This is appended between the full base domain and the resource endpoint
     */
    protected $apiBasePath;
    /**
     * @var array|null
     */
    protected $sideload;
    /**
     * @var Debug
     */
    protected $debug;

    /**
     * @var \GuzzleHttp\Client
     */
    public $guzzle;
    /**
     * @var HelpCenter
     */
    public $helpCenter;

    /**
     * @var Voice
     */
    public $voice;

    /**
     * @var Embeddable
     */
    public $embeddable;

    /**
     * @var Chat
     */
    public $chat;

    /**
     * @param string $subdomain
     * @param string $username
     * @param string $scheme
     * @param string $hostname
     * @param int $port
     * @param \GuzzleHttp\Client $guzzle
     */

    public function __construct(
        $subdomain,
        $username = '',
        $scheme = "https",
        $hostname = "zendesk.com",
        $port = 443,
        $guzzle = null
    ) {
        if (is_null($guzzle)) {
            $handler = HandlerStack::create();
            $handler->push(new RetryHandler(['retry_if' => function ($retries, $request, $response, $e) {
                return $e instanceof RequestException && strpos($e->getMessage(), 'ssl') !== false;
            }]), 'retry_handler');
            $this->guzzle = new \GuzzleHttp\Client(compact('handler'));
        } else {
            $this->guzzle = $guzzle;
        }

        $this->subdomain = $subdomain;
        $this->hostname  = $hostname;
        $this->scheme    = $scheme;
        $this->port      = $port;

        if (empty($subdomain)) {
            $this->apiUrl = "$scheme://$hostname:$port/";
        } else {
            $this->apiUrl = "$scheme://$subdomain.$hostname:$port/";
        }

        $this->debug      = new Debug();
        $this->helpCenter = new HelpCenter($this);
        $this->voice      = new Voice($this);
        $this->embeddable = new Embeddable($this);
        $this->chat       = new Chat($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getValidSubResources()
    {
        return [
            'apps'                      => Apps::class,
            'activities'                => Activities::class,
            'appInstallations'          => AppInstallations::class,
            'attachments'               => Attachments::class,
            'auditLogs'                 => AuditLogs::class,
            'autocomplete'              => Autocomplete::class,
            'automations'               => Automations::class,
            'bookmarks'                 => Bookmarks::class,
            'brands'                    => Brands::class,
            'customRoles'               => CustomRoles::class,
            'dynamicContent'            => DynamicContent::class,
            'groupMemberships'          => GroupMemberships::class,
            'groups'                    => Groups::class,
            'incremental'               => Incremental::class,
            'jobStatuses'               => JobStatuses::class,
            'locales'                   => Locales::class,
            'macros'                    => Macros::class,
            'oauthClients'              => OAuthClients::class,
            'oauthTokens'               => OAuthTokens::class,
            'organizationFields'        => OrganizationFields::class,
            'organizationMemberships'   => OrganizationMemberships::class,
            'organizations'             => Organizations::class,
            'organizationSubscriptions' => OrganizationSubscriptions::class,
            'pushNotificationDevices'   => PushNotificationDevices::class,
            'requests'                  => Requests::class,
            'satisfactionRatings'       => SatisfactionRatings::class,
            'sharingAgreements'         => SharingAgreements::class,
            'search'                    => Search::class,
            'slaPolicies'               => SlaPolicies::class,
            'sessions'                  => Sessions::class,
            'supportAddresses'          => SupportAddresses::class,
            'suspendedTickets'          => SuspendedTickets::class,
            'tags'                      => Tags::class,
            'targets'                   => Targets::class,
            'tickets'                   => Tickets::class,
            'ticketFields'              => TicketFields::class,
            'ticketImports'             => TicketImports::class,
            'triggers'                  => Triggers::class,
            'twitterHandles'            => TwitterHandles::class,
            'userFields'                => UserFields::class,
            'users'                     => Users::class,
            'views'                     => Views::class,
        ];
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Configure the authorization method
     *
     * @param       $strategy
     * @param array $options
     *
     * @throws AuthException
     */
    public function setAuth($strategy, array $options)
    {
        $this->auth = new Auth($strategy, $options);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $key The name of the header to set
     * @param string $value The value to set in the header
     * @return HttpClient
     * @internal param array $headers
     *
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Return the user agent string
     *
     * @return string
     */
    public function getUserAgent()
    {
        return 'ZendeskAPI PHP ' . self::VERSION;
    }

    /**
     * Returns the supplied subdomain
     *
     * @return string
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * Returns the generated api URL
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Sets the api base path
     *
     * @param string $apiBasePath
     */
    public function setApiBasePath($apiBasePath)
    {
        $this->apiBasePath = $apiBasePath;
    }

    /**
     * Returns the api base path
     *
     * @return string
     */
    public function getApiBasePath()
    {
        return $this->apiBasePath;
    }

    /**
     * Set debug information as an object
     *
     * @param mixed  $lastRequestHeaders
     * @param mixed  $lastRequestBody
     * @param mixed  $lastResponseCode
     * @param string $lastResponseHeaders
     * @param mixed  $lastResponseError
     */
    public function setDebug(
        $lastRequestHeaders,
        $lastRequestBody,
        $lastResponseCode,
        $lastResponseHeaders,
        $lastResponseError
    ) {
        $this->debug->lastRequestHeaders  = $lastRequestHeaders;
        $this->debug->lastRequestBody     = $lastRequestBody;
        $this->debug->lastResponseCode    = $lastResponseCode;
        $this->debug->lastResponseHeaders = $lastResponseHeaders;
        $this->debug->lastResponseError   = $lastResponseError;
    }

    /**
     * Returns debug information in an object
     *
     * @return Debug
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Sideload setter
     *
     * @param array|null $fields
     *
     * @return HttpClient
     */
    public function setSideload(array $fields = null)
    {
        $this->sideload = $fields;

        return $this;
    }

    /**
     * Sideload getter
     *
     * @param array|null $params
     *
     * @return array|null
     */
    public function getSideload(array $params = [])
    {
        // Allow both for backward compatibility
        $sideloadKeys = array('include', 'sideload');

        if (! empty($sideloads = array_intersect_key($params, array_flip($sideloadKeys)))) {
            // Merge to a single array
            return call_user_func_array('array_merge', $sideloads);
        } else {
            return $this->sideload;
        }
    }

    /**
     * This is a helper method to do a get request.
     *
     * @param       $endpoint
     * @param array $queryParams
     *
     * @return \stdClass | null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\AuthException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\ApiResponseException
     */
    public function get($endpoint, $queryParams = [])
    {
        $sideloads = $this->getSideload($queryParams);

        if (is_array($sideloads)) {
            $queryParams['include'] = implode(',', $sideloads);
            unset($queryParams['sideload']);
        }

        $response = Http::send(
            $this,
            $endpoint,
            ['queryParams' => $queryParams]
        );

        return $response;
    }

    /**
     * This is a helper method to do a post request.
     *
     * @param       $endpoint
     * @param array $postData
     *
     * @param array $options
     * @return null|\stdClass
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\AuthException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\ApiResponseException
     */
    public function post($endpoint, $postData = [], $options = [])
    {
        $extraOptions = array_merge($options, [
            'postFields' => $postData,
            'method' => 'POST'
        ]);

        $response = Http::send(
            $this,
            $endpoint,
            $extraOptions
        );

        return $response;
    }

    /**
     * This is a helper method to do a put request.
     *
     * @param       $endpoint
     * @param array $putData
     *
     * @return \stdClass | null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\AuthException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\ApiResponseException
     */
    public function put($endpoint, $putData = [])
    {
        $response = Http::send(
            $this,
            $endpoint,
            ['postFields' => $putData, 'method' => 'PUT']
        );

        return $response;
    }

    /**
     * This is a helper method to do a delete request.
     *
     * @param $endpoint
     *
     * @return null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\AuthException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\ApiResponseException
     */
    public function delete($endpoint)
    {
        $response = Http::send(
            $this,
            $endpoint,
            ['method' => 'DELETE']
        );

        return $response;
    }
}
