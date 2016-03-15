<?php
namespace LandingPages\Hook;

use LandingPages\Hook;

class Mailchimp extends Backend
{
    protected $_endpoint;

    /**
     * @todo apply rules about lead is already member, lead was unsubscribed before, etc.
     *
     * @throws \Exception
     */
    public function exec()
    {
        list($null, $dc) = explode('-', $this->getConfig('mailchimp_api_key'), 2);
        $this->_endpoint = "https://{$dc}.api.mailchimp.com/3.0";

        $mailchimp_list = $this->getConfig('list_id');

        $mailchimp_data = array();
        foreach ( $this->getConfig('map') as $mailchimp_field => $post_field ) {
            $mailchimp_data[$mailchimp_field] = $this->getData($post_field);
        }

        $this->addMember($mailchimp_list, $mailchimp_data['email'], 'subscribed', $mailchimp_data);
    }

    /**
     * Get all the account lists
     *
     * @todo trigger the error response
     *
     * @return mixed
     */
    public function getLists()
    {
        try {
            $response = $this->_command('lists', 'get');
            return $response['lists'];
        } catch (Exception $e) {
            // TODO: ERROR!!!

        }
    }

    /**
     * Add a lead to the list
     *
     * Use status:
     *  - subscribed: to add an address right away
     *  - pending: to send a confirmation email
     *  - unsubscribed/cleaned: to archive unused addresses
     *
     * @param $list
     * @param $email
     * @param $status
     * @param array $merge_fields
     */
    public function addMember($list, $email, $status, $merge_fields = array())
    {
        $statuses = array('subscribed','unsubscribed','cleaned','pending');
        if ( ! in_array($status, $statuses) ) {
            // TODO: ERROR!!!
        }

        try {
            $response = $this->_command('lists/'.$list.'/members', 'post', array(
                'email_address'     => $email,
                'status'            => $status,
                'language'          => LP_LANGUAGE,
                //'location'          => '', // TODO: get location by GeoIP
                'merge_fields'      => $merge_fields,
            ));

            $status = $response['status'];
        } catch ( Exception $e ) {
            // TODO: ERROR!!!
        }
    }

    /**
     * @param $list
     * @param $email
     * @param $status
     */
    public function updateMember($list, $email, $status)
    {
        $statuses = array('subscribed','unsubscribed','cleaned','pending');
        if ( ! in_array($status, $statuses) ) {
            // TODO: ERROR!!!
        }

        try {
            $response = $this->_command('lists/'.$list.'/members/'.md5($email), 'patch', array(
                'status'            => $status,
            ));

            $status = $response['status'];
        } catch ( Exception $e ) {
            // TODO: ERROR!!!
        }
    }

    /**
     * Check if an email is already subscribed
     *
     * @param $email
     * @param $list
     * @return bool
     */
    public function isMember($email, $list)
    {
        try {
            $response = $this->_command('lists/'.$list.'/members/'.md5($email), 'get');
            // TODO: get the right response code
            return $response['__HEADERS']['response_code'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param string $command
     * @param string $method
     * @param array $data
     *
     * @return null
     *
     * @throws Exception
     */
    protected function _command($command, $method, $data = null)
    {
        $url = $this->_endpoint . '/' . ltrim($command, '/');

        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Andelux Landing Pages',
            CURLOPT_USERPWD => 'landingpages:' . MAILCHIMP_API_KEY,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                /* 'Authorization: apikey '.MAILCHIMP_API_KEY, */
            ),

            CURLOPT_FOLLOWLOCATION => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_ENCODING => '',
        ));

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($c, CURLOPT_POST, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($c, CURLOPT_PUT, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'GET':
                curl_setopt($c, CURLOPT_URL, $url . '?' . http_build_query($data));
                break;
            case 'PATCH':
                curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                throw new Exception('Method not allowed! ' . $method);
                return null;
        }

        $content = curl_exec($c);
        $headers = curl_getinfo($c);

        $object = json_decode($content, true);
        $object['__HEADERS'] = $headers;

        return $object;
    }


}