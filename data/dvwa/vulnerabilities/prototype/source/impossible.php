<?php

$prototypeHtml = "<div class=\"info\" style=\"margin: 20px 0;\">
<strong>✓ Secure Implementation:</strong> This level uses proper protection against prototype pollution attacks.
</div>";

$vulnerabilityScript = <<<'JAVASCRIPT'
function merge(target, source) {
	const safeTarget = Object.create(null);
	
	for (let key in source) {
		const allowedKeys = ['theme', 'language', 'fontSize', 'notifications'];
		if (!allowedKeys.includes(key)) {
			console.warn('Rejected: ' + key + ' is not in whitelist');
			continue;
		}

		if (typeof source[key] === 'object' && source[key] !== null) {
			console.warn('Rejected: nested objects not allowed');
			continue;
		}

		Object.defineProperty(safeTarget, key, {
			value: source[key],
			writable: false,
			enumerable: true,
			configurable: false
		});
	}
	
	return safeTarget;
}

function applyPreferences() {
	const jsonInput = document.getElementById('jsonInput').value;
	const result = document.getElementById('result');
	const configOutput = document.getElementById('configOutput');
	
	try {
		const userInput = JSON.parse(jsonInput);

		if (Object.getPrototypeOf(userInput) !== Object.prototype) {
			throw new Error('Invalid input: must be a plain object');
		}

		const config = new Map();
		const safeConfig = merge({}, userInput);
		
		for (let key in safeConfig) {
			config.set(key, safeConfig[key]);
		}
		
		result.style.display = 'block';

		let output = '{\n';
		config.forEach((value, key) => {
			output += '  "' + key + '": "' + value + '",\n';
		});
		output = output.slice(0, -2) + '\n}';
		configOutput.textContent = output;
		configOutput.style.color = 'green';

		const theme = config.get('theme');
		if (theme === 'dark' || theme === 'light') {
			document.body.style.background = theme === 'dark' ? '#333' : '#fff';
			document.body.style.color = theme === 'dark' ? '#fff' : '#000';
		}
		
	} catch (e) {
		result.style.display = 'block';
		configOutput.textContent = 'Error: ' + e.message;
		configOutput.style.color = 'red';
	}
}

Object.freeze(Object.prototype);
JAVASCRIPT;

?>
