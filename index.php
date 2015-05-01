<?php
	date_default_timezone_set('America/New_York');

	function usage() {
		echo '... usage ...'."\n";
	}

	/**
	 * Please be aware. This method requires at least PHP 5.4 to run correctly.
	 * Otherwise consider downgrading the $opts array code to the classic "array" syntax.
	 */
	function startLogging($streamingUrl, $interval, $filename)
	{
		$offset = 0;
		$needle = 'StreamTitle=';
		$ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36';

		$opts = [
			'http' => [
				'method' => 'GET',
				'header' => 'Icy-MetaData: 1',
				'user_agent' => $ua
			]
		];

		$context = stream_context_create($opts);

		if( $stream = fopen($streamingUrl, 'r', false, $context) ) {
			while($buffer = stream_get_contents($stream, $interval, $offset)) {
				if( strpos($buffer, 'StreamTitle=') !== false ) {
					// title data.
					$title = explode('StreamTitle=', $buffer)[1];
					$title = substr($title, 1, strpos($title, ';') - 2);
					$line = sprintf("[%s] %s\n", date('m/d/Y H:i:s'), $title);
					file_put_contents($filename.'.txt', $line, FILE_APPEND);
				} else {
					if(isset($title) && strlen($title) > 0) {
						// song data. (this is SUPER dirty)
						if(!is_dir('./mp3s')) {
							mkdir('./mp3s');
						}
						if(!is_dir('./mp3s/'.$filename)) {
							mkdir('./mp3s/'.$filename);
						}
						file_put_contents('mp3s/'.$filename.'/'.preg_replace('/[^\w\s\.\-\(\)]/', '', $title).'.mp3', $buffer, FILE_APPEND);
					}
				}

				$offset += $interval;
			}
			fclose($stream);
		} else {
			throw new Exception("Unable to open stream [{$streamingUrl}]");
		}
	}

	if(isset($argc) && is_int($argc) && $argc < 2) {
		usage();
		exit(1);
	}

	if(is_string($argv[1])) {
		$url = $argv[1];

		if( filter_var($url, FILTER_VALIDATE_URL) === false ) {
			throw new Exception('"'.$url.'" is not a valid URL. Please input in the format: http://example.com:80');
		}
	}

	if( !isset($url) ) {
		throw new Exception('URL was not set.');
	}

	if(isset($argv[2]) && is_string($argv[2])) {
		$filename = $argv[2];
	}

	if( !isset($filename) ) {
		$parsed_url = parse_url($url);
		$filename = $parsed_url['host'].'-'.trim($parsed_url['path'], '/');
		$filename .= '_'.substr(sha1($filename.'_'.date('m-d-Y_H-i-s')), 0, 10);
	}

	startLogging($url, 10*1024, $filename);

	// printf("Currently playing on DEFCON radio: %s\n", getMp3StreamTitle('http://ice.somafm.com:80/defcon', 2*1024));
	// printf("Currently playing on CLASSIC TRANCE radio: %s\n", getMp3StreamTitle('http://pub1.di.fm/di_classictrance', 2*1024));