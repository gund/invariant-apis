<?php
spl_autoload_extensions(".php");
spl_autoload_register();

use GeoNames\Client;

// Get all available methods dynamically
$clientMethods = array();
foreach (Client::getMethods() as $method) {
    $clientMethods[$method->getName()] = array(
        'return' => Client::getParameterType($method),
        'params' => array()
    );
    // Get all method parameters
    foreach ($method->getParameters() as $parameter) {
        $clientMethods[$method->getName()]['params'][$parameter->getName()] = array(
            'type' => Client::getParameterType($method, 'param', $parameter->getName()),
            'optional' => $parameter->isOptional()
        );
    }
}
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Geo Names API</title>
    <style type="text/css">
        body {
            text-align: center;
        }
    </style>
</head>
<body>

<h1>Geo Names API Demo</h1>

<form name="geo-names-form" method="post" action="GeoNamesLayer.php">
    <label>
        <strong>Method to call:</strong>
        <br>
        <select name="method"></select>
    </label>

    <div id="params">
        <br>Please fill method parameters:
    </div>

    <br>
    <button type="submit">Call</button>
</form>

<script type="text/javascript">
    (function () {
        "use strict";
        var clientMethods = <?php echo json_encode($clientMethods) ?>;
        var select = document.forms['geo-names-form'].method;
        var paramsDiv = document.getElementById('params');

        // Render all methods
        for (var i in clientMethods) {
            if (!clientMethods.hasOwnProperty(i)) continue;
            var method = clientMethods[i];

            // Add div for params
            var paramDiv = document.createElement('div');
            paramDiv.setAttribute('id', i);
            paramDiv.style.display = 'none';

            // Grab all parameters of method
            var params = [];
            for (var j in method['params']) {
                if (!method['params'].hasOwnProperty(j)) continue;
                var param = method['params'][j];
                // Add param for option
                params.push((param.optional ? '[' : '') + param.type + ' ' + j + (param.optional ? ']' : ''));
                // Add param for input
                var paramInput = document.createElement('input');
                paramInput.setAttribute('type', param.type == 'string' ? 'text' : 'number');
                if (param.type != 'int') paramInput.setAttribute('step', 'any');
                paramInput.setAttribute('placeholder', j + (param.optional ? '(optional)' : ''));
                paramInput.setAttribute('name', 'param[' + j + ']');
                paramDiv.appendChild(paramInput);
            }

            // Add option to select
            var option = document.createElement('option');
            option.setAttribute('value', i);
            option.innerHTML = [
                method['return'],
                i + '(' + params.join(', ') + ')'
            ].join(' ');

            select.appendChild(option);
            paramsDiv.appendChild(paramDiv);
        }
        enableParams(document.querySelector('#params > div').id);

        // Bind switcher
        select.addEventListener('change', function () {
            // Disable all params first
            var params = document.querySelectorAll('#params > div');
            for (var i = 0; i < params.length; ++i) {
                (function () {
                    params[i].style.display = 'none';
                    var inputs = params[i].childNodes;
                    // Remove required attributes
                    for (var j = 0; j < inputs.length; ++j)
                        inputs[j].removeAttribute('required');
                })();
            }
            // Enable needle param after
            enableParams(this.value)
        });

        // Enable parameter div by id
        function enableParams(id) {
            var param = document.getElementById(id);
            param.style.display = 'block';
            var inputs = param.childNodes;
            // Add required attributes if needed
            for (var j = 0; j < inputs.length; ++j) {
                var name = inputs[j].name.match(/^param\[([a-zA-Z0-9]+)]$/)[1]; // Extract name of input
                if (!clientMethods[param.id]['params'][name].optional)
                    inputs[j].setAttribute('required', 'required');
            }
        }
    })();
</script>

</body>
</html>