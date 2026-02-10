<?php

$prototypeHtml = "";
$vulnerabilityScript = "
// Better validation - blocks multiple vectors but still bypassable
function merge(target, source) {
	for (let key in source) {
		// Block dangerous property names
		const blacklist = ['__proto__', 'constructor', 'prototype'];
		if (blacklist.includes(key)) {
			console.warn('Blocked: ' + key + ' is not allowed');
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
		
		// More secure but could still be vulnerable to:
		// - Unicode escapes: \\u005f\\u005fproto\\u005f\\u005f
		// - Bracket notation edge cases
		// - JSON parser quirks
		merge(config, userInput);
		
		result.style.display = 'block';
		configOutput.textContent = JSON.stringify(config, null, 2);
		
		if (config.theme) {
			document.body.style.background = config.theme === 'dark' ? '#333' : '#fff';
			document.body.style.color = config.theme === 'dark' ? '#fff' : '#000';
		}
		
		console.log('Configuration applied with enhanced filtering');
		
	} catch (e) {
		result.style.display = 'block';
		configOutput.textContent = 'Error: ' + e.message;
		configOutput.style.color = 'red';
	}
}
";

?>
