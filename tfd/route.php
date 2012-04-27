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
		 * @param string|function $filters Route filter or callback
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
		 * @param string|function $filter Filter to apply
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
			$method = strtolower($method);
			$filter = static::$filters['before'];
			$before = $filter();
			if (is_string($before)) return $before;
			foreach (static::$routes[$method] as $route) {
				if (preg_match('/^'.$route['match'].'$/', $request, $matches)) {
					if ($route['filter']) $route['filter']();
					return $route['callback']($matches);
				}
			}
			foreach (static::$routes['auto'] as $route) {
				if (preg_match('/^'.$route['match'].'(.+)/', $request, $matches)) {
					if ($route['filter']) $route['filter']();
					return $route['options'] + array(
						'view' => $matches[1],
						'dir' => $route['folder']
					);
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