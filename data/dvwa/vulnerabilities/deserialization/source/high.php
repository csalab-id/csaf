<?php

class UserPreferences {
	public $theme;
	public $language;
	public $file;
	
	public function __construct($theme = 'light', $language = 'en') {
		$this->theme = $theme;
		$this->language = $language;
	}
	
	public function __destruct() {
		if (!empty($this->file)) {
			if (file_exists($this->file)) {
				$content = file_get_contents($this->file);
				echo "<div class=\"vulnerable_code_area\"><h3>File Content:</h3><pre>" . htmlspecialchars($content) . "</pre></div>";
			}
		}
	}
}

$deserializeHtml = "";

if( isset( $_POST[ 'save' ] ) ) {
	$theme = $_POST[ 'theme' ];
	$language = $_POST[ 'language' ];
	
	$prefs = new UserPreferences($theme, $language);
	$serialized = serialize($prefs);
	
	$deserializeHtml .= "<div class=\"vulnerable_code_area\">";
	$deserializeHtml .= "<h3>Preferences Saved!</h3>";
	$deserializeHtml .= "<p><strong>Serialized Data:</strong></p>";
	$deserializeHtml .= "<pre>" . htmlspecialchars($serialized) . "</pre>";
	$deserializeHtml .= "<p><em>Copy this data to load your preferences later.</em></p>";
	$deserializeHtml .= "</div>";
}

if( isset( $_POST[ 'load' ] ) ) {
	$data = $_POST[ 'data' ];
	
	if( !empty( $data ) ) {
		if( strpos($data, 'UserPreferences') === false ) {
			$deserializeHtml .= "<pre>Blocked! Invalid class.</pre>";
		} elseif( strpos($data, 'file') !== false ) {
			$deserializeHtml .= "<pre>Blocked! Suspicious property detected.</pre>";
		} else {
			$prefs = @unserialize($data);
			
			if ($prefs instanceof UserPreferences) {
				if (!empty($prefs->file)) {
					$deserializeHtml .= "<pre>Error: File property must be empty.</pre>";
				} else {
					$deserializeHtml .= "<div class=\"vulnerable_code_area\">";
					$deserializeHtml .= "<h3>Preferences Loaded!</h3>";
					$deserializeHtml .= "<p><strong>Theme:</strong> " . htmlspecialchars($prefs->theme) . "</p>";
					$deserializeHtml .= "<p><strong>Language:</strong> " . htmlspecialchars($prefs->language) . "</p>";
					$deserializeHtml .= "</div>";
				}
			} else {
				$deserializeHtml .= "<pre>Error: Invalid object type.</pre>";
			}
		}
	} else {
		$deserializeHtml .= "<pre>Please provide serialized data.</pre>";
	}
}

?>
