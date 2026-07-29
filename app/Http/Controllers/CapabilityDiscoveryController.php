<?php

namespace App\Http\Controllers;

use App\Http\Controllers\v1\AccountController;
use App\Http\Controllers\v1\ApprovalEntityController;
use App\Http\Controllers\v1\AttachableDataController;
use App\Http\Controllers\v1\AuthenticationController;
use App\Http\Controllers\v1\AuthenticationProviderController;
use App\Http\Controllers\v1\AuthorisationController;
use App\Http\Controllers\v1\BanController;
use App\Http\Controllers\v1\OAuthController;
use App\Http\Controllers\v1\ProfileController;
use App\Http\Controllers\v1\ShardController;
use App\Http\Controllers\v1\CelestiaInstanceController;

/**
 * @todo Provide GraphQL interaction
 */
class CapabilityDiscoveryController extends Controller
{
    /**
     * Optional HTTP endpoint allowing clients to browse the raw Celestia Nova registry map.
     */
    public function index()
    {
        return response()->json($this->getRawStaticCapabilities());
    }

    /**
     * Public static helper for APIVersionController to find root handlers.
     */
    public static function getFeaturesMapForVersion(string $version): array
    {
        $capabilities = new self()->getRawStaticCapabilities();

        if (! isset($capabilities[$version])) {
            return [];
        }

        // Returns an array structured as: ['account' => 'App\Http\Controllers\v1\AccountController']
        return collect($capabilities[$version])->map(function ($featureConfig) {
            return $featureConfig['handler'];
        })->toArray();
    }

    /**
     * Public static helper for Feature Controllers to query their own capability lists.
     */
    public static function getCapabilitiesForFeature(string $version, string $featureKey): array
    {
        $capabilities = new self()->getRawStaticCapabilities();

        return $capabilities[$version][$featureKey]['functions'] ?? [];
    }

