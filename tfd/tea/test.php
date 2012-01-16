<?php namespace TFD\Tea;

	use TFD\Test as Tests;
	
	class Test{
	
		public static function __flags(){
			return array(
				'h' => 'help',
				'r' => 'run'
			);
		}
		
		public static function help(){
			echo <<<MAN
NAME
	Tea\Test

DESCRIPTION
	

USAGE
	tea test [command] [args]

COMMANDS
	-r run
		Run a test.
		Optional argument of test name.

SEE ALSO
	TFD: http://teafueleddoes.com/
	Tea: http://teafueleddoes.com/docs/tea/index.html
	Tea\Test: coming soon...

MAN;
			exit(0);
		}
		
		public static function run($args){
			$test = str_replace('/', '\\', $args[0]);
			$results = Tests::cli($test);
			self::print_results($results);
		}

		private static function print_results($results){
			$passes = $fails = $exceptions = $completed = $total = 0;
			$red = "\033[0;31m";
			$grn = "\033[0;32m";
			$bld_red = "\033[1;31m";
			$bld_grn = "\033[0;32m";
			$reset = "\033[0m";
			foreach($results as $name => $result){
				echo ucwords($name)."\n";
				echo str_pad("=", 70, "=")."\n";
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
					if(empty($exception)) $completed++;
					$total++;
					echo (!empty($failed) || !empty($exception)) ? $bld_red : $bld_grn;
					echo "{$test_name}{$reset}\n";
					foreach($test_results as $test){
						if($test['result'] == 'passed') $passes++;
						if($test['result'] == 'failed') $fails++;
						if($test['result'] == 'exception'){
							$test['test'] = 'Exception not handled';
							$exceptions++;
						}
						echo str_pad("    {$test['test']}", 61);
						if($test['result'] == 'passed'){
							echo "{$bld_grn}     PASS{$reset}\n";
						}else{
							$verb = ($test['result'] == 'failed') ? '     FAIL' : 'EXCEPTION';
							echo "{$bld_red}{$verb}{$reset}\n";
							echo "        {$test['message']}\n";
							echo "        at {$test['file']} line {$test['line']}\n";
						}
					}
					echo "\n";
				}
			}
			echo ($fails !== 0 || $exceptions !== 0) ? $red : $grn;
			echo str_pad("=", 70, "=")."{$reset}\n";
			echo sprintf("  %s/%s test cases complete.\n  %s passes, %s fails, %s exceptions.\n  Ran in %s seconds.\n", $completed, $total, $passes, $fails, $exceptions, \TFD\Benchmark::check('run_tests'));
			echo ($fails !== 0 || $exceptions !== 0) ? $red : $grn;
			echo str_pad("=", 70, "=")."{$reset}\n";
		}
	
	}