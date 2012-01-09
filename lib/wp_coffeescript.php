<?php

class WpCoffeescript {

	private $plugin_path = '';
	private $plugin_url = '';
	private $executable = 'coffee';
	private $output_directory_path = '';
	private $output_directory_url = '';
	private $caching_enabled = false;
	private $throw_errors = true;
	
	function __construct() {
		
		$this->plugin_path = preg_replace('/lib\/?$/', '', dirname(__FILE__));
		$this->plugin_url = site_url('/').str_replace(ABSPATH, '', $this->plugin_path);
		
		$upload_directory = wp_upload_dir();
		$this->output_directory_path = $upload_directory['basedir'].'/coffeescript-cache';
		$this->output_directory_url = $upload_directory['baseurl'].'/coffeescript-cache';
		
		$this->executable = apply_filters('wpcs_executable', $this->executable);
		$this->output_directory_path = apply_filters('wpcs_output_directory_path', $this->output_directory_path);
		$this->output_directory_url = apply_filters('wpcs_output_directory_url', $this->output_directory_url);
		$this->caching_enabled = apply_filters('wpcs_caching_enabled', $this->caching_enabled);
		
		if (!is_dir($this->output_directory_path)) {
			if (!mkdir($this->output_directory_path)) {
				$this->error('Unable to create output directory: '.$this->output_directory_path);
			}
		}
		
	}

	public function enqueue($handle, $input_path_or_paths, $deps=array(), $ver=false, $in_footer=false) {
	
		$output_url = null;
		
		if (is_string($input_path_or_paths)) {
			$output_url = $this->compile($input_path_or_paths, $handle);
		} else if (is_array($input_path_or_paths)) {
			$output_url = $this->compile_multiple($input_path_or_paths, $handle);
		}
		
		if ($output_url) {
			wp_enqueue_script($handle, $output_url, $deps, $ver, $in_footer);
			return true;
		}
		
		$this->error('Unable to compile CoffeeScript with the handle "'.$handle.'".');
		
		return false;
	
	}
	
	public function compile($input_path, $handle) {
	
		$input_path = $this->process_input_path($input_path);
		
		if (!$this->check_input_path($input_path)) {
			return null;
		}
		
		$cached_file_name = $this->get_cached_file_name($handle);
		$output_path = $this->output_directory_path.'/'.$cached_file_name;
		
		if (!$this->caching_enabled) {
			$compilation_necessary = true;
		} else {
			$compilation_necessary = false;
			if (file_exists($output_path)) {
				if (filemtime($input_path) > filemtime($output_path)) {
					$compilation_necessary = true;
				}
			} else {
				$compilation_necessary = true;
			}
		}
		
		if ($compilation_necessary) {
			$escaped_executable = escapeshellcmd($this->executable);
			$escaped_input_path = escapeshellarg($input_path);
			$command = $escaped_executable.' -c -s -p < '.$escaped_input_path.'  2>&1';
			if (!$this->execute_command_and_write_output($command, $output_path, $handle)) {
				return null;
			}
		}
		
		$output_url = $this->output_directory_url.'/'.$cached_file_name;
		return $output_url;
	
	}
	
	public function compile_multiple($input_paths, $handle) {
	
		$input_paths = array_map(array($this, 'process_input_path'), $input_paths);
		
		foreach ($input_paths as $input_path) {
			if (!$this->check_input_path($input_path)) {
				return null;
			}
		}
		
		$input_paths_hash = md5(implode($input_paths));
		$cached_file_name = $this->get_cached_file_name($handle.'---'.$input_paths_hash);
		$output_path = $this->output_directory_path.'/'.$cached_file_name;
		
		if (!$this->caching_enabled) {
			$compilation_necessary = true;
		} else {
			$compilation_necessary = false;
			if (file_exists($output_path)) {
				foreach ($input_paths as $input_path) {
					if (filemtime($input_path) > filemtime($output_path)) {
						$compilation_necessary = true;
						break;
					}
				}
			} else {
				$compilation_necessary = true;
			}
		}
		
		if ($compilation_necessary) {
			
			$escaped_executable = escapeshellcmd($this->executable);
			$escaped_input_paths = array();
			foreach ($input_paths as $input_path) {
				$escaped_input_paths[] = '$(cat '.escapeshellarg($input_path).')\n';
			}
			$escaped_input_paths = implode('', $escaped_input_paths);
			
			$command = 'echo "'.$escaped_input_paths.'" | '.$escaped_executable.' -c -s -p 2>&1';
			
			$old_cache_files = glob($this->output_directory_path.'/'.$handle.'---*');
			array_map('unlink', $old_cache_files);
			
			if (!$this->execute_command_and_write_output($command, $output_path, $handle)) {
				return null;
			}
			
		}
		
		$output_url = $this->output_directory_url.'/'.$cached_file_name;
		return $output_url;
	
	}
	
	public function deactivate() {
		$files = glob($this->output_directory_path.'/*');
		array_map('unlink', $files);
		rmdir($this->output_directory_path);
	}
	
	private function process_input_path($input_path) {
		if (substr($input_path, 0, 1) != '/') {
			$input_path = rtrim(WP_CONTENT_DIR, '/').'/'.$input_path;
		}
		return $input_path;
	}
	
	private function check_input_path($input_path) {
		if (!file_exists($input_path)) {
			$this->error('Input file not found: '.$input_path);
			return false;
		}
		return true;
	}
	
	private function get_cached_file_name($handle) {
		return $handle.'.js';
	}
	
	private function execute_command_and_write_output($command, $output_path, $handle) {
			
		exec($command, $output, $return_var);
		
		if ($return_var != 0) {
			$this->error('Error '.$return_var.' while compiling "'.$handle.'": <br /><pre>'.implode("\n", $output).'</pre>');
			return null;
		}
		
		if (!$handle = fopen($output_path, 'w')) {
			$this->error('Unable to open output file: '.$output_path);
			return null;
		}
		
		$content = implode("\n", $output);
		
		if (fwrite($handle, $content) === false) {
			$this->error('Unable to write to output file: '.$output_path);
			return null;
		}
		
		return true;
		
	}
	
	private function error($message, $error_type=E_USER_WARNING) {
		if ($this->throw_errors) {
			trigger_error('WP CoffeeScript: '.$message, $error_type);
		}
	}

}

?>