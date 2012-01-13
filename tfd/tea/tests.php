<?php namespace TFD\Tea;

	use TFD\Test;
	
	class Tests{
	
		public static function __flags(){
			return array(
				'h' => 'help',
			);
		}
		
		public static function help(){
			echo <<<MAN
NAME
	Tea\Tests

DESCRIPTION
	

USAGE
	tea tests [command] [args]

COMMANDS
	

SEE ALSO
	TFD: http://teafueleddoes.com/
	Tea: http://teafueleddoes.com/docs/tea/index.html
	Tea\Test: coming soon...

MAN;
			exit(0);
		}
		
		public static function run($args){
			$test = $args[0];
			$results = Test::cli($test);
			self::print_results($results);
		}

		private static function print_results($results){
			$passes = $fails = $exceptions = $completed = $total = 0;
			foreach($results as $name => $result){
				echo ucwords($name)."\n";
				foreach($result as $test_name => $test_results){
					$passed = array_filter($test_results, function($test){
						return($test['result'] == 'passed');
					});
					$failed = array_filter($test_results, function($test){
						return($test['result'] == 'failed');
					});
					$exception = array_filter($test_results, function($test){
						return($test['result'] == 'exception');
					});
					$class = (!empty($failed) || !empty($exception)) ? 'failed' : 'passed';
					if(empty($exception)) $completed++;
					$total++;
					echo "\t{$test_name}\n";
					foreach($test_results as $test){
						if($test['result'] == 'passed') $passes++;
						if($test['result'] == 'failed') $failed++;
						if($test['result'] == 'exception') $exception++;
						if($test['result'] == 'passed' || $test['result'] == 'failed'){
							if($test['result'] == 'passed'){
								echo "\t\t\033[0;32mPASS";
							}else{
								echo "\t\t\033[0;31mFAIL";
							}
							echo "\033[0m: {$test['test']}";
							if($test['result'] == 'failed' && !is_null($test['message'])){
								echo "({$test['message']}) [at line {$test['line']}]";
							}
							echo "\n";
						}
					}
				}
			}
			echo sprintf("%s/%s test cases complete. %s passes, %s fails, %s exceptions. Ran in %s seconds.\n", $completed, $total, $passes, $fails, $exceptions, \TFD\Benchmark::check('run_tests'));
		}
	
	}