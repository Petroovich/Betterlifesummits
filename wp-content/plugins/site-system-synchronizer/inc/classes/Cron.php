<?php

namespace SiteSystemSynchronizer;


class Cron{
	
	public static $options;
	private static $cron_task_name = "s3_data_updater";
	
	public static function initialise(){
		$self = new self();
		
		#add_action('init', [$self, 'init'], 0);
		#add_action(self::$cron_task_name, [$self, 's3_cron_task_exec']);
		#add_filter('cron_schedules', [$self, 's3_cron_add_schedule']);
	}
	
	public function init(){
		self::$options = Core::get_options();
		self::run();
	}
	
	function s3_cron_add_schedule(){
		$schedules['s3_cron_interval'] = ['interval' => (intval(self::$options['cron_interval']) * 60), 'display' => 'Every '.self::$options['cron_interval'].' minute(s)'];
		
		return $schedules;
	}
	
	public static function run(){
		#Helper::_log(__METHOD__);
		
		if(wp_next_scheduled(self::$cron_task_name) === false){
			#Helper::_log('wp_next_scheduled = false');
			wp_schedule_event(time() + self::$options['cron_interval'] * 60, 's3_cron_interval', self::$cron_task_name);
		}else{
			/*$timestamp = wp_next_scheduled(self::$cron_task_name);
			if($timestamp > time()+self::$options['cron_interval']*60){
				Helper::log('[function '.__FUNCTION__.'], schedule restart, timestamp = '.$timestamp);
				self::stop();
				//self::run();
			}*/
		}
	}
	
	public static function stop(){
		#Helper::_log(__METHOD__);

		$timestamp = wp_next_scheduled(self::$cron_task_name);
		wp_unschedule_event($timestamp, self::$cron_task_name);
		wp_clear_scheduled_hook(self::$cron_task_name);
	}
	
	public function s3_cron_task_exec(){
		#Helper::_log(__METHOD__);
		Helper::_log('------------------- START CRON -------------------');
		
		/*$lead_task = new LeadTask('InsyteLeadJob');
		$result = $lead_task->exec();
		Helper::_log('task result:');
		Helper::_log($result);*/
		
		Helper::_log('------------------- END CRON -------------------');
	}
	
}
