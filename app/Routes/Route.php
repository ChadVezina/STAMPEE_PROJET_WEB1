<?php

declare(strict_types=1);

namespace App\Routes;

use App\Core\Config;

class Route
{
    /** @var array<int, array{url: string, controller: callable|array, method: string}> */
    private static array $routes = [];

    /**
     * Enregistrer une route GET.
     * @param string $url Chemin URL (ex: "/login")
     * @param callable|array{0: object|string, 1: string} $controller Action à exécuter
     */
    public static function get(string $url, callable|array $controller): void
    {
        self::$routes[] = ['url' => $url, 'controller' => $controller, 'method' => 'GET'];
    }

    /**
     * Enregistrer une route POST.
     */
    public static function post(string $url, callable|array $controller): void
    {
        self::$routes[] = ['url' => $url, 'controller' => $controller, 'method' => 'POST'];
    }

    /**
     * Démarrer le dispatching des routes enregistrées.
     */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Calcul du chemin de base (sans "/public")
        $scriptDir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $configuredBase = (string) Config::get('app.base_path', '');
        $basePath    = $configuredBase !== '' ? $configuredBase : rtrim(preg_replace('#/public$#', '', $scriptDir), '/');

        // Normalisation du chemin de requête
        $path = $requestUri;
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }
        $path = preg_replace('#^/public(?=/|$)#', '', $path) ?: '/';
        $path = '/' . ltrim(rtrim($path, '/'), '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $routePath = '/' . ltrim(rtrim($route['url'], '/'), '/');
            if ($routePath !== $path) {
                continue;
            }

            $controller = $route['controller'];
            if (is_array($controller) && isset($controller[0], $controller[1])) {
                // Contrôleur au format [ClassName::class, 'method']
                $instance = is_string($controller[0]) ? new $controller[0]() : $controller[0];
                $action   = $controller[1];
                $args     = self::buildArgs($instance, $action, $method);
                $instance->$action(...$args);
                return;
            }
            if (is_callable($controller)) {
                // Contrôleur sous forme de closure ou fonction
                $controller();
                return;
            }
        }

        // Aucune route trouvée => 404
        http_response_code(404);
        echo '404 - Page non trouvée';
    }

    /**
     * Prépare les arguments à injecter dans la méthode du contrôleur en fonction de sa signature.
     */
    private static function buildArgs(object $instance, string $method, string $httpMethod): array
    {
        try {
            $refMethod = new \ReflectionMethod($instance, $method);
            $required = $refMethod->getNumberOfRequiredParameters();
            $total    = $refMethod->getNumberOfParameters();
        } catch (\ReflectionException $e) {
            // Méthode non trouvée, on ne passe pas d'arguments
            return [];
        }

        if ($httpMethod === 'GET') {
            if ($required === 0) {
                return [];
            }
            // Si la méthode attend au moins 1 param, on lui passe $_GET
            return [$_GET];
        } else { // POST
            if ($required === 0) {
                return [];
            }
            if ($required === 1) {
                return [$_POST];
            }
            if ($required === 2) {
                return [$_POST, $_GET];
            }
            // Cas de signature inhabituelle (plus de 2 params requis)
            return [];
        }
    }
}
