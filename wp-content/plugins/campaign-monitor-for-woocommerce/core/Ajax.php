<?php
/**
 */

namespace core;

/**
 * Handles ajax calls
 *
 * Class Ajax
 * @package core
 */
abstract class Ajax
{

    public static $methods = array();
    protected static $authToken = array('access_token' => '',
        'refresh_token' => '');
    protected static $actionUrl = '';


    public static function run()
    {
        if (is_admin()){
            add_action('wp_ajax_handle_ajax', array(__CLASS__, 'ajax_handler'));
            add_action('wp_ajax_view_client_list', array(__CLASS__, 'view_client_list'));
            add_action('wp_ajax_get_custom_fields', array(__CLASS__, 'get_custom_fields'));
            add_action('wp_ajax_set_client_list', array(__CLASS__, 'set_client_list'));
            add_action('wp_ajax_create_list', array(__CLASS__, 'create_list'));
            add_action('wp_ajax_dismiss_notice', array(__CLASS__, 'dismiss_notice'));
            add_action('wp_ajax_get_list_settings', array(__CLASS__, 'get_list_settings'));
        }

        add_action('wp_ajax_nopriv_ajax_handler_nopriv', array(__CLASS__, 'ajax_handler_nopriv'));
        self::$actionUrl = get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce_settings';

    }

    public static function dismiss_notice(){

        if (array_key_exists('method', $_POST)){
            $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $method = $_POST['method'];
            $notices = Settings::get('notices');

            if (empty($notices)){
                $notices = array($method);
            } else {
                if (is_array($notices)){
                    if (!in_array($method,$notices, TRUE)){
                        array_push($notices, $method);
                    }
                }
            }
            Settings::add('notices', $notices);
        }

    }

    public static function remove_notice($notice){
        $notices = Settings::get('notices');
        if (empty($notices)){
            $notices = array();
        }

        if (in_array($notice,$notices, TRUE )){
            $flip = array_flip($notices);
            unset($flip[$notice]);
            $notices = array_flip($flip);
        }


        Settings::add('notices', $notices);

    }


    public static function ajax_handler()
    {
        // we could further optimize the plugin with one entry point for all ajax requests

    }

    protected static function print_data($data)
    {
        $html = "<pre>";
        $html .= print_r($data, true);
        $html .= "</pre>";

        return $html;
    }

    public static function generate_modal($message){
        $html = "";
        $html .= '<div class="modal">';
        $html .= '<div class="modal-header">';
        $html .= '</div>';
        $html .= '<div class="content">';
        $html .= '<span class="btn-close dashicons dashicons-no"></span>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td>';
        $html .= $message;
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<button class="button btn-close " name="cancel">Close</button>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '<div>';
        $html .= '<div class="modal-header">';
        $html .= '</div>';
        $html .= '<div>';

        return $html;
    }


