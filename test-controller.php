<?php
require_once 'ptd-validator.php';
use function jsonptd\verify;

function print_result($passed, $failed) {
	echo "\n\nResult: " . ($failed === 0 ? "PASSED" : "FAILED");
	echo "\nPassed: " . $passed;
	echo "\nFailed: " . $failed;
}

$dir = 'unit_tests/';
$tests = scandir($dir);

$passed = 0;
$failed = 0;

for ($i = 2; $i < count($tests); $i++) {
	$current_test = file_get_contents($dir . $tests[$i]);
	$current_test = json_decode($current_test, true);
	echo "\nTest #" . ($i-1) . ' - ' . $current_test['test_name'];
	
	for ($j = 0; $j < count($current_test['test_cases']); $j++) {
		echo "\n\tTest case #" . ($j + 1);
		$val = $current_test['test_cases'][$j]['value'];
		$type_name = array_keys($current_test['test_cases'][$j]['type'])[0];
		$type = $current_test['test_cases'][$j]['type'];
		$expected_result = $current_test['test_cases'][$j]['validation'];

		$result = verify($val, $type_name, $type);
		assert($result === $expected_result);
		if ($result === $expected_result) {
			echo "\n\tPassed!";
			$passed++;
		} else {
			echo "\n\tFailed!";
			$failed++;
		}
	}
}

print_result($passed, $failed);
?>