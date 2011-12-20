<?php
/***********************************************************************************
 * PHP Class for Elasticsearch

 * Date: 18th December 2011

 * Authors: Niranjan Uma Shankar, Prasanna Venkatesan 

 * Thanks to Alf Eaton (http://hublog.hubmed.org) for the first version

 * For any queries, mail us at niranjan.u@computer.org or prasanna@computer.org

 * Please use as you wish at your own risk

************************************************************************************/

class Elasticsearch
{
        function __construct($server = 'http://localhost:9200')
        {
                $this->server = $server;
        }

        //This function returns the elasticsearch results
        function call($path, $http = array())
        {
                $content = @file_get_contents($this->server . '/' . $this->index . '/' . $path, NULL, stream_context_create(array('http' => $http)));
                if ($content === FALSE) return array(null, 0);
                else return array(json_decode($content), 1);
        }

        //curl -X PUT http://localhost:9200/{INDEX}/
        // This function is to create an index
        function create()
        {
                $this->call(NULL, array('method' => 'PUT'));
        }

        //curl -X GET http://localhost:9200/{INDEX}/_status
        function status()
        {
                return $this->call('_status');
        }

        //curl -X GET http://localhost:9200/{INDEX}/{TYPE}/_count -d {matchAll:{}}
        function count()
        {
                return $this->call($this->type . '/_count', array('method' => 'GET', 'content' => '{ matchAll:{} }'));
        }

        //curl -X PUT http://localhost:9200/{INDEX}/{TYPE}/_mapping -d ...
        function map($data)
        {
                return $this->call($this->type . '/_mapping', array('method' => 'PUT', 'content' => $data));
        }

        //curl -X PUT http://localhost:9200/{INDEX}/{TYPE}/{ID} -d ...
        function add($id, $data)
        {
                return $this->call($this->type . '/' . $id, array('method' => 'PUT', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $data));
        }

        //curl -X DELETE http://localhost:9200/{INDEX}/
        //Delete an indexed item by ID
        function delete($id)
        {
                return $this->call($this->type . '/' . $id, array('method' => 'DELETE'));
        }

        //curl -X GET http://localhost:9200/{INDEX}/{TYPE}/_search?q= ...
        function query($q)
        {
                return $this->call($this->type . '/_search?' . http_build_query(array('q' => $q)));
        }

        function query_wresultSize($query, $size=999)
        {
                return $this->call($this->type . '/_search?' . http_build_query(array('q' => $q, 'size' => $size)));
        }

        function query_all($query)
        {
                return $this->call('_search?' . http_build_query(array('q' => $q)));
        }

        function query_all_wresultSize($query, $size=999)
        {
                return $this->call('_search?' . http_build_query(array('q' => $q, 'size' => $size)));     
        }

        function query_highlight($q)
        {
                return $this->call($this->type . '/_search?' . http_build_query(array('q' => $q)), array('header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => '{"highlight":{"fields":{"field_1":{"pre_tags" : ["<b style=\"background-color:#C8C8C8\">"], "post_tags" : ["</b>"]}, "field_2":{"pre_tags" : ["<b style=\"background-color:#C8C8C8\">"], "post_tags" : ["</b>"]}}}}'));
        }
}
?>