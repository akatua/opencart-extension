<?php
class ControllerPaymentAkatua extends Controller {
	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$mymode = strtolower($this->config->get('akatua_mode'));
		$appid = $this->config->get('akatua_application_id');
		$appsecret = $this->config->get('akatua_application_secret');
		$desc = base64_encode($this->config->get('config_name') ." - #".$this->session->data['order_id']);;
		$timestamp = time();

		$this->data['application_id'] = $appid;
		$this->data['signature'] = hash_hmac('sha256',"{$appid}:{$desc}:{$timestamp}", $appsecret);
		$this->data['timestamp'] = $timestamp;
		$this->data['test'] = ($mymode == "test") ? 1 : 0;
		$this->data['transaction_type'] = "checkout";
		$this->data['description'] = $desc;
		$this->data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$this->data['invoice'] = $this->session->data['order_id'];

		$this->data['serverurl'] = "https://secure.akatua.com/checkout";
		$this->data['successurl'] = $this->url->link('checkout/success/', '', 'SSL');
		$this->data['failurl'] = $this->url->link('checkout/checkout/', '', 'SSL');
		$this->data['callbackurl'] = $this->url->link('payment/akatua/callback/', '', 'SSL');
		$this->data['logourl'] = HTTP_IMAGE . $this->config->get('config_logo');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/akatua.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/akatua.tpl';
		} else {
			$this->template = 'default/template/payment/akatua.tpl';
		}

		$this->render();
	}

	public function callback() {
		if (isset($this->request->post['invoice'])) {
			$order_id = $this->request->post['invoice'];
		} else {
			$order_id = 0;
		}
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));

			$transaction_id = $this->request->post['transaction_id'];
			if (!$transaction_id) exit;

			$data['transaction_id'] = $transaction_id;
			$data['timestamp'] = time();
			if (strtolower($this->config->get('akatua_mode')) == "test") $data['test_mode'] = 1;

			$serverurl = "https://secure.akatua.com/api/v1/getTransactionDetails";

			$headers[] = "Content-Type: application/json";
			$headers[] = "Akatua-Application-ID: ".$this->config->get('akatua_application_id');
			$headers[] = "Akatua-Signature: ".hash_hmac('sha256',json_encode($data),$this->config->get('akatua_application_secret'));

			$confirm = $this->make_httprequest("GET",$serverurl,$data,$headers);

			$json = json_decode($confirm);

			if (empty($json)) exit;
			if (!isset($json->success)) exit;

			$order_amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

			if ($order_amount != $json->response->amount) {
				$this->log->write('Amount paid does not match invoice amount');
				exit;
			}

			switch($json->response->status) {
				case 'completed':
					$this->model_checkout_order->update($order_id, $this->config->get('akatua_order_status_id'), '', true);
					break;
				case 'pending':
					$this->model_checkout_order->update($order_id, $this->config->get('akatua_pending_status_id'), '', true);
					break;
				case 'failed':
					$this->model_checkout_order->update($order_id, $this->config->get('akatua_failed_status_id'), '', true);
					break;
				case 'reversed':
					$this->model_checkout_order->update($order_id, $this->config->get('akatua_reversed_status_id'), '', true);
					break;
			}
		}
		else {
			$this->log->write('Order not found');
		}
	}

	private function make_httprequest($method="GET",$url,$data=array(),$headers=array()) {
		$method = strtoupper($method);
		$json = json_encode($data);

		if (function_exists('curl_version') && strpos(ini_get('disable_functions'),'curl_exec') === false) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

			$result = curl_exec($ch);
			$error = curl_error($ch);
			if ($error) throw new Exception($error);
			curl_close($ch);
		}
		else {
			$urlbits = parse_url($url);
			$host = $urlbits['host'];
			$path = $urlbits['path'];

			$remote = fsockopen("ssl://{$host}", 443, $errno, $errstr, 30);

			if (!$remote) {
				throw new Exception("$errstr ($errno)");
			}

			$req = "{$method} {$path} HTTP/1.1\r\n";
			$req .= "Host: {$host}\r\n";
			foreach($headers as $header) {
				$req .= $header."\r\n";
			}
			$req .= "Content-Length: ".strlen($json)."\r\n";
			$req .= "Connection: Close\r\n\r\n";
			$req .= $json;
			fwrite($remote, $req);
			$response = '';
			while (!feof($remote)) {
				$response .= fgets($remote, 1024);
			}
			fclose($remote);

			$responsebits = explode("\r\n\r\n", $response, 2);
			$header = isset($responsebits[0]) ? $responsebits[0] : '';
			$result = isset($responsebits[1]) ? $responsebits[1] : '';
		}
		return $result;
	}

}
?>