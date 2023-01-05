<?php
namespace SiteSystemSynchronizer;

class WebhookRestAPI {
	
	private $request;
	private $namespace = 'webhook/v1';
	private $routes = [];
	
	public static function initialise(){
		$self = new self();
		
		#add_filter('rest_pre_dispatch', [$self, 'rest_pre_dispatch'], 10, 3);
		add_action('rest_api_init', [$self, 'rest_api_init']);
	}
	
	public function rest_pre_dispatch($response, $handler, $request){
		$this->request = $request;
	}
	
	public function rest_api_init(){

		$endpoints = array_diff(scandir(S3_ENDPOINTS_DIR), ['.', '..', 'BaseEndpoint.php']);

		if(!empty($endpoints)){
			foreach($endpoints as $endpoint){
				$class = '\\SiteSystemSynchronizer\\ApiEndpoints\\'.str_replace('.php', '', $endpoint);
				#$class = new $class_name();
				$this->routes[$class::$route] = $class::instance()->getParams();
			}
			#Helper::_log($this->routes);
		}

		#$this->routes['udb/data'] = UdbData::instance()->getParams();
		#$this->routes['wp/version'] = WpVersion::getParams();
		#$this->routes['beaver_builder'] = BeaverBuilder::getParams();

		$this->register_rest_routes();
	}

	private function register_rest_routes(){
		foreach($this->routes as $route => $args){
			register_rest_route($this->namespace, $route, $args);
		}
	}
	
	
}
