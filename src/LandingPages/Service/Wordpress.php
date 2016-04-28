<?php
namespace LandingPages\Service;

class Wordpress
{
    protected $_url;
    protected $_user;
    protected $_pass;

    public function __construct( $url, $user, $pass )
    {
        $this->_url = rtrim($url, '/');
        $this->_user = $user;
        $this->_pass = $pass;
    }

    public function getPosts( $post_type = null, $post_status = null, $limit = null, $offset = null, $orderby = null, $order = null )
    {
        $filters = array();
        if ( ! is_null($post_type) ) $filters['post_type'] = "{$post_type}";
        if ( ! is_null($post_status) ) $filters['post_status'] = "{$post_status}";
        if ( ! is_null($limit) ) $filters['number'] = intval($limit);
        if ( ! is_null($offset) ) $filters['offset'] = intval($offset);
        if ( ! is_null($orderby) ) $filters['orderby'] = "{$orderby}";
        if ( ! is_null($order) ) $filters['order'] = "{$order}";

        return $this->_call('wp.getPosts', array(
            'blog_id'   => 0,
            'username'  => $this->_user,
            'password'  => $this->_pass,
            'filter'    => $filters,
        ));

    }

    public function getAuthors()
    {
        return $this->_call('wp.getAuthors', array(
            'blog_id'   => 0,
            'username'  => $this->_user,
            'password'  => $this->_pass,
        ));
    }

    public function getPostExcerpt($post, $limit = 40)
    {
        if ($post['excerpt']) return $post['excerpt'];

        return substr(strip_tags($post['content']), 0, $limit);
    }

    // PROTECTED ///////////////////////////////////////////////////////////////////////////////////////////////////////

    protected function _xmlType($value)
    {
        if ( is_string($value) ) {
            return "<string>{$value}</string>";
        } else if ( is_int($value) ) {
            return "<int>{$value}</int>";
        } else if ( is_double($value) ) {
            return "<double>{$value}</double>";
        } else if ( is_array($value) && count($value) > 0 ) {
            $r = "<struct>\n";
            foreach ( $value as $n => $v ) {
                $r .= "\t<member>\n";
                $r .= "\t\t<name>{$n}</name>\n";
                $r .= "\t\t<value>{$this->_xmlType($v)}</value>\n";
                $r .= "\t</member>\n";
            }
            return "{$r}\n</struct>\n";
        }

        return "<string><![[{$value}]]></string>";
    }

    protected function _call($method, $params = array())
    {
        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_URL             => "{$this->_url}/xmlrpc.php",
            CURLOPT_USERAGENT       => 'Andelux Landing Pages',
            CURLOPT_HTTPHEADER      => array(
                'Content-Type: text/xml',
            ),

            CURLOPT_FOLLOWLOCATION  => true,
            CURLINFO_HEADER_OUT     => true,
            CURLOPT_RETURNTRANSFER  => true,

            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_TIMEOUT         => 10,
            CURLOPT_ENCODING        => '',

            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $this->_generateXml($method, $params),

        ));

        $content = curl_exec($c);
        $headers = curl_getinfo($c);

        if ( $headers['http_code'] == 200 ) {
            $xml = simplexml_load_string($content);
            return $this->_formatValue($xml->params->param->value);
        }

        return null;
    }

    protected function _generateXml( $method, $params )
    {
        // Generate XML
        $data = <<<DATA
<?xml version="1.0"?>
<methodCall>
    <methodName>{$method}</methodName>
    <params>
DATA;
        foreach ( $params as $name => $value ) {
            $data .= "<param>{$this->_xmlType($value)}</param>";
        }
        $data .= <<<DATA
    </params>
</methodCall>
DATA;

        return $data;
    }

    protected function _formatValue( $value )
    {
        if ( is_null($value) ) return null;

        $value = (array) $value;

        $value_type = array_shift(array_keys($value));
        $value = array_shift(array_values($value));
        switch ( $value_type ) {
            case 'string': $value = (string) $value; break;
            case 'dateTime.iso8601': $value = strtotime($value); break;
            case 'int': $value = intval($value); break;
            case 'boolean': $value = ($value ? true : false); break;
            case 'array':
                $vs = array();
                foreach ( $value->data->value as $item ) {
                    foreach ( $item->struct->member as $member ) {
                        $v["{$member->name}"] = $this->_formatValue((array)$member->value);
                    }
                    $vs[] = $v;
                }
                $value = $vs;
                break;
            case 'struct':
                $v = array();
                foreach ( $value->member as $member ) {
                    $v["{$member->name}"] = $this->_formatValue((array)$member->value);
                }
                $value = $v;
                break;

            case 'data':
            case 'value':
                $value = $this->_formatValue((array)$value);
                break;

            default:
                $noop = null;
        }

        return $value;
    }

}