    protected static function generate_custom_fields_list($customFields){
        $html = '<ul class="list custom-fields">';
        $count = 1;
        foreach ($customFields as $field) {
            $html .= '<li>';
            $html .= '<p  href="" data-visibleinpreferencecenter="' . $field->VisibleInPreferenceCenter . '" data-fieldoptions="' . $field->FieldOptions . '" data-key="' . $field->Key . '" data-datatype="' . $field->DataType . '" >';
            $html .= $count++;
            $html .=  "&nbsp;&nbsp;&nbsp;";
            $html .= $field->FieldName;
            $html .= '</p>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public static function set_client_list($clientId = '', $listID = '')
    {
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $params = $_POST;

        // settings for individual list
        $listSettings = array();

        if (!empty($clientId)){
            $params['ClientID'] = $clientId;
        }

        if (!empty($listID)){
            $params['ListID'] = $listID;
        }

        $toggleSubscription = false;
        if (array_key_exists('subscriptionBox', $params )){
            $toggleSubscription = ($params['subscriptionBox'] == 'true') ? true : false;
            Helper::updateOption('toggle_subscription_box',$toggleSubscription);
            $listSettings['toggle_subscription_box'] = $toggleSubscription;
        }


        if (array_key_exists('subscribe', $params )){
            $subscribe = ($params['subscribe'] == 'true') ? true : false;

            if ($subscribe && $toggleSubscription){
                Helper::updateOption('automatic_subscription',$subscribe);
                $listSettings['automatic_subscription'] = $subscribe;
            }
            else{
                Helper::updateOption('automatic_subscription',false);
                $listSettings['automatic_subscription'] = false;
            }
        }

        if (array_key_exists('subscribe_text', $params )){
            $subscribeText = $params['subscribe_text'];
            Helper::updateOption('subscribe_text',$subscribeText);
            $listSettings['subscribe_text'] = $subscribeText;
        }
        if (array_key_exists('debug', $params )){
            $debug = ($params['debug'] == 'true') ? true : false;
            Helper::updateOption('debug', $debug );
            $listSettings['debug'] = $debug;
        }
        if (array_key_exists('subscriptionBox', $params )){
            $toggleSubscription = ($params['subscriptionBox'] == 'true') ? true : false;
            Helper::updateOption('toggle_subscription_box',$toggleSubscription);
            $listSettings['toggle_subscription_box'] = $toggleSubscription;

        }

        $requestResults = new \stdClass();
        if (array_key_exists('ClientID', $params)) {

            $subscribe = false;
            $debug = false;
            $subscribeText = "";

            self::remove_notice('connected_list_notice');

            if (array_key_exists('new_list_name', $params ) && !empty($params['new_list_name'])){
                $optIn = ($params['new_list_type'] == 2) ? true : false;
                $newListName = $params['new_list_name'];
                $newListId = App::$CampaignMonitor->create_list($params['ClientID'], $newListName, $optIn);
                $params['ListID'] = $newListId;
            }


            $user = wp_get_current_user();
            $clientId = $params['ClientID'];
            $listId = $params['ListID'];

            ClientList::add($listId, $listSettings);

            if (Settings::get('default_list') != $listId) {


                $fields = Fields::get_required();
                $segmentsInAccount = App::$CampaignMonitor->get_segments($listId);
                $customFields = App::$CampaignMonitor->get_custom_fields($listId);

                $maximumFieldsCount = Helper::getMaximumFieldsCount();
                $campaignMonitorFieldCount = count($customFields);
                $usableFieldCount = $maximumFieldsCount - $campaignMonitorFieldCount;
                $requiredFieldsCount = count($fields);

                $maximumReached = ($campaignMonitorFieldCount == $maximumFieldsCount);

                if ($maximumReached || ($usableFieldCount < $requiredFieldsCount)  ) {
                    $message = ' <div class="notice notice-error is-dismissible">';
                    $message .= '<p>There are not enough custom fields in this list to transfer.</p>';
                    if ($maximumReached){
                        $message .= '<p>You already have '. $maximumFieldsCount .' custom fields defined for this list.</p>';
                    } else {
                        $message .= '<p>You need at least '. $requiredFieldsCount .' custom fields available.</p>';
                    }
                    $message .= '<p>Please delete some of the custom fields on Campaign Monitor or Create a New List.</p>';
                    $message .= '</div>';
                    $message .= self::generate_custom_fields_list($customFields);

                    $requestResults->content = '';
                    $requestResults->error = true;
                    $requestResults->modal = self::generate_modal($message);
                    wp_send_json($requestResults);
                    return;
                }


                $prefix = 'WooCommerce';
                $segmentedFields = array();

                foreach ($fields as $item) {
                    $fieldName = $prefix . " " . $item['field']['name'];
                    $suffix = 1;

                    $segmentedFields[] = $item['field'];
                    if (!empty($customFields)) {
                        foreach ($customFields as $field) {
                            if ($field->FieldName == $fieldName) {
                                $fieldName = $prefix . " " . $item['field']['name'] . " " . $suffix++;
                            }
                        }
                    }

                    $createdField = App::$CampaignMonitor->create_custom_field($listId, $fieldName, $item['field']['type']);
                    if (!empty($createdField)){
                        Map::add($item['field']['code'], $createdField);
                    }
                }

                $mapped = Map::get();
                $orderCountMappedLabel = $mapped['orders_count'];
                $orderAmountLabel = $mapped['total_spent'];
                $newsletterSubscriberLabel = $mapped['newsletter_subscribers'];
                // Default segments to create
                $rule = new \core\Rule($orderCountMappedLabel, array('EQUALS 1'));
                $rule2 = new \core\Rule($orderCountMappedLabel, array('GREATER_THAN_OR_EQUAL 5'));
                $rule3 = new \core\Rule($orderCountMappedLabel, array('EQUALS 0'));
                $newsletterRule = new \core\Rule($newsletterSubscriberLabel, array('EQUALS YES'));
                $rule4 = new \core\Rule($orderCountMappedLabel, array('GREATER_THAN_OR_EQUAL 5'));
                $rule5 = new \core\Rule($orderAmountLabel, array('GREATER_THAN_OR_EQUAL 500'));


                $segmentsToCreate = array();
                $segmentsToCreate[] = new \core\Segment('First Time Customers', array($rule));
                $segmentsToCreate[] = new \core\Segment('Repeat Customers', array($rule4));
                $segmentsToCreate[] = new \core\Segment('High Spending Customers', array($rule5));
                $segmentsToCreate[] = new \core\Segment('Newsletter Subscribers', array($newsletterRule));
                $segmentsToCreate[] = new \core\Segment('High Spending Repeat Customers', array($rule2, $rule5));
                $segmentsToCreate[] = new \core\Segment('Customers with 0 Purchases', array($rule3));

                $createdSegments = array();
                foreach ($segmentsToCreate as $segmentInstance) {

                    $segmentTitle = $prefix . " " . $segmentInstance->getTitle();
                    $suffix = 1;

                    if (!empty($segmentsInAccount)) {
                        foreach ($segmentsInAccount as $field) {
                            if ($field->Title == $segmentTitle) {
                                $segmentTitle = $prefix . " " . $segmentInstance->getTitle() . " " . $suffix++;
                            }
                        }
                    }

                    $segmentInstance->setTitle($segmentTitle);
                    $createdSegments[] = App::$CampaignMonitor->create_segment($listId, $segmentInstance->toArray());
                }
                Settings::add('data_sync', true );
            }


            Settings::add('default_list', $listId);
            Settings::add('default_client',$clientId );

            $imagesUrl = App::getDirectoryUrl(). 'views/admin/images/';
            $html = "";
            $html .= '<div class="box main-container text-center modal">';
//            $html .= '<img class="connected-icon" src="https://live.dev.apps-market.cm/shopifyApp/images/circleCheck.png">';
//            $html .= '<h1>Success! Your list is now syncing.</h1>';
//            $html .= '<p>It might take a while to sync your data from Shopify to Campaign Monitor. We\'ll email you the moment the data sync is complete.</p>';
//            $html .= '<h2>We\'ve created these segments for you</h2>';
//            $html .= '<p>';
//            $html .= 'Segments help you focus email content on smaller, more targeted groups of subscribers for more  creative email marketing and lead nurturing.';
//            $html .= '</p>';
//            $html .= '<div class="segments">';
//            $html .= '<ul>';
//            $html .= '<li><img class="responsive-img" src="'.$imagesUrl.'/Illustrations-10.png"><span class="segmentTitle">High spending customers</span></li>';
//            $html .= '<li><img class="responsive-img" src="'.$imagesUrl.'/Illustrations-06.png"><span class="segmentTitle">Repeat customers</span></li>';
//            $html .= '<li><img class="responsive-img" src="'.$imagesUrl.'/Illustrations-05.png"><span class="segmentTitle">First time customers</span></li>';
//            $html .= '<li><img class="responsive-img" src="'.$imagesUrl.'/Illustrations-08.png"><span class="segmentTitle">Newsletter subscribers</span></li>';
//            $html .= '</ul>';
//            $html .= '</div>';
            $html .= '</div>';
            $html .= '<script>';
            $html .= 'setTimeout(function () { window.location = "'.Helper::getActionUrl().'"; }, 5);';
            $html .= '</script>';

            $requestResults->content = $html;
        }

        wp_send_json($requestResults);
    }

    public static function create_list($clientId = '', $listName = '')
    {
        $requestResults = new \stdClass();
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $params = $_POST;
        $clientId = '';
        $requestResults = new \stdClass();

        if (empty($params)){
            $params['ClientID'] = $clientId;
            $params['list_name'] = $listName;
        }

        if (array_key_exists('list_name', $params)) {
            $clientId = $params['ClientID'];

            $newList = App::$CampaignMonitor->create_list($clientId, $params['list_name']);

            self::view_client_list($clientId);

        } else {
            $clientId = $params['ClientID'];

            $html = '<table class="list client-list">';
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<p>';
            $html .= 'Create a list so you can sync data from Shopify and send personalized email campaigns.';
            $html .= '</p>';
            $html .= '<p>';
            $html .= '<a class="ajax-call button primary button-primary button-large" href="' . self::$actionUrl . '&ClientID=' . $clientId . '&action=create_list">';
            $html .= 'Create List';
            $html .= '</a>';
            $html .= '</p>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';

            $html .= '<div class="modal">';
            $html .= '<div class="modal-header">';

            $html .= '</div>';
            $html .= '<div class="content">';
            $html .= '<span class="btn-close dashicons dashicons-no"></span>';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<td>';

            $html .= '<input type="text" style="width:100%;" name="list_name" id="newListName" class="" placeholder="Type list title in here">';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';


            $html .= '<td>';
            $html .= '<button class="button btn-close " name="cancel">Create List</button>';
            $html .= '<button id="btnCreateClientList" class="button primary button-primary" data-url="' . self::$actionUrl . '&ClientID=' . $clientId . '&action=create_list" name="submit">Create List</button>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '<div>';
            $html .= '<div class="modal-header">';
            $html .= '</div>';
            $html .= '<div>';


            $requestResults->content = $html;
            wp_send_json($requestResults);
        }
    }

    public static function get_list_settings($listId = ''){

        if (empty($listId)){
            $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $listId  = $_POST['ListID'];
        }
        $settings = ClientList::get($listId);
        wp_send_json($settings);
    }

    public static function view_client_list($clientId = '')
    {
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $params = $_POST;
        if (!empty($clientId)) {
            $params['ClientID'] = $clientId;
        }
        $requestResults = new \stdClass();

        if (array_key_exists('ClientID', $params)) {

            $clientId = $params['ClientID'];
            $lists = App::$CampaignMonitor->get_client_list($clientId);



            $html = '';
            if ($lists) {
                $html = '<select id="lists"  class="ajax-call list client-list dropdown-select ">';

                $selectedList = Settings::get('default_list');
                $html .= '<option class="ajax-call">';
                $html .= 'Please select list';
                $html .= '</option>';
                $html .= '<option class="ajax-call" data-url="' . self::$actionUrl . '&ClientID=' . $clientId . '&action=create_list">';
                $html .= 'Create new list';
                $html .= '</option>';
                $html .= '<option class="ajax-call" disabled >';
                $html .= '---';
                $html .= '</option>';
                $isSelected = false;
                foreach ($lists as $list) {
                    $id = $list->ListID;

                    $viewClientListUrl = http_build_query((array)$list);
                    $fields = App::$CampaignMonitor->get_stats($id);
                    $selected = '';

                    if ($id == $selectedList){
                        $selected = 'selected="selected"';
                        $isSelected = true;
                    }


                    $html .= '<option '.$selected .' value="'.$clientId.'" data-id="'.$id.'"  data-url="' . self::$actionUrl . '&' . $viewClientListUrl . '&ClientID=' . $clientId . '&action=get_list_settings">';
                    $html .= Util::htmlDecodeEncode($list->Name);
                    $html .= '</option>';

                }

                $html .= '</select>';

                $requestResults->selected_list = $isSelected;
                $requestResults->selected_list_id = $selectedList;
                $requestResults->content = $html;

            } else {

                $html = '<select id="lists" class="ajax-call list client-list dropdown-select">';
                $html .= '<option selected="selected" class="ajax-call" data-url="' . self::$actionUrl . '&ClientID=' . $clientId . '&action=create_list">';
                $html .= 'Please create a list to get started';
                $html .= '</option>';
                $html .= '</select>';

                $requestResults->show = '.new-list-creation';
                $requestResults->content = $html;
            }
        }

        wp_send_json($requestResults);
    }

    public static function get_custom_fields($listId = '')
    {
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $params = $_POST;
        $requestResults = new \stdClass();

        if (array_key_exists('ListID', $params)) {
            $listId = $params['ListID'];
            $customFields = App::$CampaignMonitor->get_custom_fields($listId);
            $list = App::$CampaignMonitor->get_list_details($listId);
            $fieldNumber = 1;

            $html = '';
            $html .= '<div class="modal">';
            $html .= '<div class="content">';
            $html .= '<span class="btn-close dashicons dashicons-no"></span>';
            $html .= '<h2>';
            $html .= $list->Title;
            $html .= '</h2>';
            $html .= '<h4>';
            $html .= count($customFields) . ' of '. Helper::getMaximumFieldsCount() .' custom fields used';
            $html .= '</h4>';
            if ($customFields) {
                $html .= '<ul class="list custom-fields">';
                $html .= '<li><strong>#	&nbsp;&nbsp;Campaign Monitor Custom Field</strong></li>';
                foreach ($customFields as $field) {
                    $html .= '<li>';
                    $html .= '<p  href="" data-visibleinpreferencecenter="' . $field->VisibleInPreferenceCenter . '" data-fieldoptions="' . $field->FieldOptions . '" data-key="' . $field->Key . '" data-datatype="' . $field->DataType . '" >';
                    $html .= $fieldNumber++ . ' &nbsp;&nbsp;' . $field->FieldName;
                    $html .= '</p>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</div>';

                $requestResults->modal = $html;

            } else {
                $html .= '<ul class="list custom-fields">';
                $html .= '<li>';
                $html .= '<p>This list has no custom fields yet!</p>';
                $html .= '</li>';
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</div>';

                $requestResults->modal = $html;
            }
        }

        wp_send_json($requestResults);
    }

    // non authenticated users
    public static function ajax_handler_nopriv()
    {

    }



}