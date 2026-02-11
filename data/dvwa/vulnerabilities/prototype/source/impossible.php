<?php

$prototypeHtml = "<div class=\"info\" style=\"margin: 20px 0;\">
<strong>✓ Secure Implementation:</strong> This level uses proper protection against prototype pollution attacks.
</div>";

$vulnerabilityScript = <<<'JAVASCRIPT'
// SECURE: Multiple layers of protection
function merge(target, source) {
	// Use Object.create(null) to create objects without prototype
	const safeTarget = Object.create(null);
	
	for (let key in source) {
		// Strict whitelist - only allow expected properties
		const allowedKeys = ['theme', 'language', 'fontSize', 'notifications'];
		if (!allowedKeys.includes(key)) {
			console.warn('Rejected: ' + key + ' is not in whitelist');
			continue;
		}
		
		// Type validation
		if (typeof source[key] === 'object' && source[key] !== null) {
			// Don't allow nested objects
			console.warn('Rejected: nested objects not allowed');
			continue;
		}
		
		// Use Object.defineProperty for controlled assignment
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
		
		// Additional validation: ensure it's a plain object
		if (Object.getPrototypeOf(userInput) !== Object.prototype) {
			throw new Error('Invalid input: must be a plain object');
		}
		
		// Use Map instead of plain object for extra safety
		const config = new Map();
		const safeConfig = merge({}, userInput);
		
		for (let key in safeConfig) {
			config.set(key, safeConfig[key]);
		}
		
		result.style.display = 'block';
		
		// Display configuration safely
		let output = '{\n';
		config.forEach((value, key) => {
			output += '  "' + key + '": "' + value + '",\n';
		});
		output = output.slice(0, -2) + '\n}';
		configOutput.textContent = output;
		configOutput.style.color = 'green';
		
		// Apply theme safely
		const theme = config.get('theme');
		if (theme === 'dark' || theme === 'light') {
			document.body.style.background = theme === 'dark' ? '#333' : '#fff';
			document.body.style.color = theme === 'dark' ? '#fff' : '#000';
		}
		
		console.log('Secure configuration applied');
		console.log('Protection methods used:');
		console.log('- Object.create(null)');
		console.log('- Whitelist validation');
		console.log('- Object.defineProperty');
		console.log('- Map instead of plain object');
		console.log('- No nested objects allowed');
		
	} catch (e) {
		result.style.display = 'block';
		configOutput.textContent = 'Error: ' + e.message;
		configOutput.style.color = 'red';
	}
}

// Freeze Object.prototype as additional protection
Object.freeze(Object.prototype);
JAVASCRIPT;

?>
