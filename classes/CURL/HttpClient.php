<?php
namespace CURL;

class HttpClient
{
	const METHOD_GET = 0;
	const METHOD_POST = 1;

	private $ch;
	private $basicUsername = '';
	private $basicPassword = '';
	private $curlHeader = false;
	private $curlFollowlocation = false;
	private $curlHeaderOut = false;
	private $curlInfo = '';
	private $curlError = '';
	private $timeout = 30;
	private $cookieDir = '';
	private $requestPost = '';
	private $response = '';
	private $responseHeader = '';

	public function __construct() {
		$this->ch = curl_init();
	}

	public function __destruct() {
		curl_close($this->ch);
	}

	public function setBasicUsername($username) {
		$this->basicUsername = $username;
	}

	public function setBasicPassword($password) {
		$this->basicPassword = $password;
	}

    public function getHttpCode() {
        return $this->curlInfo['http_code'];
    }

	public function fetchBasicUsernamePassword() {
		return $this->basicUsername . ':' . $this->basicPassword;
	}

	public function getResponse() {
		return $this->response;
	}

	public function enableHeader() {
		$this->curlHeader = true;
	}

	public function disableHeader() {
		$this->curlHeader = false;
	}

	public function enableFollowlocation() {
		$this->curlFollowlocation = true;
	}

	public function disableFollowlocation() {
		$this->curlFollowlocation = false;
	}

	public function enableHeaderOut() {
		$this->curlHeaderOut = true;
	}

	public function disableHeaderOut() {
		$this->curlHeaderOut = false;
	}

	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	public function setRequestHeaders($headers) {
		if(is_array($headers) && (count($headers) > 0)) {
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		}
	}

	public function setRequestPost($data) {
		$this->requestPost = $data;
	}

	private function fetchRequestPost() {
		$output = $this->requestPost;
		if (is_array($this->requestPost)) {
			$output = http_build_query($this->requestPost);
		}
		return $output;
	}

	private function extractHeaders() {
		$response = $this->response;
		$responseList = explode("\r\n\r\n", $this->response);
		$responseCount = count($responseList) - 1;
		if($responseCount > 0) {
			$response = $responseList[ $responseCount ];
			$this->responseHeader = str_replace($response, '', $this->response);
		}
		$this->response = $response;
	}

	public function fetchDebug() {
        // :TODO: Add COOKIE in debug
		$requestHeader = trim($this->curlInfo['request_header']);
		unset($this->curlInfo['request_header']);

		$responseHeader = trim($this->responseHeader);

		$curlInfoCert = '';
		if(!empty($this->curlInfo['certinfo'])) {
			foreach ($this->curlInfo['certinfo'] as $key => $value) {
				$curlInfoCert .= $key . ": " . $value . "\r\n";
			}
			$curlInfoCert = trim($curlInfoCert);
		}
		unset($this->curlInfo['certinfo']);

		$cInfo = '';
		foreach ($this->curlInfo as $key => $value) {
			$cInfo .= $key . ": " . $value . "\r\n";
		}
		$cInfo .= "certinfo: " . $curlInfoCert;
		$cInfo = trim($cInfo);

		$cError = trim($this->curlError);

		$output = "HTTP request header\r\n"
			. "--------------------------------\r\n"
			. $requestHeader . "\r\n\r\n\r\n"
			. "HTTP POST request\r\n"
			. "--------------------------------\r\n"
			. $this->fetchRequestPost() . "\r\n\r\n\r\n"
			. "HTTP response header\r\n"
			. "--------------------------------\r\n"
			. $responseHeader . "\r\n\r\n\r\n"
			. "cURL info\r\n"
			. "--------------------------------\r\n"
			. $cInfo . "\r\n\r\n\r\n"
			. "cURL error\r\n"
			. "--------------------------------\r\n"
			. $cError . "\r\n\r\n\r\n";

		return $output;
	}

	public function setCookieDir($cookieDir) {
		$this->cookieDir = $cookieDir;
	}

	public function enableCookie() {
		$c = new \CURL\Cookie();
		if (!empty($this->cookieDir)) {
			$c->setStoragePath($this->cookieDir);
		}
		$c->createStorage();

		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $c->getStorage());
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $c->getStorage());
	}

	public function sendRequest($url, $query = array(), $method = \CURL\HttpClient::METHOD_GET) {
		if (! is_array($query) && strlen($query) > 0) {
            $url .= '?' . $query;
        }
        elseif (count($query) > 0) {
			$queryString = http_build_query($query);
			$url .= '?' . $queryString;
		}

		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_HEADER, $this->curlHeader);
		curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)');
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $this->curlFollowlocation);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->ch, CURLINFO_HEADER_OUT, $this->curlHeaderOut);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

		if (!empty($this->basicUsername) && !empty($this->basicPassword)) {
			curl_setopt($this->ch, CURLOPT_USERPWD, $this->fetchBasicUsernamePassword());
		}

		if ($method == \CURL\HttpClient::METHOD_POST) {
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->requestPost);
		}

		$this->response = curl_exec($this->ch);

		if (!empty($this->response)) {
			$this->extractHeaders();
        }
        $this->curlInfo = curl_getinfo($this->ch);
        $this->curlError = curl_error($this->ch);
	}
}