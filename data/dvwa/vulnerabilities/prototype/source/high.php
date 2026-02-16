<?php

$prototypeHtml = "";
$vulnerabilityScript = <<<'JAVASCRIPT'
function merge(target, source, isTopLevel = true) {
	for (let key in source) {
		if (isTopLevel) {
			const blacklist = ['__proto__', 'constructor', 'prototype'];
			if (blacklist.includes(key)) {
				console.warn('Blocked: ' + key + ' is not allowed');
				continue;
			}
		}
		
		if (typeof source[key] === 'object' && source[key] !== null) {
			if (!target[key]) {
				target[key] = {};
			}
			merge(target[key], source[key], false);
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
		
		merge(config, userInput);
		
		result.style.display = 'block';
		configOutput.textContent = JSON.stringify(config, null, 2);
		
		if (config.theme) {
			document.body.style.background = config.theme === 'dark' ? '#333' : '#fff';
			document.body.style.color = config.theme === 'dark' ? '#fff' : '#000';
		}

		console.log('Testing pollution - Check empty object:', {});
		
	} catch (e) {
		result.style.display = 'block';
		configOutput.textContent = 'Error: ' + e.message;
		configOutput.style.color = 'red';
	}
}
JAVASCRIPT;

?>
