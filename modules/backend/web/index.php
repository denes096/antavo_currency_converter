<?php
// pull in classes
require_once '../autoloader.php';

const DEFAULT_ROUTE = 'index';

// parse url, query string to pretty url
$requestString = trim(
	str_replace(['?', '&', '='], ['/', '/', '/'], $_SERVER['REQUEST_URI'] . "&" . http_build_query($_POST)),
	'/'
);

// controller name
$controller = DEFAULT_ROUTE;
$action = DEFAULT_ROUTE;
if (!empty(trim($requestString, '/'))) {
	$urlParams = explode('/', $requestString);

	$action = array_shift($urlParams) ?? DEFAULT_ROUTE;
	$params = $urlParams;
} else {
	// todo no route response
}

// class controller name and action
$class = ucfirst(strtolower($controller)) . 'Controller';
$action = 'action' .  ucfirst(strtolower($action));

// fill up params: urlPrePart/paramName/paramValue/... -> paramName=paramValue&...
$dispatchParams = [];
while (!empty($params)) {
	$dispatchParams[array_shift($params)] = array_shift($params);
}

// check params
$controllerClass = new $class();
// check required params of function
$r = new ReflectionMethod($controllerClass, $action);
$functionParams = $r->getParameters();
$callFuncParams = [];
$missingParameters = [];
foreach ($functionParams as $param) {
	//$param is an instance of ReflectionParameter
	$paramName = $param->getName();

	if (isset($dispatchParams[$paramName])) {
		$callFuncParams[] = $dispatchParams[$paramName];
	} else if (!$param->isOptional()) {
		$missingParameters[] = $paramName;
	}
}

// return json
header('Content-type: application/json');

if (!empty($missingParameters)) {
	$result = [
		'message' => 'Missing parameters',
		'required' => $missingParameters
	];
} else {
	// call function
	$result = call_user_func_array([$controllerClass, $action], $callFuncParams);

	// make sure result is array
	if (!is_array($result)) {
		$result = [$result];
	}
}

echo json_encode($result);