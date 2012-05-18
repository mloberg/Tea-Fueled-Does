<?php namespace TFD;

	class Route {

		private static $routes = array(
			'get' => array(),
			'post' => array(),
			'put' => array(),
			'delete' => array(),
			'auto' => array()
		);
		private static $filters = array();

		/**
		 * Catch-all to add routes.
		 *
		 * @param string $route Route
		 * @param string|function|array $filters Route filter or callback
		 * @param function $callback Route callback
		 */

		public static function __callStatic($name, $args) {
			$route = array_shift($args);
			$callback = array_shift($args);
			if (!empty($args)) {
				$filter = $callback;
				$callback = array_shift($args);
				if (is_string($filter)) $filter = static::$filters[$filter];
			}
			static::$routes[$name][] = array('route' => $route, 'match' => static::escape($route), 'filter' => $filter ?: null, 'callback' => $callback);
		}

		/**
		 * Auto-route a folder
		 * 
		 * @param string $route Route to match
		 * @param string $folder Folder within content/views
		 * @param string|function|array $filter Filter to apply
		 * @param array $options Render options
		 */

		public static function auto($route, $folder, $filter = null, $options = array()) {
			if (is_string($filter)) $filter = static::$filters[$filter];
			 static::$routes['auto'][] = array('route' => $route, 'match' => static::escape($route), 'folder' => $folder, 'filter' => $filter, 'options' => $options);
		}

		/**
		 * Add a route filter.
		 *
		 * @param string $name Filter name
		 * @param function $callback Filter callback
		 */

		public static function filter($name, $callback) {
			static::$filters[$name] = $callback;
		}

		/**
		 * Get the route result for a request.
		 * 
		 * @param string $request Reqest
		 * @param string $method Request method
		 * @return mixed False if no route match, other callback return
		 */

		public static function run($request, $method = 'GET') {
			// "before" is a global filter. If it is a string, return that and don't run routes
			$filter = static::$filters['before'] ?: function() { return; };
			if (is_string($before = $filter($request, $method))) return $before;
			foreach (static::$routes[strtolower($method)] ?: array() as $route) {
				if (preg_match('/^'.$route['match'].'$/', $request, $matches)) {
					// before filter
					$filter = is_array($route['filter']) ? $route['filter']['before'] : $route['filter'];
					if (is_string($filter)) $filter = static::$filters[$filter];
					if ($filter && is_string($before = $filter($request, $method))) return $before;
					$route_result = $route['callback']($matches);
					// after filter
					$after = is_array($route['filter']) ? $route['filter']['after'] : null;
					if (!is_null($after)) {
						if (is_string($after)) $after = static::$filters[$after];
						if (is_string($after = $after($route_result, $request, $method))) return $after;
					}
					return $route_result;
				}
			}
			foreach (static::$routes['auto'] ?: array() as $route) {
				if (preg_match('/^'.$route['match'].'(.+)/', $request, $matches)) {
					$filter = is_array($route['filter']) ? $route['filter']['before'] : $route['filter'];
					if (is_string($filter)) $filter = static::$filters[$filter];
					if ($filter && is_string($before = $filter($request, $method))) return $before;
					$route_result = $route['options'] + array(
						'view' => $matches[1],
						'dir' => $route['folder']
					);
					$after = is_array($route['filter']) ? $route['filter']['after'] : null;
					if (!is_null($after)) {
						if (is_string($after)) $after = static::$filters[$after];
						if (is_string($after = $after($route_result, $request, $method))) return $after;
					}
					return $route_result;
				}
			}
			return false;
		}

		/**
		 * Escape a route string and replace wildcards
		 * 
		 * @param string $route Route to escape
		 * @return string Escaped route
		 */

		private static function escape($route) {
			return str_replace(array('/', ':any', ':num'), array('\/', '[\w\?\.\#\-\+\*\^\/]+', '\d+'), $route);
		}

	}
