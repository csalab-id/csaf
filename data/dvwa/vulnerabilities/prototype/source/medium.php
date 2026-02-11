<?php

$prototypeHtml = "";
$vulnerabilityScript = <<<'JAVASCRIPT'
// Basic blacklist - blocks __proto__ but not other vectors
function merge(target, source) {
	for (let key in source) {
		// Block __proto__ keyword
		if (key === '__proto__') {
			console.warn('Blocked: __proto__ is not allowed');
			continue;
		}
		
		if (typeof source[key] === 'object' && source[key] !== null) {
			if (!target[key]) {
				target[key] = {};
			}
			merge(target[key], source[key]);
		} else {
			target[key] = source[key];
		}
	}
	return target;
}

function applyPreferences() {
	const jsonInput = document.getElementById('jsonInput').value;
	const result = document.getElementById('result');
	const configOutput = document.getElementById('configOutput');
	
	try {
		const userInput = JSON.parse(jsonInput);
		const config = {};
		
		// Still vulnerable to: constructor.prototype
		merge(config, userInput);
		
		result.style.display = 'block';
		configOutput.textContent = JSON.stringify(config, null, 2);
		
		if (config.theme) {
			document.body.style.background = config.theme === 'dark' ? '#333' : '#fff';
			document.body.style.color = config.theme === 'dark' ? '#fff' : '#000';
		}
		
		console.log('Configuration applied with __proto__ filter');
		
	} catch (e) {
		result.style.display = 'block';
		configOutput.textContent = 'Error: ' + e.message;
		configOutput.style.color = 'red';
	}
}
JAVASCRIPT;

?>
