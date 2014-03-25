<?php
class ControllerPaymentAkatua extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/akatua');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('akatua', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');

		$this->data['entry_application_id'] = $this->language->get('entry_application_id');
		$this->data['entry_application_secret'] = $this->language->get('entry_application_secret');
		$this->data['entry_mode'] = $this->language->get('entry_mode');
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_live'] = $this->language->get('entry_live');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$this->data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$this->data['entry_reversed_status'] = $this->language->get('entry_reversed_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

  	if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['application_id'])) {
			$this->data['error_application_id'] = $this->error['application_id'];
		} else {
			$this->data['error_application_id'] = '';
		}

 		if (isset($this->error['application_secret'])) {
			$this->data['error_application_secret'] = $this->error['application_secret'];
		} else {
			$this->data['error_application_secret'] = '';
		}

 		if (isset($this->error['mode'])) {
			$this->data['error_mode'] = $this->error['mode'];
		} else {
			$this->data['error_mode'] = '';
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/akatua', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/akatua', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['akatua_application_id'])) {
			$this->data['akatua_application_id'] = $this->request->post['akatua_application_id'];
		} else {
			$this->data['akatua_application_id'] = $this->config->get('akatua_application_id');
		}

		if (isset($this->request->post['akatua_application_secret'])) {
			$this->data['akatua_application_secret'] = $this->request->post['akatua_application_secret'];
		} else {
			$this->data['akatua_application_secret'] = $this->config->get('akatua_application_secret');
		}

		if (isset($this->request->post['akatua_mode'])) {
			$this->data['akatua_mode'] = $this->request->post['akatua_mode'];
		} else {
			$this->data['akatua_mode'] = $this->config->get('akatua_mode');
		}

		if (isset($this->request->post['akatua_order_status_id'])) {
			$this->data['akatua_order_status_id'] = $this->request->post['akatua_order_status_id'];
		} else {
			$this->data['akatua_order_status_id'] = $this->config->get('akatua_order_status_id');
		}

		if (isset($this->request->post['akatua_pending_status_id'])) {
			$this->data['akatua_pending_status_id'] = $this->request->post['akatua_pending_status_id'];
		} else {
			$this->data['akatua_pending_status_id'] = $this->config->get('akatua_pending_status_id');
		}

		if (isset($this->request->post['akatua_failed_status_id'])) {
			$this->data['akatua_failed_status_id'] = $this->request->post['akatua_failed_status_id'];
		} else {
			$this->data['akatua_failed_status_id'] = $this->config->get('akatua_failed_status_id');
		}

		if (isset($this->request->post['akatua_reversed_status_id'])) {
			$this->data['akatua_reversed_status_id'] = $this->request->post['akatua_reversed_status_id'];
		} else {
			$this->data['akatua_reversed_status_id'] = $this->config->get('akatua_reversed_status_id');
		}


		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['akatua_geo_zone_id'])) {
			$this->data['akatua_geo_zone_id'] = $this->request->post['akatua_geo_zone_id'];
		} else {
			$this->data['akatua_geo_zone_id'] = $this->config->get('akatua_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['akatua_status'])) {
			$this->data['akatua_status'] = $this->request->post['akatua_status'];
		} else {
			$this->data['akatua_status'] = $this->config->get('akatua_status');
		}

		if (isset($this->request->post['akatua_sort_order'])) {
			$this->data['akatua_sort_order'] = $this->request->post['akatua_sort_order'];
		} else {
			$this->data['akatua_sort_order'] = $this->config->get('akatua_sort_order');
		}

		$this->template = 'payment/akatua.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/akatua')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['akatua_application_id']) {
			$this->error['application_id'] = $this->language->get('error_application_id');
		}

		if (!$this->request->post['akatua_application_secret']) {
			$this->error['application_secret'] = $this->language->get('error_application_secret');
		}

		if (!$this->request->post['akatua_mode']) {
			$this->error['mode'] = $this->language->get('error_mode');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>