    /**
     * The central static registry placeholder that Celestia Nova will dynamically inject into later.
     *
     * @TODO: Deny if HTTP method mismatches (using upcoming middleware that uses the ErrorHandlerTrait)
     *
     * @TODO: Add paging
     *
     * @TODO: Middleware for token validation stuff
     */
    private function getRawStaticCapabilities(): array
    {
        return [
            'v1' => [
                'authentication' => [
                    'handler' => AuthenticationController::class,
                    'functions' => [
                        // Authentication
                        'index' => ['description' => 'Lists the types of resources that can use Authentication (No-Auth)'], // Applications(Applications.Bot, Application.App), Account, Impersonated-Account, Profile

                        'login' => ['description' => 'Issue structural session bearer token credentials', 'method' => 'POST'],
                        'logout' => ['description' => 'Revoke structural session bearer token credentials', 'method' => 'POST'],
                        'refresh' => ['description' => 'Refresh structural session bearer token credentials', 'method' => 'POST'],
                        'validate' => ['description' => 'Validate structural session bearer token credentials', 'method' => 'POST'],
                        'revoke' => ['description' => 'Revoke any structural session bearer token credentials (Uses revoke-issued-tokens)', 'method' => 'DELETE'],

                        'introspect' => ['description' => 'Introspect structural session bearer token credentials', 'method' => 'POST'],
                        'exchange' => ['description' => 'Exchange structural session bearer token credentials for other credentials', 'method' => 'POST'],

                        'list-impersonations' => ['description' => 'List active impersonations issued by the authenticatable identity', 'method' => 'GET'],
                        'impersonate' => ['description' => 'Issue structural session bearer token credentials for a target authenticatable identity', 'method' => 'POST'],
                        'revoke-impersonation' => ['description' => 'Revoke an impersonation issued by the authenticatable identity (Uses revoke-issued-tokens)', 'method' => 'DELETE'],

                        'list-delegation' => ['description' => 'List active delegations issued by the authenticatable identity', 'method' => 'GET'],
                        'delegate' => ['description' => 'Issue structural session bearer token credentials with a subset of the permissions of the original credentials for a target authenticatable identity', 'method' => 'POST'],
                        'revoke-delegation' => ['description' => 'Revoke a delegation issued by the authenticatable identity (Uses revoke-issued-tokens)', 'method' => 'DELETE'],

                        // AuthenticatableUpdateLogs
                        'lookup-update-logs' => ['description' => 'Retrieve a target authenticatable identity update logs (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-update-log' => ['description' => 'Create an authenticatable identity update log entry', 'method' => 'POST'],
                        'update-update-log' => ['description' => 'Update an authenticatable identity update log entry', 'method' => 'PUT'],
                        'delete-update-log' => ['description' => 'Delete an authenticatable identity update log entry', 'method' => 'DELETE'],

                        // IssuedTokens
                        'lookup-issued-tokens' => ['description' => 'Retrieve a target authenticatable identity issued tokens (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'revoke-issued-token' => ['description' => 'Revoke an issued token of the authenticatable identity', 'method' => 'DELETE'],
                        'revoke-all-issued-tokens' => ['description' => 'Revoke all issued tokens of the authenticatable identity', 'method' => 'DELETE'],
                    ],
                ],
                'oauth' => [
                    'handler' => OAuthController::class,
                    'functions' => [
                        'index' => ['description' => 'Lists OAuth-compatible application authentication capabilities (No-Auth)', 'method' => 'GET'],
                        'token' => ['description' => 'Issue a custom PS512 bearer token for a registered OAuth application using client credentials', 'method' => 'POST'],
                        'introspect' => ['description' => 'Inspect a token for a registered OAuth application using client authentication', 'method' => 'POST'],
                        'provision-application' => ['description' => 'KeyForge-only, idempotent confidential OAuth application provisioning', 'method' => 'POST'],
                        'device-authorize' => ['description' => 'Create a Nova ID device pairing and return a user verification link for a registered OAuth application', 'method' => 'POST'],
                        'device-approve' => ['description' => 'Approve a pending Nova ID device pairing using an authenticated user bearer token', 'method' => 'POST'],
                        'device-token' => ['description' => 'Poll a one-time Nova ID device pairing code for a scoped bearer token', 'method' => 'POST'],
                    ],
                ],
                'celestia-instances' => [
                    'handler' => CelestiaInstanceController::class,
                    'functions' => [
                        'index' => ['description' => 'List active, OAuth-authenticated Celestia mesh instances.', 'method' => 'GET'],
                        'heartbeat' => ['description' => 'Publish an expiring authenticated Celestia instance heartbeat.', 'method' => 'POST'],
                        'election' => ['description' => 'Return active membership and deterministic primary-election policy.', 'method' => 'GET'],
                        'update-manifest' => ['description' => 'Return the static signed-update manifest for Celestia daemon channels.', 'method' => 'GET'],
                    ],
                ],
                'accounts' => [
                    'handler' => AccountController::class,
                    'functions' => [
                        // Account
                        'index' => ['description' => 'Retrieve all accounts bound to authenticatable context', 'method' => 'GET'],

                        'lookup' => ['description' => 'Locate a target account identity (Minimal)', 'method' => 'GET'],
                        'show' => ['description' => 'Fetch isolated account entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create' => ['description' => 'Create a target account identity (Requires Authenticatable-Handle)', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of an account entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete an account entry', 'method' => 'DELETE'],

                        // AccountNotes
                        'lookup-note' => ['description' => 'Locate a target account note identity (Minimal)', 'method' => 'GET'],
                        'list-notes' => ['description' => 'Fetch isolated account note entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-note' => ['description' => 'Create a target account note identity', 'method' => 'POST'],
                        'update-note' => ['description' => 'Update attributes of an account note entry', 'method' => 'PUT'],
                        'delete-note' => ['description' => 'Delete an account note entry', 'method' => 'DELETE'],
                    ],
                ],
                'authentication-providers' => [
                    'handler' => AuthenticationProviderController::class,
                    'functions' => [
                        // AuthenticationProvider & AuthenticationProviderParameters
                        'index' => ['description' => 'Retrieve all authentication providers in the system (No-Auth)', 'method' => 'GET'],
                        'lookup' => ['description' => 'Locate a target authentication provider identity (No-Auth, Minimal)', 'method' => 'GET'],
                        'list' => ['description' => 'Fetch isolated authentication provider entity attributes (No-Auth, Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],

                        'call' => ['description' => 'Call an authentication provider specific function (Parameters depend on the provider, but generally used for things like sending Password, 2FA codes or similar)', 'method' => 'POST'],
                        'create' => ['description' => 'Provision a new authentication provider entry', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of an authentication provider entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete an authentication provider entry', 'method' => 'DELETE'],

                        // AttachedAuthenticationProviders
                        'index-attached' => ['description' => 'Retrieve all authentication providers attached to the authenticatable context (No-Auth)', 'method' => 'GET'],
                        'attach' => ['description' => 'Attach an authentication provider to the authenticatable context', 'method' => 'POST'],
                        'update-attachment' => ['description' => 'Update the attachment of an authentication provider to the authenticatable context', 'method' => 'PUT'],
                        'detach' => ['description' => 'Detach an authentication provider from the authenticatable context', 'method' => 'DELETE'],

                        // AttachedAuthenticationProviderScriptsBlacklist
                        'index-blacklist' => ['description' => 'Retrieve all blacklisted scripts for attached authentication providers (No-Auth)', 'method' => 'GET'],
                        'blacklist' => ['description' => 'Blacklist a script for attached authentication providers', 'method' => 'POST'],
                        'unblacklist' => ['description' => 'Remove a script from the blacklist for attached authentication providers', 'method' => 'DELETE'],
                    ],
                ],
                'approval-entities' => [
                    'handler' => ApprovalEntityController::class,
                    'functions' => [
                        // ApprovalEntity & ApprovalEntityParameters
                        'index' => ['description' => 'Retrieve all approval entities in the system (No-Auth)', 'method' => 'GET'],
                        'lookup' => ['description' => 'Locate a target approval entity identity (Minimal)', 'method' => 'GET'],
                        'list' => ['description' => 'Fetch isolated approval entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create' => ['description' => 'Provision a new approval entity entry', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of an approval entity entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete an approval entity entry', 'method' => 'DELETE'],

                        // ApprovalEntityData
                        'list-data' => ['description' => 'Fetch isolated approval entity data attributes on an Authenticatable (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-data' => ['description' => 'Provision a new approval entity data entry on an Authenticatable ', 'method' => 'POST'],
                        'update-data' => ['description' => 'Update attributes of an approval entity data entry on an Authenticatable ', 'method' => 'PUT'],
                        'delete-data' => ['description' => 'Delete an approval entity data entry on an Authenticatable ', 'method' => 'DELETE'],
                    ],
                ],
                'attachable-data' => [
                    'handler' => AttachableDataController::class,
                    'functions' => [
                        // AttachableData & AttachableDataStates
                        'index' => ['description' => 'Retrieve all possible to attach data types with their states (No-Auth)', 'method' => 'GET'],

                        'create-type' => ['description' => 'Create a new attachable data type', 'method' => 'POST'],
                        'update-type' => ['description' => 'Update an attachable data type', 'method' => 'PUT'],
                        'delete-type' => ['description' => 'Delete an attachable data type', 'method' => 'DELETE'],

                        // AttachedData & TemporarilyAttachedData
                        'lookup' => ['description' => 'Retrieve a target resource identity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'attach' => ['description' => 'Attach a data entry to an resource identity', 'method' => 'POST'],
                        'update' => ['description' => 'Update a data entry attached to an resource identity', 'method' => 'PUT'],
                        'detach' => ['description' => 'Detach a data entry from an resource identity', 'method' => 'DELETE'],

                        'lookup-temporary' => ['description' => 'Retrieve a target resource identity attributes for temporarily attached data (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'attach-temporary' => ['description' => 'Temporarily attach a data entry to an resource identity', 'method' => 'POST'],
                        'update-temporary' => ['description' => 'Update a temporarily attached data entry attached to an resource identity', 'method' => 'PUT'],
                        'detach-temporary' => ['description' => 'Detach a temporarily attached data entry from an resource identity', 'method' => 'DELETE'],
                        'create-data' => ['description' => 'Compatibility alias for attaching data to a resource identity', 'method' => 'POST'],
                        'update-data' => ['description' => 'Compatibility alias for updating temporarily attached data', 'method' => 'PUT'],
                        'delete-data' => ['description' => 'Compatibility alias for detaching data from a resource identity', 'method' => 'DELETE'],
                    ],
                ],
                'profiles' => [
                    'handler' => ProfileController::class,
                    'functions' => [
                        // Profiles
                        'index' => ['description' => 'Retrieve all profiles bound to account context (No-Auth)', 'method' => 'GET'],

                        'lookup' => ['description' => 'Locate a target profile identity. (Minimal)', 'method' => 'GET'],
                        'list' => ['description' => 'Fetch isolated profile entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create' => ['description' => 'Provision a clean structural profile identity entry', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of a profile identity entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete a profile identity entry', 'method' => 'DELETE'],

                        // ReservedProfileIdentifiers & RejectedRequestForProfileIdentifier
                        'lookup-reserved' => ['description' => 'Retrieve all reserved profile identifiers (Auth or No-Auth)', 'method' => 'GET'],
                        'reserve' => ['description' => 'Reserve a profile', 'method' => 'POST'],
                        'unreserve' => ['description' => 'Unreserve a profile', 'method' => 'DELETE'],

                        'lookup-rejected' => ['description' => 'Retrieve all rejected profile identifier reservation requests (Auth or No-Auth)', 'method' => 'GET'],
                        'reject' => ['description' => 'Reject a profile identifier reservation request', 'method' => 'POST'],
                        'unreject' => ['description' => 'Unreject a profile identifier reservation request', 'method' => 'DELETE'],
                    ],
                ],
                'authorisation' => [
                    'handler' => AuthorisationController::class,
                    'functions' => [
                        // Role & RoleIgnoredErrors & RequiredRoleForParameter
                        'index' => ['description' => 'Retrieve all roles in the system (No-Auth)', 'method' => 'GET'],
                        'lookup' => ['description' => 'Locate a target role identity (No-Auth, Minimal)', 'method' => 'GET'],
                        'list' => ['description' => 'Fetch isolated role entity attributes (No-Auth, Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create' => ['description' => 'Provision a new role entry', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of a role entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete a role entry', 'method' => 'DELETE'],

                        // Scope
                        'index-scope' => ['description' => 'Retrieve all scopes in the system (No-Auth)', 'method' => 'GET'],
                        'lookup-scope' => ['description' => 'Locate a target scope identity (No-Auth, Minimal)', 'method' => 'GET'],
                        'list-scope' => ['description' => 'Fetch isolated scope entity attributes (No-Auth, Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-scope' => ['description' => 'Provision a new scope entry', 'method' => 'POST'],
                        'update-scope' => ['description' => 'Update attributes of a scope entry', 'method' => 'PUT'],
                        'delete-scope' => ['description' => 'Delete a scope entry', 'method' => 'DELETE'],

                        // Permission & PermissionCollection
                        'index-permission' => ['description' => 'Retrieve all permissions in the system (No-Auth)', 'method' => 'GET'],
                        'lookup-permission' => ['description' => 'Locate a target permission identity (Minimal, No-Auth)', 'method' => 'GET'],
                        'list-permission' => ['description' => 'Fetch isolated permission entity attributes (No-Auth, Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-permission' => ['description' => 'Provision a new permission entry', 'method' => 'POST'],
                        'update-permission' => ['description' => 'Update attributes of a permission entry', 'method' => 'PUT'],
                        'delete-permission' => ['description' => 'Delete a permission entry', 'method' => 'DELETE'],

                    ],
                ],
                'shards' => [
                    'handler' => ShardController::class,
                    'functions' => [
                        'index' => ['description' => 'Retrieve all shards in the system (No-Auth)', 'method' => 'GET'],

                        'lookup' => ['description' => 'Locate a target shard identity (Minimal)', 'method' => 'GET'],
                        'list' => ['description' => 'Fetch isolated shard entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create' => ['description' => 'Provision a new shard entry', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of a shard entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete a shard entry', 'method' => 'DELETE'],
                    ],
                ],
                'bans' => [
                    'handler' => BanController::class,
                    'functions' => [
                        // BannedAuthenticatable (Ban & BannedIn)
                        'index' => ['description' => 'Retrieve all bans bound to authenticatable context (No-Auth)', 'method' => 'GET'],

                        'lookup' => ['description' => 'Locate a target ban identity (Minimal)', 'method' => 'GET'],
                        'list' => ['description' => 'Fetch isolated ban entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create' => ['description' => 'Provision a new ban entry', 'method' => 'POST'],
                        'update' => ['description' => 'Update attributes of a ban entry', 'method' => 'PUT'],
                        'delete' => ['description' => 'Delete a ban entry', 'method' => 'DELETE'],

                        // BannedAuthenticatableAppeal
                        'index-appeals' => ['description' => 'Retrieve all ban appeals bound to authenticatable context', 'method' => 'GET'],

                        'lookup-appeal' => ['description' => 'Locate a target ban appeal identity (Minimal)', 'method' => 'GET'],
                        'list-appeal' => ['description' => 'Fetch isolated ban appeal entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-appeal' => ['description' => 'Provision a new ban appeal entry', 'method' => 'POST'],
                        'update-appeal' => ['description' => 'Update attributes of a ban appeal entry', 'method' => 'PUT'],
                        'delete-appeal' => ['description' => 'Delete a ban appeal entry', 'method' => 'DELETE'],

                        // BannedAuthenticatableEvidences
                        'index-evidences' => ['description' => 'Retrieve all ban evidences bound to a ban entry', 'method' => 'GET'],

                        'lookup-evidence' => ['description' => 'Locate a target ban evidence identity (Minimal)', 'method' => 'GET'],
                        'list-evidence' => ['description' => 'Fetch isolated ban evidence entity attributes (Default: All, Scoped: Only Requested Parameters)', 'method' => 'GET'],
                        'create-evidence' => ['description' => 'Provision a new ban evidence entry', 'method' => 'POST'],
                        'update-evidence' => ['description' => 'Update attributes of a ban evidence entry', 'method' => 'PUT'],
                        'delete-evidence' => ['description' => 'Delete a ban evidence entry', 'method' => 'DELETE'],
                    ],
                ],
            ],
        ];
    }
}
