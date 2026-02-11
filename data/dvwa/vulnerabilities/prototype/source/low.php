<?php

$prototypeHtml = "";
$vulnerabilityScript = <<<'JAVASCRIPT'
// VULNERABLE: Deep merge without prototype pollution protection
function merge(target, source) {
	for (let key in source) {
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
		
		// DANGEROUS: No validation of property names
		// Allows __proto__ and constructor.prototype pollution
		merge(config, userInput);
		
		result.style.display = 'block';
		configOutput.textContent = JSON.stringify(config, null, 2);
		
		// Apply theme
		if (config.theme) {
			document.body.style.background = config.theme === 'dark' ? '#333' : '#fff';
			document.body.style.color = config.theme === 'dark' ? '#fff' : '#000';
		}
		
		console.log('Configuration applied:', config);
		console.log('Checking for pollution - Empty object:', {});
		
	} catch (e) {
		result.style.display = 'block';
		configOutput.textContent = 'Error: ' + e.message;
		configOutput.style.color = 'red';
	}
}
JAVASCRIPT;

?>
