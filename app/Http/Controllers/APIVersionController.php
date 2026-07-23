<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ReflectionMethod;

class APIVersionController extends Controller
{
    public function index($version)
    {
        // Queries the discovery engine for the specific version map
        $features = $this->getFeaturesMapFor($version);

        return response()->json([
            'api_version' => $version,
            'engine' => 'Celestia Nova Discovery Link',
            'features' => array_keys($features),
        ]);
    }

    public function handleDynamicMapping(Request $request, string $version, string $feature, ?string $action = null)
    {
        $featuresMap = $this->getFeaturesMapFor($version);

        $rootSegment = $feature;
        $subSegment = $action;
        $any = $feature.($action ? '/'.$action : '');

        if (! $rootSegment || ! array_key_exists($rootSegment, $featuresMap)) {
            return response()->json([
                'error' => "Feature gateway endpoint [{$rootSegment}] is not supported under version {$version}.",
            ], 404);
        }

        $targetControllerClass = $featuresMap[$rootSegment];
        $targetMethod = $this->resolveTargetMethod($request, $subSegment);

        if (! method_exists($targetControllerClass, $targetMethod)) {
            return response()->json([
                'error' => "Action [{$targetMethod}] not found on handler for /{$version}/{$any}",
            ], 404);
        }

        $accessCheck = $this->authorizeTargetMethod($request, $targetControllerClass, $targetMethod);
        if ($accessCheck !== null) {
            return $accessCheck;
        }

        $parameters = [];
        if ($subSegment && ($this->isIdentifier($subSegment) || is_numeric($subSegment))) {
            $parameters['id'] = $subSegment;
        }

        return app()->call([app($targetControllerClass), $targetMethod], $parameters);
    }

    private function resolveTargetMethod(Request $request, ?string $subSegment): string
    {
        if ($subSegment && ! is_numeric($subSegment) && ! $this->isIdentifier($subSegment)) {
            return Str::camel($subSegment);
        }

        return match ($request->method()) {
            'GET' => $subSegment ? 'show' : 'index',
            'POST' => 'store',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'destroy',
            default => 'index'
        };
    }

    private function isIdentifier(string $string): bool
    {
        return Str::isUuid($string) || (strlen($string) === 26 && ctype_alnum($string));
    }

    private function getFeaturesMapFor(string $version): array
    {
        // Hands off mapping responsibility safely to CapabilityDiscoveryController
        return CapabilityDiscoveryController::getFeaturesMapForVersion($version);
    }

    private function authorizeTargetMethod(Request $request, string $controllerClass, string $method): ?JsonResponse
    {
        $reflectionMethod = new ReflectionMethod($controllerClass, $method);
        $docComment = $reflectionMethod->getDocComment() ?: '';
        $payload = $request->attributes->get('bearer_payload', []);

        if (preg_match_all('/@required-role\s+([A-Za-z0-9_.-]+)/', $docComment, $roleMatches) > 0) {
            $roles = $payload['Roles'] ?? [];
            foreach ($roleMatches[1] as $requiredRole) {
                if (! in_array($requiredRole, $roles, true)) {
                    return response()->json([
                        'error' => 'Missing required role.',
                        'required_role' => $requiredRole,
                    ], 403);
                }
            }
        }

        if (preg_match_all('/@required-permission\s+([A-Za-z0-9_.-]+)/', $docComment, $permissionMatches) > 0) {
            $permissions = $payload['Permissions'] ?? [];
            foreach ($permissionMatches[1] as $requiredPermission) {
                if (! in_array($requiredPermission, $permissions, true)) {
                    return response()->json([
                        'error' => 'Missing required permission.',
                        'required_permission' => $requiredPermission,
                    ], 403);
                }
            }
        }

        return null;
    }
}
